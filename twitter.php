<?php
/**
 * Twitter/X Platform Landing Page
 * Users choose to either sign up or log in
 */

session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>X - Twitter Training</title>
    <link rel="stylesheet" href="assets/css/twitter.css">
</head>
<body>
    <div class="twitter-container">
        <div class="twitter-sidebar">
            <div class="twitter-logo">𝕏</div>
            <h1>Happening now</h1>
        </div>

        <div class="twitter-main">
            <div class="twitter-card">
                <h2>Join today.</h2>

                <div class="button-group">
                    <a href="twitter-signup.php" class="btn btn-primary">
                        Create account
                    </a>

                    <div class="terms">
                        <p>By signing up, you agree to the Terms of Service and Privacy Policy.</p>
                        <p class="help-text">ℹ <strong>What this means:</strong> On real social media sites, you must agree to rules about how your data is used. Always read these carefully!</p>
                    </div>
                </div>

                <div class="separator">
                    <span>Already have an account?</span>
                </div>

                <a href="twitter-login.php" class="btn btn-secondary">
                    Sign in
                </a>

                <div class="help-section">
                    <h3>Learning About X (Twitter)</h3>
                    <ul>
                        <li><strong>What is X?</strong> X (formerly called Twitter) is a social media platform where people share short messages called "posts" or "tweets"</li>
                        <li><strong>Creating an account:</strong> You'll need an email address or phone number, and you'll create a password</li>
                        <li><strong>Username:</strong> This is your unique identifier that starts with @ (like @username)</li>
                        <li><strong>Safety tip:</strong> Never share your password with anyone, even if they claim to work for X</li>
                    </ul>
                </div>
            </div>

            <footer class="twitter-footer">
                <a href="index.php">← Back to Platform Selection</a>
                <span>This is a training simulator - not the real X/Twitter</span>
            </footer>
        </div>
    </div>
</body>
</html>
