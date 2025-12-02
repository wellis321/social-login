<?php
/**
 * Facebook Login Page
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
        $errors[] = "Please enter both email/phone and password";
    } else {
        $user_id = authenticateUser($email, $password, 'facebook');

        if ($user_id) {
            logActivity($user_id, 'facebook', 'login', 'User logged in successfully');

            $_SESSION['user_id'] = $user_id;
            $_SESSION['platform'] = 'facebook';

            header('Location: facebook-dashboard.php');
            exit;
        } else {
            $errors[] = "The email/phone or password you entered isn't connected to an account.";
            logActivity(null, 'facebook', 'login_failed', 'Failed login attempt: ' . $email);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log in to Facebook</title>
    <link rel="stylesheet" href="assets/css/facebook.css">
</head>
<body>
    <div class="facebook-login-page">
        <div class="login-container">
            <div class="login-header">
                <h1>facebook</h1>
            </div>

            <div class="login-card">
                <h2>Log in to Facebook</h2>

                <div class="help-box">
                    <h3>🔐 Logging In to Facebook</h3>
                    <p><strong>What you need:</strong></p>
                    <ul>
                        <li>The email address or phone number you used to sign up</li>
                        <li>Your Facebook password</li>
                    </ul>
                    <p><strong>Security tips:</strong></p>
                    <ul>
                        <li>Never enter your password on suspicious websites</li>
                        <li>Check that you're on the real Facebook URL</li>
                        <li>Don't save passwords on shared computers</li>
                        <li>Log out when you're done, especially on public devices</li>
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
                                <li>Make sure Caps Lock is off</li>
                                <li>Check you're using the correct email or phone number</li>
                                <li>Passwords are case-sensitive</li>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <input type="text" id="email" name="email"
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                               placeholder="Email address or phone number" required autofocus>
                        <small>Enter the email address or phone number you used when you created your account</small>
                    </div>

                    <div class="form-group">
                        <input type="password" id="password" name="password"
                               placeholder="Password" required>
                        <small>Passwords are case-sensitive (ABC is different from abc)</small>
                    </div>

                    <button type="submit" class="btn btn-login-submit">Log in</button>
                </form>

                <div class="divider">
                    <span>or</span>
                </div>

                <a href="facebook-signup.php" class="btn btn-create-new">Create new account</a>

                <div class="info-box">
                    <p>💡 <strong>Practice Tip:</strong> This is a safe training environment. If you forget your test account details, you can create a new one or ask an admin for help.</p>
                </div>

                <div class="footer-links">
                    <a href="facebook-forgot-password.php">Forgot password?</a> •
                    <a href="facebook.php">← Back to Facebook home</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
