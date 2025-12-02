<?php
/**
 * Database setup script
 * Run this file once to initialize the database and create tables
 */

require_once __DIR__ . '/database.php';

echo "Setting up database...\n";

try {
    initializeDatabase();
    echo "✓ Database initialized successfully!\n";
    echo "✓ Tables created\n";
    echo "✓ Default admin account created (username: admin, password: admin123)\n";
    echo "\nIMPORTANT: Please change the default admin password!\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
