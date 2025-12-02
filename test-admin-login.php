<?php
/**
 * Debug script for admin login issues
 * Visit this page to test database connection and password verification
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Admin Login Debug Test</h2>";
echo "<hr>";

// Test 1: Database connection
echo "<h3>1. Testing Database Connection...</h3>";
try {
    require_once __DIR__ . '/config/database.php';
    $conn = getDbConnection();
    echo "✅ Database connected successfully<br>";
    echo "Database: " . $conn->query('SELECT DATABASE()')->fetch_row()[0] . "<br>";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
    exit;
}

// Test 2: Check if admin_users table exists
echo "<hr><h3>2. Checking admin_users table...</h3>";
$result = $conn->query("SHOW TABLES LIKE 'admin_users'");
if ($result->num_rows > 0) {
    echo "✅ admin_users table exists<br>";
} else {
    echo "❌ admin_users table does NOT exist<br>";
    echo "Please run: php config/setup.php<br>";
    exit;
}

// Test 3: Check admin user
echo "<hr><h3>3. Checking admin user...</h3>";
$result = $conn->query("SELECT id, username, password_hash, is_active FROM admin_users WHERE username = 'admin'");
if ($result->num_rows > 0) {
    $admin = $result->fetch_assoc();
    echo "✅ Admin user found<br>";
    echo "ID: " . $admin['id'] . "<br>";
    echo "Username: " . $admin['username'] . "<br>";
    echo "Password Hash: " . substr($admin['password_hash'], 0, 30) . "...<br>";
    echo "Is Active: " . ($admin['is_active'] ? 'Yes' : 'No') . "<br>";

    // Test 4: Verify password
    echo "<hr><h3>4. Testing Password Verification...</h3>";
    $test_password = 'admin123';
    echo "Testing password: <code>$test_password</code><br>";

    if (password_verify($test_password, $admin['password_hash'])) {
        echo "✅ Password verification PASSED<br>";
        echo "<strong style='color: green;'>The password 'admin123' is correct!</strong><br>";
    } else {
        echo "❌ Password verification FAILED<br>";
        echo "<strong style='color: red;'>The password 'admin123' does NOT match!</strong><br>";

        // Generate a new hash for comparison
        echo "<hr><h3>5. Generating New Password Hash...</h3>";
        $new_hash = password_hash($test_password, PASSWORD_DEFAULT);
        echo "New hash for 'admin123': <code>$new_hash</code><br>";

        // Test the new hash
        if (password_verify($test_password, $new_hash)) {
            echo "✅ New hash verification works<br>";
            echo "<br><strong>Solution:</strong> Update the password hash in your database:<br>";
            echo "<textarea style='width: 100%; height: 80px;'>UPDATE admin_users SET password_hash = '$new_hash' WHERE username = 'admin';</textarea><br>";
        }
    }
} else {
    echo "❌ Admin user NOT found<br>";
    echo "Please run: php config/setup.php<br>";
}

// Test 5: Check PHP password functions
echo "<hr><h3>6. PHP Environment Check...</h3>";
echo "PHP Version: " . phpversion() . "<br>";
echo "password_hash() available: " . (function_exists('password_hash') ? '✅ Yes' : '❌ No') . "<br>";
echo "password_verify() available: " . (function_exists('password_verify') ? '✅ Yes' : '❌ No') . "<br>";

echo "<hr>";
echo "<p><strong>Instructions:</strong></p>";
echo "<ul>";
echo "<li>If all tests pass, the login should work with username: <code>admin</code> and password: <code>admin123</code></li>";
echo "<li>If password verification fails, copy and run the UPDATE query shown above</li>";
echo "<li>After fixing, delete this test file for security</li>";
echo "</ul>";
echo "<hr>";
echo "<p><a href='admin/'>Go to Admin Login</a></p>";
?>
