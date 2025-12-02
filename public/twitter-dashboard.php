<?php
/**
 * Twitter/X Dashboard - Success page after login
 * Shows user they've successfully logged in
 */

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['platform'] !== 'twitter') {
    header('Location: twitter-login.php');
    exit;
}

$user = getUserById($_SESSION['user_id']);

// Handle logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    logActivity($_SESSION['user_id'], 'twitter', 'logout', 'User logged out');
    session_destroy();
    header('Location: twitter.php');
    exit;
}

// Handle account deletion
if (isset($_POST['delete_account'])) {
    logActivity($_SESSION['user_id'], 'twitter', 'delete_account', 'User deleted their account');
    deleteUser($_SESSION['user_id']);
    session_destroy();
    header('Location: twitter.php?deleted=1');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home / X</title>
    <link rel="stylesheet" href="../assets/css/twitter.css">
</head>
<body>
    <div class="twitter-dashboard">
        <nav class="twitter-nav">
            <div class="nav-logo">𝕏</div>
            <div class="nav-user">
                <span>@<?= htmlspecialchars($user['username']) ?></span>
            </div>
        </nav>

        <div class="dashboard-content">
            <div class="success-message">
                <h1>✅ Success! You're logged in!</h1>
                <p class="welcome-text">Welcome back, <?= htmlspecialchars($user['full_name']) ?>!</p>
            </div>

            <div class="learning-box">
                <h2>🎓 What You've Just Learned</h2>

                <div class="learning-section">
                    <h3>✅ Registration Process</h3>
                    <p>You successfully completed all the steps to create an X/Twitter account:</p>
                    <ul>
                        <li><strong>Email verification</strong> - Providing a valid email address</li>
                        <li><strong>Password creation</strong> - Creating a secure password</li>
                        <li><strong>Profile setup</strong> - Choosing a username and adding personal information</li>
                    </ul>
                </div>

                <div class="learning-section">
                    <h3>🔐 Login Process</h3>
                    <p>You know how to log in to X using:</p>
                    <ul>
                        <li>Your registered email address</li>
                        <li>Your password</li>
                    </ul>
                </div>

                <div class="learning-section">
                    <h3>💡 Important Things to Remember</h3>
                    <ul>
                        <li><strong>Keep your password safe</strong> - Never share it with anyone</li>
                        <li><strong>Log out when done</strong> - Especially on shared computers</li>
                        <li><strong>Check the URL</strong> - Make sure you're on the real website</li>
                        <li><strong>Use a strong password</strong> - Mix letters, numbers, and symbols</li>
                        <li><strong>Email is your key</strong> - Keep access to the email you registered with</li>
                    </ul>
                </div>

                <div class="learning-section warning">
                    <h3>⚠️ Common Mistakes to Avoid</h3>
                    <ul>
                        <li>Using the same password for multiple sites</li>
                        <li>Clicking on suspicious links in emails</li>
                        <li>Sharing your login details with others</li>
                        <li>Using public WiFi without caution</li>
                        <li>Falling for phishing scams (fake login pages)</li>
                    </ul>
                </div>
            </div>

            <div class="account-info">
                <h3>Your Practice Account Details</h3>
                <table class="info-table">
                    <tr>
                        <td><strong>Email:</strong></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Username:</strong></td>
                        <td>@<?= htmlspecialchars($user['username']) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Name:</strong></td>
                        <td><?= htmlspecialchars($user['full_name']) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Account created:</strong></td>
                        <td><?= date('F j, Y', strtotime($user['created_at'])) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Last login:</strong></td>
                        <td><?= $user['last_login'] ? date('F j, Y g:i A', strtotime($user['last_login'])) : 'First time!' ?></td>
                    </tr>
                </table>
            </div>

            <div class="action-buttons">
                <a href="?action=logout" class="btn btn-secondary">Log Out</a>
                <a href="twitter.php" class="btn btn-secondary">Go to X Home (will log out)</a>
                <a href="../index.php" class="btn btn-secondary">Try Another Platform</a>
            </div>

            <div class="danger-zone">
                <h3>⚠️ Practice Account Management</h3>
                <p>Since this is a training environment, you can delete this practice account:</p>
                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this practice account? This cannot be undone.');">
                    <button type="submit" name="delete_account" class="btn btn-danger">Delete Practice Account</button>
                </form>
            </div>
        </div>

        <footer class="dashboard-footer">
            <p>This is a training simulator - not the real X/Twitter</p>
            <p>You've successfully practiced creating and logging into a social media account!</p>
        </footer>
    </div>
</body>
</html>
