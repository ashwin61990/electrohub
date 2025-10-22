<?php
// Quick script to check and fix missing image for EH415492

require_once 'config/Database.php';

$missingFile = 'uploads/products/product_68f89dc8ca3e61.90325887.jpeg';
$productSKU = 'EH415492';

echo "<h2>Missing Image Fix for $productSKU</h2>";

// Check if file exists in different locations
$possiblePaths = [
    __DIR__ . '/' . $missingFile,
    __DIR__ . '/uploads/products/',
    '/opt/lampp/htdocs/electrohub/' . $missingFile,
    '/opt/lampp/htdocs/electrohub/uploads/products/'
];

echo "<h3>Checking possible file locations:</h3>";
foreach ($possiblePaths as $path) {
    if (is_file($path)) {
        echo "✅ Found file: $path<br>";
    } else {
        echo "❌ Not found: $path<br>";
    }
}

// List all files in uploads/products
echo "<h3>Files currently in uploads/products/:</h3>";
$uploadDir = __DIR__ . '/uploads/products/';
if (is_dir($uploadDir)) {
    $files = scandir($uploadDir);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            echo "- $file<br>";
        }
    }
} else {
    echo "Directory not found<br>";
}

// Option to clear the image path from database
echo "<h3>Fix Options:</h3>";
echo "<p>1. <strong>Upload new image:</strong> Edit the product in admin panel and upload a new image</p>";
echo "<p>2. <strong>Clear image path:</strong> <a href='?clear_image=1'>Click here to remove the broken image path from database</a></p>";

if (isset($_GET['clear_image'])) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "UPDATE products SET image = '' WHERE sku = :sku";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':sku', $productSKU);
        
        if ($stmt->execute()) {
            echo "<div style='background: #d4edda; padding: 10px; border: 1px solid #c3e6cb; color: #155724; margin: 10px 0;'>";
            echo "✅ Successfully cleared image path for $productSKU. The product will now show 'No Image' placeholder.";
            echo "</div>";
        } else {
            echo "<div style='background: #f8d7da; padding: 10px; border: 1px solid #f5c6cb; color: #721c24; margin: 10px 0;'>";
            echo "❌ Failed to update database.";
            echo "</div>";
        }
    } catch (Exception $e) {
        echo "<div style='background: #f8d7da; padding: 10px; border: 1px solid #f5c6cb; color: #721c24; margin: 10px 0;'>";
        echo "❌ Error: " . $e->getMessage();
        echo "</div>";
    }
}

echo "<br><strong>Delete this file after use!</strong>";
?>
