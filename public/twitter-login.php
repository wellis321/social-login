<?php
/**
 * Twitter/X Login Page
 * Authenticate existing users
 */

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $errors[] = "Please enter both email and password";
    } else {
        $user_id = authenticateUser($email, $password, 'twitter');

        if ($user_id) {
            logActivity($user_id, 'twitter', 'login', 'User logged in successfully');

            $_SESSION['user_id'] = $user_id;
            $_SESSION['platform'] = 'twitter';

            header('Location: twitter-dashboard.php');
            exit;
        } else {
            $errors[] = "Incorrect email or password. Please try again.";
            logActivity(null, 'twitter', 'login_failed', 'Failed login attempt: ' . $email);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in to X</title>
    <link rel="stylesheet" href="assets/css/twitter.css">
</head>
<body>
    <div class="twitter-container centered">
        <div class="twitter-login-card">
            <div class="twitter-logo-small">𝕏</div>

            <h1>Sign in to X</h1>

            <div class="help-box">
                <h3>🔐 Logging In Safely</h3>
                <p><strong>What you need:</strong></p>
                <ul>
                    <li>The email address you used to sign up</li>
                    <li>Your password</li>
                </ul>
                <p><strong>Security tips:</strong></p>
                <ul>
                    <li>Make sure you're on the real website (check the URL)</li>
                    <li>Never enter your password on a site you don't trust</li>
                    <li>If you forgot your password, use the "Forgot password?" link</li>
                    <li>Don't save your password on shared computers</li>
                </ul>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="error-box">
                    <?php foreach ($errors as $error): ?>
                        <p>⚠️ <?= htmlspecialchars($error) ?></p>
                    <?php endforeach; ?>
                    <div class="error-help">
                        <p><strong>Common issues:</strong></p>
                        <ul>
                            <li>Check that Caps Lock is off</li>
                            <li>Make sure you're using the correct email</li>
                            <li>Try typing your password carefully</li>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="email">Email address</label>
                    <input type="email" id="email" name="email"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                           placeholder="Enter your email" required autofocus>
                    <small>This is the email you used when signing up</small>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password"
                           placeholder="Enter your password" required>
                    <small>Passwords are case-sensitive (ABC is different from abc)</small>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Sign in</button>
            </form>

            <div class="footer-links" style="margin-top: 16px;">
                <a href="twitter-forgot-password.php">Forgot password?</a> •
                <a href="twitter-forgot-username.php">Forgot username?</a>
            </div>

            <div class="divider">
                <span>or</span>
            </div>

            <div class="footer-links">
                <p>Don't have an account? <a href="twitter-signup.php">Sign up</a></p>
                <a href="twitter.php" class="secondary-link">← Back to X home</a>
            </div>

            <div class="info-box">
                <p>💡 <strong>Practice Tip:</strong> In this training environment, if you can't remember your test account details, you can always create a new one or ask an admin to help you.</p>
            </div>
        </div>
    </div>
</body>
</html>
