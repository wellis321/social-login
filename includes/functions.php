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
    $email = $conn->real_escape_string($email);
    $platform = $conn->real_escape_string($platform);

    $sql = "SELECT id, password_hash FROM users WHERE email = '$email' AND platform = '$platform'";
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
