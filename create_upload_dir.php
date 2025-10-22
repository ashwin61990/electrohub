<?php
// Temporary script to diagnose and fix upload directory issues
// Run this once via browser, then delete this file

echo "<h2>Upload Directory Diagnostic</h2>";

// Test all possible upload directories
$possibleDirs = [
    __DIR__ . '/uploads/products/',
    $_SERVER['DOCUMENT_ROOT'] . '/uploads/products/',
    '/var/www/html/uploads/products/',
    '/tmp/uploads/products/',
    sys_get_temp_dir() . '/uploads/products/'
];

echo "<h3>Testing Possible Upload Directories:</h3>";

foreach ($possibleDirs as $dir) {
    echo "<div style='border: 1px solid #ccc; margin: 10px; padding: 10px;'>";
    echo "<strong>Testing: $dir</strong><br>";
    
    $parentDir = dirname($dir);
    echo "Parent directory: $parentDir<br>";
    
    // Check if parent exists and is writable
    if (file_exists($parentDir)) {
        echo "✅ Parent directory exists<br>";
        if (is_writable($parentDir)) {
            echo "✅ Parent directory is writable<br>";
            
            // Try to create the upload directory
            if (!file_exists($dir)) {
                if (mkdir($dir, 0775, true)) {
                    echo "✅ Created upload directory<br>";
                } else {
                    echo "❌ Failed to create upload directory<br>";
                }
            } else {
                echo "ℹ️ Upload directory already exists<br>";
            }
            
            // Test write access
            if (file_exists($dir)) {
                $testFile = $dir . 'test_' . time() . '.txt';
                if (file_put_contents($testFile, 'test')) {
                    echo "✅ Directory is writable - SUCCESS!<br>";
                    unlink($testFile);
                    echo "<strong style='color: green;'>This directory can be used for uploads!</strong><br>";
                } else {
                    echo "❌ Directory is not writable<br>";
                }
            }
        } else {
            echo "❌ Parent directory is not writable<br>";
        }
    } else {
        echo "❌ Parent directory does not exist<br>";
        
        // Try to create parent directory
        if (mkdir($parentDir, 0755, true)) {
            echo "✅ Created parent directory<br>";
        } else {
            echo "❌ Failed to create parent directory<br>";
        }
    }
    
    echo "</div>";
}

echo "<h3>System Information:</h3>";
echo "Current user: " . get_current_user() . "<br>";
echo "Document root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Current directory: " . __DIR__ . "<br>";
echo "Temp directory: " . sys_get_temp_dir() . "<br>";

echo "<h3>Manual Fix Commands:</h3>";
echo "<pre>";
echo "# Run these commands on your AWS server:\n";
echo "sudo mkdir -p /var/www/html/uploads/products\n";
echo "sudo chmod 775 /var/www/html/uploads/products\n";
echo "sudo chown apache:apache /var/www/html/uploads/products\n";
echo "\n# Or for Ubuntu/Debian:\n";
echo "sudo chown www-data:www-data /var/www/html/uploads/products\n";
echo "</pre>";

echo "<br><strong style='color: red;'>Delete this file after use for security!</strong>";
?>
