<?php
session_start();
include_once __DIR__ . '/../db_config.php';
include 'log_api.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        logUserAction(
            null,
            $email,
            'forgot_password_request_failed',
            'Invalid email format',
            $_SERVER['REQUEST_URI'],
            'POST',
            ['email' => $email],
            400,
            ['error' => 'Invalid email format'],
            $ip_address,
            $user_agent
        );
        $_SESSION['error'] = "Please enter a valid company email address.";
        header("Location: forgot_password.php");
        exit();
    }

    try {
        $conn->begin_transaction();

        // Check if employee exists with this email
        $stmt = $conn->prepare("SELECT employee_id, emp_name, email, zone_name, cluster_name FROM employee WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows != 1) {
            // Generic message for security
            logUserAction(
                null,
                $email,
                'forgot_password_request_failed',
                'Email not found in employee records',
                $_SERVER['REQUEST_URI'],
                'POST',
                ['email' => $email],
                404,
                ['error' => 'Employee not found'],
                $ip_address,
                $user_agent
            );
            
            $_SESSION['success'] = "If this email exists in our system, you'll receive a reset link shortly.";
            header("Location: forgot_password.php");
            exit();
        }

        $employee = $result->fetch_assoc();
        
        // Delete any existing tokens for this email
        $delete_stmt = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
        $delete_stmt->bind_param("s", $email);
        $delete_stmt->execute();

        // Generate secure token
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', time() + 1800); // 30 minutes expiry

        // Insert new token
        $insert_stmt = $conn->prepare("INSERT INTO password_resets (email, token, expiry) VALUES (?, ?, ?)");
        $insert_stmt->bind_param("sss", $email, $token, $expiry);
        $insert_stmt->execute();
		
			// With this more reliable version:
			$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https://" : "http://";
			$host = $_SERVER['HTTP_HOST'];
			$path = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
			$reset_link = $protocol . $host . $path . '/reset_password.php?token=' . urlencode($token);
					// Send email using PHPMailer
        $mail = new PHPMailer(true);
        try {
            $mail->SMTPDebug = SMTP::DEBUG_OFF;
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'rsubhashinisubha004@gmail.com';
            $mail->Password = 'mbhi gsjg hgio lhzk';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('rsubhashinisubha004@gmail.com', 'Test Management System');
            $mail->addAddress($email, $employee['emp_name']);

            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request - Test Management System';
            $mail->Body = "
                <h3>Password Reset Request</h3>
                <p>Dear {$employee['emp_name']},</p>
                <p>We received a request to reset your password for the Test Management System.</p>
                <p><strong>Employee ID:</strong> {$employee['employee_id']}</p>
                <p><strong>Zone:</strong> {$employee['zone_name']}</p>
                <p><strong>Cluster:</strong> {$employee['cluster_name']}</p>
                <p>To reset your password, please click the link below:</p>
                <p><a href='{$reset_link}' style='color: #0d6efd;'>Reset My Password</a></p>
                <p><em>This link will expire in 30 minutes.</em></p>
                <p>If you didn't request this, please contact your system administrator immediately.</p>
                <hr>
                <p style='font-size: 0.8em; color: #6c757d;'>This is an automated message. Please do not reply.</p>
            ";
            
            $mail->AltBody = "Password Reset Link for {$employee['emp_name']}:\n{$reset_link}\n\nExpires in 30 minutes.";

            $mail->send();
            
            logUserAction(
                $employee['employee_id'],
                $employee['emp_name'],
                'forgot_password_request',
                'Password reset link sent to employee',
                $_SERVER['REQUEST_URI'],
                'POST',
                ['employee_id' => $employee['employee_id'], 'email' => $email],
                200,
                ['message' => 'Reset email sent'],
                $ip_address,
                $user_agent
            );

            $conn->commit();
            $_SESSION['success'] = "Password reset link has been sent to your company email.";
        } catch (Exception $e) {
            $conn->rollback();
            
            logUserAction(
                $employee['employee_id'],
                $employee['emp_name'],
                'forgot_password_request_failed',
                'Failed to send reset email to employee',
                $_SERVER['REQUEST_URI'],
                'POST',
                ['employee_id' => $employee['employee_id'], 'email' => $email],
                500,
                ['error' => $mail->ErrorInfo],
                $ip_address,
                $user_agent
            );

            $_SESSION['error'] = "System unable to send email. Please contact your administrator.";
        }
    } catch (Exception $e) {
        $conn->rollback();
        
        logUserAction(
            null,
            $email,
            'forgot_password_request_failed',
            'System error during employee password reset',
            $_SERVER['REQUEST_URI'],
            'POST',
            ['email' => $email],
            500,
            ['error' => $e->getMessage()],
            $ip_address,
            $user_agent
        );

        $_SESSION['error'] = "A system error occurred. Please try again later or contact IT support.";
    }

    header("Location: forgot_password.php");
    exit();
}