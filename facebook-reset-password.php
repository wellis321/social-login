<?php
/**
 * Facebook Reset Password
 * Set new password with valid token
 */

require_once __DIR__ . '/includes/security.php';

$error = '';
$success = '';
$token = $_GET['token'] ?? '';
$valid_token = false;
$user_email = '';
$user_id = null;

if (empty($token)) {
    $error = "Invalid or missing reset token";
} else {
    // Verify token using prepared statement
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT id, email FROM users WHERE reset_token = ? AND reset_token_expires > NOW() AND platform = 'facebook'");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $valid_token = true;
        $user_data = $result->fetch_assoc();
        $user_id = $user_data['id'];
        $user_email = $user_data['email'];
    } else {
        $error = "This reset link has expired or is invalid. Please request a new one.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid_token) {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $error = "Invalid security token. Please refresh the page and try again.";
    } else {
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
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $user_id_safe = intval($user_id);

            // Update password and clear reset token using prepared statement
            $stmt = $conn->prepare("UPDATE users SET password_hash = ?, reset_token = NULL, reset_token_expires = NULL WHERE id = ? AND reset_token = ? AND platform = 'facebook'");
            $stmt->bind_param("sis", $password_hash, $user_id_safe, $token);
            $update_result = $stmt->execute();

            if ($update_result) {
                // Log activity
                if (function_exists('logActivity') && $user_id) {
                    @logActivity($user_id, 'facebook', 'password_reset', "Password successfully reset");
                }
                $success = "Your password has been reset successfully! You can now log in with your new password.";
            } else {
                error_log("Password reset update failed: " . $conn->error);
                $error = "Failed to update password. Please try again or request a new reset link.";
            }
        }
    }
}

$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Facebook</title>
    <link rel="stylesheet" href="assets/css/facebook.css">
</head>
<body>
    <div class="facebook-container centered">
        <div class="facebook-login-card">
            <div class="facebook-logo-small">facebook</div>
            <h1>Reset Your Password</h1>

            <?php if ($error): ?>
                <div class="error-box">
                    <p>⚠ <?= htmlspecialchars($error) ?></p>
                </div>
                <div class="footer-links">
                    <a href="facebook-forgot-password.php">Request New Reset Link</a> •
                    <a href="facebook-login.php">Back to Login</a>
                </div>
            <?php elseif ($success): ?>
                <div class="success-box">
                    <p>✓ <?= htmlspecialchars($success) ?></p>
                </div>
                <a href="facebook-login.php" class="btn btn-primary btn-block">Go to Login</a>
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
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
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
                    <a href="facebook-login.php">Cancel and return to login</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
