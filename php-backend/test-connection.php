<?php
/**
 * Test Database Connection
 * Upload this file to test if your database connection works
 * Access: https://yourdomain.infinityfreeapp.com/php-backend/test-connection.php
 * DELETE THIS FILE after testing for security!
 */

// Include config
require_once 'config.php';

echo "<h1>Database Connection Test</h1>";
echo "<hr>";

try {
    $conn = getDBConnection();
    
    echo "<p style='color: green;'>✅ <strong>Database connected successfully!</strong></p>";
    
    // Test categories table
    $result = $conn->query("SELECT COUNT(*) as count FROM categories");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "<p>✅ Categories table exists: <strong>" . $row['count'] . " categories found</strong></p>";
    }
    
    // Test posts table
    $result = $conn->query("SELECT COUNT(*) as count FROM posts");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "<p>✅ Posts table exists: <strong>" . $row['count'] . " posts found</strong></p>";
    }
    
    // Check uploads directory
    if (is_dir('../uploads')) {
        if (is_writable('../uploads')) {
            echo "<p style='color: green;'>✅ Uploads directory exists and is writable</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ Uploads directory exists but is NOT writable. Set permissions to 755 or 777</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Uploads directory does NOT exist. Create it manually.</p>";
    }
    
    // PHP Info
    echo "<hr>";
    echo "<h2>Server Information</h2>";
    echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
    echo "<p><strong>Upload Max Filesize:</strong> " . ini_get('upload_max_filesize') . "</p>";
    echo "<p><strong>Post Max Size:</strong> " . ini_get('post_max_size') . "</p>";
    echo "<p><strong>Max Execution Time:</strong> " . ini_get('max_execution_time') . " seconds</p>";
    
    echo "<hr>";
    echo "<p style='color: red;'><strong>⚠️ IMPORTANT: Delete this file after testing for security!</strong></p>";
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ <strong>Connection failed:</strong> " . $e->getMessage() . "</p>";
    echo "<p>Please check your database credentials in config.php</p>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Database Connection Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        h1 {
            color: #333;
        }
        p {
            padding: 10px;
            background: white;
            border-radius: 5px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
</body>
</html>
