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
        // Step 1: Initial signup form
        $first_name = sanitizeInput($_POST['first_name'] ?? '');
        $last_name = sanitizeInput($_POST['last_name'] ?? '');
        $day = sanitizeInput($_POST['day'] ?? '');
        $month = sanitizeInput($_POST['month'] ?? '');
        $year = sanitizeInput($_POST['year'] ?? '');
        $gender = sanitizeInput($_POST['gender'] ?? '');
        $pronoun = sanitizeInput($_POST['pronoun'] ?? '');
        $contact_value = sanitizeInput($_POST['contact_value'] ?? '');
        $password = $_POST['password'] ?? '';

        // Validation
        if (empty($first_name)) {
            $errors[] = "First name is required";
        }

        if (empty($last_name)) {
            $errors[] = "Surname is required";
        }

        if (empty($day) || empty($month) || empty($year)) {
            $errors[] = "Date of birth is required";
        } else {
            // Validate date
            if (!checkdate($month, $day, $year)) {
                $errors[] = "Please enter a valid date of birth";
            } else {
                $dob = sprintf('%04d-%02d-%02d', $year, $month, $day);
                // Check age requirement (13 years old)
                $age = date_diff(date_create($dob), date_create('today'))->y;
                if ($age < 13) {
                    $errors[] = "You must be at least 13 years old to use Facebook";
                }
            }
        }

        if (empty($contact_value)) {
            $errors[] = "Mobile number or email address is required";
        } else {
            // Detect if it's an email or phone number
            $is_email = validateEmail($contact_value);
            if ($is_email && userExists($contact_value, 'facebook')) {
                $errors[] = "An account with this email already exists. Try logging in instead.";
            }
        }

        if (empty($password)) {
            $errors[] = "Password is required";
        } elseif (strlen($password) < 8) {
            $errors[] = "Password must be at least 8 characters long";
        }

        if (empty($errors)) {
            // Store signup data and generate verification code
            $_SESSION['signup_data']['first_name'] = $first_name;
            $_SESSION['signup_data']['last_name'] = $last_name;
            $_SESSION['signup_data']['date_of_birth'] = $dob;
            $_SESSION['signup_data']['gender'] = $gender;
            $_SESSION['signup_data']['pronoun'] = $pronoun;
            $_SESSION['signup_data']['email'] = $contact_value;
            $_SESSION['signup_data']['password'] = $password;
            // Generate verification code with FB- prefix
            $_SESSION['signup_data']['verification_code'] = sprintf('FB-%05d', mt_rand(0, 99999));
            header('Location: facebook-signup.php?step=2');
            exit;
        }
    } elseif ($step === 2) {
        // Step 2: Email verification code
        $entered_code = sanitizeInput($_POST['verification_code'] ?? '');
        $expected_code = $_SESSION['signup_data']['verification_code'] ?? '';

        if (empty($entered_code)) {
            $errors[] = "Verification code is required";
        } elseif ($entered_code !== $expected_code) {
            $errors[] = "Incorrect verification code. Please try again.";
        } else {
            // Code verified, show confirmation and move to human verification
            $_SESSION['signup_data']['email_verified'] = true;
            header('Location: facebook-signup.php?step=3');
            exit;
        }
    } elseif ($step === 3) {
        // Step 3: Account confirmed - auto proceed to human verification
        header('Location: facebook-signup.php?step=4');
        exit;
    } elseif ($step === 4) {
        // Step 4: Human verification prompt - auto proceed to CAPTCHA
        header('Location: facebook-signup.php?step=5');
        exit;
    } elseif ($step === 5) {
        // Step 5: CAPTCHA verification
        $captcha_code = sanitizeInput($_POST['captcha_code'] ?? '');
        $expected_captcha = $_SESSION['signup_data']['captcha_code'] ?? '';

        if (empty($captcha_code)) {
            $errors[] = "Please enter the code from the image";
        } elseif ($captcha_code !== $expected_captcha) {
            $errors[] = "Incorrect code. Please try again.";
        } else {
            // CAPTCHA verified, proceed to video selfie (optional)
            $_SESSION['signup_data']['captcha_verified'] = true;
            header('Location: facebook-signup.php?step=6');
            exit;
        }
    } elseif ($step === 6) {
        // Step 6: Video selfie - can skip for training
        $skip_video = isset($_POST['skip_video']) && $_POST['skip_video'] === 'yes';

        if ($skip_video || isset($_POST['video_uploaded'])) {
            // Create the account
            $full_name = trim($_SESSION['signup_data']['first_name'] . ' ' . $_SESSION['signup_data']['last_name']);
            $userData = [
                'email' => $_SESSION['signup_data']['email'],
                'password' => $_SESSION['signup_data']['password'],
                'full_name' => $full_name,
                'date_of_birth' => $_SESSION['signup_data']['date_of_birth'],
                'gender' => $_SESSION['signup_data']['gender']
            ];

            if (createUser('facebook', $userData)) {
                $user_id = authenticateUser($_SESSION['signup_data']['email'], $_SESSION['signup_data']['password'], 'facebook');
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

// Generate CAPTCHA code if needed
if ($step === 5 && !isset($_SESSION['signup_data']['captcha_code'])) {
    $_SESSION['signup_data']['captcha_code'] = sprintf('%06d', mt_rand(100000, 999999));
}

// Get form data for repopulation
$first_name = $_SESSION['signup_data']['first_name'] ?? ($_POST['first_name'] ?? '');
$last_name = $_SESSION['signup_data']['last_name'] ?? ($_POST['last_name'] ?? '');
$day = $_POST['day'] ?? '';
$month = $_POST['month'] ?? '';
$year = $_POST['year'] ?? '';
$gender = $_SESSION['signup_data']['gender'] ?? ($_POST['gender'] ?? '');
$pronoun = $_SESSION['signup_data']['pronoun'] ?? ($_POST['pronoun'] ?? '');
$contact_value = $_SESSION['signup_data']['email'] ?? ($_POST['contact_value'] ?? '');
$verification_code = $_SESSION['signup_data']['verification_code'] ?? '';
$captcha_code = $_SESSION['signup_data']['captcha_code'] ?? '';
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
                <?php if ($step === 1): ?>
                    <h2>Create a new account</h2>
                    <p class="subtitle">It's quick and easy.</p>

                    <?php if (!empty($errors)): ?>
                        <div class="error-box">
                            <?php foreach ($errors as $error): ?>
                                <p>⚠ <?= htmlspecialchars($error) ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <input type="text" id="first_name" name="first_name"
                                   value="<?= htmlspecialchars($first_name) ?>"
                                   placeholder="First name" required autofocus>
                        </div>
                        <div class="form-group">
                            <input type="text" id="last_name" name="last_name"
                                   value="<?= htmlspecialchars($last_name) ?>"
                                   placeholder="Surname" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="date_of_birth">Date of birth <span style="color: #1877f2; cursor: help;" title="We use your date of birth to help keep your account secure and to show you age-appropriate content.">?</span></label>
                        <div class="form-row" style="gap: 8px;">
                            <div class="form-group" style="flex: 1;">
                                <select name="day" id="day" required style="width: 100%;">
                                    <option value="">Day</option>
                                    <?php for ($i = 1; $i <= 31; $i++): ?>
                                        <option value="<?= $i ?>" <?= $day == $i ? 'selected' : '' ?>><?= $i ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="form-group" style="flex: 1;">
                                <select name="month" id="month" required style="width: 100%;">
                                    <option value="">Month</option>
                                    <?php
                                    $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                                    foreach ($months as $idx => $month_name) {
                                        $month_num = $idx + 1;
                                        echo "<option value=\"$month_num\"" . ($month == $month_num ? ' selected' : '') . ">$month_name</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group" style="flex: 1;">
                                <select name="year" id="year" required style="width: 100%;">
                                    <option value="">Year</option>
                                    <?php
                                    $current_year = date('Y');
                                    for ($i = $current_year; $i >= $current_year - 100; $i--):
                                    ?>
                                        <option value="<?= $i ?>" <?= $year == $i ? 'selected' : '' ?>><?= $i ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Gender <span style="color: #1877f2; cursor: help;" title="You can change who can see this later.">?</span></label>
                        <div class="gender-options">
                            <label class="radio-label">
                                <input type="radio" name="gender" value="Female" <?= $gender === 'Female' ? 'checked' : '' ?>>
                                Female
                            </label>
                            <label class="radio-label">
                                <input type="radio" name="gender" value="Male" <?= $gender === 'Male' ? 'checked' : '' ?>>
                                Male
                            </label>
                            <label class="radio-label">
                                <input type="radio" name="gender" value="Custom" <?= $gender === 'Custom' ? 'checked' : '' ?> onchange="togglePronoun()">
                                Custom
                            </label>
                        </div>
                        <small style="color: #65676b; margin-top: 8px; display: block;">Your gender won't be visible to others unless you choose to share it.</small>
                    </div>

                    <div class="form-group" id="pronoun-group" style="display: <?= $gender === 'Custom' ? 'block' : 'none' ?>;">
                        <select name="pronoun" id="pronoun">
                            <option value="">Select your pronoun</option>
                            <option value="She" <?= $pronoun === 'She' ? 'selected' : '' ?>>She: "Wish her a happy birthday!"</option>
                            <option value="He" <?= $pronoun === 'He' ? 'selected' : '' ?>>He: "Wish him a happy birthday!"</option>
                            <option value="They" <?= $pronoun === 'They' ? 'selected' : '' ?>>They: "Wish them a happy birthday!"</option>
                        </select>
                        <small>Your pronoun is visible to everyone.</small>
                    </div>

                    <div class="form-group">
                        <input type="text" id="contact_value" name="contact_value"
                               value="<?= htmlspecialchars($contact_value) ?>"
                               placeholder="Mobile number or email address" required>
                    </div>

                    <div class="form-group">
                        <label for="password">New password *</label>
                        <input type="password" id="password" name="password"
                               placeholder="New password" required>
                        <small style="color: #65676b; margin-top: 6px; display: block;">
                            Use at least 8 characters. Mix uppercase and lowercase letters, numbers, and symbols for better security.
                        </small>
                    </div>

                    <div class="help-box" style="margin-bottom: 16px;">
                        <h4 style="color: #1877f2; margin-bottom: 8px; font-size: 13px;">🔒 Password Tips</h4>
                        <ul style="margin: 0; padding-left: 20px; font-size: 12px;">
                            <li>Use at least 8 characters</li>
                            <li>Mix uppercase and lowercase letters</li>
                            <li>Include numbers and symbols (!@#$%)</li>
                            <li>Don't use common words like "password" or "123456"</li>
                            <li>Don't use your name or birthday</li>
                            <li><strong>Never share your password with anyone!</strong></li>
                        </ul>
                    </div>

                    <p class="terms">
                        People who use our service may have uploaded your contact information to Facebook. <a href="#" style="color: #1877f2;">Learn more</a>.
                    </p>

                    <p class="terms">
                        By clicking Sign Up, you agree to our <a href="#" style="color: #1877f2;">Terms</a>. Learn how we collect, use and share your data in our <a href="#" style="color: #1877f2;">Privacy Policy</a> and how we use cookies and similar technology in our <a href="#" style="color: #1877f2;">Cookies Policy</a>. You may receive SMS notifications from us and can opt out at any time.
                    </p>

                    <button type="submit" class="btn btn-signup btn-block">Sign Up</button>
                </form>

                <script>
                function togglePronoun() {
                    const customSelected = document.querySelector('input[name="gender"][value="Custom"]').checked;
                    document.getElementById('pronoun-group').style.display = customSelected ? 'block' : 'none';
                }
                </script>

                <?php elseif ($step === 2): ?>
                    <!-- Step 2: Email Verification Code -->
                    <h2>Enter the code from your email</h2>
                    <p>Let us know that this email address belongs to you. Enter the code from the email sent to <strong><?= htmlspecialchars($contact_value) ?></strong>.</p>

                    <?php if (!empty($errors)): ?>
                        <div class="error-box">
                            <?php foreach ($errors as $error): ?>
                                <p>⚠ <?= htmlspecialchars($error) ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="form-group">
                            <input type="text" name="verification_code" id="verification_code"
                                   placeholder="FB-" value="FB-" maxlength="9" required autofocus
                                   style="font-size: 16px; letter-spacing: 2px;">
                        </div>

                        <div style="margin: 16px 0;">
                            <a href="#" style="color: #1877f2; text-decoration: none;">Send Email Again</a>
                        </div>

                        <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                            <button type="button" class="btn btn-secondary" onclick="window.location.href='facebook-signup.php?step=1'">Update Contact Info</button>
                            <button type="submit" class="btn btn-signup" id="continue-btn">Continue</button>
                        </div>
                    </form>

                    <div style="background: #e7f3ff; border: 1px solid #1877f2; padding: 16px; border-radius: 8px; margin-top: 20px;">
                        <p style="color: #1877f2; margin: 0; font-size: 12px;"><strong>✉ Training Mode - Your Code:</strong></p>
                        <p style="color: #1877f2; font-size: 24px; font-weight: 700; margin: 8px 0; letter-spacing: 4px; font-family: monospace; text-align: center;"><?= $verification_code ?></p>
                        <p style="color: #1877f2; font-size: 12px; margin: 0; text-align: center;">In real life, you'd receive this via email. Copy and paste it above!</p>
                    </div>

                    <script>
                    // Ensure FB- prefix is always present and only allow numbers after it
                    const codeInput = document.getElementById('verification_code');
                    codeInput.addEventListener('input', function(e) {
                        let value = e.target.value;

                        // Remove everything except FB- prefix and numbers
                        const prefix = 'FB-';
                        let numbers = value.replace(/^FB-/, '').replace(/[^0-9]/g, '');

                        // Limit to 5 digits after FB-
                        if (numbers.length > 5) {
                            numbers = numbers.substring(0, 5);
                        }

                        // Reconstruct value with prefix
                        value = prefix + numbers;
                        e.target.value = value;
                    });

                    // Handle paste events
                    codeInput.addEventListener('paste', function(e) {
                        e.preventDefault();
                        let pasted = (e.clipboardData || window.clipboardData).getData('text');

                        // Extract numbers from pasted text
                        let numbers = pasted.replace(/^FB-?/i, '').replace(/[^0-9]/g, '');

                        // Limit to 5 digits
                        if (numbers.length > 5) {
                            numbers = numbers.substring(0, 5);
                        }

                        // Set value with FB- prefix
                        e.target.value = 'FB-' + numbers;
                    });
                    </script>

                <?php elseif ($step === 3): ?>
                    <!-- Step 3: Account Confirmed Modal -->
                    <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000;">
                        <div style="background: white; border-radius: 8px; padding: 24px; max-width: 400px; width: 90%; box-shadow: 0 4px 12px rgba(0,0,0,0.3);">
                            <div style="background: #1877f2; color: white; padding: 16px; border-radius: 8px 8px 0 0; margin: -24px -24px 20px -24px;">
                                <h2 style="color: white; margin: 0; font-size: 20px;">Account Confirmed</h2>
                            </div>
                            <p>You have successfully confirmed your account with the email <strong><?= htmlspecialchars($contact_value) ?></strong>. You will use this email address to log in.</p>
                            <form method="POST" style="text-align: right; margin-top: 20px;">
                                <button type="submit" class="btn btn-signup">OK</button>
                            </form>
                        </div>
                    </div>
                    <script>
                    // Auto-redirect after 2 seconds if user doesn't click OK
                    setTimeout(function() {
                        document.querySelector('form').submit();
                    }, 2000);
                    </script>

                <?php elseif ($step === 4): ?>
                    <!-- Step 4: Human Verification Prompt (auto-redirects) -->
                    <div style="text-align: center; padding: 40px 20px;">
                        <div style="font-size: 64px; margin-bottom: 20px;">🛡</div>
                        <h2><?= htmlspecialchars($first_name . ' ' . $last_name) ?>, confirm that you're human to use your account</h2>
                        <form method="POST" style="margin-top: 30px;">
                            <button type="submit" class="btn btn-signup btn-block">Continue</button>
                        </form>
                    </div>

                <?php elseif ($step === 5): ?>
                    <!-- Step 5: CAPTCHA Verification -->
                    <h2>Confirm that you're human</h2>
                    <p>Enter the text from the image.</p>

                    <?php if (!empty($errors)): ?>
                        <div class="error-box">
                            <?php foreach ($errors as $error): ?>
                                <p>⚠ <?= htmlspecialchars($error) ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div style="background: #f0f2f5; border: 1px solid #dddfe2; border-radius: 8px; padding: 24px; margin: 20px 0; text-align: center;">
                            <div style="font-size: 48px; font-weight: 700; color: #1c1e21; letter-spacing: 8px; margin: 20px 0; position: relative;">
                                <span style="text-decoration: line-through; opacity: 0.3; position: absolute; top: 0; left: 0; width: 100%;">━━━━━━</span>
                                <span style="position: relative; z-index: 1;"><?= $captcha_code ?></span>
                            </div>
                            <p style="color: #65676b; font-size: 14px; margin-top: 16px;">
                                Can't read this text? <a href="#" style="color: #1877f2;">Hear this code</a>
                            </p>
                        </div>

                        <div class="form-group">
                            <input type="text" name="captcha_code" id="captcha_code"
                                   placeholder="Enter code" maxlength="6" required autofocus
                                   style="text-align: center; font-size: 18px; letter-spacing: 4px;">
                        </div>

                        <div style="background: #e0f2fe; border: 1px solid #1877f2; padding: 16px; border-radius: 8px; margin-top: 20px;">
                            <p style="color: #1877f2; margin: 0; font-size: 12px;"><strong>🔒 Training Mode - CAPTCHA Code:</strong></p>
                            <p style="color: #1877f2; font-size: 24px; font-weight: 700; margin: 8px 0; letter-spacing: 8px; font-family: monospace; text-align: center;"><?= $captcha_code ?></p>
                            <p style="color: #1877f2; font-size: 12px; margin: 0; text-align: center;">Enter this code above!</p>
                        </div>

                        <button type="submit" class="btn btn-signup btn-block" style="margin-top: 20px;">Continue</button>
                    </form>

                <?php elseif ($step === 6): ?>
                    <!-- Step 6: Video Selfie (optional) -->
                    <h2>Confirm your identity with a video selfie</h2>
                    <p>To make sure that you're a real person, we need you to record a video selfie. We'll ask you to move your head during the recording to help us capture your face at different angles.</p>

                    <form method="POST">
                        <div style="background: #f0f2f5; border: 1px solid #dddfe2; border-radius: 8px; padding: 24px; margin: 20px 0; text-align: center;">
                            <div style="font-size: 64px; margin-bottom: 16px;">📹</div>
                            <p style="color: #65676b; font-size: 14px; margin: 16px 0;">
                                <strong>Training Mode:</strong> Video selfie verification is simulated. In real life, you would record a video here.
                            </p>
                            <p style="color: #65676b; font-size: 12px; margin-top: 16px;">
                                Your selfie will only be used to confirm your identity and for the safety, security and integrity of our platform. It will be deleted within 30 days.
                            </p>
                        </div>

                        <div style="display: flex; gap: 10px; margin-top: 20px;">
                            <button type="submit" name="skip_video" value="yes" class="btn btn-secondary" style="flex: 1;">Skip for Training</button>
                            <button type="submit" name="video_uploaded" value="yes" class="btn btn-signup" style="flex: 1;">Upload Video</button>
                        </div>
                    </form>

                <?php endif; ?>

                <?php if ($step === 1): ?>
                <div class="footer-links">
                    <a href="facebook-login.php">Already have an account?</a>
                    <a href="facebook.php" class="secondary-link">← Back to Facebook home</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
