<?php 
header("Content-Type: application/json");
include_once __DIR__ . '/../db_config.php';
include_once __DIR__ . '/log_api.php';

// Request handling
$method = $_SERVER['REQUEST_METHOD'];
$ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;

switch ($method) {
    case 'POST': createCluster($conn, $ip_address, $user_agent); break;
    case 'GET': 
        isset($_GET['cluster_id']) ? getCluster($conn, $ip_address, $user_agent) : 
        listClusters($conn, $ip_address, $user_agent);
        break;
    case 'PUT': updateCluster($conn, $ip_address, $user_agent); break;
    case 'DELETE': deleteCluster($conn, $ip_address, $user_agent); break;
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
// 1. Create Cluster (auto-generates CID_XXX)
function createCluster($conn, $ip_address, $user_agent) {
    $input = json_decode(file_get_contents("php://input"), true);
    
    // Validate
    if (empty($input['cluster_name'])) {
        sendResponse('error', 'cluster_name is required', null, 400);
        logAction('Create failed: Missing name', 'error', $ip_address, $user_agent, $input);
        return;
    }

    // Insert (trigger auto-generates CID_XXX)
    $sql = "INSERT INTO cluster (cluster_name) VALUES (?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $input['cluster_name']);
    
    if ($stmt->execute()) {
        // Fetch the newly created cluster
        $result = $conn->query("SELECT * FROM cluster ORDER BY created_at DESC LIMIT 1");
        $cluster = $result->fetch_assoc();
        
        sendResponse('success', 'Cluster created', $cluster, 201);
        logAction('Cluster created', 'create', $ip_address, $user_agent, $cluster);
    } else {
        sendResponse('error', 'Failed to create cluster: ' . $conn->error, null, 500);
        logAction('Create failed', 'error', $ip_address, $user_agent, ['error' => $conn->error]);
    }
}

// 2. List All Clusters
function listClusters($conn, $ip_address, $user_agent) {
    $sql = "SELECT cluster_id, cluster_name, status, created_at FROM cluster";
    $result = $conn->query($sql);
    
    $clusters = [];
    while ($row = $result->fetch_assoc()) {
        $clusters[] = $row;
    }
    
    sendResponse('success', 'Clusters retrieved', $clusters);
    logAction('Listed clusters', 'read', $ip_address, $user_agent, ['count' => count($clusters)]);
}

// 3. Get Single Cluster
function getCluster($conn, $ip_address, $user_agent) {
    $cluster_id = $conn->real_escape_string($_GET['cluster_id']);
    $sql = "SELECT * FROM cluster WHERE cluster_id = '$cluster_id'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $cluster = $result->fetch_assoc();
        
        sendResponse('success', 'Cluster retrieved', $cluster);
        logAction('Viewed cluster', 'read', $ip_address, $user_agent, ['cluster_id' => $cluster_id]);
    } else {
        sendResponse('error', 'Cluster not found', null, 404);
        logAction('Cluster not found', 'error', $ip_address, $user_agent, ['cluster_id' => $cluster_id]);
    }
}

// 4. Update Cluster
function updateCluster($conn, $ip_address, $user_agent) {
    $input = json_decode(file_get_contents("php://input"), true);
    
    if (empty($input['cluster_id'])) {
        sendResponse('error', 'cluster_id is required', null, 400);
        logAction('Update failed: Missing ID', 'error', $ip_address, $user_agent, $input);
        return;
    }
    
    $cluster_id = $conn->real_escape_string($input['cluster_id']);
    $updates = [];
    
    if (isset($input['cluster_name'])) {
        $updates[] = "cluster_name = '" . $conn->real_escape_string($input['cluster_name']) . "'";
    }
    if (isset($input['status'])) {
        $updates[] = "status = '" . $conn->real_escape_string($input['status']) . "'";
    }
    
    if (empty($updates)) {
        sendResponse('error', 'No fields to update', null, 400);
        logAction('Update failed: No fields', 'error', $ip_address, $user_agent, $input);
        return;
    }
    
    $sql = "UPDATE cluster SET " . implode(", ", $updates) . " WHERE cluster_id = '$cluster_id'";
    
    if ($conn->query($sql)) {
        sendResponse('success', 'Cluster updated', ['cluster_id' => $cluster_id]);
        logAction('Cluster updated', 'update', $ip_address, $user_agent, $input);
    } else {
        sendResponse('error', 'Update failed: ' . $conn->error, null, 500);
        logAction('Update failed', 'error', $ip_address, $user_agent, ['error' => $conn->error]);
    }
}

// 5. Delete Cluster
function deleteCluster($conn, $ip_address, $user_agent) {
    $input = json_decode(file_get_contents("php://input"), true);
    
    if (empty($input['cluster_id'])) {
        sendResponse('error', 'cluster_id is required', null, 400);
        logAction('Delete failed: Missing ID', 'error', $ip_address, $user_agent, $input);
        return;
    }
    
    $cluster_id = $conn->real_escape_string($input['cluster_id']);
    $sql = "DELETE FROM cluster WHERE cluster_id = '$cluster_id'";
    
    if ($conn->query($sql)) {
        sendResponse('success', 'Cluster deleted', ['cluster_id' => $cluster_id]);
        logAction('Cluster deleted', 'delete', $ip_address, $user_agent, ['cluster_id' => $cluster_id]);
    } else {
        sendResponse('error', 'Deletion failed: ' . $conn->error, null, 500);
        logAction('Delete failed', 'error', $ip_address, $user_agent, ['error' => $conn->error]);
    }
}
?>
