<?php
/**
 * Facebook Forgot Password
 * Request password reset email
 */

require_once __DIR__ . '/includes/security.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $error = "Invalid security token. Please refresh the page and try again.";
    } else {
        $contact_value = sanitizeInput($_POST['contact_value'] ?? '');

        if (empty($contact_value)) {
            $error = "Email address or phone number is required";
        } else {
            // Check rate limit (3 attempts per 15 minutes)
            if (!checkRateLimit('password_reset', $contact_value, 3, 900)) {
                $error = "Too many password reset requests. Please wait 15 minutes before trying again.";
            } else {
        try {
            $conn = getDbConnection();

            if (!$conn) {
                throw new Exception("Database connection failed");
            }

            // Normalize the input - if it's a phone number, normalize it (same as authenticateUser)
            $input = trim($contact_value);
            if (function_exists('isPhoneNumber') && function_exists('normalizePhone')) {
                if (isPhoneNumber($input)) {
                    $input = normalizePhone($input);
                }
            }
            $input_safe = $conn->real_escape_string($input);

            // Try exact match first
            $result = $conn->query("SELECT id, email FROM users WHERE email = '$input_safe' AND platform = 'facebook'");

            // Check for query errors
            if (!$result) {
                error_log("Database query error in facebook-forgot-password.php: " . $conn->error);
                $error = "A database error occurred. Please try again later.";
            } else {
                // If exact match failed and input looks like a phone, try normalized match (same as authenticateUser)
                if ($result->num_rows === 0 && function_exists('isPhoneNumber') && isPhoneNumber($contact_value)) {
                    // Get all users for this platform and check normalized phone numbers
                    $all_users = $conn->query("SELECT id, email FROM users WHERE platform = 'facebook'");
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
                        if (!$result) {
                            error_log("Database query error in facebook-forgot-password.php: " . $conn->error);
                            $error = "A database error occurred. Please try again later.";
                        }
                    }
                }

                if (!$error && $result && $result->num_rows > 0) {
                    $user_data = $result->fetch_assoc();
                    $user_id = $user_data['id'];
                    $user_email = $user_data['email'];

                    // Generate reset token
                    $token = bin2hex(random_bytes(32));
                    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

                    // Store token
                    $update_result = $conn->query("UPDATE users SET reset_token = '$token', reset_token_expires = '$expires' WHERE id = $user_id");
                    if (!$update_result) {
                        error_log("Database update error in facebook-forgot-password.php: " . $conn->error);
                        $error = "A database error occurred. Please try again later.";
                    } else {
                        // In a real app, you would email this link
                        // For training purposes, we'll display it
                        $reset_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/facebook-reset-password.php?token=$token";

                        $success = "Password reset instructions have been sent! In a real application, this would be emailed to you.";
                        $_SESSION['reset_link'] = $reset_link;
                        $_SESSION['reset_email'] = $user_email;

                        // Log activity (if function exists)
                        if (function_exists('logActivity')) {
                            @logActivity($user_id, 'facebook', 'password_reset_request', "Reset token generated for $user_email");
                        }
                    }
                } else {
                    // For security, show same message even if account doesn't exist
                    if (!$error) {
                        $success = "If an account exists with this email/phone, you will receive reset instructions.";
                    }
                }
            }
            } catch (Exception $e) {
                error_log("Error in facebook-forgot-password.php: " . $e->getMessage());
                $error = "An error occurred. Please try again later.";
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
    <title>Forgot Password - Facebook</title>
    <link rel="stylesheet" href="assets/css/facebook.css">
</head>
<body>
    <div class="facebook-container centered">
        <div class="facebook-login-card">
            <div class="facebook-logo-small">facebook</div>
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
                            <p style="margin-top: 12px; font-size: 12px; color: #65676b;">This link expires in 1 hour.</p>
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
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
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
                <a href="facebook-login.php">← Back to Login</a>
            </div>
        </div>
    </div>
</body>
</html>
