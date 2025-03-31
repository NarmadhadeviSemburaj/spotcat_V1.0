<?php
header("Content-Type: application/json");
include_once __DIR__ . '/../db_config.php';
include_once __DIR__ . '/log_api.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Request handling
$method = $_SERVER['REQUEST_METHOD'];
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

// Logging helper
function logAction($action, $type, $ip, $agent, $details = []) {
    logUserAction(null, 'system', $type, $action, $_SERVER['REQUEST_URI'], 
                 $_SERVER['REQUEST_METHOD'], $details, $ip, $agent);
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

function createCluster($conn, $ip_address, $user_agent) {
    $input = json_decode(file_get_contents("php://input"), true);

    // Validate required fields
    $required = ['clusters_name', 'cluster_id', 'zone_id', 'dcm_id'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            sendResponse('error', "$field is required", null, 400);
        }
    }

    // Extract values
    $clusters_name = $input['clusters_name'];
    $clusters_location = $input['clusters_location'] ?? null;
    $clusters_pincode = $input['clusters_pincode'] ?? null;
    $cluster_id = $input['cluster_id'];
    $zone_id = $input['zone_id'];
    $dcm_id = $input['dcm_id'];

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
        $sql = "INSERT INTO clusters (clusters_name, clusters_location, clusters_pincode, 
                 cluster_id, zone_id, dcm_id, zone_name, dcm_name) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception('Database preparation failed: ' . $conn->error);
        }

        // Bind parameters
        $stmt->bind_param("ssssssss", 
            $clusters_name,
            $clusters_location,
            $clusters_pincode,
            $cluster_id,
            $dcm_data['zone_id'],
            $dcm_data['dcm_id'],
            $dcm_data['zone_name'],
            $dcm_data['dcm_name']
        );

        if (!$stmt->execute()) {
            throw new Exception('Failed to create cluster: ' . $stmt->error);
        }

        // Get the most recently created cluster with these parameters
        $get_sql = "SELECT c.*, cl.cluster_name 
                   FROM clusters c
                   LEFT JOIN cluster cl ON c.cluster_id = cl.cluster_id
                   WHERE c.clusters_name = ? 
                   AND c.cluster_id = ?
                   AND c.dcm_id = ?
                   ORDER BY c.clusters_id DESC
                   LIMIT 1";
        
        $get_stmt = $conn->prepare($get_sql);
        if (!$get_stmt) {
            throw new Exception('Database preparation failed: ' . $conn->error);
        }

        $get_stmt->bind_param("sss", $clusters_name, $cluster_id, $dcm_data['dcm_id']);
        $get_stmt->execute();
        $result = $get_stmt->get_result();
        $cluster = $result->fetch_assoc();

        if (!$cluster) {
            throw new Exception('Failed to retrieve created cluster');
        }

        $conn->commit();

        // Prepare response data
        $response_data = [
            'clusters_id' => $cluster['clusters_id'],
            'clusters_name' => $cluster['clusters_name'],
            'clusters_location' => $cluster['clusters_location'],
            'clusters_pincode' => $cluster['clusters_pincode'],
            'cluster_id' => $cluster['cluster_id'],
            'cluster_name' => $cluster['cluster_name'],
            'zone_id' => $cluster['zone_id'],
            'zone_name' => $cluster['zone_name'],
            'dcm_id' => $cluster['dcm_id'],
            'dcm_name' => $cluster['dcm_name']
        ];
        
        sendResponse('success', 'Cluster created', $response_data, 201);
        logAction('Create cluster', 'create', $ip_address, $user_agent, $response_data);

    } catch (Exception $e) {
        $conn->rollback();
        sendResponse('error', $e->getMessage(), null, 500);
        logAction('Create cluster failed', 'error', $ip_address, $user_agent, [
            'error' => $e->getMessage(),
            'input' => $input
        ]);
    }
}

function listClusters($conn, $ip_address, $user_agent) {
    $sql = "SELECT c.*, cl.cluster_name 
            FROM clusters c
            LEFT JOIN cluster cl ON c.cluster_id = cl.cluster_id
            ORDER BY c.clusters_name";
    $result = $conn->query($sql);
    
    if (!$result) {
        sendResponse('error', 'Database error: ' . $conn->error, null, 500);
    }

    $clusters = [];
    while ($row = $result->fetch_assoc()) {
        $clusters[] = [
            'clusters_id' => $row['clusters_id'],
            'clusters_name' => $row['clusters_name'],
            'clusters_location' => $row['clusters_location'],
            'clusters_pincode' => $row['clusters_pincode'],
            'cluster_id' => $row['cluster_id'],
            'cluster_name' => $row['cluster_name'],
            'zone_id' => $row['zone_id'],
            'zone_name' => $row['zone_name'],
            'dcm_id' => $row['dcm_id'],
            'dcm_name' => $row['dcm_name']
        ];
    }

    sendResponse('success', 'Clusters retrieved', $clusters);
    logAction('List clusters', 'read', $ip_address, $user_agent, [
        'count' => count($clusters)
    ]);
}

function getCluster($conn, $ip_address, $user_agent) {
    $clusters_id = $_GET['clusters_id'] ?? null;
    
    if (!$clusters_id) {
        sendResponse('error', 'clusters_id parameter is required', null, 400);
    }

    $sql = "SELECT c.*, cl.cluster_name 
            FROM clusters c
            LEFT JOIN cluster cl ON c.cluster_id = cl.cluster_id
            WHERE c.clusters_id = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        sendResponse('error', 'Database preparation failed: ' . $conn->error, null, 500);
    }

    $stmt->bind_param("s", $clusters_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        sendResponse('error', 'Cluster not found', null, 404);
        logAction('Cluster not found', 'error', $ip_address, $user_agent, [
            'clusters_id' => $clusters_id
        ]);
        return;
    }

    $cluster = $result->fetch_assoc();
    $response_data = [
        'clusters_id' => $cluster['clusters_id'],
        'clusters_name' => $cluster['clusters_name'],
        'clusters_location' => $cluster['clusters_location'],
        'clusters_pincode' => $cluster['clusters_pincode'],
        'cluster_id' => $cluster['cluster_id'],
        'cluster_name' => $cluster['cluster_name'],
        'zone_id' => $cluster['zone_id'],
        'zone_name' => $cluster['zone_name'],
        'dcm_id' => $cluster['dcm_id'],
        'dcm_name' => $cluster['dcm_name']
    ];
    
    sendResponse('success', 'Cluster retrieved', $response_data);
    logAction('View cluster', 'read', $ip_address, $user_agent, $response_data);
}

function updateCluster($conn, $ip_address, $user_agent) {
    $input = json_decode(file_get_contents("php://input"), true);

    if (empty($input['clusters_id'])) {
        sendResponse('error', 'clusters_id is required', null, 400);
    }

    $clusters_id = $input['clusters_id'];
    
    // Initialize variables
    $zone_name = null;
    $dcm_name = null;
    $zone_id = null;
    $dcm_id = null;

    // Check if updating zone_id or dcm_id
    if (isset($input['zone_id']) || isset($input['dcm_id'])) {
        // Get current values if not provided in update
        $current = $conn->query("SELECT zone_id, dcm_id FROM clusters WHERE clusters_id = '" . 
                              $conn->real_escape_string($clusters_id) . "'")->fetch_assoc();
        
        $zone_id = isset($input['zone_id']) ? $input['zone_id'] : $current['zone_id'];
        $dcm_id = isset($input['dcm_id']) ? $input['dcm_id'] : $current['dcm_id'];
        
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
    $clusters_name = isset($input['clusters_name']) ? $input['clusters_name'] : null;
    $clusters_location = isset($input['clusters_location']) ? $input['clusters_location'] : null;
    $clusters_pincode = isset($input['clusters_pincode']) ? $input['clusters_pincode'] : null;
    $cluster_id = isset($input['cluster_id']) ? $input['cluster_id'] : null;

    if ($clusters_name !== null) {
        $updates[] = "clusters_name = ?";
        $params[] = $clusters_name;
        $types .= "s";
    }
    if ($clusters_location !== null) {
        $updates[] = "clusters_location = ?";
        $params[] = $clusters_location;
        $types .= "s";
    }
    if ($clusters_pincode !== null) {
        $updates[] = "clusters_pincode = ?";
        $params[] = $clusters_pincode;
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

    // Add clusters_id to params
    $params[] = $clusters_id;
    $types .= "s";

    $sql = "UPDATE clusters SET " . implode(", ", $updates) . " WHERE clusters_id = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        sendResponse('error', 'Database error: ' . $conn->error, null, 500);
    }
    
    // Bind parameters using the variables
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            // Return updated cluster data with all fields
            $result = $conn->query("SELECT c.*, cl.cluster_name 
                                  FROM clusters c
                                  LEFT JOIN cluster cl ON c.cluster_id = cl.cluster_id
                                  WHERE c.clusters_id = '" . 
                                  $conn->real_escape_string($clusters_id) . "'");
            $cluster = $result->fetch_assoc();
            
            $response_data = [
                'clusters_id' => $cluster['clusters_id'],
                'clusters_name' => $cluster['clusters_name'],
                'clusters_location' => $cluster['clusters_location'],
                'clusters_pincode' => $cluster['clusters_pincode'],
                'cluster_id' => $cluster['cluster_id'],
                'cluster_name' => $cluster['cluster_name'],
                'zone_id' => $cluster['zone_id'],
                'zone_name' => $cluster['zone_name'],
                'dcm_id' => $cluster['dcm_id'],
                'dcm_name' => $cluster['dcm_name']
            ];
            
            sendResponse('success', 'Cluster updated', $response_data);
            logAction('Update cluster', 'update', $ip_address, $user_agent, $response_data);
        } else {
            sendResponse('error', 'No changes made or cluster not found', null, 404);
        }
    } else {
        sendResponse('error', 'Update failed: ' . $stmt->error, null, 500);
        logAction('Update cluster failed', 'error', $ip_address, $user_agent, [
            'error' => $stmt->error,
            'input' => $input
        ]);
    }
}

function deleteCluster($conn, $ip_address, $user_agent) {
    $input = json_decode(file_get_contents("php://input"), true);

    if (empty($input['clusters_id'])) {
        sendResponse('error', 'clusters_id is required', null, 400);
    }

    $clusters_id = $input['clusters_id'];
    $stmt = $conn->prepare("DELETE FROM clusters WHERE clusters_id = ?");
    $stmt->bind_param("s", $clusters_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            sendResponse('success', 'Cluster deleted', ['clusters_id' => $clusters_id]);
            logAction('Delete cluster', 'delete', $ip_address, $user_agent, [
                'clusters_id' => $clusters_id
            ]);
        } else {
            sendResponse('error', 'Cluster not found', null, 404);
        }
    } else {
        sendResponse('error', 'Deletion failed: ' . $stmt->error, null, 500);
        logAction('Delete cluster failed', 'error', $ip_address, $user_agent, [
            'error' => $stmt->error,
            'input' => $input
        ]);
    }
}

switch ($method) {
    case 'POST': 
        createCluster($conn, $ip_address, $user_agent); 
        break;
    case 'GET':
        if (isset($_GET['validate_dcm_zone'])) {
            validateDcmZoneEndpoint($conn);
        } else {
            isset($_GET['clusters_id']) ? getCluster($conn, $ip_address, $user_agent) : 
            listClusters($conn, $ip_address, $user_agent);
        }
        break;
    case 'PUT': 
        updateCluster($conn, $ip_address, $user_agent); 
        break;
    case 'DELETE': 
        deleteCluster($conn, $ip_address, $user_agent); 
        break;
    default:
        sendResponse('error', 'Invalid request method', null, 405);
        logAction('Invalid method', 'error', $ip_address, $user_agent);
        break;
}

$conn->close();
?>