<?php
/**
 * Instagram Signup Flow
 * Multi-step registration matching real Instagram flow
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
        // Step 1: Mobile/Email, Password, Full Name, Username (all on one page)
        $contact_value = sanitizeInput($_POST['contact_value'] ?? '');
        $password = $_POST['password'] ?? '';
        $full_name = sanitizeInput($_POST['full_name'] ?? '');
        $username = sanitizeInput($_POST['username'] ?? '');

        if (empty($contact_value)) {
            $errors[] = "Mobile number or email address is required";
        } else {
            $is_email = validateEmail($contact_value);
            if ($is_email && userExists($contact_value, 'instagram')) {
                $errors[] = "An account with this email already exists. Try logging in instead.";
            }
        }

        if (empty($password)) {
            $errors[] = "Password is required";
        } elseif (strlen($password) < 6) {
            $errors[] = "Password must be at least 6 characters long";
        }

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

        if (empty($errors)) {
            $_SESSION['signup_data']['email'] = $contact_value;
            $_SESSION['signup_data']['password'] = $password;
            $_SESSION['signup_data']['full_name'] = $full_name;
            $_SESSION['signup_data']['username'] = $username;
            header('Location: instagram-signup.php?step=2');
            exit;
        }
    } elseif ($step === 2) {
        // Step 2: Date of birth
        $month = sanitizeInput($_POST['month'] ?? '');
        $day = sanitizeInput($_POST['day'] ?? '');
        $year = sanitizeInput($_POST['year'] ?? '');

        if (empty($month) || empty($day) || empty($year)) {
            $errors[] = "Please select your complete date of birth";
        } else {
            // Validate date
            $dob = sprintf('%04d-%02d-%02d', $year, $month, $day);
            if (!checkdate($month, $day, $year)) {
                $errors[] = "Please enter a valid date of birth";
            } else {
                // Check age requirement (13 years old)
                $age = date_diff(date_create($dob), date_create('today'))->y;
                if ($age < 13) {
                    $errors[] = "You must be at least 13 years old to use Instagram";
                } else {
                    $_SESSION['signup_data']['date_of_birth'] = $dob;
                    header('Location: instagram-signup.php?step=3');
                    exit;
                }
            }
        }
    } elseif ($step === 3) {
        // Step 3: CAPTCHA verification (simulated)
        $captcha_verified = isset($_POST['captcha_verified']) && $_POST['captcha_verified'] === 'yes';

        if (!$captcha_verified) {
            $errors[] = "Please complete the security check";
        } else {
            // Generate verification code
            $_SESSION['signup_data']['verification_code'] = sprintf('%06d', mt_rand(0, 999999));
            header('Location: instagram-signup.php?step=4');
            exit;
        }
    } elseif ($step === 4) {
        // Step 4: Verification code
        $entered_code = sanitizeInput($_POST['verification_code'] ?? '');
        $expected_code = $_SESSION['signup_data']['verification_code'] ?? '';

        if (empty($entered_code)) {
            $errors[] = "Verification code is required";
        } elseif ($entered_code !== $expected_code) {
            $errors[] = "Incorrect verification code. Please try again.";
        } else {
            // Create the account
            $userData = [
                'email' => $_SESSION['signup_data']['email'],
                'password' => $_SESSION['signup_data']['password'],
                'full_name' => $_SESSION['signup_data']['full_name'],
                'username' => $_SESSION['signup_data']['username'],
                'date_of_birth' => $_SESSION['signup_data']['date_of_birth']
            ];

            if (createUser('instagram', $userData)) {
                $user_id = authenticateUser($_SESSION['signup_data']['email'], $_SESSION['signup_data']['password'], 'instagram');
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
$contact_value = $_SESSION['signup_data']['email'] ?? '';
$full_name = $_SESSION['signup_data']['full_name'] ?? '';
$username = $_SESSION['signup_data']['username'] ?? '';
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
<body style="background-color: #000;">
    <div class="instagram-signup-page">
        <div class="signup-container">
            <div class="signup-card">
                <h1 class="instagram-logo">Instagram</h1>
                <p class="signup-subtitle">Sign up to see photos and videos from your friends.</p>

                <?php if (!empty($errors)): ?>
                    <div class="error-box">
                        <?php foreach ($errors as $error): ?>
                            <p>⚠ <?= htmlspecialchars($error) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ($step === 1): ?>
                    <!-- Step 1: Mobile/Email, Password, Full Name, Username -->
                    <form method="POST">
                        <div class="form-group">
                            <input type="text" name="contact_value"
                                   value="<?= htmlspecialchars($contact_value) ?>"
                                   placeholder="Mobile number or email address" required autofocus>
                        </div>

                        <div class="form-group">
                            <input type="password" name="password"
                                   placeholder="Password" required>
                        </div>

                        <div class="form-group">
                            <input type="text" name="full_name"
                                   value="<?= htmlspecialchars($full_name) ?>"
                                   placeholder="Full Name" required>
                        </div>

                        <div class="form-group">
                            <input type="text" name="username"
                                   value="<?= htmlspecialchars($username) ?>"
                                   placeholder="Username"
                                   pattern="[a-zA-Z0-9._]{1,30}" required>
                        </div>

                        <p class="terms">
                            People who use our service may have uploaded your contact information to Instagram. <a href="#" style="color: #0095f6;">Learn more</a>
                        </p>

                        <p class="terms">
                            By signing up, you agree to our <a href="#" style="color: #0095f6;">Terms</a>. Learn how we collect, use and share your data in our <a href="#" style="color: #0095f6;">Privacy Policy</a> and how we use cookies and similar technology in our <a href="#" style="color: #0095f6;">Cookies Policy</a>.
                        </p>

                        <button type="submit" class="btn btn-signup-submit btn-block">Next</button>
                    </form>

                <?php elseif ($step === 2): ?>
                    <!-- Step 2: Date of birth -->
                    <div style="text-align: center; margin: 20px 0;">
                        <div style="font-size: 48px; margin-bottom: 16px;">🎂</div>
                        <h2 style="color: #fff; margin-bottom: 8px;">Add your date of birth</h2>
                        <p style="color: #8e8e8e; margin-bottom: 20px;">This won't be part of your public profile.</p>
                        <a href="#" style="color: #0095f6; text-decoration: none;">Why do I need to provide my date of birth?</a>
                    </div>

                    <form method="POST">
                        <div class="form-row" style="gap: 8px;">
                            <div class="form-group" style="flex: 1;">
                                <select name="month" id="month" required style="width: 100%; padding: 12px; background-color: #262626; border: 1px solid #363636; border-radius: 4px; color: #fff; font-size: 14px;">
                                    <option value="">Month</option>
                                    <?php
                                    $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                                    foreach ($months as $idx => $month_name) {
                                        $month_num = $idx + 1;
                                        echo "<option value=\"$month_num\">$month_name</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group" style="flex: 1;">
                                <select name="day" id="day" required style="width: 100%; padding: 12px; background-color: #262626; border: 1px solid #363636; border-radius: 4px; color: #fff; font-size: 14px;">
                                    <option value="">Day</option>
                                    <?php for ($i = 1; $i <= 31; $i++): ?>
                                        <option value="<?= $i ?>"><?= $i ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="form-group" style="flex: 1;">
                                <select name="year" id="year" required style="width: 100%; padding: 12px; background-color: #262626; border: 1px solid #363636; border-radius: 4px; color: #fff; font-size: 14px;">
                                    <option value="">Year</option>
                                    <?php
                                    $current_year = date('Y');
                                    for ($i = $current_year; $i >= $current_year - 100; $i--):
                                    ?>
                                        <option value="<?= $i ?>"><?= $i ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>

                        <p style="color: #8e8e8e; font-size: 12px; margin: 16px 0; text-align: center;">
                            Use your own date of birth, even if this account is for a business, pet or something else
                        </p>

                        <button type="submit" class="btn btn-signup-submit btn-block">Next</button>
                        <div style="text-align: center; margin-top: 16px;">
                            <a href="instagram-signup.php?step=1" style="color: #fff; text-decoration: none;">Go back</a>
                        </div>
                    </form>

                <?php elseif ($step === 3): ?>
                    <!-- Step 3: CAPTCHA Verification -->
                    <div style="text-align: center; margin: 20px 0;">
                        <h2 style="color: #fff; margin-bottom: 16px;">Security check</h2>
                        <p style="color: #8e8e8e; margin-bottom: 20px;">This is a standard security test that we use to prevent spammers from sending automated requests.</p>
                    </div>

                    <form method="POST">
                        <div style="background: #262626; border: 1px solid #363636; border-radius: 8px; padding: 24px; margin-bottom: 20px;">
                            <div style="background: #1877f2; color: #fff; padding: 16px; border-radius: 4px; margin-bottom: 16px; text-align: center; font-weight: 600;">
                                Select all images with cars
                            </div>
                            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px;">
                                <?php for ($i = 1; $i <= 9; $i++): ?>
                                    <div style="aspect-ratio: 1; background: #363636; border: 2px solid #363636; border-radius: 4px; cursor: pointer; display: flex; align-items: center; justify-content: center; color: #8e8e8e; font-size: 12px;"
                                         onclick="selectImage(this, <?= $i % 3 === 0 ? 'true' : 'false' ?>)">
                                        Image <?= $i ?>
                                    </div>
                                <?php endfor; ?>
                            </div>
                            <input type="hidden" name="captcha_verified" id="captcha_verified" value="no">
                            <button type="button" onclick="verifyCaptcha()" class="btn btn-signup-submit" style="margin-top: 16px; width: 100%;">VERIFY</button>
                        </div>

                        <button type="submit" class="btn btn-signup-submit btn-block" id="next-btn" style="display: none;">Next</button>
                        <div style="text-align: center; margin-top: 16px;">
                            <a href="instagram-signup.php?step=2" style="color: #fff; text-decoration: none;">Go back</a>
                        </div>
                    </form>

                    <script>
                    let selectedImages = [];
                    function selectImage(el, hasCar) {
                        if (el.style.borderColor === 'rgb(0, 149, 246)') {
                            el.style.borderColor = '#363636';
                            el.style.background = '#363636';
                            selectedImages = selectedImages.filter(i => i !== el);
                        } else {
                            el.style.borderColor = '#0095f6';
                            el.style.background = '#1a4d7a';
                            selectedImages.push(el);
                        }
                    }
                    function verifyCaptcha() {
                        // Simulate CAPTCHA verification
                        document.getElementById('captcha_verified').value = 'yes';
                        document.getElementById('next-btn').style.display = 'block';
                        alert('Security check passed! Click Next to continue.');
                    }
                    </script>

                <?php elseif ($step === 4): ?>
                    <!-- Step 4: Verification Code -->
                    <div style="text-align: center; margin: 20px 0;">
                        <div style="font-size: 48px; margin-bottom: 16px;">📱</div>
                        <h2 style="color: #fff; margin-bottom: 8px;">Just one more step</h2>
                        <p style="color: #8e8e8e; margin-bottom: 8px;">Enter the 6-digit code we sent to:</p>
                        <p style="color: #fff; font-weight: 600; margin-bottom: 20px;"><?= htmlspecialchars($contact_value) ?></p>
                    </div>

                    <form method="POST">
                        <div class="form-group">
                            <input type="text" name="verification_code" id="verification_code"
                                   placeholder="######" maxlength="6" pattern="[0-9]{6}"
                                   required autofocus style="font-size: 32px; letter-spacing: 12px; text-align: center; font-family: monospace; padding: 16px;">
                        </div>

                        <button type="submit" class="btn btn-signup-submit btn-block">Confirm</button>

                        <div style="text-align: center; margin-top: 16px; color: #0095f6;">
                            <a href="#" style="color: #0095f6; text-decoration: none; margin: 0 8px;">Change number</a> |
                            <a href="#" style="color: #0095f6; text-decoration: none; margin: 0 8px;">Request new code</a>
                        </div>

                        <div style="text-align: center; margin-top: 20px;">
                            <a href="instagram-signup.php?step=3" style="color: #fff; text-decoration: none;">Go back</a>
                        </div>
                    </form>

                    <div style="background: #e0f2fe; border: 1px solid #0095f6; padding: 16px; border-radius: 8px; margin-top: 20px;">
                        <p style="color: #0095f6; margin: 0; font-size: 12px;"><strong>📱 Training Mode - Your Code:</strong></p>
                        <p style="color: #0095f6; font-size: 32px; font-weight: 700; margin: 8px 0; letter-spacing: 8px; font-family: monospace; text-align: center;"><?= $verification_code ?></p>
                        <p style="color: #0095f6; font-size: 12px; margin: 0; text-align: center;">In real life, you'd receive this via SMS or email. Copy and paste it above!</p>
                    </div>

                <?php endif; ?>

                <div class="login-box">
                    <p>Have an account? <a href="instagram-login.php">Log in</a></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
