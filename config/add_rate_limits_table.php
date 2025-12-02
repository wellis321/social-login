<?php
/**
 * Migration script to add rate_limits table
 * Run this once to add the rate limiting table to existing databases
 */

require_once __DIR__ . '/database.php';

$conn = getDbConnection();

// Create rate_limits table
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

if ($conn->query($sql)) {
    echo "✓ Rate limits table created successfully!\n";
} else {
    echo "✗ Error creating rate_limits table: " . $conn->error . "\n";
}

echo "\nMigration complete!\n";
