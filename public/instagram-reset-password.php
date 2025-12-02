<?php
/**
 * Instagram Reset Password
 * Set new password with valid token
 */

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$error = '';
$success = '';
$token = $_GET['token'] ?? '';
$valid_token = false;
$user_email = '';

if (empty($token)) {
    $error = "Invalid or missing reset token";
} else {
    // Verify token
    $conn = getDbConnection();
    $token_safe = $conn->real_escape_string($token);

    $result = $conn->query("SELECT id, email FROM users WHERE reset_token = '$token_safe' AND reset_token_expires > NOW() AND platform = 'instagram'");

    if ($result->num_rows > 0) {
        $valid_token = true;
        $user_data = $result->fetch_assoc();
        $user_email = $user_data['email'];
    } else {
        $error = "This reset link has expired or is invalid. Please request a new one.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid_token) {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($password)) {
        $error = "Password is required";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } else {
        $conn = getDbConnection();
        $token_safe = $conn->real_escape_string($token);
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Update password and clear reset token
        $conn->query("UPDATE users SET password_hash = '$password_hash', reset_token = NULL, reset_token_expires = NULL WHERE reset_token = '$token_safe' AND platform = 'instagram'");

        logActivity($user_data['id'], 'instagram', 'password_reset', "Password successfully reset for $user_email");

        $success = "Your password has been reset successfully! You can now log in with your new password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password • Instagram</title>
    <link rel="stylesheet" href="assets/css/instagram.css">
</head>
<body>
    <div class="instagram-container centered">
        <div class="instagram-login-card">
            <div class="instagram-logo-small">Instagram</div>
            <h1>Reset Your Password</h1>

            <?php if ($error): ?>
                <div class="error-box">
                    <p>⚠️ <?= htmlspecialchars($error) ?></p>
                </div>
                <div class="footer-links">
                    <a href="instagram-forgot-password.php">Request New Reset Link</a> •
                    <a href="instagram-login.php">Back to Login</a>
                </div>
            <?php elseif ($success): ?>
                <div class="success-box">
                    <p>✓ <?= htmlspecialchars($success) ?></p>
                </div>
                <a href="instagram-login.php" class="btn btn-primary btn-block">Go to Login</a>
            <?php else: ?>
                <p>Create a new password for your account.</p>

                <div class="help-box">
                    <h3>🔒 Creating a Strong Password</h3>
                    <ul>
                        <li>Use at least 8 characters</li>
                        <li>Mix uppercase and lowercase letters</li>
                        <li>Include numbers and symbols (!@#$%)</li>
                        <li>Don't reuse old passwords</li>
                        <li>Don't use common words or personal info</li>
                    </ul>
                </div>

                <form method="POST">
                    <div class="form-group">
                        <label for="password">New password</label>
                        <input type="password" id="password" name="password"
                               placeholder="Enter a strong password" required autofocus>
                        <small>Minimum 8 characters</small>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm new password</label>
                        <input type="password" id="confirm_password" name="confirm_password"
                               placeholder="Enter the same password again" required>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Reset Password</button>
                </form>

                <div class="footer-links">
                    <a href="instagram-login.php">Cancel and return to login</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
