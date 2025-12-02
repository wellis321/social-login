<?php
/**
 * Instagram Forgot Username
 * Retrieve username by email
 */

session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$error = '';
$success = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email']);

    if (empty($email)) {
        $error = "Email is required";
    } elseif (!validateEmail($email)) {
        $error = "Please enter a valid email address";
    } else {
        $conn = getDbConnection();
        $email_safe = $conn->real_escape_string($email);

        $result = $conn->query("SELECT username FROM users WHERE email = '$email_safe' AND platform = 'instagram'");

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $username = $user['username'];
            $success = "Account found! Your username is displayed below.";
        } else {
            $error = "No Instagram account found with this email address.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Username • Instagram</title>
    <link rel="stylesheet" href="assets/css/instagram.css">
</head>
<body>
    <div class="instagram-container centered">
        <div class="instagram-login-card">
            <div class="instagram-logo-small">Instagram</div>
            <h1>Forgot Your Username?</h1>
            <p>Enter your email address to find your username.</p>

            <?php if ($error): ?>
                <div class="error-box">
                    <p>⚠️ <?= htmlspecialchars($error) ?></p>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="success-box">
                    <p>✓ <?= htmlspecialchars($success) ?></p>
                    <div class="info-box" style="margin-top: 16px;">
                        <h3>👤 Your Username</h3>
                        <p style="font-size: 24px; font-weight: 700; color: #e1306c; margin: 16px 0;">@<?= htmlspecialchars($username) ?></p>
                        <p style="margin-top: 12px;">You can now use this username to log in!</p>
                    </div>
                </div>
                <a href="instagram-login.php" class="btn btn-primary btn-block">Go to Login</a>
            <?php else: ?>
                <div class="help-box">
                    <h3>👤 Find Your Username</h3>
                    <p>Enter the email address you used when creating your Instagram account. We'll show you the username associated with that email.</p>
                    <p><strong>Remember:</strong> Your username appears as @username on Instagram</p>
                </div>

                <form method="POST">
                    <div class="form-group">
                        <label for="email">Email address</label>
                        <input type="email" id="email" name="email"
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                               placeholder="your@email.com" required autofocus>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Find My Username</button>
                </form>
            <?php endif; ?>

            <div class="footer-links">
                <a href="instagram-login.php">← Back to Login</a> •
                <a href="instagram-forgot-password.php">Forgot Password?</a>
            </div>
        </div>
    </div>
</body>
</html>
