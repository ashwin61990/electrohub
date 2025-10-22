<?php
// Temporary script to check image paths in database
require_once 'config/Database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT id, name, image FROM products LIMIT 5";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Image Path Debug</h2>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Image Path in DB</th><th>File Exists?</th><th>Admin Path</th><th>Frontend Path</th></tr>";
    
    foreach ($products as $product) {
        $imagePath = $product['image'];
        $adminPath = '../' . $imagePath;
        $frontendPath = $imagePath;
        
        // Check if file exists
        $fileExists = !empty($imagePath) && file_exists($imagePath) ? 'YES' : 'NO';
        $fileExistsAlt = !empty($imagePath) && file_exists(__DIR__ . '/' . $imagePath) ? 'YES (with __DIR__)' : 'NO';
        
        echo "<tr>";
        echo "<td>" . $product['id'] . "</td>";
        echo "<td>" . htmlspecialchars($product['name']) . "</td>";
        echo "<td>" . htmlspecialchars($imagePath) . "</td>";
        echo "<td>" . $fileExists . " / " . $fileExistsAlt . "</td>";
        echo "<td>" . htmlspecialchars($adminPath) . "</td>";
        echo "<td>" . htmlspecialchars($frontendPath) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>Directory Structure:</h3>";
    echo "Current directory: " . __DIR__ . "<br>";
    echo "Document root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
    
    if (is_dir('uploads/products')) {
        echo "<br>Files in uploads/products/:<br>";
        $files = scandir('uploads/products');
        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                echo "- " . $file . "<br>";
            }
        }
    } else {
        echo "<br>uploads/products directory does not exist<br>";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

echo "<br><br><strong>Delete this file after checking!</strong>";
?>
