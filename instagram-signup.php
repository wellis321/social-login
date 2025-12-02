<?php
/**
 * Instagram Signup Flow
 * Single-page registration with educational guidance
 */

session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email']);
    $full_name = sanitizeInput($_POST['full_name']);
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];

    // Validation
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!validateEmail($email)) {
        $errors[] = "Please enter a valid email address";
    } elseif (userExists($email, 'instagram')) {
        $errors[] = "An account with this email already exists. Try logging in instead.";
    }

    if (empty($full_name)) {
        $errors[] = "Full name is required";
    }

    if (empty($username)) {
        $errors[] = "Username is required";
    } elseif (!preg_match('/^[a-zA-Z0-9._]{1,30}$/', $username)) {
        $errors[] = "Username can only contain letters, numbers, periods, and underscores (max 30 characters)";
    } else {
        // Check if username is already taken
        $conn = getDbConnection();
        $username_check = $conn->real_escape_string($username);
        $result = $conn->query("SELECT id FROM users WHERE username = '$username_check' AND platform = 'instagram'");
        if ($result->num_rows > 0) {
            $errors[] = "This username is already taken. Please choose a different one.";
        }
    }

    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long";
    }

    if (empty($errors)) {
        // Create the account
        $userData = [
            'email' => $email,
            'full_name' => $full_name,
            'username' => $username,
            'password' => $password
        ];

        if (createUser('instagram', $userData)) {
            $user_id = authenticateUser($email, $password, 'instagram');
            logActivity($user_id, 'instagram', 'register', 'New account created');

            $_SESSION['user_id'] = $user_id;
            $_SESSION['platform'] = 'instagram';

            header('Location: instagram-dashboard.php');
            exit;
        } else {
            $errors[] = "Failed to create account. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign up • Instagram</title>
    <link rel="stylesheet" href="assets/css/instagram.css">
</head>
<body>
    <div class="instagram-signup-page">
        <div class="signup-container">
            <div class="signup-card">
                <h1 class="instagram-logo">Instagram</h1>
                <p class="signup-subtitle">Sign up to see photos and videos from your friends.</p>

                <?php if (!empty($errors)): ?>
                    <div class="error-box">
                        <?php foreach ($errors as $error): ?>
                            <p>⚠️ <?= htmlspecialchars($error) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="help-box">
                    <h3>📝 What You'll Need</h3>
                    <ul>
                        <li><strong>Email:</strong> Your email address for account verification</li>
                        <li><strong>Full name:</strong> Your real name (friends can find you easier)</li>
                        <li><strong>Username:</strong> Your unique @username (this is how people find you)</li>
                        <li><strong>Password:</strong> At least 6 characters to keep your account secure</li>
                    </ul>
                    <p style="color: #ed4956; font-weight: 600; margin-top: 12px;">⚠️ Your username must be UNIQUE - no one else can have the same username!</p>
                </div>

                <form method="POST">
                    <div class="form-group">
                        <input type="email" name="email"
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                               placeholder="Mobile Number or Email" required autofocus>
                        <small>You'll use this to log in and recover your account</small>
                    </div>

                    <div class="form-group">
                        <input type="text" name="full_name"
                               value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>"
                               placeholder="Full Name" required>
                        <small>Use your real name so friends can recognize you</small>
                    </div>

                    <div class="form-group">
                        <input type="text" name="username"
                               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                               placeholder="Username (must be unique)"
                               pattern="[a-zA-Z0-9._]{1,30}" required>
                        <small>Letters, numbers, periods, and underscores only (max 30 characters). <strong style="color: #ed4956;">Must be unique!</strong></small>
                    </div>

                    <div class="form-group">
                        <input type="password" name="password"
                               placeholder="Password" required>
                        <small>At least 6 characters</small>
                    </div>

                    <div class="help-box tips">
                        <h4>💡 Username Tips</h4>
                        <ul>
                            <li>Choose something memorable and easy to share</li>
                            <li>Your username appears in your profile URL</li>
                            <li><strong style="color: #ed4956;">⚠️ It MUST be unique - no one else can have it!</strong></li>
                            <li>If your username is taken, try adding numbers or underscores</li>
                            <li>You can change it later in settings</li>
                        </ul>
                    </div>

                    <button type="submit" class="btn btn-signup-submit">Sign up</button>
                </form>

                <p class="terms">
                    By signing up, you agree to our Terms, Privacy Policy and Cookies Policy.
                    <strong>Note:</strong> This is a training environment.
                </p>
            </div>

            <div class="login-box">
                <p>Have an account? <a href="instagram-login.php">Log in</a></p>
            </div>

            <div class="footer-links">
                <a href="instagram.php">← Back to Instagram home</a>
            </div>
        </div>
    </div>
</body>
</html>
