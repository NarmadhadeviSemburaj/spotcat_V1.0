<?php
header("Content-Type: application/json");
include_once __DIR__ . '/../db_config.php';
include_once __DIR__ . '/log_api.php';

// Get the request method
$method = $_SERVER['REQUEST_METHOD'];

// Get client IP and user agent for logging
$ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;

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
        $response = [
            'status' => 'error',
            'message' => 'Invalid request method',
            'data' => null
        ];
        echo json_encode($response);
        logUserAction(null, 'system', 'error', 'Invalid request method', $_SERVER['REQUEST_URI'], $method, null, 'error', $response, $ip_address, $user_agent);
        break;
}

function createDCM($conn, $ip_address, $user_agent) {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!isset($data['dcm_name']) || empty($data['dcm_name']) || !isset($data['zone_id']) || empty($data['zone_id'])) {
        $response = [
            'status' => 'error',
            'message' => 'DCM name and Zone ID are required',
            'data' => null
        ];
        echo json_encode($response);
        logUserAction(null, 'system', 'validation_error', 'Missing required fields', $_SERVER['REQUEST_URI'], 'POST', $data, 'error', $response, $ip_address, $user_agent);
        return;
    }

    $dcm_name = $conn->real_escape_string($data['dcm_name']);
    $dcm_location = isset($data['dcm_location']) ? $conn->real_escape_string($data['dcm_location']) : null;
    $dcm_pincode = isset($data['dcm_pincode']) ? $conn->real_escape_string($data['dcm_pincode']) : null;
    $zone_id = $conn->real_escape_string($data['zone_id']);

    $sql = "INSERT INTO dcm (dcm_name, dcm_location, dcm_pincode, zone_id) VALUES ('$dcm_name', '$dcm_location', '$dcm_pincode', '$zone_id')";

    if ($conn->query($sql)) {
        $dcm_id = $conn->insert_id;
        $response = [
            'status' => 'success',
            'message' => 'DCM created successfully',
            'data' => ['dcm_id' => $dcm_id]
        ];
        echo json_encode($response);
        logUserAction(null, 'system', 'create', 'DCM created', $_SERVER['REQUEST_URI'], 'POST', $data, 'success', $response, $ip_address, $user_agent);
    } else {
        $response = [
            'status' => 'error',
            'message' => 'Failed to create DCM: ' . $conn->error,
            'data' => null
        ];
        echo json_encode($response);
        logUserAction(null, 'system', 'error', 'DCM creation failed', $_SERVER['REQUEST_URI'], 'POST', $data, 'error', $response, $ip_address, $user_agent);
    }
}

function fetchDCMs($conn, $ip_address, $user_agent) {
    $sql = "SELECT dcm_id, dcm_name, dcm_location, dcm_pincode, zone_id FROM dcm";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $dcms = [];
        while ($row = $result->fetch_assoc()) {
            $dcms[] = $row;
        }
        $response = [
            'status' => 'success',
            'message' => 'DCMs fetched successfully',
            'data' => $dcms
        ];
        echo json_encode($response);
        logUserAction(null, 'system', 'read', 'DCMs fetched', $_SERVER['REQUEST_URI'], 'GET', null, 'success', ['count' => count($dcms)], $ip_address, $user_agent);
    } else {
        $response = [
            'status' => 'error',
            'message' => 'No DCMs found',
            'data' => []
        ];
        echo json_encode($response);
        logUserAction(null, 'system', 'read', 'No DCMs found', $_SERVER['REQUEST_URI'], 'GET', null, 'error', $response, $ip_address, $user_agent);
    }
}

function updateDCM($conn, $ip_address, $user_agent) {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!isset($data['dcm_id']) || empty($data['dcm_id'])) {
        $response = [
            'status' => 'error',
            'message' => 'DCM ID is required',
            'data' => null
        ];
        echo json_encode($response);
        logUserAction(null, 'system', 'validation_error', 'Missing DCM ID', $_SERVER['REQUEST_URI'], 'PUT', $data, 'error', $response, $ip_address, $user_agent);
        return;
    }

    $dcm_id = $conn->real_escape_string($data['dcm_id']);
    $updates = [];
    
    if (isset($data['dcm_name'])) {
        $updates[] = "dcm_name = '" . $conn->real_escape_string($data['dcm_name']) . "'";
    }
    if (isset($data['dcm_location'])) {
        $updates[] = "dcm_location = '" . $conn->real_escape_string($data['dcm_location']) . "'";
    }
    if (isset($data['dcm_pincode'])) {
        $updates[] = "dcm_pincode = '" . $conn->real_escape_string($data['dcm_pincode']) . "'";
    }
    if (isset($data['zone_id'])) {
        $updates[] = "zone_id = '" . $conn->real_escape_string($data['zone_id']) . "'";
    }
    
    if (empty($updates)) {
        $response = [
            'status' => 'error',
            'message' => 'No fields to update',
            'data' => null
        ];
        echo json_encode($response);
        logUserAction(null, 'system', 'validation_error', 'No fields to update', $_SERVER['REQUEST_URI'], 'PUT', $data, 'error', $response, $ip_address, $user_agent);
        return;
    }
    
    $sql = "UPDATE dcm SET " . implode(", ", $updates) . " WHERE dcm_id = '$dcm_id'";
    
    if ($conn->query($sql)) {
        $response = [
            'status' => 'success',
            'message' => 'DCM updated successfully',
            'data' => ['dcm_id' => $dcm_id]
        ];
        echo json_encode($response);
        logUserAction(null, 'system', 'update', 'DCM updated', $_SERVER['REQUEST_URI'], 'PUT', $data, 'success', $response, $ip_address, $user_agent);
    } else {
        $response = [
            'status' => 'error',
            'message' => 'Failed to update DCM: ' . $conn->error,
            'data' => null
        ];
        echo json_encode($response);
        logUserAction(null, 'system', 'error', 'DCM update failed', $_SERVER['REQUEST_URI'], 'PUT', $data, 'error', $response, $ip_address, $user_agent);
    }
}

function deleteDCM($conn, $ip_address, $user_agent) {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['dcm_id']) || empty($data['dcm_id'])) {
        $response = [
            'status' => 'error',
            'message' => 'DCM ID is required',
            'data' => null
        ];
        echo json_encode($response);
        logUserAction(null, 'system', 'validation_error', 'Missing DCM ID', $_SERVER['REQUEST_URI'], 'DELETE', $data, 'error', $response, $ip_address, $user_agent);
        return;
    }

    $dcm_id = $conn->real_escape_string($data['dcm_id']);
    $sql = "DELETE FROM dcm WHERE dcm_id = '$dcm_id'";

    if ($conn->query($sql)) {
        $response = [
            'status' => 'success',
            'message' => 'DCM deleted successfully',
            'data' => ['dcm_id' => $dcm_id]
        ];
        echo json_encode($response);
        logUserAction(null, 'system', 'delete', 'DCM deleted', $_SERVER['REQUEST_URI'], 'DELETE', $data, 'success', $response, $ip_address, $user_agent);
    } else {
        $response = [
            'status' => 'error',
            'message' => 'Failed to delete DCM: ' . $conn->error,
            'data' => null
        ];
        echo json_encode($response);
        logUserAction(null, 'system', 'error', 'DCM deletion failed', $_SERVER['REQUEST_URI'], 'DELETE', $data, 'error', $response, $ip_address, $user_agent);
    }
}
?>