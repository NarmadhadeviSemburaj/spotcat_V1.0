<?php
header("Content-Type: application/json");
include_once __DIR__ . '/../db_config.php';
include_once __DIR__ . '/log_api.php';

// Request handling
$method = $_SERVER['REQUEST_METHOD'];
$ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;

switch ($method) {
    case 'POST': createDcmStaffing($conn, $ip_address, $user_agent); break;
    case 'GET': 
        isset($_GET['staffing_id']) ? getDcmStaffing($conn, $ip_address, $user_agent) : 
        listDcmStaffings($conn, $ip_address, $user_agent);
        break;
    case 'PUT': updateDcmStaffing($conn, $ip_address, $user_agent); break;
    case 'DELETE': deleteDcmStaffing($conn, $ip_address, $user_agent); break;
    default:
        sendResponse('error', 'Invalid request method', null, 405);
        logAction('Invalid method', 'error', $ip_address, $user_agent);
        break;
}

// Helper functions
function sendResponse($status, $message, $data, $httpCode = 200) {
    http_response_code($httpCode);
    echo json_encode(['status' => $status, 'message' => $message, 'data' => $data]);
}

function logAction($action, $type, $ip, $agent, $details = []) {
    logUserAction(null, 'system', $type, $action, 
                $_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD'], 
                $details, $type, $details, $ip, $agent);
}

// --- CRUD Operations ---

// 1. Create DCM Staffing
function createDcmStaffing($conn, $ip_address, $user_agent) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    if (empty($input['dcm_name'])) {
        sendResponse('error', 'dcm_name is required', null, 400);
        return;
    }
    if (empty($input['zone_name'])) {
        sendResponse('error', 'zone_name is required', null, 400);
        return;
    }

    // Set defaults
    $regular = $input['regular_staff_count'] ?? 4;
    $backup = $input['backup_count'] ?? 1;
    $incharge = $input['incharge_count'] ?? 1;

    try {
        // Verify the DCM exists
        $dcmStmt = $conn->prepare("SELECT dcm_id FROM dcm WHERE dcm_name = ?");
        $dcmStmt->bind_param("s", $input['dcm_name']);
        $dcmStmt->execute();
        $dcmResult = $dcmStmt->get_result();
        
        if ($dcmResult->num_rows === 0) {
            sendResponse('error', 'DCM name not found', null, 404);
            return;
        }
        $dcm = $dcmResult->fetch_assoc();
        
        // Verify the Zone exists
        $zoneStmt = $conn->prepare("SELECT zone_id FROM zone WHERE zone_name = ?");
        $zoneStmt->bind_param("s", $input['zone_name']);
        $zoneStmt->execute();
        $zoneResult = $zoneStmt->get_result();
        
        if ($zoneResult->num_rows === 0) {
            sendResponse('error', 'Zone name not found', null, 404);
            return;
        }
        $zone = $zoneResult->fetch_assoc();

        // Insert into dcm_staffing (trigger will handle staffing_id)
        $insertStmt = $conn->prepare("
            INSERT INTO dcm_staffing 
            (dcm_id, dcm_name, zone_id, zone_name, regular_staff_count, backup_count, incharge_count) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $insertStmt->bind_param(
            "ssssiii", 
            $dcm['dcm_id'],
            $input['dcm_name'],
            $zone['zone_id'],
            $input['zone_name'],
            $regular,
            $backup,
            $incharge
        );
        
        if ($insertStmt->execute()) {
            // Get the newly created record
            $result = $conn->query("
                SELECT * FROM dcm_staffing 
                ORDER BY created_at DESC LIMIT 1
            ");
            $record = $result->fetch_assoc();
            
            sendResponse('success', 'DCM staffing created', $record, 201);
            logAction('DCM staffing created', 'create', $ip_address, $user_agent, $record);
        } else {
            sendResponse('error', 'Failed to create staffing', null, 500);
        }
    } catch (mysqli_sql_exception $e) {
        sendResponse('error', 'Database error: ' . $e->getMessage(), null, 500);
    }
}

// 2. List All DCM Staffings
function listDcmStaffings($conn, $ip_address, $user_agent) {
    $sql = "SELECT * FROM dcm_staffing";
    $result = $conn->query($sql);
    
    $staffings = [];
    while ($row = $result->fetch_assoc()) {
        $staffings[] = $row;
    }
    
    sendResponse('success', 'DCM staffings retrieved', $staffings);
    logAction('Listed DCM staffings', 'read', $ip_address, $user_agent, ['count' => count($staffings)]);
}

// 3. Get Single DCM Staffing
function getDcmStaffing($conn, $ip_address, $user_agent) {
    $staffing_id = $conn->real_escape_string($_GET['staffing_id']);
    $sql = "SELECT * FROM dcm_staffing WHERE staffing_id = '$staffing_id'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $staffing = $result->fetch_assoc();
        
        sendResponse('success', 'DCM staffing retrieved', $staffing);
        logAction('Viewed DCM staffing', 'read', $ip_address, $user_agent, ['staffing_id' => $staffing_id]);
    } else {
        sendResponse('error', 'DCM staffing not found', null, 404);
        logAction('DCM staffing not found', 'error', $ip_address, $user_agent, ['staffing_id' => $staffing_id]);
    }
}

// 4. Update DCM Staffing
function updateDcmStaffing($conn, $ip_address, $user_agent) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (empty($input['staffing_id'])) {
        sendResponse('error', 'staffing_id is required', null, 400);
        logAction('Update failed: Missing ID', 'error', $ip_address, $user_agent, $input);
        return;
    }
    
    $staffing_id = $conn->real_escape_string($input['staffing_id']);
    $updates = [];
    
    // Build update fields
    if (isset($input['dcm_name'])) {
        // Verify new DCM exists
        $dcmStmt = $conn->prepare("SELECT dcm_id FROM dcm WHERE dcm_name = ?");
        $dcmStmt->bind_param("s", $input['dcm_name']);
        $dcmStmt->execute();
        $dcmResult = $dcmStmt->get_result();
        
        if ($dcmResult->num_rows === 0) {
            sendResponse('error', 'New DCM name not found', null, 404);
            return;
        }
        $dcm = $dcmResult->fetch_assoc();
        
        $updates[] = "dcm_id = '" . $conn->real_escape_string($dcm['dcm_id']) . "'";
        $updates[] = "dcm_name = '" . $conn->real_escape_string($input['dcm_name']) . "'";
    }
    
    if (isset($input['zone_name'])) {
        // Verify new Zone exists
        $zoneStmt = $conn->prepare("SELECT zone_id FROM zone WHERE zone_name = ?");
        $zoneStmt->bind_param("s", $input['zone_name']);
        $zoneStmt->execute();
        $zoneResult = $zoneStmt->get_result();
        
        if ($zoneResult->num_rows === 0) {
            sendResponse('error', 'New Zone name not found', null, 404);
            return;
        }
        $zone = $zoneResult->fetch_assoc();
        
        $updates[] = "zone_id = '" . $conn->real_escape_string($zone['zone_id']) . "'";
        $updates[] = "zone_name = '" . $conn->real_escape_string($input['zone_name']) . "'";
    }
    
    if (isset($input['regular_staff_count'])) {
        $updates[] = "regular_staff_count = " . intval($input['regular_staff_count']);
    }
    if (isset($input['backup_count'])) {
        $updates[] = "backup_count = " . intval($input['backup_count']);
    }
    if (isset($input['incharge_count'])) {
        $updates[] = "incharge_count = " . intval($input['incharge_count']);
    }
    
    if (empty($updates)) {
        sendResponse('error', 'No fields to update', null, 400);
        logAction('Update failed: No fields', 'error', $ip_address, $user_agent, $input);
        return;
    }
    
    $sql = "UPDATE dcm_staffing SET " . implode(", ", $updates) . " WHERE staffing_id = '$staffing_id'";
    
    if ($conn->query($sql)) {
        // Fetch updated record
        $result = $conn->query("SELECT * FROM dcm_staffing WHERE staffing_id = '$staffing_id'");
        $staffing = $result->fetch_assoc();
        
        sendResponse('success', 'DCM staffing updated', $staffing);
        logAction('DCM staffing updated', 'update', $ip_address, $user_agent, $input);
    } else {
        sendResponse('error', 'Update failed: ' . $conn->error, null, 500);
        logAction('Update failed', 'error', $ip_address, $user_agent, ['error' => $conn->error]);
    }
}

// 5. Delete DCM Staffing
function deleteDcmStaffing($conn, $ip_address, $user_agent) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (empty($input['staffing_id'])) {
        sendResponse('error', 'staffing_id is required', null, 400);
        logAction('Delete failed: Missing ID', 'error', $ip_address, $user_agent, $input);
        return;
    }
    
    $staffing_id = $conn->real_escape_string($input['staffing_id']);
    $sql = "DELETE FROM dcm_staffing WHERE staffing_id = '$staffing_id'";
    
    if ($conn->query($sql)) {
        sendResponse('success', 'DCM staffing deleted', ['staffing_id' => $staffing_id]);
        logAction('DCM staffing deleted', 'delete', $ip_address, $user_agent, ['staffing_id' => $staffing_id]);
    } else {
        sendResponse('error', 'Deletion failed: ' . $conn->error, null, 500);
        logAction('Delete failed', 'error', $ip_address, $user_agent, ['error' => $conn->error]);
    }
}
?>