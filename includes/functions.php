<?php
/**
 * Common functions used throughout the application
 */

/**
 * Sanitize user input
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
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
    $email = $conn->real_escape_string($email);
    $platform = $conn->real_escape_string($platform);

    $sql = "SELECT id FROM users WHERE email = '$email' AND platform = '$platform'";
    $result = $conn->query($sql);

    return $result->num_rows > 0;
}

/**
 * Create a new user account
 */
function createUser($platform, $data) {
    $conn = getDbConnection();

    $platform = $conn->real_escape_string($platform);
    $email = $conn->real_escape_string($data['email']);

    // Normalize phone numbers for consistent storage
    if (isPhoneNumber($email)) {
        $email = normalizePhone($email);
    }

    $username = isset($data['username']) ? $conn->real_escape_string($data['username']) : null;
    $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
    $full_name = isset($data['full_name']) ? $conn->real_escape_string($data['full_name']) : null;
    $phone = isset($data['phone']) ? $conn->real_escape_string($data['phone']) : null;
    $dob = isset($data['date_of_birth']) ? $conn->real_escape_string($data['date_of_birth']) : null;

    $sql = "INSERT INTO users (platform, email, username, password_hash, full_name, phone, date_of_birth)
            VALUES ('$platform', '$email', " .
            ($username ? "'$username'" : "NULL") . ", " .
            "'$password_hash', " .
            ($full_name ? "'$full_name'" : "NULL") . ", " .
            ($phone ? "'$phone'" : "NULL") . ", " .
            ($dob ? "'$dob'" : "NULL") . ")";

    return $conn->query($sql);
}

/**
 * Authenticate user login
 */
function authenticateUser($email, $password, $platform) {
    $conn = getDbConnection();
    $platform = $conn->real_escape_string($platform);

    // Normalize the input - if it's a phone number, normalize it
    $input = trim($email);
    if (isPhoneNumber($input)) {
        $input = normalizePhone($input);
    }
    $input = $conn->real_escape_string($input);

    // Try exact match first
    $sql = "SELECT id, password_hash, email FROM users WHERE email = '$input' AND platform = '$platform'";
    $result = $conn->query($sql);

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password_hash'])) {
            // Update last login
            $user_id = $user['id'];
            $conn->query("UPDATE users SET last_login = NOW() WHERE id = $user_id");
            return $user['id'];
        }
    }

    // If exact match failed and input looks like a phone, try normalized match
    if (isPhoneNumber($input)) {
        // Get all users for this platform and check normalized phone numbers
        $sql = "SELECT id, password_hash, email FROM users WHERE platform = '$platform'";
        $all_users = $conn->query($sql);

        while ($user = $all_users->fetch_assoc()) {
            $stored_email = $user['email'];
            if (isPhoneNumber($stored_email)) {
                $normalized_stored = normalizePhone($stored_email);
                if ($normalized_stored === $input) {
                    if (password_verify($password, $user['password_hash'])) {
                        $user_id = $user['id'];
                        $conn->query("UPDATE users SET last_login = NOW() WHERE id = $user_id");
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

    $sql = "DELETE FROM users WHERE id = $user_id";
    return $conn->query($sql);
}

/**
 * Get user by ID
 */
function getUserById($user_id) {
    $conn = getDbConnection();
    $user_id = intval($user_id);

    $sql = "SELECT * FROM users WHERE id = $user_id";
    $result = $conn->query($sql);

    return $result->num_rows === 1 ? $result->fetch_assoc() : null;
}
