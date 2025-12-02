<?php
/**
 * Social Media Login Training Simulator
 * Main entry point - Platform selection page
 */

session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Social Media Login Training</title>
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Social Media Login Training Simulator</h1>
            <p class="subtitle">Practice logging in and registering for social media platforms in a safe environment</p>
        </header>

        <main>
            <div class="platform-selection">
                <h2>Choose a Platform to Practice</h2>

                <div class="platform-grid">
                    <a href="twitter.php" class="platform-card twitter">
                        <div class="platform-icon">𝕏</div>
                        <h3>Twitter / X</h3>
                        <p>Learn how to create an account and log in to X (formerly Twitter)</p>
                    </a>

                    <a href="facebook.php" class="platform-card facebook">
                        <div class="platform-icon">f</div>
                        <h3>Facebook</h3>
                        <p>Learn how to create an account and log in to Facebook</p>
                    </a>

                    <a href="instagram.php" class="platform-card instagram">
                        <div class="platform-icon">📷</div>
                        <h3>Instagram</h3>
                        <p>Learn how to create an account and log in to Instagram</p>
                    </a>
                </div>
            </div>

            <div class="info-section">
                <h3>About This Training Tool</h3>
                <p>This is a safe practice environment where you can learn how to register and log in to social media platforms without using the real websites.</p>
                <ul>
                    <li>All accounts created here are for practice only</li>
                    <li>You can delete or reset your account at any time</li>
                    <li>Each step includes helpful guidance and tips</li>
                    <li>Take your time and practice as many times as you need</li>
                </ul>
            </div>
        </main>

        <footer>
            <p>Duke of Edinburgh Awards - Training Session Tool</p>
            <a href="../admin/">Admin Panel</a>
        </footer>
    </div>
</body>
</html>
