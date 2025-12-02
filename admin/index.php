<?php
/**
 * Admin Login Page
 * Secure authentication for administrators
 */

require_once __DIR__ . '/../includes/security.php';

$error = '';

// Check if already logged in
if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $error = "Invalid security token. Please refresh the page and try again.";
    } else {
        $username = trim($_POST['username']);
        $password = $_POST['password'];

        if (empty($username) || empty($password)) {
            $error = "Please enter both username and password";
        } else {
            // Check rate limit (50 attempts per 10 minutes for training)
            if (!checkRateLimit('admin_login', $username, 50, 600)) {
                $error = "Too many login attempts. Please wait 10 minutes before trying again.";
            } else {
                $conn = getDbConnection();
                $stmt = $conn->prepare("SELECT id, password_hash FROM admin_users WHERE username = ? AND is_active = 1");
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows === 1) {
                    $admin = $result->fetch_assoc();
                    if (password_verify($password, $admin['password_hash'])) {
                        $_SESSION['admin_id'] = $admin['id'];
                        $_SESSION['admin_username'] = $username;

                        // Update last login
                        $admin_id = $admin['id'];
                        $update_stmt = $conn->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
                        $update_stmt->bind_param("i", $admin_id);
                        $update_stmt->execute();

                        header('Location: dashboard.php');
                        exit;
                    }
                }

                $error = "Invalid username or password";
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
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
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
