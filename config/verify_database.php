<?php
/**
 * Verify database setup
 * Shows all tables and their structure
 */

require_once __DIR__ . '/database.php';

echo "Verifying database setup...\n\n";

try {
    $conn = getDbConnection();

    // Show all tables
    echo "=== TABLES IN DATABASE ===\n";
    $result = $conn->query("SHOW TABLES");

    while ($row = $result->fetch_array()) {
        echo "✓ " . $row[0] . "\n";
    }

    echo "\n=== TABLE STRUCTURES ===\n\n";

    // Show structure of each table
    $tables = ['users', 'admin_users', 'activity_log'];

    foreach ($tables as $table) {
        echo "--- $table ---\n";
        $result = $conn->query("DESCRIBE $table");

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                echo "  {$row['Field']} ({$row['Type']})";
                if ($row['Key']) echo " [{$row['Key']}]";
                if ($row['Default'] !== null) echo " DEFAULT: {$row['Default']}";
                echo "\n";
            }
        }
        echo "\n";
    }

    // Show admin users
    echo "=== ADMIN ACCOUNTS ===\n";
    $result = $conn->query("SELECT id, username, email, created_at FROM admin_users");

    while ($row = $result->fetch_assoc()) {
        echo "  ID: {$row['id']}, Username: {$row['username']}, Email: " . ($row['email'] ?? 'N/A') . "\n";
    }

    echo "\n✓ Database verification complete!\n";

} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
