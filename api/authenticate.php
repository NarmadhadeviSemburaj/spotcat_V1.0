<?php
session_start();
include_once __DIR__ . '/../db_config.php';
include 'log_api.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email_or_mobile = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email_or_mobile) || empty($password)) {
        $_SESSION['error'] = "Email/Mobile and Password are required.";
        header("Location: ../index.php"); // Changed to go up one level
        exit();
    }

    // Determine if input is email or mobile number
    $is_email = filter_var($email_or_mobile, FILTER_VALIDATE_EMAIL);
    $is_mobile = preg_match('/^[6-9]\d{9}$/', $email_or_mobile);

    if (!$is_email && !$is_mobile) {
        $_SESSION['error'] = "Invalid email or mobile number format.";
        header("Location: ../index.php"); // Changed to go up one level
        exit();
    }

    // Fetch user details securely
    $sql = "SELECT employee_id, emp_name, is_admin, password, email, mobile_number FROM employee WHERE email = ? OR mobile_number = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email_or_mobile, $email_or_mobile);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $stored_password = $row['password'];

        // Migrate MD5 Passwords to Bcrypt
        if (strlen($stored_password) === 32 && ctype_xdigit($stored_password)) {
            if (md5($password) === $stored_password) {
                // Convert MD5 to bcrypt
                $new_password_hash = password_hash($password, PASSWORD_BCRYPT);
                $update_sql = "UPDATE employee SET password = ? WHERE employee_id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("ss", $new_password_hash, $row['employee_id']);
                $update_stmt->execute();
            } else {
                $_SESSION['error'] = "Invalid email/mobile or password.";
                header("Location: ../index.php"); // Changed to go up one level
                exit();
            }
        } 
        // Verify Bcrypt Password
        elseif (!password_verify($password, $stored_password)) {
            $_SESSION['error'] = "Invalid email/mobile or password.";
            header("Location: ../index.php"); // Changed to go up one level
            exit();
        }

        // Secure Session Handling
        session_regenerate_id(true);
        
        // Store user information in session
        $_SESSION['employee_id'] = $row['employee_id'];
        $_SESSION['user'] = $row['emp_name'];
        $_SESSION['is_admin'] = (int)$row['is_admin'] === 1;
        $_SESSION['last_activity'] = time();

        // Log the login action
        logUserAction(
            $row['employee_id'],
            $row['emp_name'],
            'login',
            'User logged in successfully',
            $_SERVER['REQUEST_URI'],
            $_SERVER['REQUEST_METHOD'],
            ['email' => $email_or_mobile],
            200,
            ['message' => 'Login successful'],
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT']
        );

        header("Location: ../home.php"); // Changed to go up one level
        exit();
    } else {
        // Log failed login attempt
        logUserAction(
            null,
            $email_or_mobile,
            'login_failed',
            'Failed login attempt',
            $_SERVER['REQUEST_URI'],
            $_SERVER['REQUEST_METHOD'],
            ['email' => $email_or_mobile],
            401,
            ['message' => 'Invalid credentials'],
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT']
        );

        $_SESSION['error'] = "Invalid email/mobile or password.";
        header("Location: ../index.php"); // Changed to go up one level
        exit();
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: ../index.php"); // Changed to go up one level
    exit();
}
?>