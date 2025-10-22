<?php
// Temporary script to create upload directory with proper permissions
// Run this once via browser, then delete this file

$uploadDir = __DIR__ . '/uploads/products/';

echo "<h2>Upload Directory Setup</h2>";

try {
    // Create directory
    if (!file_exists($uploadDir)) {
        if (mkdir($uploadDir, 0775, true)) {
            echo "✅ Created directory: $uploadDir<br>";
        } else {
            echo "❌ Failed to create directory: $uploadDir<br>";
        }
    } else {
        echo "ℹ️ Directory already exists: $uploadDir<br>";
    }
    
    // Check permissions
    $perms = fileperms($uploadDir);
    echo "Current permissions: " . substr(sprintf('%o', $perms), -4) . "<br>";
    
    // Try to make it writable
    if (chmod($uploadDir, 0775)) {
        echo "✅ Set permissions to 775<br>";
    } else {
        echo "❌ Failed to set permissions<br>";
    }
    
    // Test write access
    $testFile = $uploadDir . 'test.txt';
    if (file_put_contents($testFile, 'test')) {
        echo "✅ Directory is writable<br>";
        unlink($testFile); // Clean up
    } else {
        echo "❌ Directory is not writable<br>";
    }
    
    echo "<br><strong>If successful, delete this file for security!</strong>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
