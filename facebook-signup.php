<?php
/**
 * Facebook Signup Flow
 * Single-page registration with educational guidance
 */

session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$errors = [];
$success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $full_name = sanitizeInput($_POST['full_name']);
    $dob = sanitizeInput($_POST['date_of_birth']);
    $gender = sanitizeInput($_POST['gender'] ?? '');

    // Validation
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!validateEmail($email)) {
        $errors[] = "Please enter a valid email address";
    } elseif (userExists($email, 'facebook')) {
        $errors[] = "An account with this email already exists. Try logging in instead.";
    }

    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long";
    }

    if (empty($full_name)) {
        $errors[] = "Name is required";
    }

    if (empty($dob)) {
        $errors[] = "Date of birth is required";
    }

    if (empty($errors)) {
        // Create the account
        $userData = [
            'email' => $email,
            'password' => $password,
            'full_name' => $full_name,
            'date_of_birth' => $dob
        ];

        if (createUser('facebook', $userData)) {
            $user_id = authenticateUser($email, $password, 'facebook');
            logActivity($user_id, 'facebook', 'register', 'New account created');

            $_SESSION['user_id'] = $user_id;
            $_SESSION['platform'] = 'facebook';

            header('Location: facebook-dashboard.php');
            exit;
        } else {
            $errors[] = "Failed to create account. Please try again.";
        }
    }
}
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
                <h2>Sign Up</h2>
                <p class="subtitle">It's quick and easy.</p>

                <?php if (!empty($errors)): ?>
                    <div class="error-box">
                        <?php foreach ($errors as $error): ?>
                            <p>⚠️ <?= htmlspecialchars($error) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="help-box">
                    <h3>📋 What You'll Need</h3>
                    <ul>
                        <li><strong>Your name:</strong> Use your real name so friends can find you</li>
                        <li><strong>Email address:</strong> We'll send a confirmation to this email</li>
                        <li><strong>Password:</strong> Choose something secure (at least 8 characters)</li>
                        <li><strong>Birthday:</strong> You must be at least 13 years old to use Facebook</li>
                    </ul>
                </div>

                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="full_name">Full Name *</label>
                            <input type="text" id="full_name" name="full_name"
                                   value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>"
                                   placeholder="First and last name" required autofocus>
                            <small>Use your real name so friends can recognize you</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email">Email address *</label>
                        <input type="email" id="email" name="email"
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                               placeholder="Email" required>
                        <small>You'll use this to log in and recover your account</small>
                    </div>

                    <div class="form-group">
                        <label for="password">New password *</label>
                        <input type="password" id="password" name="password"
                               placeholder="Password" required>
                        <small>At least 8 characters</small>
                    </div>

                    <div class="form-group">
                        <label for="date_of_birth">Birthday *</label>
                        <input type="date" id="date_of_birth" name="date_of_birth"
                               value="<?= htmlspecialchars($_POST['date_of_birth'] ?? '') ?>"
                               max="<?= date('Y-m-d', strtotime('-13 years')) ?>" required>
                        <small>You need to be at least 13 years old. Your birthday won't be shared publicly.</small>
                    </div>

                    <div class="form-group">
                        <label>Gender (Optional)</label>
                        <div class="gender-options">
                            <label class="radio-label">
                                <input type="radio" name="gender" value="Female"
                                       <?= ($_POST['gender'] ?? '') === 'Female' ? 'checked' : '' ?>>
                                Female
                            </label>
                            <label class="radio-label">
                                <input type="radio" name="gender" value="Male"
                                       <?= ($_POST['gender'] ?? '') === 'Male' ? 'checked' : '' ?>>
                                Male
                            </label>
                            <label class="radio-label">
                                <input type="radio" name="gender" value="Custom"
                                       <?= ($_POST['gender'] ?? '') === 'Custom' ? 'checked' : '' ?>>
                                Custom
                            </label>
                        </div>
                        <small>This helps us personalize your experience</small>
                    </div>

                    <div class="help-box tips">
                        <h4>💡 Password Tips</h4>
                        <ul>
                            <li>Mix uppercase and lowercase letters</li>
                            <li>Include numbers and symbols</li>
                            <li>Don't use common words or personal information</li>
                            <li>Don't share your password with anyone</li>
                        </ul>
                    </div>

                    <p class="terms">
                        By clicking Sign Up, you agree to our Terms, Privacy Policy and Cookies Policy.
                        <strong>Note:</strong> This is a training environment - these are simulated terms.
                    </p>

                    <button type="submit" class="btn btn-signup">Sign Up</button>
                </form>

                <div class="footer-links">
                    <a href="facebook-login.php">Already have an account?</a>
                    <a href="facebook.php" class="secondary-link">← Back to Facebook home</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
