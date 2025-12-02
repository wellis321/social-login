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
                        <p><strong>What is X?</strong> X (formerly Twitter) is a social media platform where you can share short messages (tweets), follow news, connect with friends, and join conversations about topics you care about.</p>
                        <p style="margin-top: 8px;"><strong>Why use it?</strong> Stay updated with real-time news, follow your interests, share your thoughts, and connect with people worldwide. Great for quick updates and public conversations.</p>
                    </a>

                    <a href="facebook.php" class="platform-card facebook">
                        <div class="platform-icon">f</div>
                        <h3>Facebook</h3>
                        <p><strong>What is Facebook?</strong> Facebook is a social networking platform where you can connect with friends and family, share photos and updates, join groups, and stay in touch with people you know.</p>
                        <p style="margin-top: 8px;"><strong>Why use it?</strong> Connect with friends and family, share life updates, join community groups, discover events, and keep in touch with people from your past and present. Great for personal connections.</p>
                    </a>

                    <a href="instagram.php" class="platform-card instagram">
                        <div class="platform-icon">📷</div>
                        <h3>Instagram</h3>
                        <p><strong>What is Instagram?</strong> Instagram is a photo and video sharing platform where you can share visual content, follow friends and interests, and discover creative content through photos, videos, and stories.</p>
                        <p style="margin-top: 8px;"><strong>Why use it?</strong> Share your life through photos and videos, follow friends and influencers, discover new interests, express your creativity, and stay connected visually. Great for visual storytelling.</p>
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

        <div class="security-info-bar">
            <div class="security-info-content">
                <div class="security-icon">🔒</div>
                <div class="security-text">
                    <h3>Check if Your Email is Secure</h3>
                    <p>Visit <a href="https://haveibeenpwned.com/" target="_blank" rel="noopener noreferrer">Have I Been Pwned</a> to check if your email address has been involved in any data breaches. If your email has been compromised, you should change your password and review your account security settings.</p>
                </div>
                <a href="https://haveibeenpwned.com/" target="_blank" rel="noopener noreferrer" class="security-link-btn">Check Your Email →</a>
            </div>
        </div>

        <footer>
            <p>Duke of Edinburgh Awards - Training Session Tool</p>
            <a href="../admin/">Admin Panel</a>
        </footer>
    </div>
</body>
</html>
