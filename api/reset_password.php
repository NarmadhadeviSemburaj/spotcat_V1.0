<?php
session_start();
include_once __DIR__ . '/../db_config.php';
include 'log_api.php';

// Debugging - check if token exists
if (empty($_GET['token'])) {
    logUserAction(null, 'guest', 'password_reset', 'Reset page accessed without token', $_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
    $_SESSION['error'] = "Invalid reset link - no token provided";
    error_log("Reset password accessed without token");
    header("Location: forgot_password.php");
    exit();
}

$token = $_GET['token'];
logUserAction(null, 'guest', 'password_reset', "Reset page accessed with token: $token", $_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
$current_time = date('Y-m-d H:i:s');

// Check token validity
try {
    logUserAction(null, 'guest', 'token_validation', "Starting validation for token: $token", $_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
    $stmt = $conn->prepare("SELECT email FROM password_resets WHERE token = ? AND expiry > ? AND is_used = 0");
    $stmt->bind_param("ss", $token, $current_time);
    
    if (!$stmt->execute()) {
        logUserAction(null, 'guest', 'token_validation', "Query failed for token: $token - Error: " . $stmt->error, $_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
        throw new Exception("Token validation query failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        logUserAction(null, 'guest', 'token_validation', "Invalid/expired token: $token", $_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
        error_log("Invalid or expired token: $token");
        $_SESSION['error'] = "Invalid or expired reset link";
        header("Location: forgot_password.php");
        exit();
    }

    $reset_request = $result->fetch_assoc();
    $_SESSION['reset_email'] = $reset_request['email'];
    logUserAction(null, $reset_request['email'], 'token_validation', "Token validated for email", $_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
    
    error_log("Valid token for email: " . $reset_request['email']);
    
} catch (Exception $e) {
    logUserAction(null, 'guest', 'token_validation', "Error for token: $token - " . $e->getMessage(), $_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
    error_log("Token validation error: " . $e->getMessage());
    $_SESSION['error'] = "System error during token validation";
    header("Location: forgot_password.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .reset-container { max-width: 500px; margin: 50px auto; }
    </style>
</head>
<body>
    <div class="container reset-container">
        <div class="card shadow p-4">
            <h2 class="text-center mb-4">Reset Your Password</h2>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            
            <form action="process_reset_password.php" method="POST">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                
                <div class="mb-3">
                    <label for="new_password" class="form-label">New Password</label>
                    <input type="password" class="form-control" id="new_password" name="new_password" required minlength="8">
                </div>
                
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>
                
                <button type="submit" class="btn btn-primary w-100">Reset Password</button>
            </form>
        </div>
    </div>
</body>
</html>