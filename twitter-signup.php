<?php
/**
 * Twitter/X Signup Flow
 * Multi-step registration with educational guidance and verification simulation
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
        } elseif ($contact_type === 'email' && userExists($contact_value, 'twitter')) {
            $errors[] = "An account with this email already exists. Try logging in instead.";
        } else {
            $_SESSION['signup_data']['contact_type'] = $contact_type;
            $_SESSION['signup_data']['email'] = $contact_value;
            // Generate a fake verification code for training
            $_SESSION['signup_data']['verification_code'] = sprintf('%06d', mt_rand(0, 999999));
            header('Location: twitter-signup.php?step=2');
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
            header('Location: twitter-signup.php?step=3');
            exit;
        }
    } elseif ($step === 3) {
        // Step 3: Password creation
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        if (empty($password)) {
            $errors[] = "Password is required";
        } elseif (strlen($password) < 8) {
            $errors[] = "Password must be at least 8 characters long";
        } elseif ($password !== $confirm_password) {
            $errors[] = "Passwords do not match";
        } else {
            $_SESSION['signup_data']['password'] = $password;
            header('Location: twitter-signup.php?step=4');
            exit;
        }
    } elseif ($step === 4) {
        // Step 4: Profile information
        $full_name = sanitizeInput($_POST['full_name']);
        $username = sanitizeInput($_POST['username']);
        $dob = sanitizeInput($_POST['date_of_birth']);

        if (empty($full_name)) {
            $errors[] = "Name is required";
        }
        if (empty($username)) {
            $errors[] = "Username is required";
        } elseif (!preg_match('/^[a-zA-Z0-9_]{3,15}$/', $username)) {
            $errors[] = "Username must be 3-15 characters, letters, numbers, and underscores only";
        } else {
            // Check if username is already taken
            $conn = getDbConnection();
            $username_check = $conn->real_escape_string($username);
            $result = $conn->query("SELECT id FROM users WHERE username = '$username_check' AND platform = 'twitter'");
            if ($result->num_rows > 0) {
                $errors[] = "This username is already taken. Please choose a different one.";
            }
        }
        if (empty($dob)) {
            $errors[] = "Date of birth is required";
        }

        if (empty($errors)) {
            // Create the account
            $userData = [
                'email' => $_SESSION['signup_data']['email'],
                'password' => $_SESSION['signup_data']['password'],
                'full_name' => $full_name,
                'username' => $username,
                'date_of_birth' => $dob
            ];

            if (createUser('twitter', $userData)) {
                $user_id = getUserById($userData['email']);
                logActivity($user_id['id'] ?? null, 'twitter', 'register', 'New account created');

                $_SESSION['user_id'] = $user_id['id'];
                $_SESSION['platform'] = 'twitter';
                unset($_SESSION['signup_data']);

                header('Location: twitter-dashboard.php');
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
    <title>Create your account - X</title>
    <link rel="stylesheet" href="assets/css/twitter.css">
</head>
<body>
    <div class="twitter-container centered">
        <div class="twitter-signup-card">
            <div class="twitter-logo-small">𝕏</div>

            <!-- Progress indicator -->
            <div class="progress-bar">
                <div class="progress-step <?= $step >= 1 ? 'active' : '' ?>">1</div>
                <div class="progress-line <?= $step >= 2 ? 'active' : '' ?>"></div>
                <div class="progress-step <?= $step >= 2 ? 'active' : '' ?>">2</div>
                <div class="progress-line <?= $step >= 3 ? 'active' : '' ?>"></div>
                <div class="progress-step <?= $step >= 3 ? 'active' : '' ?>">3</div>
                <div class="progress-line <?= $step >= 4 ? 'active' : '' ?>"></div>
                <div class="progress-step <?= $step >= 4 ? 'active' : '' ?>">4</div>
            </div>

            <h1>Create your account</h1>
            <p class="step-indicator">Step <?= $step ?> of 4</p>

            <?php if (!empty($errors)): ?>
                <div class="error-box">
                    <?php foreach ($errors as $error): ?>
                        <p>⚠ <?= htmlspecialchars($error) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($step === 1): ?>
                <!-- Step 1: Email or Phone -->
                <div class="help-box">
                    <h3>✉ 📱 How Do You Want to Sign Up?</h3>
                    <p>X (Twitter) lets you sign up using either:</p>
                    <ul>
                        <li><strong>Email:</strong> Use your email address</li>
                        <li><strong>Phone:</strong> Use your mobile phone number</li>
                    </ul>
                    <p><strong>Why?</strong> X will send you a verification code to confirm it's really you. This helps prevent fake accounts and keeps X secure.</p>
                    <p class="warning-text">⚠ <strong>Training Mode:</strong> We'll simulate the verification process since we can't send real SMS/emails.</p>
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

                    <button type="submit" class="btn btn-primary btn-block">Next</button>
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
                    <h3>🔒 Enter Your Verification Code</h3>
                    <p>We've sent a 6-digit code to:</p>
                    <p style="font-weight: 700; font-size: 16px; color: #1d9bf0;"><?= htmlspecialchars($email) ?></p>
                    <p><strong>What is a verification code?</strong></p>
                    <ul>
                        <li>A temporary 6-digit number (like: 123456)</li>
                        <li>Proves you own this <?= $contact_type === 'email' ? 'email' : 'phone number' ?></li>
                        <li>Usually arrives within 1-2 minutes</li>
                        <li>Expires after 10 minutes for security</li>
                    </ul>
                    <div style="background: #0d2818; border: 1px solid #00ba7c; padding: 16px; border-radius: 8px; margin-top: 12px;">
                        <p style="color: #00ba7c; margin: 0;"><strong>📱 Training Mode - Your Code:</strong></p>
                        <p style="color: #00ba7c; font-size: 32px; font-weight: 700; margin: 8px 0; letter-spacing: 8px; font-family: monospace;"><?= $verification_code ?></p>
                        <p style="color: #00ba7c; font-size: 12px; margin: 0;">In real life, you'd receive this via <?= $contact_type === 'email' ? 'email' : 'SMS' ?>. Copy and paste it below!</p>
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

                    <button type="submit" class="btn btn-primary btn-block">Verify</button>

                    <div style="margin-top: 16px; text-align: center;">
                        <small style="color: #71767b;">Didn't receive a code? In real life, you could request a new one.</small>
                    </div>
                </form>

                <div class="button-row" style="margin-top: 16px;">
                    <a href="twitter-signup.php?step=1" class="btn btn-secondary">← Back</a>
                </div>

            <?php elseif ($step === 3): ?>
                <!-- Step 3: Password -->
                <div class="help-box">
                    <h3>🔒 Creating a Strong Password</h3>
                    <p>A strong password helps protect your account from hackers.</p>
                    <ul>
                        <li>Use at least 8 characters</li>
                        <li>Mix uppercase and lowercase letters</li>
                        <li>Include numbers and symbols (!@#$%)</li>
                        <li>Don't use common words like "password" or "123456"</li>
                        <li>Don't use your name or birthday</li>
                    </ul>
                    <p><strong>Important:</strong> Never share your password with anyone!</p>
                </div>

                <form method="POST">
                    <div class="form-group">
                        <label for="password">Create password *</label>
                        <input type="password" id="password" name="password"
                               placeholder="Enter a strong password" required autofocus>
                        <small>Minimum 8 characters</small>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm password *</label>
                        <input type="password" id="confirm_password" name="confirm_password"
                               placeholder="Enter the same password again" required>
                        <small>Type your password again to make sure it's correct</small>
                    </div>

                    <div class="button-row">
                        <a href="twitter-signup.php?step=2" class="btn btn-secondary">Back</a>
                        <button type="submit" class="btn btn-primary">Next</button>
                    </div>
                </form>

            <?php elseif ($step === 4): ?>
                <!-- Step 4: Profile Info -->
                <div class="help-box">
                    <h3>👤 Your Profile Information</h3>
                    <p><strong>Username:</strong> This is how people will find you on X. It appears as @username</p>
                    <p><strong>Name:</strong> Your full name or the name you want to display</p>
                    <p><strong>Date of birth:</strong> You must be 13 years or older to use X</p>
                    <p class="warning-text">⚠ <strong>IMPORTANT:</strong> Your username must be unique - no one else can have the same username!</p>
                    <p><strong>Privacy tip:</strong> You can change your display name later, but your username is permanent!</p>
                </div>

                <form method="POST">
                    <div class="form-group">
                        <label for="full_name">Name *</label>
                        <input type="text" id="full_name" name="full_name"
                               placeholder="Your full name" required autofocus>
                    </div>

                    <div class="form-group">
                        <label for="username">Username * <span style="color: #f91880;">(must be unique)</span></label>
                        <div class="input-with-prefix">
                            <span class="prefix">@</span>
                            <input type="text" id="username" name="username"
                                   placeholder="username" pattern="[a-zA-Z0-9_]{3,15}" required>
                        </div>
                        <small>3-15 characters, letters, numbers, and underscores only. Must be unique!</small>
                    </div>

                    <div class="form-group">
                        <label for="date_of_birth">Date of birth *</label>
                        <input type="date" id="date_of_birth" name="date_of_birth"
                               max="<?= date('Y-m-d', strtotime('-13 years')) ?>" required>
                        <small>You must be at least 13 years old</small>
                    </div>

                    <div class="button-row">
                        <a href="twitter-signup.php?step=3" class="btn btn-secondary">Back</a>
                        <button type="submit" class="btn btn-primary">Create Account</button>
                    </div>
                </form>
            <?php endif; ?>

            <div class="footer-links">
                <a href="twitter.php">Cancel and return to start</a>
            </div>
        </div>
    </div>
</body>
</html>
