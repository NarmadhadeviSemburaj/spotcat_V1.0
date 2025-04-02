<?php
header("Content-Type: application/json");
include_once __DIR__ . '/../db_config.php';
include_once __DIR__ . '/log_api.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$method = $_SERVER['REQUEST_METHOD'];
$ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;

function sendResponse($status, $message, $data = null, $httpCode = 200) {
    http_response_code($httpCode);
    echo json_encode([
        'status' => $status,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// ✅ **Create Rule**
function createRule($conn, $ip_address, $user_agent) {
    $input = json_decode(file_get_contents("php://input"), true);
    
    if (empty($input['rule_name']) || empty($input['rule_value'])) {
        sendResponse('error', 'rule_name and rule_value are required', null, 400);
    }

    $rule_name = $input['rule_name'];
    $rule_value = $input['rule_value'];
    $description = $input['description'] ?? null;

    // Check for duplicate rule name
    $check_stmt = $conn->prepare("SELECT rule_id FROM rule_config WHERE rule_name = ?");
    $check_stmt->bind_param("s", $rule_name);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        sendResponse('error', 'Rule with this name already exists', null, 409);
    }

    $sql = "INSERT INTO rule_config (rule_name, rule_value, description) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $rule_name, $rule_value, $description);

    if ($stmt->execute()) {
        // Get the newly created rule using the rule_name
        $get_stmt = $conn->prepare("SELECT * FROM rule_config WHERE rule_name = ? ORDER BY rule_id DESC LIMIT 1");
        $get_stmt->bind_param("s", $rule_name);
        $get_stmt->execute();
        $result = $get_stmt->get_result();
        
        if ($result->num_rows === 0) {
            logUserAction(null, 'System', 'CREATE_RULE_FAILED', "Rule created but could not retrieve it: $rule_name", '/api/rule', 'POST', $input, 500, null, $ip_address, $user_agent);
            sendResponse('error', 'Rule created but could not retrieve it', null, 500);
        }
        
        $new_rule = $result->fetch_assoc();
        
        logUserAction(null, 'System', 'CREATE_RULE', "Rule created: {$new_rule['rule_id']}", '/api/rule', 'POST', $input, 201, $new_rule, $ip_address, $user_agent);
        sendResponse('success', 'Rule created', $new_rule, 201);
    } else {
        logUserAction(null, 'System', 'CREATE_RULE_FAILED', "Failed to create rule: $rule_name", '/api/rule', 'POST', $input, 500, ['error' => $conn->error], $ip_address, $user_agent);
        sendResponse('error', 'Failed to create rule', null, 500);
    }
}

// ✅ **List All Rules**
function listRules($conn, $ip_address, $user_agent) {
    // Check if created_at column exists
    $column_check = $conn->query("SHOW COLUMNS FROM rule_config LIKE 'created_at'");
    $order_by = ($column_check->num_rows > 0) ? "created_at" : "last_updated";
    
    $sql = "SELECT * FROM rule_config ORDER BY $order_by DESC";
    $result = $conn->query($sql);

    $rules = [];
    while ($row = $result->fetch_assoc()) {
        $rules[] = $row;
    }

    logUserAction(null, 'System', 'LIST_RULES', 'Retrieved rules list', '/api/rule', 'GET', null, 200, ['count' => count($rules)], $ip_address, $user_agent);
    sendResponse('success', 'Rules retrieved', $rules);
}

// ✅ **Get Rule by ID**
function getRuleById($conn, $ip_address, $user_agent) {
    $rule_id = $_GET['rule_id'] ?? null;
    if (!$rule_id) {
        sendResponse('error', 'rule_id is required', null, 400);
    }

    $stmt = $conn->prepare("SELECT * FROM rule_config WHERE rule_id = ?");
    $stmt->bind_param("s", $rule_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        sendResponse('error', 'Rule not found', null, 404);
    }

    $rule = $result->fetch_assoc();
    logUserAction(null, 'System', 'GET_RULE', "Retrieved rule: $rule_id", '/api/rule', 'GET', ['rule_id' => $rule_id], 200, $rule, $ip_address, $user_agent);
    sendResponse('success', 'Rule retrieved', $rule);
}

// ✅ **Update Rule**
function updateRule($conn, $ip_address, $user_agent) {
    $input = json_decode(file_get_contents("php://input"), true);
    
    if (empty($input['rule_id'])) {
        sendResponse('error', 'rule_id is required', null, 400);
    }

    $rule_id = $input['rule_id'];
    
    // Verify rule exists
    $stmt = $conn->prepare("SELECT * FROM rule_config WHERE rule_id = ?");
    $stmt->bind_param("s", $rule_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        sendResponse('error', 'Rule not found', null, 404);
    }
    
    $current_rule = $result->fetch_assoc();
    
    // Prepare update fields
    $update_fields = [];
    $params = [];
    $types = '';
    
    if (isset($input['rule_name'])) {
        $update_fields[] = "rule_name = ?";
        $params[] = $input['rule_name'];
        $types .= 's';
    }
    
    if (isset($input['rule_value'])) {
        $update_fields[] = "rule_value = ?";
        $params[] = $input['rule_value'];
        $types .= 's';
    }
    
    if (isset($input['description'])) {
        $update_fields[] = "description = ?";
        $params[] = $input['description'];
        $types .= 's';
    }
    
    if (empty($update_fields)) {
        sendResponse('error', 'No fields to update', null, 400);
    }
    
    $update_fields[] = "last_updated = NOW()";
    $params[] = $rule_id;
    $types .= 's';
    
    $sql = "UPDATE rule_config SET " . implode(', ', $update_fields) . " WHERE rule_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    
    if ($stmt->execute()) {
        // Return updated rule
        $stmt = $conn->prepare("SELECT * FROM rule_config WHERE rule_id = ?");
        $stmt->bind_param("s", $rule_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $updated_rule = $result->fetch_assoc();
        
        logUserAction(null, 'System', 'UPDATE_RULE', "Updated rule: $rule_id", '/api/rule', 'PUT', $input, 200, $updated_rule, $ip_address, $user_agent);
        sendResponse('success', 'Rule updated', $updated_rule);
    } else {
        logUserAction(null, 'System', 'UPDATE_RULE_FAILED', "Failed to update rule: $rule_id", '/api/rule', 'PUT', $input, 500, ['error' => $conn->error], $ip_address, $user_agent);
        sendResponse('error', 'Failed to update rule', null, 500);
    }
}

// ✅ **Delete Rule**
function deleteRule($conn, $ip_address, $user_agent) {
    $input = json_decode(file_get_contents("php://input"), true);
    $rule_id = $input['rule_id'] ?? null;
    
    if (!$rule_id) {
        sendResponse('error', 'rule_id is required in request body', null, 400);
    }

    // Verify rule exists
    $stmt = $conn->prepare("SELECT * FROM rule_config WHERE rule_id = ?");
    $stmt->bind_param("s", $rule_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        sendResponse('error', 'Rule not found', null, 404);
    }
    
    $rule_to_delete = $result->fetch_assoc();

    // Delete the rule
    $stmt = $conn->prepare("DELETE FROM rule_config WHERE rule_id = ?");
    $stmt->bind_param("s", $rule_id);

    if ($stmt->execute()) {
        logUserAction(null, 'System', 'DELETE_RULE', "Deleted rule: $rule_id", '/api/rule', 'DELETE', ['rule_id' => $rule_id], 200, $rule_to_delete, $ip_address, $user_agent);
        sendResponse('success', 'Rule deleted', $rule_to_delete);
    } else {
        logUserAction(null, 'System', 'DELETE_RULE_FAILED', "Failed to delete rule: $rule_id", '/api/rule', 'DELETE', ['rule_id' => $rule_id], 500, ['error' => $conn->error], $ip_address, $user_agent);
        sendResponse('error', 'Failed to delete rule', null, 500);
    }
}

// ✅ **Handle API Requests**
switch ($method) {
    case 'GET':
        if (isset($_GET['rule_id'])) {
            getRuleById($conn, $ip_address, $user_agent);
        } else {
            listRules($conn, $ip_address, $user_agent);
        }
        break;

    case 'POST':
        createRule($conn, $ip_address, $user_agent);
        break;

    case 'PUT':
        updateRule($conn, $ip_address, $user_agent);
        break;

    case 'DELETE':
        deleteRule($conn, $ip_address, $user_agent);
        break;

    default:
        sendResponse('error', 'Invalid request method', null, 405);
}
?>