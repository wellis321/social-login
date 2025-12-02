<?php
/**
 * Twitter/X Forgot Password
 * Request password reset email
 */

session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

// Check if required functions exist
if (!function_exists('isPhoneNumber') || !function_exists('normalizePhone')) {
    error_log("Missing required functions in twitter-forgot-password.php");
}
if (!function_exists('sanitizeInput')) {
    error_log("Missing sanitizeInput function in twitter-forgot-password.php");
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contact_value = function_exists('sanitizeInput') ? sanitizeInput($_POST['contact_value'] ?? '') : trim($_POST['contact_value'] ?? '');

    if (empty($contact_value)) {
        $error = "Email address or phone number is required";
    } else {
        $conn = getDbConnection();

        // Normalize the input - if it's a phone number, normalize it (same as authenticateUser)
        $input = trim($contact_value);
        if (function_exists('isPhoneNumber') && function_exists('normalizePhone')) {
            if (isPhoneNumber($input)) {
                $input = normalizePhone($input);
            }
        }
        $input_safe = $conn->real_escape_string($input);

        // Try exact match first
        $result = $conn->query("SELECT id, email FROM users WHERE email = '$input_safe' AND platform = 'twitter'");

        // If exact match failed and input looks like a phone, try normalized match (same as authenticateUser)
        if ($result && $result->num_rows === 0 && function_exists('isPhoneNumber') && isPhoneNumber($contact_value)) {
            // Get all users for this platform and check normalized phone numbers
            $all_users = $conn->query("SELECT id, email FROM users WHERE platform = 'twitter'");
            $found_user = null;

            if ($all_users) {
                while ($user = $all_users->fetch_assoc()) {
                    $stored_email = $user['email'];
                    if (function_exists('isPhoneNumber') && function_exists('normalizePhone')) {
                        if (isPhoneNumber($stored_email)) {
                            $normalized_stored = normalizePhone($stored_email);
                            if ($normalized_stored === $input) {
                                $found_user = $user;
                                break;
                            }
                        }
                    }
                }
            }

            if ($found_user) {
                $result = $conn->query("SELECT id, email FROM users WHERE id = " . intval($found_user['id']));
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
                // In a real app, you would email this link
                // For training purposes, we'll display it
                $reset_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/twitter-reset-password.php?token=$token";

                $success = "Password reset instructions have been sent! In a real application, this would be emailed to you.";
                $_SESSION['reset_link'] = $reset_link;
                $_SESSION['reset_email'] = $user_email;

                // Log activity (if function exists)
                if (function_exists('logActivity')) {
                    @logActivity($user_id, 'twitter', 'password_reset_request', "Reset token generated for $user_email");
                }
            } else {
                error_log("Database update error in twitter-forgot-password.php: " . $conn->error);
                $error = "A database error occurred. Please try again later.";
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
                    <p><span class="icon-warning">⚠</span> <?= htmlspecialchars($error) ?></p>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="success-box">
                    <p><span class="icon-check">✓</span> <?= htmlspecialchars($success) ?></p>
                    <?php if (isset($_SESSION['reset_link'])): ?>
                        <div class="info-box" style="margin-top: 16px;">
                            <h3><span class="icon-email">✉</span> Training Mode - Password Reset Link</h3>
                            <p>In a real application, this link would be emailed to you. Click below to reset your password:</p>
                            <a href="<?= htmlspecialchars($_SESSION['reset_link']) ?>" class="btn btn-primary btn-block" style="margin-top: 12px;">Reset My Password</a>
                            <p style="margin-top: 12px; font-size: 12px; color: #71767b;">This link expires in 1 hour.</p>
                        </div>
                        <?php unset($_SESSION['reset_link']); ?>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="help-box">
                    <h3><span class="icon-lock">🔒</span> How Password Reset Works</h3>
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
