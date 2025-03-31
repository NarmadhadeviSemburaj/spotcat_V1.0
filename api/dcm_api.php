<?php
header("Content-Type: application/json");
include_once __DIR__ . '/../db_config.php';
include_once __DIR__ . '/log_api.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get the request method
$method = $_SERVER['REQUEST_METHOD'];

// Get client IP and user agent for logging
$ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;

// Response helper function
function sendResponse($status, $message, $data = null, $httpCode = 200) {
    http_response_code($httpCode);
    echo json_encode([
        'status' => $status,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

switch ($method) {
    case 'POST':
        createDCM($conn, $ip_address, $user_agent);
        break;
    case 'GET':
        fetchDCMs($conn, $ip_address, $user_agent);
        break;
    case 'PUT':
        updateDCM($conn, $ip_address, $user_agent);
        break;
    case 'DELETE':
        deleteDCM($conn, $ip_address, $user_agent);
        break;
    default:
        sendResponse('error', 'Invalid request method', null, 405);
        logUserAction(null, 'system', 'error', 'Invalid request method', $_SERVER['REQUEST_URI'], $method, null, 'error', null, $ip_address, $user_agent);
        break;
}

function createDCM($conn, $ip_address, $user_agent) {
    $data = json_decode(file_get_contents("php://input"), true);
    
    // Validate required fields
    if (empty($data['dcm_name']) || empty($data['zone_id'])) {
        sendResponse('error', 'DCM name and Zone ID are required', null, 400);
    }

    // Prepare statement with parameter binding
    $stmt = $conn->prepare("INSERT INTO dcm (dcm_name, dcm_location, dcm_pincode, zone_id) VALUES (?, ?, ?, ?)");
    if (!$stmt) {
        sendResponse('error', 'Database preparation failed: ' . $conn->error, null, 500);
    }

    $dcm_name = $data['dcm_name'];
    $dcm_location = $data['dcm_location'] ?? null;
    $dcm_pincode = $data['dcm_pincode'] ?? null;
    $zone_id = $data['zone_id'];

    $stmt->bind_param("ssss", $dcm_name, $dcm_location, $dcm_pincode, $zone_id);

    if ($stmt->execute()) {
        // Get the actual generated dcm_id (works with both auto-increment and triggers)
        $result = $conn->query("SELECT dcm_id FROM dcm WHERE zone_id = '" . 
                             $conn->real_escape_string($zone_id) . 
                             "' ORDER BY dcm_id DESC LIMIT 1");
        $row = $result->fetch_assoc();
        
        $response = [
            'status' => 'success',
            'message' => 'DCM created successfully',
            'data' => ['dcm_id' => $row['dcm_id']]
        ];
        
        sendResponse('success', 'DCM created successfully', ['dcm_id' => $row['dcm_id']], 201);
        logUserAction(null, 'system', 'create', 'DCM created', $_SERVER['REQUEST_URI'], 'POST', $data, 'success', $response, $ip_address, $user_agent);
    } else {
        sendResponse('error', 'Failed to create DCM: ' . $stmt->error, null, 500);
        logUserAction(null, 'system', 'error', 'DCM creation failed', $_SERVER['REQUEST_URI'], 'POST', $data, 'error', null, $ip_address, $user_agent);
    }
    $stmt->close();
}

function fetchDCMs($conn, $ip_address, $user_agent) {
    // Check if requesting single DCM
    if (isset($_GET['dcm_id'])) {
        $dcm_id = $conn->real_escape_string($_GET['dcm_id']);
        $stmt = $conn->prepare("SELECT * FROM dcm WHERE dcm_id = ?");
        $stmt->bind_param("s", $dcm_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            sendResponse('success', 'DCM fetched successfully', $result->fetch_assoc());
        } else {
            sendResponse('error', 'DCM not found', null, 404);
        }
        $stmt->close();
        return;
    }

    // Get all DCMs
    $sql = "SELECT dcm_id, dcm_name, dcm_location, dcm_pincode, zone_id FROM dcm";
    $result = $conn->query($sql);
    
    if (!$result) {
        sendResponse('error', 'Database error: ' . $conn->error, null, 500);
    }

    $dcms = [];
    while ($row = $result->fetch_assoc()) {
        $dcms[] = $row;
    }

    sendResponse('success', 'DCMs fetched successfully', $dcms);
    logUserAction(null, 'system', 'read', 'DCMs fetched', $_SERVER['REQUEST_URI'], 'GET', null, 'success', ['count' => count($dcms)], $ip_address, $user_agent);
}

function updateDCM($conn, $ip_address, $user_agent) {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (empty($data['dcm_id'])) {
        sendResponse('error', 'DCM ID is required', null, 400);
    }

    $dcm_id = $data['dcm_id'];
    $updates = [];
    $params = [];
    $types = "";

    // Build dynamic update query
    if (isset($data['dcm_name'])) {
        $updates[] = "dcm_name = ?";
        $params[] = $data['dcm_name'];
        $types .= "s";
    }
    if (isset($data['dcm_location'])) {
        $updates[] = "dcm_location = ?";
        $params[] = $data['dcm_location'];
        $types .= "s";
    }
    if (isset($data['dcm_pincode'])) {
        $updates[] = "dcm_pincode = ?";
        $params[] = $data['dcm_pincode'];
        $types .= "s";
    }
    if (isset($data['zone_id'])) {
        $updates[] = "zone_id = ?";
        $params[] = $data['zone_id'];
        $types .= "s";
    }

    if (empty($updates)) {
        sendResponse('error', 'No fields to update', null, 400);
    }

    // Add dcm_id to params for WHERE clause
    $params[] = $dcm_id;
    $types .= "s";

    $sql = "UPDATE dcm SET " . implode(", ", $updates) . " WHERE dcm_id = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        sendResponse('error', 'Database preparation failed: ' . $conn->error, null, 500);
    }

    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            sendResponse('success', 'DCM updated successfully', ['dcm_id' => $dcm_id]);
        } else {
            sendResponse('error', 'No changes made or DCM not found', null, 404);
        }
    } else {
        sendResponse('error', 'Failed to update DCM: ' . $stmt->error, null, 500);
    }
    $stmt->close();
}

function deleteDCM($conn, $ip_address, $user_agent) {
    $data = json_decode(file_get_contents("php://input"), true);

    if (empty($data['dcm_id'])) {
        sendResponse('error', 'DCM ID is required', null, 400);
    }

    $dcm_id = $data['dcm_id'];

    // First check if DCM exists
    $check_stmt = $conn->prepare("SELECT dcm_id FROM dcm WHERE dcm_id = ?");
    $check_stmt->bind_param("s", $dcm_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        sendResponse('error', 'DCM not found', null, 404);
    }

    // Delete the DCM
    $delete_stmt = $conn->prepare("DELETE FROM dcm WHERE dcm_id = ?");
    $delete_stmt->bind_param("s", $dcm_id);

    if ($delete_stmt->execute()) {
        sendResponse('success', 'DCM deleted successfully', ['dcm_id' => $dcm_id]);
    } else {
        sendResponse('error', 'Failed to delete DCM: ' . $delete_stmt->error, null, 500);
    }
    
    $check_stmt->close();
    $delete_stmt->close();
}

$conn->close();
?>