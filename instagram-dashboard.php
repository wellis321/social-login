<?php
/**
 * Instagram Dashboard - Success page after login
 * Shows user they've successfully logged in
 */

session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['platform'] !== 'instagram') {
    header('Location: instagram-login.php');
    exit;
}

$user = getUserById($_SESSION['user_id']);

// Handle logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    logActivity($_SESSION['user_id'], 'instagram', 'logout', 'User logged out');
    session_destroy();
    header('Location: instagram.php');
    exit;
}

// Handle account deletion
if (isset($_POST['delete_account'])) {
    logActivity($_SESSION['user_id'], 'instagram', 'delete_account', 'User deleted their account');
    deleteUser($_SESSION['user_id']);
    session_destroy();
    header('Location: instagram.php?deleted=1');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instagram</title>
    <link rel="stylesheet" href="assets/css/instagram.css">
</head>
<body>
    <div class="instagram-dashboard">
        <nav class="instagram-nav">
            <div class="nav-logo">Instagram</div>
            <div class="nav-user">@<?= htmlspecialchars($user['username']) ?></div>
        </nav>

        <div class="dashboard-content">
            <div class="success-message">
                <h1>✓ Welcome to Instagram!</h1>
                <p class="welcome-text">You're all set, @<?= htmlspecialchars($user['username']) ?>!</p>
            </div>

            <div class="learning-box">
                <h2>What You've Just Learned</h2>

                <div class="learning-section">
                    <h3>✓ Creating an Instagram Account</h3>
                    <p>You successfully completed Instagram registration:</p>
                    <ul>
                        <li><strong>Email verification</strong> - Providing a valid email address</li>
                        <li><strong>Personal details</strong> - Adding your full name</li>
                        <li><strong>Username selection</strong> - Choosing your unique @username</li>
                        <li><strong>Password security</strong> - Creating a secure password</li>
                    </ul>
                </div>

                <div class="learning-section">
                    <h3>🔒 Logging In to Instagram</h3>
                    <p>You know how to access your account using:</p>
                    <ul>
                        <li>Your email address or username</li>
                        <li>Your password</li>
                    </ul>
                </div>

                <div class="learning-section">
                    <h3>📱 Understanding Instagram Features</h3>
                    <ul>
                        <li><strong>Feed:</strong> Your main timeline showing posts from accounts you follow</li>
                        <li><strong>Stories:</strong> Photos and videos that disappear after 24 hours</li>
                        <li><strong>Reels:</strong> Short, entertaining videos (similar to TikTok)</li>
                        <li><strong>Direct Messages (DMs):</strong> Private messaging with other users</li>
                        <li><strong>Explore:</strong> Discover new content and accounts based on your interests</li>
                        <li><strong>Profile:</strong> Your personal page showing your posts and bio</li>
                        <li><strong>Hashtags:</strong> Tags that help categorize and discover content (#example)</li>
                        <li><strong>Followers/Following:</strong> People who follow you and people you follow</li>
                    </ul>
                </div>

                <div class="learning-section warning">
                    <h3>⚠ Instagram Safety & Privacy Tips</h3>
                    <ul>
                        <li>Set your account to private if you only want approved followers</li>
                        <li>Be careful about what personal information you share</li>
                        <li>Don't accept follow requests from suspicious accounts</li>
                        <li>Report and block accounts that make you uncomfortable</li>
                        <li>Think before you post - it can be screenshot and shared</li>
                        <li>Don't share your password, even with friends</li>
                        <li>Be aware of scams asking for account information</li>
                        <li>Review your privacy settings regularly</li>
                        <li>Be kind - cyberbullying is never okay</li>
                    </ul>
                </div>

                <div class="learning-section">
                    <h3>Getting Started on Real Instagram</h3>
                    <p>Once you create a real Instagram account, you should:</p>
                    <ul>
                        <li>Add a profile picture and write a bio</li>
                        <li>Set your account to private (recommended for beginners)</li>
                        <li>Find and follow friends you know in real life</li>
                        <li>Customize your privacy and security settings</li>
                        <li>Learn about hashtags to discover content</li>
                        <li>Start by posting photos that represent you</li>
                        <li>Try Instagram Stories to share daily moments</li>
                        <li>Explore Reels to discover trending content</li>
                    </ul>
                </div>

                <div class="learning-section">
                    <h3>Instagram Etiquette</h3>
                    <ul>
                        <li>Like and comment on posts you genuinely enjoy</li>
                        <li>Give credit when reposting others' content</li>
                        <li>Don't spam comments or messages</li>
                        <li>Respond to comments on your posts</li>
                        <li>Use hashtags relevant to your content</li>
                        <li>Be respectful in comments and DMs</li>
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
                        <td><strong>Username:</strong></td>
                        <td>@<?= htmlspecialchars($user['username']) ?></td>
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
                <a href="instagram.php" class="btn btn-secondary">Go to Instagram Home (will log out)</a>
                <a href="index.php" class="btn btn-secondary">Try Another Platform</a>
            </div>

            <div class="danger-zone">
                <h3>⚠ Practice Account Management</h3>
                <p>Since this is a training environment, you can delete this practice account:</p>
                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this practice account? This cannot be undone.');">
                    <button type="submit" name="delete_account" class="btn btn-danger">Delete Practice Account</button>
                </form>
            </div>
        </div>

        <footer class="dashboard-footer">
            <p>This is a training simulator - not the real Instagram</p>
            <p>You've successfully practiced creating and logging into an Instagram account!</p>
        </footer>
    </div>
</body>
</html>
