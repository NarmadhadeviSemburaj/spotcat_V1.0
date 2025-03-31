<?php
header("Content-Type: application/json");
include_once __DIR__ . '/../db_config.php';
include_once __DIR__ . '/log_api.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get the request method
$method = $_SERVER['REQUEST_METHOD'];

// Response helper function with integrated logging
function sendResponse($status, $message, $data = null, $httpCode = 200) {
    global $conn;
    
    $response = [
        'status' => $status,
        'message' => $message,
        'data' => $data
    ];
    
    http_response_code($httpCode);
    
    // Log the API request
    logUserAction(
        isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null,
        isset($_SESSION['username']) ? $_SESSION['username'] : 'API_USER',
        "DCM_API_" . strtoupper($_SERVER['REQUEST_METHOD']),
        $message,
        $_SERVER['REQUEST_URI'],
        $_SERVER['REQUEST_METHOD'],
        json_decode(file_get_contents("php://input"), true),
        $status,
        $response,
        $_SERVER['REMOTE_ADDR'],
        $_SERVER['HTTP_USER_AGENT']
    );
    
    echo json_encode($response);
    exit;
}

try {
    switch ($method) {
        case 'POST':
            createDCM($conn);
            break;
        case 'GET':
            fetchDCMs($conn);
            break;
        case 'PUT':
            updateDCM($conn);
            break;
        case 'DELETE':
            deleteDCM($conn);
            break;
        default:
            sendResponse('error', 'Invalid request method', null, 405);
            break;
    }
} catch (Exception $e) {
    sendResponse('error', 'An unexpected error occurred: ' . $e->getMessage(), null, 500);
} finally {
    if (isset($conn) && $conn) {
        $conn->close();
    }
}

function createDCM($conn) {
    $data = json_decode(file_get_contents("php://input"), true);
    
    // Validate required fields
    if (empty($data['dcm_name']) || empty($data['zone_id'])) {
        sendResponse('error', 'DCM name and Zone ID are required', null, 400);
    }

    // Check if DCM already exists
    $check_sql = "SELECT dcm_id FROM dcm WHERE dcm_name = ? AND zone_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ss", $data['dcm_name'], $data['zone_id']);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        sendResponse('error', 'DCM with this name already exists in the specified zone', null, 409);
    }

    // Prepare statement with parameter binding
    $stmt = $conn->prepare("INSERT INTO dcm (dcm_name, dcm_location, dcm_pincode, zone_id) VALUES (?, ?, ?, ?)");
    if (!$stmt) {
        sendResponse('error', 'Database preparation failed: ' . $conn->error, null, 500);
    }

    $dcm_name = $conn->real_escape_string($data['dcm_name']);
    $dcm_location = isset($data['dcm_location']) ? $conn->real_escape_string($data['dcm_location']) : null;
    $dcm_pincode = isset($data['dcm_pincode']) ? $conn->real_escape_string($data['dcm_pincode']) : null;
    $zone_id = $conn->real_escape_string($data['zone_id']);

    $stmt->bind_param("ssss", $dcm_name, $dcm_location, $dcm_pincode, $zone_id);

    if ($stmt->execute()) {
        $dcm_id = $stmt->insert_id;
        sendResponse('success', 'DCM created successfully', ['dcm_id' => $dcm_id], 201);
    } else {
        sendResponse('error', 'Failed to create DCM: ' . $stmt->error, null, 500);
    }
    
    $check_stmt->close();
    $stmt->close();
}

function fetchDCMs($conn) {
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

    // Handle pagination
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = isset($_GET['limit']) ? max(1, intval($_GET['limit'])) : 100;
    $offset = ($page - 1) * $limit;

    // Get all DCMs with pagination
    $sql = "SELECT SQL_CALC_FOUND_ROWS dcm_id, dcm_name, dcm_location, dcm_pincode, zone_id FROM dcm LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if (!$result) {
        sendResponse('error', 'Database error: ' . $conn->error, null, 500);
    }

    $dcms = [];
    while ($row = $result->fetch_assoc()) {
        $dcms[] = $row;
    }

    // Get total count
    $total_result = $conn->query("SELECT FOUND_ROWS() AS total");
    $total = $total_result->fetch_assoc()['total'];
    $total_pages = ceil($total / $limit);

    $meta = [
        'page' => $page,
        'limit' => $limit,
        'total_items' => $total,
        'total_pages' => $total_pages
    ];

    sendResponse('success', 'DCMs fetched successfully', ['dcms' => $dcms, 'meta' => $meta]);
    $stmt->close();
}

function updateDCM($conn) {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (empty($data['dcm_id'])) {
        sendResponse('error', 'DCM ID is required', null, 400);
    }

    $dcm_id = $conn->real_escape_string($data['dcm_id']);
    $updates = [];
    $params = [];
    $types = "";

    // Build dynamic update query
    if (isset($data['dcm_name'])) {
        $updates[] = "dcm_name = ?";
        $params[] = $conn->real_escape_string($data['dcm_name']);
        $types .= "s";
    }
    if (isset($data['dcm_location'])) {
        $updates[] = "dcm_location = ?";
        $params[] = $conn->real_escape_string($data['dcm_location']);
        $types .= "s";
    }
    if (isset($data['dcm_pincode'])) {
        $updates[] = "dcm_pincode = ?";
        $params[] = $conn->real_escape_string($data['dcm_pincode']);
        $types .= "s";
    }
    if (isset($data['zone_id'])) {
        $updates[] = "zone_id = ?";
        $params[] = $conn->real_escape_string($data['zone_id']);
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

function deleteDCM($conn) {
    $data = json_decode(file_get_contents("php://input"), true);

    if (empty($data['dcm_id'])) {
        sendResponse('error', 'DCM ID is required', null, 400);
    }

    $dcm_id = $conn->real_escape_string($data['dcm_id']);

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
        if ($delete_stmt->affected_rows > 0) {
            sendResponse('success', 'DCM deleted successfully', ['dcm_id' => $dcm_id]);
        } else {
            sendResponse('error', 'No DCM was deleted', null, 404);
        }
    } else {
        sendResponse('error', 'Failed to delete DCM: ' . $delete_stmt->error, null, 500);
    }
    
    $check_stmt->close();
    $delete_stmt->close();
}
?>