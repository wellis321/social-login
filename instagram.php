<?php
/**
 * Instagram Platform Landing Page
 * Users choose to either sign up or log in
 */

session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instagram - Training</title>
    <link rel="stylesheet" href="assets/css/instagram.css">
</head>
<body>
    <div class="instagram-container">
        <div class="instagram-left">
            <div class="phone-mockup">
                <div class="phone-content">
                    <p>📸 Share photos and videos</p>
                    <p>💬 Connect with friends</p>
                    <p>⭐ Discover new content</p>
                </div>
            </div>
        </div>

        <div class="instagram-right">
            <div class="instagram-card">
                <h1 class="instagram-logo">Instagram</h1>

                <form action="instagram-signup.php" method="GET">
                    <button type="submit" class="btn btn-signup">Sign up</button>
                </form>

                <div class="separator">
                    <span>OR</span>
                </div>

                <a href="instagram-login.php" class="link-login">Log in</a>

                <div class="help-section">
                    <h3>Learning About Instagram</h3>
                    <ul>
                        <li><strong>What is Instagram?</strong> A photo and video sharing social networking service</li>
                        <li><strong>Creating an account:</strong> You'll need an email or phone number, full name, username, and password</li>
                        <li><strong>Username:</strong> Your unique @username that people use to find and tag you</li>
                        <li><strong>Profile:</strong> Share photos, videos, and stories with followers</li>
                        <li><strong>Explore:</strong> Discover content based on what you like</li>
                        <li><strong>Safety tip:</strong> Keep your account private if you only want approved followers to see your posts</li>
                    </ul>
                </div>
            </div>

            <div class="signup-prompt">
                <p>Don't have an account? <a href="instagram-signup.php">Sign up</a></p>
            </div>

            <div class="security-info-bar">
                <div class="security-info-content">
                    <div class="security-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></div>
                    <div class="security-text">
                        <h3>Check if Your Email is Secure</h3>
                        <p>Visit <a href="https://haveibeenpwned.com/" target="_blank" rel="noopener noreferrer">Have I Been Pwned</a> to check if your email address has been involved in any data breaches. If your email has been compromised, you should change your password and review your account security settings.</p>
                    </div>
                    <a href="https://haveibeenpwned.com/" target="_blank" rel="noopener noreferrer" class="security-link-btn">Check Your Email →</a>
                </div>
            </div>

            <footer class="instagram-footer">
                <a href="index.php">← Back to Platform Selection</a>
                <span>This is a training simulator - not the real Instagram</span>
            </footer>
        </div>
    </div>
</body>
</html>
