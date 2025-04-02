<?php
session_start();
include_once __DIR__ . '/../db_config.php';
include_once __DIR__ . '/log_api.php';

// Simple wrapper function for basic logging
function log_message($message) {
    global $conn;
    
    $sql = "INSERT INTO `log` (
        `username`,
        `action_type`,
        `action_description`,
        `ip_address`,
        `user_agent`
    ) VALUES (?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Failed to prepare log statement: " . $conn->error);
        return false;
    }
    
    $username = isset($_SESSION['username']) ? $_SESSION['username'] : 'guest';
    $action_type = 'page_access';
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    
    $stmt->bind_param(
        "sssss",
        $username,
        $action_type,
        $message,
        $ip_address,
        $user_agent
    );
    
    $result = $stmt->execute();
    if (!$result) {
        error_log("Failed to log message: " . $stmt->error);
    }
    
    $stmt->close();
    return $result;
}

log_message("Accessed forgot password page");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Staff Management System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .password-container {
            max-width: 500px;
            margin: 5% auto;
        }
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body class="bg-light">
<div class="container password-container">
    <div class="card p-4 shadow">
        <div class="logo">
            <h3 class="text-primary">Staff Management System</h3>
        </div>
        <h4 class="text-center mb-4">Forgot Password</h4>
        
        <?php
        if (isset($_SESSION['error'])) {
            log_message("Displaying error message: " . $_SESSION['error']);
            echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['error']) . '</div>';
            unset($_SESSION['error']);
        }
        if (isset($_SESSION['success'])) {
            log_message("Displaying success message: " . $_SESSION['success']);
            echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success']) . '</div>';
            unset($_SESSION['success']);
        }
        ?>
        
        <form action="process_forgot_password.php" method="POST" id="forgotForm">
            <div class="mb-3">
                <label for="email" class="form-label">Registered Email Address</label>
                <input type="email" id="email" name="email" class="form-control" required
                       placeholder="Enter your company email">
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2 mb-3">Send Reset Link</button>
            <div class="text-center">
				<a href="../index.php" class="text-decoration-none">Back to Login</a>
			</div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>