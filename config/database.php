<?php
/**
 * Database configuration and connection
 * Credentials are loaded from .env file
 */

// Load environment variables from .env file
function loadEnv($path) {
    if (!file_exists($path)) {
        die("Error: .env file not found. Please copy .env.example to .env and configure your database credentials.");
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // Parse KEY=VALUE
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            // Remove quotes if present
            $value = trim($value, '"\'');

            // Set as environment variable
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

// Load .env file
loadEnv(__DIR__ . '/../.env');

// Database credentials from environment variables
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'social_login');

// Create connection
function getDbConnection() {
    static $conn = null;

    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        if ($conn->connect_error) {
            // Log error but don't expose details to user
            error_log("Database connection failed: " . $conn->connect_error);
            die("Database connection error. Please contact the administrator.");
        }

        $conn->set_charset("utf8mb4");
    }

    return $conn;
}

// Initialize database (run this once to set up tables)
function initializeDatabase() {
    $conn = getDbConnection();

    // Users table - stores all practice accounts
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        platform VARCHAR(20) NOT NULL COMMENT 'twitter, facebook, or instagram',
        email VARCHAR(255) NOT NULL,
        username VARCHAR(100) DEFAULT NULL,
        password_hash VARCHAR(255) NOT NULL,
        full_name VARCHAR(255) DEFAULT NULL,
        phone VARCHAR(20) DEFAULT NULL,
        date_of_birth DATE DEFAULT NULL,
        reset_token VARCHAR(64) DEFAULT NULL COMMENT 'Password reset token',
        reset_token_expires TIMESTAMP NULL DEFAULT NULL COMMENT 'Token expiration time',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_login TIMESTAMP NULL DEFAULT NULL,
        is_active BOOLEAN DEFAULT TRUE COMMENT 'Allows soft deletion',
        UNIQUE KEY unique_email_platform (email, platform),
        INDEX idx_platform (platform),
        INDEX idx_email (email),
        INDEX idx_reset_token (reset_token)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    if (!$conn->query($sql)) {
        die("Error creating users table: " . $conn->error);
    }

    // Admin users table
    $sql = "CREATE TABLE IF NOT EXISTS admin_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(100) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        email VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_login TIMESTAMP NULL DEFAULT NULL,
        is_active BOOLEAN DEFAULT TRUE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    if (!$conn->query($sql)) {
        die("Error creating admin_users table: " . $conn->error);
    }

    // Activity log table - tracks user actions for training review
    $sql = "CREATE TABLE IF NOT EXISTS activity_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT DEFAULT NULL,
        platform VARCHAR(20) NOT NULL,
        action VARCHAR(50) NOT NULL COMMENT 'register, login, logout, delete_account, reset_account',
        details TEXT DEFAULT NULL,
        ip_address VARCHAR(45) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
        INDEX idx_platform (platform),
        INDEX idx_action (action),
        INDEX idx_created_at (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    if (!$conn->query($sql)) {
        error_log("Error creating activity_log table: " . $conn->error);
        return false;
    }

    // Rate limits table - for rate limiting security
    $sql = "CREATE TABLE IF NOT EXISTS rate_limits (
        id INT AUTO_INCREMENT PRIMARY KEY,
        action VARCHAR(50) NOT NULL COMMENT 'login, signup, password_reset, etc.',
        identifier VARCHAR(255) NOT NULL COMMENT 'email, username, or other identifier',
        ip_address VARCHAR(45) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_action_identifier (action, identifier),
        INDEX idx_action_ip (action, ip_address),
        INDEX idx_created_at (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    if (!$conn->query($sql)) {
        error_log("Error creating rate_limits table: " . $conn->error);
        return false;
    }

    // Note: Default admin account should be created manually or through setup script
    // for security reasons. Do not create default accounts automatically.

    return true;
}

// Log activity
function logActivity($user_id, $platform, $action, $details = null) {
    $conn = getDbConnection();
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;

    $stmt = $conn->prepare("INSERT INTO activity_log (user_id, platform, action, details, ip_address) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $user_id, $platform, $action, $details, $ip_address);

    return $stmt->execute();
}
