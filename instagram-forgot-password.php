<?php
/**
 * Instagram Forgot Password
 * Request password reset email
 */

session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email']);

    if (empty($email)) {
        $error = "Email is required";
    } elseif (!validateEmail($email)) {
        $error = "Please enter a valid email address";
    } else {
        $conn = getDbConnection();
        $email_safe = $conn->real_escape_string($email);

        // Check if account exists
        $result = $conn->query("SELECT id FROM users WHERE email = '$email_safe' AND platform = 'instagram'");

        if ($result->num_rows > 0) {
            // Generate reset token
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Store token
            $conn->query("UPDATE users SET reset_token = '$token', reset_token_expires = '$expires' WHERE email = '$email_safe' AND platform = 'instagram'");

            // In a real app, you would email this link
            // For training purposes, we'll display it
            $reset_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/instagram-reset-password.php?token=$token";

            $success = "Password reset instructions have been sent! In a real application, this would be emailed to you.";
            $_SESSION['reset_link'] = $reset_link;

            logActivity($result->fetch_assoc()['id'], 'instagram', 'password_reset_request', "Reset token generated for $email");
        } else {
            // For security, show same message even if account doesn't exist
            $success = "If an account exists with this email, you will receive reset instructions.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password • Instagram</title>
    <link rel="stylesheet" href="assets/css/instagram.css">
</head>
<body>
    <div class="instagram-container centered">
        <div class="instagram-login-card">
            <div class="instagram-logo-small">Instagram</div>
            <h1>Forgot Password?</h1>
            <p>Enter your email address and we'll help you reset your password.</p>

            <?php if ($error): ?>
                <div class="error-box">
                    <p>⚠️ <?= htmlspecialchars($error) ?></p>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="success-box">
                    <p>✓ <?= htmlspecialchars($success) ?></p>
                    <?php if (isset($_SESSION['reset_link'])): ?>
                        <div class="info-box" style="margin-top: 16px;">
                            <h3>📧 Training Mode - Password Reset Link</h3>
                            <p>In a real application, this link would be emailed to you. Click below to reset your password:</p>
                            <a href="<?= htmlspecialchars($_SESSION['reset_link']) ?>" class="btn btn-primary btn-block" style="margin-top: 12px;">Reset My Password</a>
                            <p style="margin-top: 12px; font-size: 12px; color: #737373;">This link expires in 1 hour.</p>
                        </div>
                        <?php unset($_SESSION['reset_link']); ?>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="help-box">
                    <h3>🔒 How Password Reset Works</h3>
                    <ul>
                        <li>Enter your email address</li>
                        <li>We'll send you a secure reset link</li>
                        <li>Click the link to create a new password</li>
                        <li>The link expires after 1 hour for security</li>
                    </ul>
                    <p><strong>Security tip:</strong> Never share password reset links with anyone!</p>
                </div>

                <form method="POST">
                    <div class="form-group">
                        <label for="email">Email address</label>
                        <input type="email" id="email" name="email"
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                               placeholder="your@email.com" required autofocus>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Send Reset Instructions</button>
                </form>
            <?php endif; ?>

            <div class="footer-links">
                <a href="instagram-login.php">← Back to Login</a> •
                <a href="instagram-forgot-username.php">Forgot Username?</a>
            </div>
        </div>
    </div>
</body>
</html>
