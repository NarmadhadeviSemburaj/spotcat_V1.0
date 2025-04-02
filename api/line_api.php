<?php
header("Content-Type: application/json");
include_once __DIR__ . '/../db_config.php';
include_once __DIR__ . '/log_api.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
        "LINE_API_" . strtoupper($_SERVER['REQUEST_METHOD']),
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
    // Get the request method
    $method = $_SERVER['REQUEST_METHOD'];

    switch ($method) {
        case 'POST': 
            createLine($conn); 
            break;
        case 'GET':
            if (isset($_GET['validate_dcm_zone'])) {
                validateDcmZoneEndpoint($conn);
            } else {
                isset($_GET['line_id']) ? getLine($conn) : listLines($conn);
            }
            break;
        case 'PUT': 
            updateLine($conn); 
            break;
        case 'DELETE': 
            deleteLine($conn); 
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

// Validate DCM-Zone relationship and get names
function validateDcmZoneRelationship($conn, $dcm_id, $zone_id) {
    $check_sql = "SELECT d.dcm_id, d.dcm_name, d.zone_id, z.zone_name 
                 FROM dcm d
                 JOIN zone z ON d.zone_id = z.zone_id
                 WHERE d.dcm_id = ? AND d.zone_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ss", $dcm_id, $zone_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows === 0) {
        // Get actual zone for better error messaging
        $actual_zone_sql = "SELECT z.zone_id, z.zone_name 
                          FROM dcm d
                          JOIN zone z ON d.zone_id = z.zone_id
                          WHERE d.dcm_id = ?";
        $actual_zone_stmt = $conn->prepare($actual_zone_sql);
        $actual_zone_stmt->bind_param("s", $dcm_id);
        $actual_zone_stmt->execute();
        $actual_zone = $actual_zone_stmt->get_result()->fetch_assoc();
        
        return [
            'valid' => false,
            'message' => $actual_zone ? 
                "DCM belongs to zone {$actual_zone['zone_name']} ({$actual_zone['zone_id']}), not the specified zone" :
                "DCM not found"
        ];
    }
    
    return [
        'valid' => true,
        'data' => $result->fetch_assoc()
    ];
}

function validateDcmZoneEndpoint($conn) {
    $dcm_id = $_GET['dcm_id'] ?? null;
    $zone_id = $_GET['zone_id'] ?? null;
    
    if (!$dcm_id || !$zone_id) {
        sendResponse('error', 'Both dcm_id and zone_id are required', null, 400);
    }
    
    $validation = validateDcmZoneRelationship($conn, $dcm_id, $zone_id);
    if ($validation['valid']) {
        sendResponse('success', 'Valid DCM-Zone relationship', $validation['data']);
    } else {
        sendResponse('error', $validation['message'], null, 400);
    }
}

// GET ALL LINES
function listLines($conn) {
    // Handle pagination
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = isset($_GET['limit']) ? max(1, intval($_GET['limit'])) : 100;
    $offset = ($page - 1) * $limit;

    $sql = "SELECT SQL_CALC_FOUND_ROWS l.*, c.cluster_name 
            FROM line l
            LEFT JOIN cluster c ON l.cluster_id = c.cluster_id
            ORDER BY l.line_name
            LIMIT ? OFFSET ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if (!$result) {
        sendResponse('error', 'Database error: ' . $conn->error, null, 500);
    }

    $lines = [];
    while ($row = $result->fetch_assoc()) {
        $lines[] = [
            'line_id' => $row['line_id'],
            'line_name' => $row['line_name'],
            'line_location' => $row['line_location'],
            'line_pincode' => $row['line_pincode'],
            'cluster_id' => $row['cluster_id'],
            'cluster_name' => $row['cluster_name'],
            'zone_id' => $row['zone_id'],
            'zone_name' => $row['zone_name'],
            'dcm_id' => $row['dcm_id'],
            'dcm_name' => $row['dcm_name']
        ];
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

    sendResponse('success', 'Lines retrieved', ['lines' => $lines, 'meta' => $meta]);
}

// GET SINGLE LINE
function getLine($conn) {
    $line_id = $_GET['line_id'] ?? null;
    
    if (!$line_id) {
        sendResponse('error', 'line_id parameter is required', null, 400);
    }

    $sql = "SELECT l.*, c.cluster_name 
            FROM line l
            LEFT JOIN cluster c ON l.cluster_id = c.cluster_id
            WHERE l.line_id = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        sendResponse('error', 'Database preparation failed: ' . $conn->error, null, 500);
    }

    $stmt->bind_param("s", $line_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        sendResponse('error', 'Line not found', null, 404);
        return;
    }

    $line = $result->fetch_assoc();
    $response_data = [
        'line_id' => $line['line_id'],
        'line_name' => $line['line_name'],
        'line_location' => $line['line_location'],
        'line_pincode' => $line['line_pincode'],
        'cluster_id' => $line['cluster_id'],
        'cluster_name' => $line['cluster_name'],
        'zone_id' => $line['zone_id'],
        'zone_name' => $line['zone_name'],
        'dcm_id' => $line['dcm_id'],
        'dcm_name' => $line['dcm_name']
    ];
    
    sendResponse('success', 'Line retrieved', $response_data);
}

function createLine($conn) {
    $input = json_decode(file_get_contents("php://input"), true);

    // Validate required fields
    $required = ['line_name', 'cluster_id', 'zone_id', 'dcm_id'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            sendResponse('error', "$field is required", null, 400);
        }
    }

    // Extract values
    $line_name = $conn->real_escape_string($input['line_name']);
    $line_location = isset($input['line_location']) ? $conn->real_escape_string($input['line_location']) : null;
    $line_pincode = isset($input['line_pincode']) ? $conn->real_escape_string($input['line_pincode']) : null;
    $cluster_id = $conn->real_escape_string($input['cluster_id']);
    $zone_id = $conn->real_escape_string($input['zone_id']);
    $dcm_id = $conn->real_escape_string($input['dcm_id']);

    // Validate DCM-Zone relationship and get names
    $validation = validateDcmZoneRelationship($conn, $dcm_id, $zone_id);
    if (!$validation['valid']) {
        sendResponse('error', $validation['message'], null, 400);
    }

    $dcm_data = $validation['data'];

    // Start transaction to ensure data consistency
    $conn->begin_transaction();

    try {
        // Insert into database
        $sql = "INSERT INTO line (line_name, line_location, line_pincode, 
                 cluster_id, zone_id, dcm_id, zone_name, dcm_name) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception('Database preparation failed: ' . $conn->error);
        }

        // Bind parameters
        $stmt->bind_param("ssssssss", 
            $line_name,
            $line_location,
            $line_pincode,
            $cluster_id,
            $dcm_data['zone_id'],
            $dcm_data['dcm_id'],
            $dcm_data['zone_name'],
            $dcm_data['dcm_name']
        );

        if (!$stmt->execute()) {
            throw new Exception('Failed to create line: ' . $stmt->error);
        }

        // Get the most recently created line with these parameters
        $get_sql = "SELECT l.*, c.cluster_name 
                   FROM line l
                   LEFT JOIN cluster c ON l.cluster_id = c.cluster_id
                   WHERE l.line_name = ? 
                   AND l.cluster_id = ?
                   AND l.dcm_id = ?
                   ORDER BY l.line_id DESC
                   LIMIT 1";
        
        $get_stmt = $conn->prepare($get_sql);
        if (!$get_stmt) {
            throw new Exception('Database preparation failed: ' . $conn->error);
        }

        $get_stmt->bind_param("sss", $line_name, $cluster_id, $dcm_data['dcm_id']);
        $get_stmt->execute();
        $result = $get_stmt->get_result();
        $line = $result->fetch_assoc();

        if (!$line) {
            throw new Exception('Failed to retrieve created line');
        }

        $conn->commit();

        // Prepare response data
        $response_data = [
            'line_id' => $line['line_id'],
            'line_name' => $line['line_name'],
            'line_location' => $line['line_location'],
            'line_pincode' => $line['line_pincode'],
            'cluster_id' => $line['cluster_id'],
            'cluster_name' => $line['cluster_name'],
            'zone_id' => $line['zone_id'],
            'zone_name' => $line['zone_name'],
            'dcm_id' => $line['dcm_id'],
            'dcm_name' => $line['dcm_name']
        ];
        
        sendResponse('success', 'Line created', $response_data, 201);

    } catch (Exception $e) {
        $conn->rollback();
        sendResponse('error', $e->getMessage(), null, 500);
    }
}

function updateLine($conn) {
    $input = json_decode(file_get_contents("php://input"), true);

    if (empty($input['line_id'])) {
        sendResponse('error', 'line_id is required', null, 400);
    }

    $line_id = $conn->real_escape_string($input['line_id']);
    
    // Initialize variables
    $zone_name = null;
    $dcm_name = null;
    $zone_id = null;
    $dcm_id = null;

    // Check if updating zone_id or dcm_id
    if (isset($input['zone_id']) || isset($input['dcm_id'])) {
        // Get current values if not provided in update
        $current = $conn->query("SELECT zone_id, dcm_id FROM line WHERE line_id = '" . 
                              $conn->real_escape_string($line_id) . "'")->fetch_assoc();
        
        $zone_id = isset($input['zone_id']) ? $conn->real_escape_string($input['zone_id']) : $current['zone_id'];
        $dcm_id = isset($input['dcm_id']) ? $conn->real_escape_string($input['dcm_id']) : $current['dcm_id'];
        
        // Validate the relationship
        $validation = validateDcmZoneRelationship($conn, $dcm_id, $zone_id);
        if (!$validation['valid']) {
            sendResponse('error', $validation['message'], null, 400);
        }
        
        $zone_name = $validation['data']['zone_name'];
        $dcm_name = $validation['data']['dcm_name'];
    }

    // Build dynamic update query
    $updates = [];
    $params = [];
    $types = "";

    // Extract all values into variables first
    $line_name = isset($input['line_name']) ? $conn->real_escape_string($input['line_name']) : null;
    $line_location = isset($input['line_location']) ? $conn->real_escape_string($input['line_location']) : null;
    $line_pincode = isset($input['line_pincode']) ? $conn->real_escape_string($input['line_pincode']) : null;
    $cluster_id = isset($input['cluster_id']) ? $conn->real_escape_string($input['cluster_id']) : null;

    if ($line_name !== null) {
        $updates[] = "line_name = ?";
        $params[] = $line_name;
        $types .= "s";
    }
    if ($line_location !== null) {
        $updates[] = "line_location = ?";
        $params[] = $line_location;
        $types .= "s";
    }
    if ($line_pincode !== null) {
        $updates[] = "line_pincode = ?";
        $params[] = $line_pincode;
        $types .= "s";
    }
    if ($cluster_id !== null) {
        $updates[] = "cluster_id = ?";
        $params[] = $cluster_id;
        $types .= "s";
    }
    if ($zone_id !== null) {
        $updates[] = "zone_id = ?";
        $params[] = $zone_id;
        $types .= "s";
    }
    if ($dcm_id !== null) {
        $updates[] = "dcm_id = ?";
        $params[] = $dcm_id;
        $types .= "s";
    }
    if ($zone_name !== null) {
        $updates[] = "zone_name = ?";
        $params[] = $zone_name;
        $types .= "s";
    }
    if ($dcm_name !== null) {
        $updates[] = "dcm_name = ?";
        $params[] = $dcm_name;
        $types .= "s";
    }

    if (empty($updates)) {
        sendResponse('error', 'No fields to update', null, 400);
    }

    // Add line_id to params
    $params[] = $line_id;
    $types .= "s";

    $sql = "UPDATE line SET " . implode(", ", $updates) . " WHERE line_id = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        sendResponse('error', 'Database error: ' . $conn->error, null, 500);
    }
    
    // Bind parameters using the variables
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            // Return updated line data with all fields
            $result = $conn->query("SELECT l.*, c.cluster_name 
                                  FROM line l
                                  LEFT JOIN cluster c ON l.cluster_id = c.cluster_id
                                  WHERE l.line_id = '" . 
                                  $conn->real_escape_string($line_id) . "'");
            $line = $result->fetch_assoc();
            
            $response_data = [
                'line_id' => $line['line_id'],
                'line_name' => $line['line_name'],
                'line_location' => $line['line_location'],
                'line_pincode' => $line['line_pincode'],
                'cluster_id' => $line['cluster_id'],
                'cluster_name' => $line['cluster_name'],
                'zone_id' => $line['zone_id'],
                'zone_name' => $line['zone_name'],
                'dcm_id' => $line['dcm_id'],
                'dcm_name' => $line['dcm_name']
            ];
            
            sendResponse('success', 'Line updated', $response_data);
        } else {
            sendResponse('error', 'No changes made or line not found', null, 404);
        }
    } else {
        sendResponse('error', 'Update failed: ' . $stmt->error, null, 500);
    }
}

function deleteLine($conn) {
    $input = json_decode(file_get_contents("php://input"), true);

    if (empty($input['line_id'])) {
        sendResponse('error', 'line_id is required', null, 400);
    }

    $line_id = $conn->real_escape_string($input['line_id']);
    $stmt = $conn->prepare("DELETE FROM line WHERE line_id = ?");
    $stmt->bind_param("s", $line_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            sendResponse('success', 'Line deleted', ['line_id' => $line_id]);
        } else {
            sendResponse('error', 'Line not found', null, 404);
        }
    } else {
        sendResponse('error', 'Deletion failed: ' . $stmt->error, null, 500);
    }
}
?>