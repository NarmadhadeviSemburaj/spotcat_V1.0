<?php
header("Content-Type: application/json");
include_once __DIR__ . '/../db_config.php';
include_once __DIR__ . '/log_api.php';

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Request handling
$method = $_SERVER['REQUEST_METHOD'];
$ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;

switch ($method) {
    case 'POST': createCluster($conn, $ip_address, $user_agent); break;
    case 'GET': 
        isset($_GET['clusters_id']) ? getCluster($conn, $ip_address, $user_agent) : 
        listClusters($conn, $ip_address, $user_agent);
        break;
    case 'PUT': updateCluster($conn, $ip_address, $user_agent); break;
    case 'DELETE': deleteCluster($conn, $ip_address, $user_agent); break;
    default:
        sendResponse('error', 'Invalid request method', null, 405);
        logAction('Invalid method', 'error', $ip_address, $user_agent);
        break;
}

// Helper Functions
function sendResponse($status, $message, $data, $httpCode = 200) {
    http_response_code($httpCode);
    echo json_encode(['status' => $status, 'message' => $message, 'data' => $data]);
    exit;
}

function logAction($action, $type, $ip, $agent, $details = []) {
    logUserAction(null, 'system', $type, $action, $_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD'], $details, $type, $details, $ip, $agent);
}

// 1. Create Cluster (auto-generates clusters_id via trigger)
function createCluster($conn, $ip_address, $user_agent) {
    $input = json_decode(file_get_contents("php://input"), true);

    if (!$input) {
        sendResponse('error', 'Invalid JSON input', null, 400);
    }

    // Validate required fields
    $required = ['clusters_name', 'cluster_id'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            sendResponse('error', "$field is required", null, 400);
        }
    }

    // Prepare values - this fixes the "cannot be passed by reference" error
    $clusters_name = $input['clusters_name'];
    $clusters_location = isset($input['clusters_location']) ? $input['clusters_location'] : null;
    $clusters_pincode = isset($input['clusters_pincode']) ? $input['clusters_pincode'] : null;
    $cluster_id = $input['cluster_id'];

    // Insert into database
    $sql = "INSERT INTO clusters (clusters_name, clusters_location, clusters_pincode, cluster_id) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        sendResponse('error', 'Database prepare failed: ' . $conn->error, null, 500);
    }

    // Bind parameters - now using variables instead of direct array access
    $stmt->bind_param("ssss", $clusters_name, $clusters_location, $clusters_pincode, $cluster_id);

    if ($stmt->execute()) {
        // Get the newly generated clusters_id
        $result = $conn->query("SELECT clusters_id FROM clusters WHERE cluster_id = '" . 
                             $conn->real_escape_string($cluster_id) . 
                             "' ORDER BY clusters_id DESC LIMIT 1");
        $row = $result->fetch_assoc();
        
        sendResponse('success', 'Cluster created', [
            'clusters_id' => $row['clusters_id']
        ], 201);
        logAction('Cluster created', 'create', $ip_address, $user_agent, ['clusters_id' => $row['clusters_id']]);
    } else {
        sendResponse('error', 'Create failed: ' . $stmt->error, null, 500);
        logAction('Cluster create failed', 'error', $ip_address, $user_agent, ['error' => $stmt->error]);
    }
}

// 2. List All Clusters
function listClusters($conn, $ip_address, $user_agent) {
    $sql = "SELECT c.*, cl.cluster_name 
            FROM clusters c
            JOIN cluster cl ON c.cluster_id = cl.cluster_id";
    $result = $conn->query($sql);
    
    if (!$result) {
        sendResponse('error', 'Database error: ' . $conn->error, null, 500);
    }

    $clusters = [];
    while ($row = $result->fetch_assoc()) {
        $clusters[] = $row;
    }

    sendResponse('success', 'Clusters retrieved', $clusters);
}

// 3. Get Single Cluster
function getCluster($conn, $ip_address, $user_agent) {
    $clusters_id = $conn->real_escape_string($_GET['clusters_id']);

    $stmt = $conn->prepare("SELECT c.*, cl.cluster_name 
                          FROM clusters c
                          JOIN cluster cl ON c.cluster_id = cl.cluster_id
                          WHERE c.clusters_id = ?");
    $stmt->bind_param("s", $clusters_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        sendResponse('success', 'Cluster retrieved', $result->fetch_assoc());
    } else {
        sendResponse('error', 'Cluster not found', null, 404);
    }
}

// 4. Update Cluster
function updateCluster($conn, $ip_address, $user_agent) {
    $input = json_decode(file_get_contents("php://input"), true);

    if (empty($input['clusters_id'])) {
        sendResponse('error', 'clusters_id is required', null, 400);
    }

    $updates = [];
    $params = [];
    $types = '';

    $fields = [
        'clusters_name' => 's',
        'clusters_location' => 's',
        'clusters_pincode' => 's',
        'cluster_id' => 's'
    ];

    foreach ($fields as $field => $type) {
        if (isset($input[$field])) {
            $updates[] = "$field = ?";
            $params[] = $input[$field];
            $types .= $type;
        }
    }

    if (empty($updates)) {
        sendResponse('error', 'No fields to update', null, 400);
    }

    $params[] = $input['clusters_id'];
    $types .= 's';

    $sql = "UPDATE clusters SET " . implode(", ", $updates) . " WHERE clusters_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        sendResponse('success', 'Cluster updated', ['clusters_id' => $input['clusters_id']]);
    } else {
        sendResponse('error', 'Update failed: ' . $stmt->error, null, 500);
    }
}

// 5. Delete Cluster
function deleteCluster($conn, $ip_address, $user_agent) {
    $input = json_decode(file_get_contents("php://input"), true);

    if (empty($input['clusters_id'])) {
        sendResponse('error', 'clusters_id is required', null, 400);
    }

    $stmt = $conn->prepare("DELETE FROM clusters WHERE clusters_id = ?");
    $stmt->bind_param("s", $input['clusters_id']);

    if ($stmt->execute()) {
        sendResponse('success', 'Cluster deleted', ['clusters_id' => $input['clusters_id']]);
    } else {
        sendResponse('error', 'Deletion failed: ' . $stmt->error, null, 500);
    }
}
?>