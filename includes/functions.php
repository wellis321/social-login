<?php
/**
 * Common functions used throughout the application
 */

/**
 * Initialize secure session settings
 */
function initSecureSession() {
    if (session_status() === PHP_SESSION_NONE) {
        // Configure secure session settings (only if session hasn't started)
        @ini_set('session.cookie_httponly', 1);
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            @ini_set('session.cookie_secure', 1);
        }
        @ini_set('session.use_strict_mode', 1);
        // session.cookie_samesite may not be available in older PHP versions
        if (version_compare(PHP_VERSION, '7.3.0', '>=')) {
            @ini_set('session.cookie_samesite', 'Strict');
        }

        @session_start();

        // Regenerate session ID periodically to prevent fixation
        if (!isset($_SESSION['created'])) {
            $_SESSION['created'] = time();
        } else if (time() - $_SESSION['created'] > 1800) {
            @session_regenerate_id(true);
            $_SESSION['created'] = time();
        }
    }
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 */
function validateCSRFToken($token) {
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Rate limiting: Check if action is allowed
 */
function checkRateLimit($action, $identifier, $max_attempts = 5, $time_window = 300) {
    $conn = getDbConnection();
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

    // Check if rate_limits table exists
    $table_check = $conn->query("SHOW TABLES LIKE 'rate_limits'");
    if ($table_check->num_rows === 0) {
        // Table doesn't exist yet, allow the action (graceful degradation)
        return true;
    }

    // Clean old entries (older than time_window seconds)
    $cleanup_sql = "DELETE FROM rate_limits WHERE created_at < DATE_SUB(NOW(), INTERVAL $time_window SECOND)";
    $conn->query($cleanup_sql);

    // Count attempts in time window
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM rate_limits WHERE action = ? AND (identifier = ? OR ip_address = ?) AND created_at > DATE_SUB(NOW(), INTERVAL ? SECOND)");
    $stmt->bind_param("sssi", $action, $identifier, $ip_address, $time_window);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $attempts = $row['count'];

    if ($attempts >= $max_attempts) {
        return false;
    }

    // Record this attempt
    $stmt = $conn->prepare("INSERT INTO rate_limits (action, identifier, ip_address) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $action, $identifier, $ip_address);
    $stmt->execute();

    return true;
}

/**
 * Sanitize user input
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Validate email address
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Normalize phone number for consistent storage and matching
 * Removes spaces, dashes, parentheses, and other formatting
 */
function normalizePhone($phone) {
    // Remove all non-digit characters except + at the start
    $normalized = preg_replace('/[^\d+]/', '', $phone);
    // If it starts with +, keep it, otherwise ensure it's just digits
    if (strpos($normalized, '+') === 0) {
        return $normalized;
    }
    return preg_replace('/[^\d]/', '', $phone);
}

/**
 * Check if a value looks like a phone number
 */
function isPhoneNumber($value) {
    // Remove common formatting characters
    $cleaned = preg_replace('/[\s\-\(\)]/', '', $value);
    // Check if it's mostly digits (at least 7 digits) or starts with +
    return (preg_match('/^\+?\d{7,}$/', $cleaned) && !filter_var($value, FILTER_VALIDATE_EMAIL));
}

/**
 * Check if user exists for a given platform
 */
function userExists($email, $platform) {
    $conn = getDbConnection();

    // Normalize email/phone
    $normalized_email = $email;
    if (isPhoneNumber($email)) {
        $normalized_email = normalizePhone($email);
    }

    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND platform = ?");
    $stmt->bind_param("ss", $normalized_email, $platform);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->num_rows > 0;
}

/**
 * Create a new user account
 */
function createUser($platform, $data) {
    $conn = getDbConnection();

    // Normalize phone numbers for consistent storage
    $email = $data['email'];
    if (isPhoneNumber($email)) {
        $email = normalizePhone($email);
    }

    $username = isset($data['username']) ? $data['username'] : null;
    $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
    $full_name = isset($data['full_name']) ? $data['full_name'] : null;
    $phone = isset($data['phone']) ? $data['phone'] : null;
    $dob = isset($data['date_of_birth']) ? $data['date_of_birth'] : null;

    $stmt = $conn->prepare("INSERT INTO users (platform, email, username, password_hash, full_name, phone, date_of_birth) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $platform, $email, $username, $password_hash, $full_name, $phone, $dob);

    return $stmt->execute();
}

/**
 * Authenticate user login
 */
function authenticateUser($email, $password, $platform) {
    $conn = getDbConnection();

    // Normalize the input - if it's a phone number, normalize it
    $input = trim($email);
    if (isPhoneNumber($input)) {
        $input = normalizePhone($input);
    }

    // Try exact match first using prepared statement
    $stmt = $conn->prepare("SELECT id, password_hash, email FROM users WHERE email = ? AND platform = ?");
    $stmt->bind_param("ss", $input, $platform);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password_hash'])) {
            // Update last login using prepared statement
            $user_id = $user['id'];
            $update_stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $update_stmt->bind_param("i", $user_id);
            $update_stmt->execute();
            return $user['id'];
        }
    }

    // If exact match failed and input looks like a phone, try normalized match
    if (isPhoneNumber($input)) {
        $stmt = $conn->prepare("SELECT id, password_hash, email FROM users WHERE platform = ?");
        $stmt->bind_param("s", $platform);
        $stmt->execute();
        $all_users = $stmt->get_result();

        while ($user = $all_users->fetch_assoc()) {
            $stored_email = $user['email'];
            if (isPhoneNumber($stored_email)) {
                $normalized_stored = normalizePhone($stored_email);
                if ($normalized_stored === $input) {
                    if (password_verify($password, $user['password_hash'])) {
                        $user_id = $user['id'];
                        $update_stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                        $update_stmt->bind_param("i", $user_id);
                        $update_stmt->execute();
                        return $user['id'];
                    }
                }
            }
        }
    }

    return false;
}

/**
 * Delete user account
 */
function deleteUser($user_id) {
    $conn = getDbConnection();
    $user_id = intval($user_id);

    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    return $stmt->execute();
}

/**
 * Get user by ID
 */
function getUserById($user_id) {
    $conn = getDbConnection();
    $user_id = intval($user_id);

    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->num_rows === 1 ? $result->fetch_assoc() : null;
}
