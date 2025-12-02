<?php
/**
 * Twitter/X Signup Flow
 * Multi-step registration with educational guidance
 */

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$errors = [];
$success = false;
$step = isset($_GET['step']) ? intval($_GET['step']) : 1;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step === 1) {
        // Step 1: Email validation
        $email = sanitizeInput($_POST['email']);

        if (empty($email)) {
            $errors[] = "Email is required";
        } elseif (!validateEmail($email)) {
            $errors[] = "Please enter a valid email address";
        } elseif (userExists($email, 'twitter')) {
            $errors[] = "An account with this email already exists. Try logging in instead.";
        } else {
            $_SESSION['signup_data']['email'] = $email;
            header('Location: twitter-signup.php?step=2');
            exit;
        }
    } elseif ($step === 2) {
        // Step 2: Password creation
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
            header('Location: twitter-signup.php?step=3');
            exit;
        }
    } elseif ($step === 3) {
        // Step 3: Profile information
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
                $user_id = getUserById($userData['email']); // Get created user
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

// Get stored data if going back
$email = $_SESSION['signup_data']['email'] ?? '';
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
            </div>

            <h1>Create your account</h1>
            <p class="step-indicator">Step <?= $step ?> of 3</p>

            <?php if (!empty($errors)): ?>
                <div class="error-box">
                    <?php foreach ($errors as $error): ?>
                        <p>⚠️ <?= htmlspecialchars($error) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($step === 1): ?>
                <!-- Step 1: Email -->
                <div class="help-box">
                    <h3>📧 Why do we need your email?</h3>
                    <p>Your email address is used to:</p>
                    <ul>
                        <li>Verify your identity</li>
                        <li>Send you notifications (if you choose)</li>
                        <li>Help you recover your account if you forget your password</li>
                    </ul>
                    <p><strong>Tip:</strong> Use an email address you check regularly!</p>
                </div>

                <form method="POST">
                    <div class="form-group">
                        <label for="email">Email address *</label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>"
                               placeholder="example@email.com" required autofocus>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Next</button>
                </form>

            <?php elseif ($step === 2): ?>
                <!-- Step 2: Password -->
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
                        <a href="twitter-signup.php?step=1" class="btn btn-secondary">Back</a>
                        <button type="submit" class="btn btn-primary">Next</button>
                    </div>
                </form>

            <?php elseif ($step === 3): ?>
                <!-- Step 3: Profile Info -->
                <div class="help-box">
                    <h3>👤 Your Profile Information</h3>
                    <p><strong>Username:</strong> This is how people will find you on X. It appears as @username</p>
                    <p><strong>Name:</strong> Your full name or the name you want to display</p>
                    <p><strong>Date of birth:</strong> You must be 13 years or older to use X</p>
                    <p class="warning-text">⚠️ <strong>IMPORTANT:</strong> Your username must be unique - no one else can have the same username!</p>
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
                        <a href="twitter-signup.php?step=2" class="btn btn-secondary">Back</a>
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
