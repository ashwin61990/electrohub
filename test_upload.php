<?php
// Test upload functionality
echo "<h2>Upload Test</h2>";

$uploadDir = __DIR__ . '/uploads/products/';

echo "Upload directory: $uploadDir<br>";
echo "Directory exists: " . (is_dir($uploadDir) ? 'YES' : 'NO') . "<br>";
echo "Directory writable: " . (is_writable($uploadDir) ? 'YES' : 'NO') . "<br>";

// Test file creation
$testFile = $uploadDir . 'test_' . time() . '.txt';
$testContent = 'Test upload at ' . date('Y-m-d H:i:s');

if (file_put_contents($testFile, $testContent)) {
    echo "✅ Can create files in upload directory<br>";
    echo "Test file created: " . basename($testFile) . "<br>";
    
    // Clean up
    if (unlink($testFile)) {
        echo "✅ Can delete files from upload directory<br>";
    }
} else {
    echo "❌ Cannot create files in upload directory<br>";
    echo "Error: " . error_get_last()['message'] . "<br>";
}

// Check PHP settings
echo "<h3>PHP Upload Settings:</h3>";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "<br>";
echo "post_max_size: " . ini_get('post_max_size') . "<br>";
echo "max_execution_time: " . ini_get('max_execution_time') . "<br>";
echo "memory_limit: " . ini_get('memory_limit') . "<br>";

// Check current user
echo "<h3>System Info:</h3>";
echo "PHP user: " . get_current_user() . "<br>";
echo "Process user: " . posix_getpwuid(posix_geteuid())['name'] . "<br>";

echo "<br><strong>Delete this file after testing!</strong>";
?>
