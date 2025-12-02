<?php
/**
 * Facebook Dashboard - Success page after login
 * Shows user they've successfully logged in
 */

session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['platform'] !== 'facebook') {
    header('Location: facebook-login.php');
    exit;
}

$user = getUserById($_SESSION['user_id']);

// Handle logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    logActivity($_SESSION['user_id'], 'facebook', 'logout', 'User logged out');
    session_destroy();
    header('Location: facebook.php');
    exit;
}

// Handle account deletion
if (isset($_POST['delete_account'])) {
    logActivity($_SESSION['user_id'], 'facebook', 'delete_account', 'User deleted their account');
    deleteUser($_SESSION['user_id']);
    session_destroy();
    header('Location: facebook.php?deleted=1');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facebook</title>
    <link rel="stylesheet" href="assets/css/facebook.css">
</head>
<body>
    <div class="facebook-dashboard">
        <nav class="facebook-nav">
            <div class="nav-left">
                <div class="nav-logo">facebook</div>
            </div>
            <div class="nav-right">
                <span class="nav-user"><?= htmlspecialchars($user['full_name']) ?></span>
            </div>
        </nav>

        <div class="dashboard-content">
            <div class="success-message">
                <h1>✅ Welcome to Facebook!</h1>
                <p class="welcome-text">You've successfully logged in, <?= htmlspecialchars($user['full_name']) ?>!</p>
            </div>

            <div class="learning-box">
                <h2>🎓 What You've Just Learned</h2>

                <div class="learning-section">
                    <h3>✅ Creating a Facebook Account</h3>
                    <p>You successfully completed the registration process:</p>
                    <ul>
                        <li><strong>Personal information</strong> - Providing your name and email</li>
                        <li><strong>Secure password</strong> - Creating a strong password</li>
                        <li><strong>Date of birth</strong> - Confirming you meet the age requirement (13+)</li>
                        <li><strong>Profile setup</strong> - Starting your Facebook profile</li>
                    </ul>
                </div>

                <div class="learning-section">
                    <h3>🔐 Logging In to Facebook</h3>
                    <p>You know how to access your Facebook account using:</p>
                    <ul>
                        <li>Your registered email address</li>
                        <li>Your password</li>
                    </ul>
                </div>

                <div class="learning-section">
                    <h3>💡 Important Facebook Concepts</h3>
                    <ul>
                        <li><strong>Profile:</strong> Your Facebook page that shows your information and posts</li>
                        <li><strong>News Feed:</strong> Where you see updates from friends and pages you follow</li>
                        <li><strong>Privacy Settings:</strong> Control who can see your posts and information</li>
                        <li><strong>Friends:</strong> People you connect with on Facebook</li>
                        <li><strong>Messenger:</strong> Facebook's messaging feature for private conversations</li>
                    </ul>
                </div>

                <div class="learning-section warning">
                    <h3>⚠️ Facebook Safety Tips</h3>
                    <ul>
                        <li>Never share your password with anyone</li>
                        <li>Be careful about what personal information you post publicly</li>
                        <li>Check your privacy settings regularly</li>
                        <li>Think before you post - it can be seen by many people</li>
                        <li>Don't accept friend requests from strangers</li>
                        <li>Report suspicious accounts or messages</li>
                        <li>Be aware of phishing scams asking for your login details</li>
                    </ul>
                </div>

                <div class="learning-section">
                    <h3>🎯 Next Steps on Real Facebook</h3>
                    <p>Once you create a real Facebook account, you can:</p>
                    <ul>
                        <li>Add a profile picture and cover photo</li>
                        <li>Find and connect with friends and family</li>
                        <li>Join groups based on your interests</li>
                        <li>Follow pages of businesses, celebrities, or topics you like</li>
                        <li>Share photos, videos, and status updates</li>
                        <li>Customize your privacy settings</li>
                    </ul>
                </div>
            </div>

            <div class="account-info">
                <h3>Your Practice Account Details</h3>
                <table class="info-table">
                    <tr>
                        <td><strong>Name:</strong></td>
                        <td><?= htmlspecialchars($user['full_name']) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Email:</strong></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
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
                <a href="facebook.php" class="btn btn-secondary">Go to Facebook Home (will log out)</a>
                <a href="index.php" class="btn btn-secondary">Try Another Platform</a>
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
            <p>This is a training simulator - not the real Facebook</p>
            <p>You've successfully practiced creating and logging into a Facebook account!</p>
        </footer>
    </div>
</body>
</html>
