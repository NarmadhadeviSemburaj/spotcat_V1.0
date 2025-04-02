<?php
session_start();
include_once __DIR__ . '/../db_config.php';
include 'log_api.php';

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    // Log using your existing logUserAction function
    logUserAction(null, 'guest', 'password_reset', 'Reset attempt with non-POST method', $_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
    header("Location: forgot_password.php");
    exit();
}

$token = $_POST['token'];
$new_password = $_POST['new_password'];
$confirm_password = $_POST['confirm_password'];

// Basic validation
if ($new_password !== $confirm_password) {
    logUserAction(null, 'guest', 'password_reset', "Passwords don't match for token: $token", $_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
    $_SESSION['error'] = "Passwords do not match";
    header("Location: reset_password.php?token=" . urlencode($token));
    exit();
}

if (strlen($new_password) < 8) {
    logUserAction(null, 'guest', 'password_reset', "Password too short for token: $token", $_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
    $_SESSION['error'] = "Password must be at least 8 characters";
    header("Location: reset_password.php?token=" . urlencode($token));
    exit();
}

try {
    logUserAction(null, 'guest', 'password_reset', "Starting reset process for token: $token", $_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
    $conn->begin_transaction();
    
    // Verify session email matches token
    if (empty($_SESSION['reset_email'])) {
        logUserAction(null, 'guest', 'password_reset', "Session expired for token: $token", $_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
        throw new Exception("Session expired - please request a new reset link");
    }
    
    $email = $_SESSION['reset_email'];
    $current_time = date('Y-m-d H:i:s');
    logUserAction(null, $email, 'password_reset', "Processing reset for token: $token", $_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
    
    // Verify token is still valid
    $stmt = $conn->prepare("SELECT email FROM password_resets WHERE token = ? AND email = ? AND expiry > ? AND is_used = 0");
    $stmt->bind_param("sss", $token, $email, $current_time);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        logUserAction(null, $email, 'password_reset', "Invalid/expired token: $token", $_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
        throw new Exception("Invalid or expired token");
    }
    
    // Update password
    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
    $update_stmt = $conn->prepare("UPDATE employee SET password = ? WHERE email = ?");
    $update_stmt->bind_param("ss", $hashed_password, $email);
    $update_stmt->execute();
    logUserAction(null, $email, 'password_update', "Password updated successfully", $_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
    
    // Mark token as used
    $mark_used_stmt = $conn->prepare("UPDATE password_resets SET is_used = 1 WHERE token = ?");
    $mark_used_stmt->bind_param("s", $token);
    $mark_used_stmt->execute();
    logUserAction(null, $email, 'token_usage', "Token marked as used: $token", $_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
    
    // Clean up
    unset($_SESSION['reset_email']);
    $conn->commit();
    
    logUserAction(null, $email, 'password_reset', "Password reset completed successfully", $_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
    $_SESSION['success'] = "Password updated successfully! Please login with your new password.";
    header("Location:../index.php");
    exit();
    
} catch (Exception $e) {
    $conn->rollback();
    error_log("Password reset failed: " . $e->getMessage());
    logUserAction(null, $_SESSION['reset_email'] ?? 'guest', 'password_reset_error', "Failed: " . $e->getMessage(), $_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
    $_SESSION['error'] = "Error: " . $e->getMessage();
    header("Location: forgot_password.php");
    exit();
}