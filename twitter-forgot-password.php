<?php
/**
 * Twitter/X Forgot Password
 * Request password reset email
 */

session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contact_value = sanitizeInput($_POST['contact_value'] ?? '');

    if (empty($contact_value)) {
        $error = "Email address or phone number is required";
    } else {
        $conn = getDbConnection();
        $input = trim($contact_value);

        // Try to normalize if it's a phone number (but don't fail if functions don't exist)
        if (function_exists('isPhoneNumber') && function_exists('normalizePhone')) {
            if (isPhoneNumber($input)) {
                $input = normalizePhone($input);
            }
        }

        $input_safe = $conn->real_escape_string($input);

        // Try exact match first
        $result = $conn->query("SELECT id, email FROM users WHERE email = '$input_safe' AND platform = 'twitter'");

        // If no match and it might be a phone, try to find by normalized phone
        if ($result && $result->num_rows === 0) {
            // Try phone number matching if functions exist
            if (function_exists('isPhoneNumber') && function_exists('normalizePhone')) {
                if (isPhoneNumber($contact_value)) {
                    $normalized_input = normalizePhone($contact_value);
                    $all_users = $conn->query("SELECT id, email FROM users WHERE platform = 'twitter'");

                    if ($all_users) {
                        while ($user = $all_users->fetch_assoc()) {
                            if (isPhoneNumber($user['email'])) {
                                $normalized_stored = normalizePhone($user['email']);
                                if ($normalized_stored === $normalized_input) {
                                    $result = $conn->query("SELECT id, email FROM users WHERE id = " . intval($user['id']));
                                    break;
                                }
                            }
                        }
                    }
                }
            }
        }

        if ($result && $result->num_rows > 0) {
            $user_data = $result->fetch_assoc();
            $user_id = $user_data['id'];
            $user_email = $user_data['email'];

            // Generate reset token
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Store token
            $update_result = $conn->query("UPDATE users SET reset_token = '$token', reset_token_expires = '$expires' WHERE id = $user_id");

            if ($update_result) {
                $reset_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/twitter-reset-password.php?token=$token";

                $success = "Password reset instructions have been sent! In a real application, this would be emailed to you.";
                $_SESSION['reset_link'] = $reset_link;
                $_SESSION['reset_email'] = $user_email;

                if (function_exists('logActivity')) {
                    @logActivity($user_id, 'twitter', 'password_reset_request', "Reset token generated for $user_email");
                }
            }
        } else {
            // For security, show same message even if account doesn't exist
            $success = "If an account exists with this email/phone, you will receive reset instructions.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - X</title>
    <link rel="stylesheet" href="assets/css/twitter.css">
</head>
<body>
    <div class="twitter-container centered">
        <div class="twitter-login-card">
            <div class="twitter-logo-small">𝕏</div>
            <h1>Forgot Password?</h1>
            <p>Enter your email address or phone number and we'll help you reset your password.</p>

            <?php if ($error): ?>
                <div class="error-box">
                    <p>⚠ <?= htmlspecialchars($error) ?></p>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="success-box">
                    <p>✓ <?= htmlspecialchars($success) ?></p>
                    <?php if (isset($_SESSION['reset_link'])): ?>
                        <div class="info-box" style="margin-top: 16px;">
                            <h3>✉ Training Mode - Password Reset Link</h3>
                            <p>In a real application, this link would be emailed to you. Click below to reset your password:</p>
                            <a href="<?= htmlspecialchars($_SESSION['reset_link']) ?>" class="btn btn-primary btn-block" style="margin-top: 12px;">Reset My Password</a>
                            <p style="margin-top: 12px; font-size: 12px; color: #71767b;">This link expires in 1 hour.</p>
                        </div>
                        <?php unset($_SESSION['reset_link']); ?>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="help-box">
                    <h3>🔒 How Password Reset Works</h3>
                    <ul>
                        <li>Enter your email address or phone number</li>
                        <li>We'll send you a secure reset link</li>
                        <li>Click the link to create a new password</li>
                        <li>The link expires after 1 hour for security</li>
                    </ul>
                    <p><strong>Security tip:</strong> Never share password reset links with anyone!</p>
                </div>

                <form method="POST">
                    <div class="form-group">
                        <label for="contact_value">Email address or phone number</label>
                        <input type="text" id="contact_value" name="contact_value"
                               value="<?= htmlspecialchars($_POST['contact_value'] ?? '') ?>"
                               placeholder="Email or phone number" required autofocus>
                        <small>Enter the email address or phone number you used when signing up</small>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Send Reset Instructions</button>
                </form>
            <?php endif; ?>

            <div class="footer-links">
                <a href="twitter-login.php">← Back to Login</a> •
                <a href="twitter-forgot-username.php">Forgot Username?</a>
            </div>
        </div>
    </div>
</body>
</html>
