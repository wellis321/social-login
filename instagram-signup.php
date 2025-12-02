<?php
/**
 * Instagram Signup Flow
 * Multi-step registration with email verification and educational guidance
 */

session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$errors = [];
$success = false;
$step = isset($_GET['step']) ? intval($_GET['step']) : 1;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step === 1) {
        // Step 1: Email/Phone choice and validation
        $contact_type = $_POST['contact_type'] ?? 'email';
        $contact_value = sanitizeInput($_POST['contact_value']);

        if (empty($contact_value)) {
            $errors[] = $contact_type === 'email' ? "Email is required" : "Phone number is required";
        } elseif ($contact_type === 'email' && !validateEmail($contact_value)) {
            $errors[] = "Please enter a valid email address";
        } elseif ($contact_type === 'email' && userExists($contact_value, 'instagram')) {
            $errors[] = "An account with this email already exists. Try logging in instead.";
        } else {
            $_SESSION['signup_data']['contact_type'] = $contact_type;
            $_SESSION['signup_data']['email'] = $contact_value;
            // Generate a fake verification code for training
            $_SESSION['signup_data']['verification_code'] = sprintf('%06d', mt_rand(0, 999999));
            header('Location: instagram-signup.php?step=2');
            exit;
        }
    } elseif ($step === 2) {
        // Step 2: Verification code
        $entered_code = sanitizeInput($_POST['verification_code']);
        $expected_code = $_SESSION['signup_data']['verification_code'] ?? '';

        if (empty($entered_code)) {
            $errors[] = "Verification code is required";
        } elseif ($entered_code !== $expected_code) {
            $errors[] = "Incorrect verification code. Please try again.";
        } else {
            $_SESSION['signup_data']['verified'] = true;
            header('Location: instagram-signup.php?step=3');
            exit;
        }
    } elseif ($step === 3) {
        // Step 3: Profile information, password, and username
        $email = $_SESSION['signup_data']['email'] ?? '';
        $full_name = sanitizeInput($_POST['full_name']);
        $username = sanitizeInput($_POST['username']);
        $password = $_POST['password'];

        if (empty($full_name)) {
            $errors[] = "Full name is required";
        }

        if (empty($username)) {
            $errors[] = "Username is required";
        } elseif (!preg_match('/^[a-zA-Z0-9._]{1,30}$/', $username)) {
            $errors[] = "Username can only contain letters, numbers, periods, and underscores (max 30 characters)";
        } else {
            // Check if username is already taken
            $conn = getDbConnection();
            $username_check = $conn->real_escape_string($username);
            $result = $conn->query("SELECT id FROM users WHERE username = '$username_check' AND platform = 'instagram'");
            if ($result->num_rows > 0) {
                $errors[] = "This username is already taken. Please choose a different one.";
            }
        }

        if (empty($password)) {
            $errors[] = "Password is required";
        } elseif (strlen($password) < 6) {
            $errors[] = "Password must be at least 6 characters long";
        }

        if (empty($errors)) {
            // Create the account
            $userData = [
                'email' => $email,
                'full_name' => $full_name,
                'username' => $username,
                'password' => $password
            ];

            if (createUser('instagram', $userData)) {
                $user_id = authenticateUser($email, $password, 'instagram');
                logActivity($user_id, 'instagram', 'register', 'New account created');

                $_SESSION['user_id'] = $user_id;
                $_SESSION['platform'] = 'instagram';
                unset($_SESSION['signup_data']);

                header('Location: instagram-dashboard.php');
                exit;
            } else {
                $errors[] = "Failed to create account. Please try again.";
            }
        }
    }
}

// Get stored data
$email = $_SESSION['signup_data']['email'] ?? '';
$contact_type = $_SESSION['signup_data']['contact_type'] ?? 'email';
$verification_code = $_SESSION['signup_data']['verification_code'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign up • Instagram</title>
    <link rel="stylesheet" href="assets/css/instagram.css">
</head>
<body>
    <div class="instagram-signup-page">
        <div class="signup-container">
            <div class="signup-card">
                <h1 class="instagram-logo">Instagram</h1>
                <p class="signup-subtitle">Sign up to see photos and videos from your friends.</p>

                <!-- Progress indicator -->
                <div class="progress-bar">
                    <div class="progress-step <?= $step >= 1 ? 'active' : '' ?>">1</div>
                    <div class="progress-line <?= $step >= 2 ? 'active' : '' ?>"></div>
                    <div class="progress-step <?= $step >= 2 ? 'active' : '' ?>">2</div>
                    <div class="progress-line <?= $step >= 3 ? 'active' : '' ?>"></div>
                    <div class="progress-step <?= $step >= 3 ? 'active' : '' ?>">3</div>
                </div>

                <p class="step-indicator">Step <?= $step ?> of 3</p>

                <?php if (!empty($errors)): ?>
                    <div class="error-box">
                        <?php foreach ($errors as $error): ?>
                            <p>⚠️ <?= htmlspecialchars($error) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ($step === 1): ?>
                    <!-- Step 1: Email or Phone -->
                    <div class="help-box">
                        <h3>📧📱 How Do You Want to Sign Up?</h3>
                        <p>Instagram lets you sign up using either:</p>
                        <ul>
                            <li><strong>Email:</strong> Use your email address</li>
                            <li><strong>Phone:</strong> Use your mobile phone number</li>
                        </ul>
                        <p><strong>Why?</strong> We'll send you a verification code to confirm it's really you. This helps prevent fake accounts and keeps Instagram secure.</p>
                        <p class="warning-text">⚠️ <strong>Training Mode:</strong> We'll simulate the verification process since we can't send real SMS/emails.</p>
                    </div>

                    <form method="POST">
                        <div class="form-group">
                            <label>Choose how to sign up:</label>
                            <select name="contact_type" id="contact_type" onchange="updatePlaceholder()">
                                <option value="email">Use email</option>
                                <option value="phone">Use phone</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="contact_value" id="contact_label">Email address *</label>
                            <input type="text" id="contact_value" name="contact_value"
                                   placeholder="example@email.com" required autofocus>
                            <small id="contact_hint">We'll send you a verification code</small>
                        </div>

                        <button type="submit" class="btn btn-signup-submit btn-block">Next</button>
                    </form>

                    <script>
                    function updatePlaceholder() {
                        const type = document.getElementById('contact_type').value;
                        const input = document.getElementById('contact_value');
                        const label = document.getElementById('contact_label');
                        const hint = document.getElementById('contact_hint');

                        if (type === 'email') {
                            label.textContent = 'Email address *';
                            input.placeholder = 'example@email.com';
                            input.type = 'email';
                            hint.textContent = 'We\'ll send you a verification code';
                        } else {
                            label.textContent = 'Phone number *';
                            input.placeholder = '+1 234 567 8900';
                            input.type = 'tel';
                            hint.textContent = 'We\'ll send you a verification code via SMS';
                        }
                    }
                    </script>

                <?php elseif ($step === 2): ?>
                    <!-- Step 2: Verification Code -->
                    <div class="help-box">
                        <h3>🔐 Enter Your Verification Code</h3>
                        <p>We've sent a 6-digit code to:</p>
                        <p style="font-weight: 700; font-size: 16px; color: #0095f6;"><?= htmlspecialchars($email) ?></p>
                        <p><strong>What is a verification code?</strong></p>
                        <ul>
                            <li>A temporary 6-digit number (like: 123456)</li>
                            <li>Proves you own this <?= $contact_type === 'email' ? 'email' : 'phone number' ?></li>
                            <li>Usually arrives within 1-2 minutes</li>
                            <li>Expires after 10 minutes for security</li>
                        </ul>
                        <div style="background: #e0f2fe; border: 1px solid #0095f6; padding: 16px; border-radius: 8px; margin-top: 12px;">
                            <p style="color: #0095f6; margin: 0;"><strong>📱 Training Mode - Your Code:</strong></p>
                            <p style="color: #0095f6; font-size: 32px; font-weight: 700; margin: 8px 0; letter-spacing: 8px; font-family: monospace;"><?= $verification_code ?></p>
                            <p style="color: #0095f6; font-size: 12px; margin: 0;">In real life, you'd receive this via <?= $contact_type === 'email' ? 'email' : 'SMS' ?>. Copy and paste it below!</p>
                        </div>
                    </div>

                    <form method="POST">
                        <div class="form-group">
                            <label for="verification_code">Verification code *</label>
                            <input type="text" id="verification_code" name="verification_code"
                                   placeholder="Enter 6-digit code" maxlength="6" pattern="[0-9]{6}"
                                   required autofocus style="font-size: 24px; letter-spacing: 8px; text-align: center; font-family: monospace;">
                            <small>Check your <?= $contact_type === 'email' ? 'email inbox' : 'text messages' ?> for the code</small>
                        </div>

                        <button type="submit" class="btn btn-signup-submit btn-block">Verify</button>

                        <div style="margin-top: 16px; text-align: center;">
                            <small style="color: #8e8e8e;">Didn't receive a code? In real life, you could request a new one.</small>
                        </div>
                    </form>

                    <div class="button-row" style="margin-top: 16px;">
                        <a href="instagram-signup.php?step=1" class="btn btn-secondary">← Back</a>
                    </div>

                <?php elseif ($step === 3): ?>
                    <!-- Step 3: Profile Info, Password, and Username -->
                    <div class="help-box">
                        <h3>👤 Complete Your Profile</h3>
                        <p><strong>Full name:</strong> Use your real name so friends can find you</p>
                        <p><strong>Username:</strong> Your unique @username (this is how people find you)</p>
                        <p><strong>Password:</strong> Choose something secure (at least 6 characters)</p>
                        <p style="color: #ed4956; font-weight: 600; margin-top: 12px;">⚠️ Your username must be UNIQUE - no one else can have the same username!</p>
                    </div>

                    <form method="POST">
                        <div class="form-group">
                            <label for="full_name">Full Name *</label>
                            <input type="text" id="full_name" name="full_name"
                                   value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>"
                                   placeholder="Full Name" required autofocus>
                            <small>Use your real name so friends can recognize you</small>
                        </div>

                        <div class="form-group">
                            <label for="username">Username * <span style="color: #ed4956;">(must be unique)</span></label>
                            <input type="text" id="username" name="username"
                                   value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                                   placeholder="Username (must be unique)"
                                   pattern="[a-zA-Z0-9._]{1,30}" required>
                            <small>Letters, numbers, periods, and underscores only (max 30 characters). <strong style="color: #ed4956;">Must be unique!</strong></small>
                        </div>

                        <div class="form-group">
                            <label for="password">Password *</label>
                            <input type="password" id="password" name="password"
                                   placeholder="Password" required>
                            <small>At least 6 characters. Mix uppercase, lowercase, numbers, and symbols for security.</small>
                        </div>

                        <div class="help-box tips">
                            <h4>💡 Username Tips</h4>
                            <ul>
                                <li>Choose something memorable and easy to share</li>
                                <li>Your username appears in your profile URL</li>
                                <li><strong style="color: #ed4956;">⚠️ It MUST be unique - no one else can have it!</strong></li>
                                <li>If your username is taken, try adding numbers or underscores</li>
                                <li>You can change it later in settings</li>
                            </ul>
                        </div>

                        <p class="terms">
                            By clicking Sign Up, you agree to our Terms, Privacy Policy and Cookies Policy.
                            <strong>Note:</strong> This is a training environment - these are simulated terms.
                        </p>

                        <div class="button-row">
                            <a href="instagram-signup.php?step=2" class="btn btn-secondary">Back</a>
                            <button type="submit" class="btn btn-signup-submit">Sign Up</button>
                        </div>
                    </form>

                <?php endif; ?>

                <div class="login-box">
                    <p>Have an account? <a href="instagram-login.php">Log in</a></p>
                </div>

                <div class="footer-links">
                    <a href="instagram.php">← Back to Instagram home</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
