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

            <footer class="instagram-footer">
                <a href="index.php">← Back to Platform Selection</a>
                <span>This is a training simulator - not the real Instagram</span>
            </footer>
        </div>
    </div>
</body>
</html>
