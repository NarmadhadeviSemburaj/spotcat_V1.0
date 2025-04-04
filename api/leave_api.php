<?php
header("Content-Type: application/json");

// Enable detailed error reporting (disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database Configuration
$host = "localhost";
$user = "root";
$password = "";
$dbname = "spotcat_db";

// Create connection
$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    http_response_code(500);
    die(json_encode([
        "error" => "Database connection failed",
        "details" => $conn->connect_error // Only for development
    ]));
}

// Security headers
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");

// Helper functions
function generateId($prefix) {
    return $prefix . '_' . time() . '_' . rand(100, 999);
}

function validateInput($data) {
    if ($data === null) {
        return null;
    }
    
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            $data[$key] = validateInput($value);
        }
        return $data;
    }
    
    // Handle empty strings and other values
    $data = is_string($data) ? trim($data) : $data;
    return htmlspecialchars(strip_tags($data), ENT_QUOTES, 'UTF-8');
}

function isAdmin($employeeId) {
    global $conn;
    $stmt = $conn->prepare("SELECT is_admin FROM employee WHERE employee_id = ?");
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        return false;
    }
    
    $stmt->bind_param("s", $employeeId);
    if (!$stmt->execute()) {
        error_log("Execute failed: " . $stmt->error);
        return false;
    }
    
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        return $row['is_admin'] == 1;
    }
    return false;
}

function employeeExists($employeeId) {
    global $conn;
    $stmt = $conn->prepare("SELECT 1 FROM employee WHERE employee_id = ?");
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        return false;
    }
    
    $stmt->bind_param("s", $employeeId);
    if (!$stmt->execute()) {
        error_log("Execute failed: " . $stmt->error);
        return false;
    }
    
    $result = $stmt->get_result();
    return $result->num_rows === 1;
}

function calculateDaysNotice($leaveDate) {
    try {
        $currentDate = new DateTime('today');
        $requestedDate = new DateTime($leaveDate);
        
        if ($requestedDate < $currentDate) {
            return -1; // Past date
        }
        
        return $currentDate->diff($requestedDate)->days;
    } catch (Exception $e) {
        error_log("Date calculation error: " . $e->getMessage());
        return -1;
    }
}

// GET - Retrieve leave requests
function handleGetRequest() {
    global $conn;
    
    $employeeId = isset($_GET['employeeId']) ? validateInput($_GET['employeeId']) : null;
    $dcmId = isset($_GET['dcmId']) ? validateInput($_GET['dcmId']) : null;
    $status = isset($_GET['status']) ? validateInput($_GET['status']) : null;
    $fromDate = isset($_GET['fromDate']) ? validateInput($_GET['fromDate']) : null;
    $toDate = isset($_GET['toDate']) ? validateInput($_GET['toDate']) : null;
    $isEmergency = isset($_GET['isEmergency']) ? validateInput($_GET['isEmergency']) : null;
    
    try {
        // Base query with joins
        $query = "
            SELECT 
                lr.*, 
                e.emp_name, 
                e.designation, 
                e.dcm_id, 
                e.dcm_name,
                a.emp_name as approver_name
            FROM leave_request lr
            JOIN employee e ON lr.employee_id = e.employee_id
            LEFT JOIN employee a ON lr.approved_by = a.employee_id
        ";
        
        // Build WHERE conditions
        $conditions = [];
        $params = [];
        $types = "";
        
        if ($employeeId) {
            $conditions[] = "lr.employee_id = ?";
            $params[] = $employeeId;
            $types .= "s";
        }
        
        if ($dcmId) {
            $conditions[] = "e.dcm_id = ?";
            $params[] = $dcmId;
            $types .= "s";
        }
        
        if ($status) {
            $conditions[] = "lr.status = ?";
            $params[] = $status;
            $types .= "s";
        }
        
        if ($fromDate) {
            if (!DateTime::createFromFormat('Y-m-d', $fromDate)) {
                http_response_code(400);
                echo json_encode(["error" => "Invalid from date format. Use YYYY-MM-DD"]);
                return;
            }
            $conditions[] = "lr.leave_date >= ?";
            $params[] = $fromDate;
            $types .= "s";
        }
        
        if ($toDate) {
            if (!DateTime::createFromFormat('Y-m-d', $toDate)) {
                http_response_code(400);
                echo json_encode(["error" => "Invalid to date format. Use YYYY-MM-DD"]);
                return;
            }
            $conditions[] = "lr.leave_date <= ?";
            $params[] = $toDate;
            $types .= "s";
        }
        
        if ($isEmergency !== null) {
            $conditions[] = "lr.is_emergency = ?";
            $params[] = $isEmergency ? 1 : 0;
            $types .= "i";
        }
        
        // Add conditions to query
        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }
        
        // Order by leave date (newest first)
        $query .= " ORDER BY lr.leave_date DESC, lr.created_at DESC";
        
        // Prepare and execute query
        $stmt = $conn->prepare($query);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        $leaveRequests = $result->fetch_all(MYSQLI_ASSOC);
        
        // Calculate days notice for each request
        $currentDate = new DateTime();
        foreach ($leaveRequests as &$request) {
            $leaveDate = new DateTime($request['leave_date']);
            $interval = $currentDate->diff($leaveDate);
            $request['days_notice'] = $interval->days;
        }
        
        // Return results
        echo json_encode([
            "success" => true,
            "count" => count($leaveRequests),
            "data" => $leaveRequests
        ]);
        
    } catch (Exception $e) {
        error_log("Get leave requests error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(["error" => "Failed to retrieve leave requests"]);
    }
}

// Route handling
$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? validateInput($_GET['action']) : '';

switch ($method) {
    case 'POST':
        handlePostRequest();
        break;
    case 'GET':
        handleGetRequest();
        break;
    case 'PUT':
        handlePutRequest();
        break;
    case 'DELETE':
        handleDeleteRequest();
        break;
    default:
        http_response_code(405);
        echo json_encode(["error" => "Method not allowed"]);
        break;
}

// POST - Create leave request
function handlePostRequest() {
    global $conn;
    
    // Log raw input for debugging
    $jsonInput = file_get_contents('php://input');
    error_log("Received POST data: " . $jsonInput);
    
    $data = json_decode($jsonInput, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("JSON decode error: " . json_last_error_msg());
        http_response_code(400);
        echo json_encode([
            "error" => "Invalid JSON data",
            "details" => json_last_error_msg()
        ]);
        return;
    }
    
    $data = validateInput($data);
    
    $employeeId = $data['employeeId'] ?? null;
    $leaveDate = $data['leaveDate'] ?? null;
    $reason = $data['reason'] ?? '';
    $isEmergency = filter_var($data['isEmergency'] ?? false, FILTER_VALIDATE_BOOLEAN);
    
    // Validation
    if (!$employeeId || !$leaveDate) {
        http_response_code(400);
        echo json_encode(["error" => "Employee ID and leave date are required"]);
        return;
    }
    
    try {
        // Verify employee exists
        if (!employeeExists($employeeId)) {
            http_response_code(404);
            echo json_encode(["error" => "Employee not found"]);
            return;
        }
        
        // Validate date format
        if (!DateTime::createFromFormat('Y-m-d', $leaveDate)) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid date format. Use YYYY-MM-DD"]);
            return;
        }
        
        // Check for duplicate leave request
        $stmt = $conn->prepare("SELECT * FROM leave_request WHERE employee_id = ? AND leave_date = ?");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("ss", $employeeId, $leaveDate);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            http_response_code(400);
            echo json_encode(["error" => "Leave request already exists for this date"]);
            return;
        }
        
        // Calculate days notice
        $daysNotice = calculateDaysNotice($leaveDate);
        
        if ($daysNotice < 0) {
            http_response_code(400);
            echo json_encode(["error" => "Cannot request leave for past dates"]);
            return;
        }
        
        // Determine status based on business rules
        $status = "Pending";
        $approvedBy = null;
        $requiresApproval = true;
        
        // Automatic approval rules:
        // 1. Not an emergency leave
        // 2. Requested at least 2 full days in advance
        if (!$isEmergency && $daysNotice >= 2) {
            $status = "Approved";
            $approvedBy = null; // Set to NULL for system approvals
            $requiresApproval = false;
            error_log("Leave auto-approved: $employeeId for $leaveDate ($daysNotice days notice)");
        }
        
        // Emergency leaves always need approval
        if ($isEmergency) {
            $status = "Pending";
            $requiresApproval = true;
            error_log("Emergency leave requires approval: $employeeId for $leaveDate");
        }
        
        // Create leave request
        $leaveId = generateId("LEAVE");
        $isEmergencyInt = $isEmergency ? 1 : 0;
        
        if ($status === "Approved") {
            $stmt = $conn->prepare("
                INSERT INTO leave_request 
                (leave_id, employee_id, leave_date, reason, status, is_emergency, approved_by, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, NULL, NOW())
            ");
            $bindParams = [$leaveId, $employeeId, $leaveDate, $reason, $status, $isEmergencyInt];
            $paramTypes = "sssssi";
        } else {
            $stmt = $conn->prepare("
                INSERT INTO leave_request 
                (leave_id, employee_id, leave_date, reason, status, is_emergency, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            $bindParams = [$leaveId, $employeeId, $leaveDate, $reason, $status, $isEmergencyInt];
            $paramTypes = "sssssi";
        }
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param($paramTypes, ...$bindParams);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        // Return success response
        echo json_encode([
            "success" => true,
            "message" => "Leave request created successfully",
            "data" => [
                "leaveId" => $leaveId,
                "status" => $status,
                "autoApproved" => !$requiresApproval,
                "isEmergency" => $isEmergency,
                "daysNotice" => $daysNotice,
                "requiresApproval" => $requiresApproval,
                "leaveDate" => $leaveDate,
                "requestDate" => date('Y-m-d')
            ]
        ]);
        
    } catch (Exception $e) {
        error_log("Exception in handlePostRequest: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        
        http_response_code(500);
        echo json_encode([
            "error" => "Failed to create leave request",
            "details" => $e->getMessage() // Only for development
        ]);
    }
}

// PUT - Approve/Reject leave request
function handlePutRequest() {
    global $conn;
    
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(["error" => "Invalid JSON data"]);
        return;
    }
    
    $data = validateInput($data);
    
    $leaveId = $data['leaveId'] ?? null;
    $status = $data['status'] ?? null;
    $approvedBy = $data['approvedBy'] ?? null;
    $rejectionReason = $data['rejectionReason'] ?? null;
    
    // Validation
    if (!$leaveId || !$status || !$approvedBy) {
        http_response_code(400);
        echo json_encode(["error" => "Leave ID, status, and approver ID are required"]);
        return;
    }
    
    // Normalize status
    $status = strtolower($status) === 'approved' ? 'Approved' : 'Rejected';
    
    try {
        // Verify leave request exists
        $stmt = $conn->prepare("SELECT * FROM leave_request WHERE leave_id = ?");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("s", $leaveId);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            http_response_code(404);
            echo json_encode(["error" => "Leave request not found"]);
            return;
        }
        
        $leaveRequest = $result->fetch_assoc();
        
        // Check if already processed
        if ($leaveRequest['status'] !== 'Pending') {
            http_response_code(400);
            echo json_encode(["error" => "Leave request has already been processed"]);
            return;
        }
        
        // Verify approver exists and is admin
        if (!employeeExists($approvedBy)) {
            http_response_code(404);
            echo json_encode(["error" => "Approver not found"]);
            return;
        }
        
        if (!isAdmin($approvedBy)) {
            http_response_code(403);
            echo json_encode(["error" => "Only admins can approve leave requests"]);
            return;
        }
        
        // Update leave request
        if ($status === "Approved") {
            $stmt = $conn->prepare("
                UPDATE leave_request 
                SET status = ?, approved_by = ?, updated_at = NOW(), rejection_reason = NULL
                WHERE leave_id = ?
            ");
            $stmt->bind_param("sss", $status, $approvedBy, $leaveId);
        } else {
            $stmt = $conn->prepare("
                UPDATE leave_request 
                SET status = ?, approved_by = ?, updated_at = NOW(), rejection_reason = ?
                WHERE leave_id = ?
            ");
            $stmt->bind_param("ssss", $status, $approvedBy, $rejectionReason, $leaveId);
        }
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        // Return success response
        echo json_encode([
            "success" => true,
            "message" => "Leave request " . strtolower($status) . " successfully",
            "data" => [
                "leaveId" => $leaveId,
                "status" => $status,
                "approvedBy" => $approvedBy,
                "updatedAt" => date('Y-m-d H:i:s')
            ]
        ]);
        
    } catch (Exception $e) {
        error_log("Update leave request error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            "error" => "Failed to update leave request",
            "details" => $e->getMessage()
        ]);
    }
}

// DELETE - Cancel leave request
function handleDeleteRequest() {
    global $conn;
    
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(["error" => "Invalid JSON data"]);
        return;
    }
    
    $data = validateInput($data);
    
    $leaveId = $data['leaveId'] ?? null;
    $employeeId = $data['employeeId'] ?? null;
    
    if (!$leaveId || !$employeeId) {
        http_response_code(400);
        echo json_encode(["error" => "Leave ID and employee ID are required"]);
        return;
    }
    
    try {
        // Verify leave request exists and belongs to employee
        $stmt = $conn->prepare("SELECT * FROM leave_request WHERE leave_id = ? AND employee_id = ?");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("ss", $leaveId, $employeeId);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            http_response_code(404);
            echo json_encode(["error" => "Leave request not found or doesn't belong to this employee"]);
            return;
        }
        
        $leaveRequest = $result->fetch_assoc();
        
        // Check if already processed
        if ($leaveRequest['status'] !== 'Pending') {
            http_response_code(400);
            echo json_encode(["error" => "Only pending leave requests can be cancelled"]);
            return;
        }
        
        // Check if the leave date is in the future
        $leaveDate = new DateTime($leaveRequest['leave_date']);
        $today = new DateTime('today');
        
        if ($leaveDate <= $today) {
            http_response_code(400);
            echo json_encode(["error" => "Cannot cancel leave for today or past dates"]);
            return;
        }
        
        // Delete the request
        $stmt = $conn->prepare("DELETE FROM leave_request WHERE leave_id = ?");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("s", $leaveId);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        echo json_encode([
            "success" => true,
            "message" => "Leave request cancelled successfully",
            "data" => [
                "leaveId" => $leaveId,
                "employeeId" => $employeeId
            ]
        ]);
        
    } catch (Exception $e) {
        error_log("Cancel leave request error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            "error" => "Failed to cancel leave request",
            "details" => $e->getMessage()
        ]);
    }
}

$conn->close();
?>