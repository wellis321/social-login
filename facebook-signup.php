<?php
/**
 * Facebook Signup Flow
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
        } elseif ($contact_type === 'email' && userExists($contact_value, 'facebook')) {
            $errors[] = "An account with this email already exists. Try logging in instead.";
        } else {
            $_SESSION['signup_data']['contact_type'] = $contact_type;
            $_SESSION['signup_data']['email'] = $contact_value;
            // Generate a fake verification code for training
            $_SESSION['signup_data']['verification_code'] = sprintf('%06d', mt_rand(0, 999999));
            header('Location: facebook-signup.php?step=2');
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
            header('Location: facebook-signup.php?step=3');
            exit;
        }
    } elseif ($step === 3) {
        // Step 3: Profile information, password, birthday, and gender
        $password = $_POST['password'];
        $full_name = sanitizeInput($_POST['full_name']);
        $dob = sanitizeInput($_POST['date_of_birth']);
        $gender = sanitizeInput($_POST['gender'] ?? '');

        if (empty($full_name)) {
            $errors[] = "Name is required";
        }

        if (empty($password)) {
            $errors[] = "Password is required";
        } elseif (strlen($password) < 8) {
            $errors[] = "Password must be at least 8 characters long";
        }

        if (empty($dob)) {
            $errors[] = "Date of birth is required";
        }

        if (empty($errors)) {
            // Create the account
            $userData = [
                'email' => $_SESSION['signup_data']['email'],
                'password' => $password,
                'full_name' => $full_name,
                'date_of_birth' => $dob,
                'gender' => $gender
            ];

            if (createUser('facebook', $userData)) {
                $user_id = authenticateUser($_SESSION['signup_data']['email'], $password, 'facebook');
                logActivity($user_id, 'facebook', 'register', 'New account created');

                $_SESSION['user_id'] = $user_id;
                $_SESSION['platform'] = 'facebook';
                unset($_SESSION['signup_data']);

                header('Location: facebook-dashboard.php');
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
    <title>Sign Up for Facebook</title>
    <link rel="stylesheet" href="assets/css/facebook.css">
</head>
<body>
    <div class="facebook-signup-page">
        <div class="signup-container">
            <div class="signup-header">
                <h1>facebook</h1>
                <p>Create an account to connect with friends and family</p>
            </div>

            <div class="signup-card">
                <!-- Progress indicator -->
                <div class="progress-bar">
                    <div class="progress-step <?= $step >= 1 ? 'active' : '' ?>">1</div>
                    <div class="progress-line <?= $step >= 2 ? 'active' : '' ?>"></div>
                    <div class="progress-step <?= $step >= 2 ? 'active' : '' ?>">2</div>
                    <div class="progress-line <?= $step >= 3 ? 'active' : '' ?>"></div>
                    <div class="progress-step <?= $step >= 3 ? 'active' : '' ?>">3</div>
                </div>

                <h2>Sign Up</h2>
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
                        <p>Facebook lets you sign up using either:</p>
                        <ul>
                            <li><strong>Email:</strong> Use your email address</li>
                            <li><strong>Phone:</strong> Use your mobile phone number</li>
                        </ul>
                        <p><strong>Why?</strong> We'll send you a verification code to confirm it's really you. This helps prevent fake accounts and keeps Facebook secure.</p>
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

                        <button type="submit" class="btn btn-signup btn-block">Next</button>
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
                        <p style="font-weight: 700; font-size: 16px; color: #1877f2;"><?= htmlspecialchars($email) ?></p>
                        <p><strong>What is a verification code?</strong></p>
                        <ul>
                            <li>A temporary 6-digit number (like: 123456)</li>
                            <li>Proves you own this <?= $contact_type === 'email' ? 'email' : 'phone number' ?></li>
                            <li>Usually arrives within 1-2 minutes</li>
                            <li>Expires after 10 minutes for security</li>
                        </ul>
                        <div style="background: #e7f3ff; border: 1px solid #1877f2; padding: 16px; border-radius: 8px; margin-top: 12px;">
                            <p style="color: #1877f2; margin: 0;"><strong>📱 Training Mode - Your Code:</strong></p>
                            <p style="color: #1877f2; font-size: 32px; font-weight: 700; margin: 8px 0; letter-spacing: 8px; font-family: monospace;"><?= $verification_code ?></p>
                            <p style="color: #1877f2; font-size: 12px; margin: 0;">In real life, you'd receive this via <?= $contact_type === 'email' ? 'email' : 'SMS' ?>. Copy and paste it below!</p>
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

                        <button type="submit" class="btn btn-signup btn-block">Verify</button>

                        <div style="margin-top: 16px; text-align: center;">
                            <small style="color: #65676b;">Didn't receive a code? In real life, you could request a new one.</small>
                        </div>
                    </form>

                    <div class="button-row" style="margin-top: 16px;">
                        <a href="facebook-signup.php?step=1" class="btn btn-secondary">← Back</a>
                    </div>

                <?php elseif ($step === 3): ?>
                    <!-- Step 3: Profile Info, Password, Birthday, Gender -->
                    <div class="help-box">
                        <h3>👤 Complete Your Profile</h3>
                        <p><strong>Name:</strong> Use your real name so friends can find you</p>
                        <p><strong>Password:</strong> Choose something secure (at least 8 characters)</p>
                        <p><strong>Date of birth:</strong> You must be 13 years or older to use Facebook</p>
                        <p><strong>Gender:</strong> This helps us personalize your experience</p>
                        <p class="warning-text">⚠️ <strong>Privacy:</strong> Your birthday won't be shared publicly.</p>
                    </div>

                    <form method="POST">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="full_name">Full Name *</label>
                                <input type="text" id="full_name" name="full_name"
                                       placeholder="First and last name" required autofocus>
                                <small>Use your real name so friends can recognize you</small>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="password">New password *</label>
                            <input type="password" id="password" name="password"
                                   placeholder="Password" required>
                            <small>At least 8 characters. Mix uppercase, lowercase, numbers, and symbols for security.</small>
                        </div>

                        <div class="form-group">
                            <label for="date_of_birth">Birthday *</label>
                            <input type="date" id="date_of_birth" name="date_of_birth"
                                   max="<?= date('Y-m-d', strtotime('-13 years')) ?>" required>
                            <small>You need to be at least 13 years old. Your birthday won't be shared publicly.</small>
                        </div>

                        <div class="form-group">
                            <label>Gender (Optional)</label>
                            <div class="gender-options">
                                <label class="radio-label">
                                    <input type="radio" name="gender" value="Female">
                                    Female
                                </label>
                                <label class="radio-label">
                                    <input type="radio" name="gender" value="Male">
                                    Male
                                </label>
                                <label class="radio-label">
                                    <input type="radio" name="gender" value="Custom">
                                    Custom
                                </label>
                            </div>
                            <small>This helps us personalize your experience</small>
                        </div>

                        <div class="help-box tips">
                            <h4>💡 Password Tips</h4>
                            <ul>
                                <li>Mix uppercase and lowercase letters</li>
                                <li>Include numbers and symbols (!@#$%)</li>
                                <li>Don't use common words like "password" or "123456"</li>
                                <li>Don't use your name or birthday</li>
                                <li>Don't share your password with anyone</li>
                            </ul>
                        </div>

                        <p class="terms">
                            By clicking Sign Up, you agree to our Terms, Privacy Policy and Cookies Policy.
                            <strong>Note:</strong> This is a training environment - these are simulated terms.
                        </p>

                        <div class="button-row">
                            <a href="facebook-signup.php?step=2" class="btn btn-secondary">Back</a>
                            <button type="submit" class="btn btn-signup">Sign Up</button>
                        </div>
                    </form>

                <?php endif; ?>

                <div class="footer-links">
                    <a href="facebook-login.php">Already have an account?</a>
                    <a href="facebook.php" class="secondary-link">← Back to Facebook home</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
