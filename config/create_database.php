<?php
/**
 * Create the database first
 * Run this before setup.php if the database doesn't exist
 */

// Load environment variables from .env file
function loadEnv($path) {
    if (!file_exists($path)) {
        die("Error: .env file not found. Please copy .env.example to .env and configure your database credentials.\n");
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
$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
$dbname = getenv('DB_NAME') ?: 'social_login_training';

echo "Creating database '$dbname'...\n";

try {
    // Connect to MySQL server (without selecting a database)
    $conn = new mysqli($host, $user, $pass);

    if ($conn->connect_error) {
        die("✗ Connection failed: " . $conn->connect_error . "\n");
    }

    // Create database
    $sql = "CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";

    if ($conn->query($sql) === TRUE) {
        echo "✓ Database '$dbname' created successfully (or already exists)\n";
    } else {
        echo "✗ Error creating database: " . $conn->error . "\n";
        exit(1);
    }

    $conn->close();

    echo "\nNow run: php config/setup.php\n";

} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
