<?php
// Debug script to help identify AWS hosting issues
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>ElectroHub Debug Information</h2>";

// Check PHP version
echo "<h3>PHP Information</h3>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Current Directory: " . __DIR__ . "<br>";

// Check database connection
echo "<h3>Database Connection Test</h3>";
try {
    require_once 'config/Database.php';
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        echo "✅ Database connection successful<br>";
        
        // Test a simple query
        $stmt = $db->query("SELECT 1 as test");
        $result = $stmt->fetch();
        if ($result['test'] == 1) {
            echo "✅ Database query test successful<br>";
        }
        
        // Check if products table exists
        $stmt = $db->query("SHOW TABLES LIKE 'products'");
        if ($stmt->rowCount() > 0) {
            echo "✅ Products table exists<br>";
        } else {
            echo "❌ Products table does not exist<br>";
        }
        
        // Check if categories table exists
        $stmt = $db->query("SHOW TABLES LIKE 'categories'");
        if ($stmt->rowCount() > 0) {
            echo "✅ Categories table exists<br>";
        } else {
            echo "❌ Categories table does not exist<br>";
        }
        
    } else {
        echo "❌ Database connection failed<br>";
    }
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
}

// Check file permissions
echo "<h3>File Permissions Test</h3>";
$uploadDir = __DIR__ . '/uploads/products/';
echo "Upload directory: " . $uploadDir . "<br>";

if (!file_exists($uploadDir)) {
    if (mkdir($uploadDir, 0755, true)) {
        echo "✅ Created upload directory<br>";
    } else {
        echo "❌ Failed to create upload directory<br>";
    }
} else {
    echo "✅ Upload directory exists<br>";
}

if (is_writable($uploadDir)) {
    echo "✅ Upload directory is writable<br>";
} else {
    echo "❌ Upload directory is not writable<br>";
}

// Check required files
echo "<h3>Required Files Check</h3>";
$requiredFiles = [
    'config/Database.php',
    'classes/Admin.php',
    'admin/products.php'
];

foreach ($requiredFiles as $file) {
    if (file_exists($file)) {
        echo "✅ $file exists<br>";
    } else {
        echo "❌ $file missing<br>";
    }
}

// Check session
echo "<h3>Session Test</h3>";
session_start();
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "✅ Sessions are working<br>";
    echo "Session ID: " . session_id() . "<br>";
} else {
    echo "❌ Sessions are not working<br>";
}

// Check environment variables (for AWS)
echo "<h3>Environment Variables</h3>";
$awsVars = ['RDS_HOSTNAME', 'RDS_DB_NAME', 'RDS_USERNAME', 'RDS_PASSWORD'];
foreach ($awsVars as $var) {
    if (isset($_SERVER[$var])) {
        echo "✅ $var is set<br>";
    } else {
        echo "ℹ️ $var is not set (using local config)<br>";
    }
}

// Check loaded extensions
echo "<h3>PHP Extensions</h3>";
$requiredExtensions = ['pdo', 'pdo_mysql', 'gd', 'fileinfo'];
foreach ($requiredExtensions as $ext) {
    if (extension_loaded($ext)) {
        echo "✅ $ext extension loaded<br>";
    } else {
        echo "❌ $ext extension not loaded<br>";
    }
}

echo "<h3>Memory and Limits</h3>";
echo "Memory Limit: " . ini_get('memory_limit') . "<br>";
echo "Max Execution Time: " . ini_get('max_execution_time') . "<br>";
echo "Upload Max Filesize: " . ini_get('upload_max_filesize') . "<br>";
echo "Post Max Size: " . ini_get('post_max_size') . "<br>";

?>
