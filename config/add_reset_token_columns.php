<?php
/**
 * Migration script to add reset_token columns to users table
 * Run this once to update existing databases
 */

require_once __DIR__ . '/database.php';

$conn = getDbConnection();

// Check if columns already exist
$check_columns = $conn->query("SHOW COLUMNS FROM users LIKE 'reset_token'");
if ($check_columns->num_rows > 0) {
    echo "✓ reset_token column already exists\n";
} else {
    // Add reset_token column
    $sql = "ALTER TABLE users ADD COLUMN reset_token VARCHAR(64) DEFAULT NULL COMMENT 'Password reset token' AFTER date_of_birth";
    if ($conn->query($sql)) {
        echo "✓ Added reset_token column\n";
    } else {
        echo "✗ Error adding reset_token column: " . $conn->error . "\n";
    }
}

$check_expires = $conn->query("SHOW COLUMNS FROM users LIKE 'reset_token_expires'");
if ($check_expires->num_rows > 0) {
    echo "✓ reset_token_expires column already exists\n";
} else {
    // Add reset_token_expires column
    $sql = "ALTER TABLE users ADD COLUMN reset_token_expires TIMESTAMP NULL DEFAULT NULL COMMENT 'Token expiration time' AFTER reset_token";
    if ($conn->query($sql)) {
        echo "✓ Added reset_token_expires column\n";
    } else {
        echo "✗ Error adding reset_token_expires column: " . $conn->error . "\n";
    }
}

// Add index if it doesn't exist
$check_index = $conn->query("SHOW INDEX FROM users WHERE Key_name = 'idx_reset_token'");
if ($check_index->num_rows > 0) {
    echo "✓ idx_reset_token index already exists\n";
} else {
    $sql = "ALTER TABLE users ADD INDEX idx_reset_token (reset_token)";
    if ($conn->query($sql)) {
        echo "✓ Added idx_reset_token index\n";
    } else {
        echo "✗ Error adding index: " . $conn->error . "\n";
    }
}

echo "\nMigration complete!\n";
