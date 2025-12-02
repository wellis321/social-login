<?php
/**
 * Twitter/X Forgot Username
 * Retrieve username by email
 */

session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$error = '';
$success = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contact_type = $_POST['contact_type'] ?? 'email';
    $contact_value = sanitizeInput($_POST['contact_value']);

    if (empty($contact_value)) {
        $error = $contact_type === 'email' ? "Email is required" : "Phone number is required";
    } elseif ($contact_type === 'email' && !validateEmail($contact_value)) {
        $error = "Please enter a valid email address";
    } else {
        $conn = getDbConnection();
        $contact_safe = $conn->real_escape_string($contact_value);

        $result = $conn->query("SELECT username FROM users WHERE email = '$contact_safe' AND platform = 'twitter'");

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $username = $user['username'];
            $success = "Account found! Your username is displayed below.";
        } else {
            $error = $contact_type === 'email'
                ? "No Twitter/X account found with this email address."
                : "No Twitter/X account found with this phone number.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Username - X</title>
    <link rel="stylesheet" href="assets/css/twitter.css">
</head>
<body>
    <div class="twitter-container centered">
        <div class="twitter-login-card">
            <div class="twitter-logo-small">𝕏</div>
            <h1>Forgot Your Username?</h1>
            <p>Enter your email address or phone number to find your username.</p>

            <?php if ($error): ?>
                <div class="error-box">
                    <p>⚠ <?= htmlspecialchars($error) ?></p>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="success-box">
                    <p>✓ <?= htmlspecialchars($success) ?></p>
                    <div class="info-box" style="margin-top: 16px;">
                        <h3>✉ Your Username</h3>
                        <p style="font-size: 24px; font-weight: 700; color: #1d9bf0; margin: 16px 0;">@<?= htmlspecialchars($username) ?></p>
                        <p style="margin-top: 12px;">You can now use this username to log in!</p>
                    </div>
                </div>
                <a href="twitter-login.php" class="btn btn-primary btn-block">Go to Login</a>
            <?php else: ?>
                <div class="help-box">
                    <h3>👤 Find Your Username</h3>
                    <p>Enter the email address or phone number you used when creating your X account. We'll show you the username associated with that contact information.</p>
                    <p><strong>Remember:</strong> Your username appears as @username on X</p>
                </div>

                <form method="POST">
                    <div class="form-group">
                        <label>Search by:</label>
                        <select name="contact_type" id="contact_type" onchange="updatePlaceholder()">
                            <option value="email">Email address</option>
                            <option value="phone">Phone number</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="contact_value" id="contact_label">Email address</label>
                        <input type="text" id="contact_value" name="contact_value"
                               value="<?= htmlspecialchars($_POST['contact_value'] ?? '') ?>"
                               placeholder="your@email.com" required autofocus>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Find My Username</button>
                </form>

                <script>
                function updatePlaceholder() {
                    const type = document.getElementById('contact_type').value;
                    const input = document.getElementById('contact_value');
                    const label = document.getElementById('contact_label');

                    if (type === 'email') {
                        label.textContent = 'Email address';
                        input.placeholder = 'your@email.com';
                        input.type = 'email';
                    } else {
                        label.textContent = 'Phone number';
                        input.placeholder = '+1 234 567 8900';
                        input.type = 'tel';
                    }
                }
                </script>
            <?php endif; ?>

            <div class="footer-links">
                <a href="twitter-login.php">← Back to Login</a> •
                <a href="twitter-forgot-password.php">Forgot Password?</a>
            </div>
        </div>
    </div>
</body>
</html>
