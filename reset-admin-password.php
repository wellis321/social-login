<?php
/**
 * Reset Admin Password
 * Use this if you're locked out of the admin panel
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Reset Admin Password</h2>";
echo "<hr>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($new_password)) {
        echo "<p style='color: red;'>✗ Password cannot be empty</p>";
    } elseif ($new_password !== $confirm_password) {
        echo "<p style='color: red;'>✗ Passwords do not match</p>";
    } else {
        try {
            require_once __DIR__ . '/config/database.php';
            $conn = getDbConnection();

            // Generate new password hash
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);

            // Update admin password
            $stmt = $conn->prepare("UPDATE admin_users SET password_hash = ? WHERE username = 'admin'");
            $stmt->bind_param("s", $password_hash);

            if ($stmt->execute()) {
                echo "<div style='background: #d4edda; border: 1px solid #28a745; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
                echo "<h3 style='color: #155724; margin: 0 0 10px 0;'>✓ Password Reset Successful!</h3>";
                echo "<p style='margin: 0;'>You can now log in with:</p>";
                echo "<ul style='margin: 10px 0;'>";
                echo "<li><strong>Username:</strong> admin</li>";
                echo "<li><strong>Password:</strong> " . htmlspecialchars($new_password) . "</li>";
                echo "</ul>";
                echo "<p style='margin: 0;'><a href='admin/' style='color: #155724; font-weight: bold;'>→ Go to Admin Login</a></p>";
                echo "</div>";

                echo "<hr>";
                echo "<p style='color: #856404; background: #fff3cd; border: 1px solid #ffc107; padding: 15px; border-radius: 8px;'>";
                echo "⚠ <strong>Security:</strong> Delete this file (reset-admin-password.php) after resetting your password!";
                echo "</p>";
                exit;
            } else {
                echo "<p style='color: red;'>✗ Failed to update password: " . $conn->error . "</p>";
            }
        } catch (Exception $e) {
            echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reset Admin Password</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            max-width: 500px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f7fa;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #4a5568;
        }
        input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            box-sizing: border-box;
        }
        input:focus {
            outline: none;
            border-color: #667eea;
        }
        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }
        button:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        .info {
            background: #e7f3ff;
            border: 1px solid #1877f2;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="info">
        <p><strong>ℹ Use this tool to reset the admin password if you're locked out.</strong></p>
        <p style="margin-bottom: 0;">Current admin username: <code>admin</code></p>
    </div>

    <form method="POST">
        <div class="form-group">
            <label for="new_password">New Password</label>
            <input type="password" id="new_password" name="new_password"
                   placeholder="Enter new password" required autofocus>
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password"
                   placeholder="Enter password again" required>
        </div>

        <button type="submit">Reset Password</button>
    </form>

    <hr style="margin: 30px 0;">
    <p style="text-align: center;">
        <a href="admin/" style="color: #667eea; text-decoration: none;">← Back to Admin Login</a>
    </p>
</body>
</html>
