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
        // Step 1: First name, Last name, Date of birth, Gender
        $first_name = sanitizeInput($_POST['first_name'] ?? '');
        $last_name = sanitizeInput($_POST['last_name'] ?? '');
        $dob = sanitizeInput($_POST['date_of_birth'] ?? '');
        $gender = sanitizeInput($_POST['gender'] ?? '');
        $pronoun = sanitizeInput($_POST['pronoun'] ?? '');

        if (empty($first_name)) {
            $errors[] = "First name is required";
        }

        if (empty($last_name)) {
            $errors[] = "Last name is required";
        }

        if (empty($dob)) {
            $errors[] = "Date of birth is required";
        }

        if (empty($errors)) {
            $_SESSION['signup_data']['first_name'] = $first_name;
            $_SESSION['signup_data']['last_name'] = $last_name;
            $_SESSION['signup_data']['date_of_birth'] = $dob;
            $_SESSION['signup_data']['gender'] = $gender;
            if ($gender === 'Custom' && !empty($pronoun)) {
                $_SESSION['signup_data']['pronoun'] = $pronoun;
            }
            header('Location: facebook-signup.php?step=2');
            exit;
        }
    } elseif ($step === 2) {
        // Step 2: Mobile number or email, Password
        $contact_value = sanitizeInput($_POST['contact_value'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($contact_value)) {
            $errors[] = "Mobile number or email is required";
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
            // Create the account
            $full_name = trim($_SESSION['signup_data']['first_name'] . ' ' . $_SESSION['signup_data']['last_name']);
            $userData = [
                'email' => $contact_value,
                'password' => $password,
                'full_name' => $full_name,
                'date_of_birth' => $_SESSION['signup_data']['date_of_birth'],
                'gender' => $_SESSION['signup_data']['gender']
            ];

            if (createUser('facebook', $userData)) {
                $user_id = authenticateUser($contact_value, $password, 'facebook');
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
$first_name = $_SESSION['signup_data']['first_name'] ?? '';
$last_name = $_SESSION['signup_data']['last_name'] ?? '';
$dob = $_SESSION['signup_data']['date_of_birth'] ?? '';
$gender = $_SESSION['signup_data']['gender'] ?? '';
$pronoun = $_SESSION['signup_data']['pronoun'] ?? '';
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
                </div>

                <h2>Sign Up</h2>
                <p class="step-indicator">Step <?= $step ?> of 2</p>

                <?php if (!empty($errors)): ?>
                    <div class="error-box">
                        <?php foreach ($errors as $error): ?>
                            <p>⚠️ <?= htmlspecialchars($error) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ($step === 1): ?>
                    <!-- Step 1: First name, Last name, Date of birth, Gender -->
                    <div class="help-box">
                        <h3>👤 Create Your Account</h3>
                        <p><strong>Name:</strong> Use your real first and last name so friends can find you</p>
                        <p><strong>Date of birth:</strong> You must be 13 years or older to use Facebook</p>
                        <p><strong>Gender:</strong> This helps us personalize your experience (optional)</p>
                        <p class="warning-text">⚠️ <strong>Privacy:</strong> Your birthday won't be shared publicly.</p>
                    </div>

                    <form method="POST">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name">First name *</label>
                                <input type="text" id="first_name" name="first_name"
                                       value="<?= htmlspecialchars($first_name) ?>"
                                       placeholder="First name" required autofocus>
                            </div>
                            <div class="form-group">
                                <label for="last_name">Last name *</label>
                                <input type="text" id="last_name" name="last_name"
                                       value="<?= htmlspecialchars($last_name) ?>"
                                       placeholder="Surname" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="date_of_birth">Birthday *</label>
                            <input type="date" id="date_of_birth" name="date_of_birth"
                                   value="<?= htmlspecialchars($dob) ?>"
                                   max="<?= date('Y-m-d', strtotime('-13 years')) ?>" required>
                            <small>You need to be at least 13 years old. Your birthday won't be shared publicly.</small>
                        </div>

                        <div class="form-group">
                            <label>Gender (Optional)</label>
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
                            <small>This helps us personalize your experience</small>
                        </div>

                        <div class="form-group" id="pronoun-group" style="display: <?= $gender === 'Custom' ? 'block' : 'none' ?>;">
                            <label for="pronoun">Select your pronoun</label>
                            <select name="pronoun" id="pronoun">
                                <option value="">Select your pronoun</option>
                                <option value="She" <?= $pronoun === 'She' ? 'selected' : '' ?>>She: "Wish her a happy birthday!"</option>
                                <option value="He" <?= $pronoun === 'He' ? 'selected' : '' ?>>He: "Wish him a happy birthday!"</option>
                                <option value="They" <?= $pronoun === 'They' ? 'selected' : '' ?>>They: "Wish them a happy birthday!"</option>
                            </select>
                            <small>Your pronoun is visible to everyone.</small>
                        </div>

                        <button type="submit" class="btn btn-signup btn-block">Next</button>
                    </form>

                    <script>
                    function togglePronoun() {
                        const customSelected = document.querySelector('input[name="gender"][value="Custom"]').checked;
                        document.getElementById('pronoun-group').style.display = customSelected ? 'block' : 'none';
                    }
                    </script>

                <?php elseif ($step === 2): ?>
                    <!-- Step 2: Mobile number or email, Password -->
                    <div class="help-box">
                        <h3>📧🔒 Complete Your Registration</h3>
                        <p><strong>Mobile number or email:</strong> Use this to log in and recover your account</p>
                        <p><strong>Password:</strong> Choose something secure (at least 8 characters)</p>
                        <p class="warning-text">⚠️ <strong>Note:</strong> Facebook may verify your contact information later for security.</p>
                    </div>

                    <form method="POST">
                        <div class="form-group">
                            <label>Mobile number or email *</label>
                            <input type="text" id="contact_value" name="contact_value"
                                   placeholder="Mobile number or email address" required autofocus>
                            <small>You'll use this to log in and recover your account</small>
                        </div>

                        <div class="form-group">
                            <label for="password">New password *</label>
                            <input type="password" id="password" name="password"
                                   placeholder="New password" required>
                            <small>At least 8 characters. Mix uppercase, lowercase, numbers, and symbols for security.</small>
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
                            By clicking Sign Up, you agree to our Terms, Privacy Policy and Cookies Policy. You may receive SMS Notifications from us and can opt out any time.
                            <strong>Note:</strong> This is a training environment - these are simulated terms.
                        </p>

                        <div class="button-row">
                            <a href="facebook-signup.php?step=1" class="btn btn-secondary">Back</a>
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
