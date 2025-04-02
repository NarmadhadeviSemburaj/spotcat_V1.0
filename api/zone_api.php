<?php
header("Content-Type: application/json");
include_once __DIR__ . '/../db_config.php';
include 'log_api.php';

// Function to send JSON response with detailed logging
function sendResponse($status, $message, $data = null, $http_status_code = 200) {
    global $conn;

    $response = ["status" => $status, "message" => $message];
    if ($data !== null) {
        $response["data"] = $data;
    }

    // Get the current endpoint
    $endpoint = $_SERVER['REQUEST_URI'];
    $method = $_SERVER['REQUEST_METHOD'];
    
    // Get the request payload
    $request_payload = file_get_contents("php://input");
    $decoded_payload = json_decode($request_payload, true) ?: [];

    // Log the API request with all details
    logUserAction(
        isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null, // user_id if available
        isset($_SESSION['username']) ? $_SESSION['username'] : 'API_USER', // username if available
        "ZONE_API_" . strtoupper($method), // action type
        $message, // action description
        $endpoint, // endpoint
        $method, // HTTP method
        $decoded_payload, // request payload
        $status, // response status
        $response, // response data
        $_SERVER['REMOTE_ADDR'], // IP address
        $_SERVER['HTTP_USER_AGENT'] // User agent
    );

    // Set HTTP status code
    http_response_code($http_status_code);
    
    echo json_encode($response);
    exit;
}

// Get HTTP method
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'POST': // Create Zone
            $data = json_decode(file_get_contents("php://input"), true);
            
            // Validate required fields
            if (empty($data['zone_name']) || empty($data['zone_location']) || empty($data['zone_pincode'])) {
                sendResponse("error", "All fields (zone_name, zone_location, zone_pincode) are required", null, 400);
            }

            $zone_name = $conn->real_escape_string($data['zone_name']);
            $zone_location = $conn->real_escape_string($data['zone_location']);
            $zone_pincode = $conn->real_escape_string($data['zone_pincode']);

            // Check if zone already exists
            $check_sql = "SELECT zone_id FROM zone WHERE zone_name = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("s", $zone_name);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                sendResponse("error", "Zone with this name already exists", null, 409);
            }

            // Insert new zone
            $sql = "INSERT INTO zone (zone_name, zone_location, zone_pincode) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            
            if (!$stmt) {
                sendResponse("error", "Database error: " . $conn->error, null, 500);
            }
            
            $stmt->bind_param("sss", $zone_name, $zone_location, $zone_pincode);
            
            if ($stmt->execute()) {
                $zone_id = $stmt->insert_id;
                sendResponse("success", "Zone created successfully", ["zone_id" => $zone_id], 201);
            } else {
                sendResponse("error", "Error creating zone: " . $stmt->error, null, 500);
            }
            
            $check_stmt->close();
            $stmt->close();
            break;

        case 'GET': // Retrieve Zones (All or Specific)
            if (isset($_GET['zone_id'])) {
                // Get single zone
                $zone_id = $conn->real_escape_string($_GET['zone_id']);
                $sql = "SELECT * FROM zone WHERE zone_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $zone_id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    sendResponse("success", "Zone found", $result->fetch_assoc());
                } else {
                    sendResponse("error", "Zone not found", null, 404);
                }
                $stmt->close();
            } else {
                // Get all zones with optional pagination
                $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
                $limit = isset($_GET['limit']) ? max(1, intval($_GET['limit'])) : 100;
                $offset = ($page - 1) * $limit;

                $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM zone LIMIT ? OFFSET ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $limit, $offset);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if (!$result) {
                    sendResponse("error", "Database error: " . $conn->error, null, 500);
                }
                
                $zones = [];
                while ($row = $result->fetch_assoc()) {
                    $zones[] = $row;
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

                sendResponse("success", "Zones retrieved", ['zones' => $zones, 'meta' => $meta]);
                $stmt->close();
            }
            break;

        case 'PUT': // Update Zone (Partial updates supported)
            $data = json_decode(file_get_contents("php://input"), true);
            
            if (empty($data['zone_id'])) {
                sendResponse("error", "Zone ID is required", null, 400);
            }

            $zone_id = $conn->real_escape_string($data['zone_id']);
            $updates = [];
            $params = [];
            $types = "";

            // Build dynamic update query
            if (isset($data['zone_name'])) {
                $updates[] = "zone_name = ?";
                $params[] = $conn->real_escape_string($data['zone_name']);
                $types .= "s";
            }
            if (isset($data['zone_location'])) {
                $updates[] = "zone_location = ?";
                $params[] = $conn->real_escape_string($data['zone_location']);
                $types .= "s";
            }
            if (isset($data['zone_pincode'])) {
                $updates[] = "zone_pincode = ?";
                $params[] = $conn->real_escape_string($data['zone_pincode']);
                $types .= "s";
            }

            if (empty($updates)) {
                sendResponse("error", "No fields to update", null, 400);
            }

            // Add zone_id to params
            $params[] = $zone_id;
            $types .= "s";

            $sql = "UPDATE zone SET " . implode(", ", $updates) . " WHERE zone_id = ?";
            $stmt = $conn->prepare($sql);
            
            if (!$stmt) {
                sendResponse("error", "Database error: " . $conn->error, null, 500);
            }
            
            $stmt->bind_param($types, ...$params);

            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    sendResponse("success", "Zone updated successfully", ["zone_id" => $zone_id]);
                } else {
                    sendResponse("error", "No changes made or zone not found", null, 404);
                }
            } else {
                sendResponse("error", "Error updating zone: " . $stmt->error, null, 500);
            }
            $stmt->close();
            break;

        case 'DELETE': // Delete Zone
            $data = json_decode(file_get_contents("php://input"), true);
            
            if (empty($data['zone_id'])) {
                sendResponse("error", "Zone ID is required", null, 400);
            }

            $zone_id = $conn->real_escape_string($data['zone_id']);

            // Check if zone exists
            $check_sql = "SELECT zone_id FROM zone WHERE zone_id = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("s", $zone_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows === 0) {
                sendResponse("error", "Zone not found", null, 404);
            }

            // Delete the zone
            $delete_sql = "DELETE FROM zone WHERE zone_id = ?";
            $delete_stmt = $conn->prepare($delete_sql);
            $delete_stmt->bind_param("s", $zone_id);
            
            if ($delete_stmt->execute()) {
                if ($delete_stmt->affected_rows > 0) {
                    sendResponse("success", "Zone deleted successfully");
                } else {
                    sendResponse("error", "No zone was deleted", null, 404);
                }
            } else {
                sendResponse("error", "Error deleting zone: " . $delete_stmt->error, null, 500);
            }
            
            $check_stmt->close();
            $delete_stmt->close();
            break;

        default:
            sendResponse("error", "Invalid request method", null, 405);
    }
} catch (Exception $e) {
    // Log unexpected errors
    error_log("Zone API Error: " . $e->getMessage());
    sendResponse("error", "An unexpected error occurred: " . $e->getMessage(), null, 500);
} finally {
    if (isset($conn) && $conn) {
        $conn->close();
    }
}
?>