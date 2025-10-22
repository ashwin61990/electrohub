<?php
// Check products in database and display logic
require_once 'config/Database.php';
require_once 'classes/Admin.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    $admin = new Admin($db);
    
    echo "<h2>Product Database Check</h2>";
    
    // Get all products from database
    echo "<h3>All Products in Database:</h3>";
    $query = "SELECT id, sku, name, status, featured, created_at, image FROM products ORDER BY created_at DESC LIMIT 10";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $allProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($allProducts)) {
        echo "<p style='color: red;'>❌ No products found in database!</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>SKU</th><th>Name</th><th>Status</th><th>Featured</th><th>Created</th><th>Image</th></tr>";
        
        foreach ($allProducts as $product) {
            $statusColor = $product['status'] == 'active' ? 'green' : 'red';
            $featuredText = $product['featured'] ? 'YES' : 'NO';
            
            echo "<tr>";
            echo "<td>" . $product['id'] . "</td>";
            echo "<td>" . htmlspecialchars($product['sku']) . "</td>";
            echo "<td>" . htmlspecialchars($product['name']) . "</td>";
            echo "<td style='color: $statusColor;'>" . $product['status'] . "</td>";
            echo "<td>" . $featuredText . "</td>";
            echo "<td>" . $product['created_at'] . "</td>";
            echo "<td>" . (empty($product['image']) ? 'No image' : htmlspecialchars($product['image'])) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Check featured products (what shows on homepage)
    echo "<h3>Featured Products (Homepage Display):</h3>";
    $featuredQuery = "SELECT id, sku, name, status, image FROM products WHERE featured = 1 AND status = 'active' ORDER BY created_at DESC";
    $featuredStmt = $db->prepare($featuredQuery);
    $featuredStmt->execute();
    $featuredProducts = $featuredStmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($featuredProducts)) {
        echo "<p style='color: orange;'>⚠️ No featured products found! Products need to be marked as 'Featured' to show on homepage.</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>SKU</th><th>Name</th><th>Status</th><th>Image</th></tr>";
        
        foreach ($featuredProducts as $product) {
            echo "<tr>";
            echo "<td>" . $product['id'] . "</td>";
            echo "<td>" . htmlspecialchars($product['sku']) . "</td>";
            echo "<td>" . htmlspecialchars($product['name']) . "</td>";
            echo "<td>" . $product['status'] . "</td>";
            echo "<td>" . (empty($product['image']) ? 'No image' : htmlspecialchars($product['image'])) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Check admin products display
    echo "<h3>Admin Products Display Test:</h3>";
    $adminProducts = $admin->getAllProducts(10, 0, '');
    echo "<p>Admin getAllProducts() returned: " . count($adminProducts) . " products</p>";
    
    // Check recent uploads
    echo "<h3>Recent Product Additions (Last 24 hours):</h3>";
    $recentQuery = "SELECT id, sku, name, status, featured, created_at FROM products WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) ORDER BY created_at DESC";
    $recentStmt = $db->prepare($recentQuery);
    $recentStmt->execute();
    $recentProducts = $recentStmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($recentProducts)) {
        echo "<p style='color: orange;'>⚠️ No products added in the last 24 hours.</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>SKU</th><th>Name</th><th>Status</th><th>Featured</th><th>Created</th></tr>";
        
        foreach ($recentProducts as $product) {
            echo "<tr>";
            echo "<td>" . $product['id'] . "</td>";
            echo "<td>" . htmlspecialchars($product['sku']) . "</td>";
            echo "<td>" . htmlspecialchars($product['name']) . "</td>";
            echo "<td>" . $product['status'] . "</td>";
            echo "<td>" . ($product['featured'] ? 'YES' : 'NO') . "</td>";
            echo "<td>" . $product['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<h3>Troubleshooting Tips:</h3>";
    echo "<ul>";
    echo "<li><strong>For Homepage:</strong> Products must be marked as 'Featured' AND have status 'Active'</li>";
    echo "<li><strong>For Admin Panel:</strong> All products should show regardless of status</li>";
    echo "<li><strong>New Products:</strong> Check if they were saved with correct status and featured settings</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

echo "<br><strong>Delete this file after checking!</strong>";
?>
