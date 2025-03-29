<?php
header("Content-Type: application/json");
include_once __DIR__ . '/../db_config.php';

// Get the request method
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST': // Create DCM
        createDCM($conn);
        break;
    
    case 'GET': // Read DCMs
        fetchDCMs($conn);
        break;
    
    case 'PUT': // Update DCM
        updateDCM($conn);
        break;
    
    case 'DELETE': // Delete DCM
        deleteDCM($conn);
        break;
    
    default:
        echo json_encode(["status" => "error", "message" => "Invalid request method"]);
        break;
}

// Function to create a new DCM
function createDCM($conn) {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!isset($data['dcm_name']) || empty($data['dcm_name']) || !isset($data['zone_id']) || empty($data['zone_id'])) {
        echo json_encode(["status" => "error", "message" => "DCM name and Zone ID are required"]);
        return;
    }

    $dcm_name = $conn->real_escape_string($data['dcm_name']);
    $dcm_location = isset($data['dcm_location']) ? $conn->real_escape_string($data['dcm_location']) : NULL;
    $dcm_pincode = isset($data['dcm_pincode']) ? $conn->real_escape_string($data['dcm_pincode']) : NULL;
    $zone_id = $conn->real_escape_string($data['zone_id']);

    $sql = "INSERT INTO dcm (dcm_name, dcm_location, dcm_pincode, zone_id) VALUES ('$dcm_name', '$dcm_location', '$dcm_pincode', '$zone_id')";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(["status" => "success", "message" => "DCM added successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to add DCM: " . $conn->error]);
    }
}

// Function to fetch all DCMs
function fetchDCMs($conn) {
    $sql = "SELECT dcm_custom_id, dcm_name, dcm_location, dcm_pincode, zone_id FROM dcm";
    $result = $conn->query($sql);
    $dcms = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $dcms[] = $row;
        }
        echo json_encode(["status" => "success", "data" => $dcms, "message" => "DCMs fetched successfully"]);
    } else {
        echo json_encode(["status" => "error", "data" => [], "message" => "No DCMs found"]);
    }
}

// Function to update a DCM
function updateDCM($conn) {
    parse_str(file_get_contents("php://input"), $data);
    
    if (!isset($data['dcm_custom_id']) || empty($data['dcm_custom_id'])) {
        echo json_encode(["status" => "error", "message" => "DCM ID is required"]);
        return;
    }

    $dcm_id = $conn->real_escape_string($data['dcm_custom_id']);
    $dcm_name = isset($data['dcm_name']) ? $conn->real_escape_string($data['dcm_name']) : NULL;
    $dcm_location = isset($data['dcm_location']) ? $conn->real_escape_string($data['dcm_location']) : NULL;
    $dcm_pincode = isset($data['dcm_pincode']) ? $conn->real_escape_string($data['dcm_pincode']) : NULL;
    $zone_id = isset($data['zone_id']) ? $conn->real_escape_string($data['zone_id']) : NULL;

    $sql = "UPDATE dcm SET dcm_name = '$dcm_name', dcm_location = '$dcm_location', dcm_pincode = '$dcm_pincode', zone_id = '$zone_id' WHERE dcm_custom_id = '$dcm_id'";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(["status" => "success", "message" => "DCM updated successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to update DCM: " . $conn->error]);
    }
}

// Function to delete a DCM
function deleteDCM($conn) {
    parse_str(file_get_contents("php://input"), $data);

    if (!isset($data['dcm_custom_id']) || empty($data['dcm_custom_id'])) {
        echo json_encode(["status" => "error", "message" => "DCM ID is required"]);
        return;
    }

    $dcm_id = $conn->real_escape_string($data['dcm_custom_id']);
    $sql = "DELETE FROM dcm WHERE dcm_custom_id = '$dcm_id'";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(["status" => "success", "message" => "DCM deleted successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to delete DCM: " . $conn->error]);
    }
}
?>
