<?php
/**
 * Instagram Login Page
 * Authenticate existing users
 */

session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $errors[] = "Please enter both email/username and password";
    } else {
        $user_id = authenticateUser($email, $password, 'instagram');

        if ($user_id) {
            logActivity($user_id, 'instagram', 'login', 'User logged in successfully');

            $_SESSION['user_id'] = $user_id;
            $_SESSION['platform'] = 'instagram';

            header('Location: instagram-dashboard.php');
            exit;
        } else {
            $errors[] = "Sorry, your password was incorrect. Please double-check your password.";
            logActivity(null, 'instagram', 'login_failed', 'Failed login attempt: ' . $email);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login • Instagram</title>
    <link rel="stylesheet" href="assets/css/instagram.css">
</head>
<body>
    <div class="instagram-login-page">
        <div class="login-container">
            <div class="login-card">
                <h1 class="instagram-logo">Instagram</h1>

                <div class="help-box">
                    <h3>🔐 Logging In to Instagram</h3>
                    <p><strong>What you need:</strong></p>
                    <ul>
                        <li>Your email address, phone number, or username</li>
                        <li>Your Instagram password</li>
                    </ul>
                    <p><strong>Security tips:</strong></p>
                    <ul>
                        <li>Make sure you're on the real Instagram website</li>
                        <li>Never share your password with anyone</li>
                        <li>Enable two-factor authentication for extra security</li>
                        <li>Log out on shared or public devices</li>
                    </ul>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="error-box">
                        <?php foreach ($errors as $error): ?>
                            <p>⚠️ <?= htmlspecialchars($error) ?></p>
                        <?php endforeach; ?>
                        <div class="error-help">
                            <p><strong>Trouble logging in?</strong></p>
                            <ul>
                                <li>Check that Caps Lock is turned off</li>
                                <li>Make sure you're using the correct email or username</li>
                                <li>Passwords are case-sensitive</li>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <input type="text" name="email"
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                               placeholder="Phone number, username, or email" required autofocus>
                        <small>Enter the email, phone number, or username you used when signing up</small>
                    </div>

                    <div class="form-group">
                        <input type="password" name="password"
                               placeholder="Password" required>
                        <small>Passwords are case-sensitive</small>
                    </div>

                    <button type="submit" class="btn btn-login-submit">Log in</button>
                </form>

                <div class="separator">
                    <span>OR</span>
                </div>

                <div class="info-box">
                    <p>💡 <strong>Practice Tip:</strong> This is a safe training environment. If you can't remember your test account details, you can create a new one or ask an admin for help.</p>
                </div>
            </div>

            <div class="signup-box">
                <p>Don't have an account? <a href="instagram-signup.php">Sign up</a></p>
            </div>

            <div class="footer-links">
                <a href="instagram-forgot-password.php">Forgot password?</a> •
                <a href="instagram-forgot-username.php">Forgot username?</a> •
                <a href="instagram.php">← Back to Instagram home</a>
            </div>
        </div>
    </div>
</body>
</html>
