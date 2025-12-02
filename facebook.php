<?php
/**
 * Facebook Platform Landing Page
 * Users choose to either sign up or log in
 */

session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facebook - Training</title>
    <link rel="stylesheet" href="assets/css/facebook.css">
</head>
<body>
    <div class="facebook-container">
        <div class="facebook-left">
            <div class="facebook-logo">facebook</div>
            <p class="facebook-tagline">Connect with friends and the world around you on Facebook.</p>
        </div>

        <div class="facebook-right">
            <div class="facebook-card">
                <h2>Log in to Facebook</h2>

                <form action="facebook-login.php" method="GET">
                    <button type="submit" class="btn btn-login">Log In</button>
                </form>

                <div class="separator">
                    <span>or</span>
                </div>

                <a href="facebook-signup.php" class="btn btn-create">Create new account</a>

                <div class="help-section">
                    <h3>Learning About Facebook</h3>
                    <ul>
                        <li><strong>What is Facebook?</strong> A social networking site where people connect with friends, family, and communities</li>
                        <li><strong>Creating an account:</strong> You'll need your name, email or mobile number, password, date of birth, and gender</li>
                        <li><strong>Profile:</strong> Your Facebook profile shows your information and posts to friends</li>
                        <li><strong>Privacy:</strong> You can control who sees your posts and information</li>
                        <li><strong>Safety tip:</strong> Use a strong password and never share your login details</li>
                    </ul>
                </div>
            </div>

            <footer class="facebook-footer">
                <a href="index.php">← Back to Platform Selection</a>
                <span>This is a training simulator - not the real Facebook</span>
            </footer>
        </div>
    </div>
</body>
</html>
