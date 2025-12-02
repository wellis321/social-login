<?php
/**
 * Admin Login Page
 * Secure authentication for administrators
 */

session_start();
require_once __DIR__ . '/../config/database.php';

$error = '';

// Check if already logged in
if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password";
    } else {
        $conn = getDbConnection();
        $username = $conn->real_escape_string($username);

        $sql = "SELECT id, password_hash FROM admin_users WHERE username = '$username' AND is_active = 1";
        $result = $conn->query($sql);

        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();
            if (password_verify($password, $admin['password_hash'])) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $username;

                // Update last login
                $admin_id = $admin['id'];
                $conn->query("UPDATE admin_users SET last_login = NOW() WHERE id = $admin_id");

                header('Location: dashboard.php');
                exit;
            }
        }

        $error = "Invalid username or password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Social Login Training</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-login-page">
        <div class="login-container">
            <div class="login-card">
                <div class="admin-header">
                    <h1>🔒 Admin Panel</h1>
                    <p>Social Login Training Simulator</p>
                </div>

                <?php if ($error): ?>
                    <div class="error-box">
                        <p>⚠ <?= htmlspecialchars($error) ?></p>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username"
                               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                               placeholder="Admin username" required autofocus>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password"
                               placeholder="Admin password" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Log In</button>
                </form>

                <div class="info-box">
                    <p><strong>Default Credentials:</strong></p>
                    <p>Username: <code>admin</code></p>
                    <p>Password: <code>admin123</code></p>
                    <p class="warning">⚠ Change these credentials in production!</p>
                </div>

                <div class="back-link">
                    <a href="../index.php">← Back to Home</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
