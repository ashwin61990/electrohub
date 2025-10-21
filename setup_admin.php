<?php
/**
 * Admin Setup Script
 * Creates admin user and updates database for admin functionality
 */

require_once 'config/Database.php';

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    die("Database connection failed!");
}

echo "<h2>Admin Setup Script</h2>";

try {
    // Add is_admin column to users table if it doesn't exist
    $checkColumn = $db->query("SHOW COLUMNS FROM users LIKE 'is_admin'")->rowCount();
    
    if ($checkColumn == 0) {
        $db->exec("ALTER TABLE users ADD COLUMN is_admin TINYINT(1) DEFAULT 0 AFTER email_verified");
        echo "<p style='color: green;'>✅ Added is_admin column to users table</p>";
    } else {
        echo "<p style='color: gray;'>⚪ is_admin column already exists</p>";
    }
    
    // Check if admin user exists
    $checkAdmin = $db->prepare("SELECT id FROM users WHERE email = 'ElectroHub@gmail.com'");
    $checkAdmin->execute();
    
    if ($checkAdmin->rowCount() == 0) {
        // Create admin user
        $adminPassword = password_hash('admin123', PASSWORD_BCRYPT);
        
        $createAdmin = $db->prepare("
            INSERT INTO users (username, email, password, full_name, is_admin, is_active, email_verified) 
            VALUES ('admin', 'ElectroHub@gmail.com', ?, 'ElectroHub Administrator', 1, 1, 1)
        ");
        
        $createAdmin->execute([$adminPassword]);
        echo "<p style='color: green;'>✅ Created admin user successfully!</p>";
        echo "<p><strong>Admin Credentials:</strong></p>";
        echo "<ul>";
        echo "<li>Email: ElectroHub@gmail.com</li>";
        echo "<li>Password: admin123</li>";
        echo "</ul>";
    } else {
        echo "<p style='color: blue;'>ℹ️ Admin user already exists</p>";
        
        // Update admin password in case it changed
        $updatePassword = password_hash('admin123', PASSWORD_BCRYPT);
        $updateAdmin = $db->prepare("UPDATE users SET password = ?, is_admin = 1 WHERE email = 'ElectroHub@gmail.com'");
        $updateAdmin->execute([$updatePassword]);
        echo "<p style='color: green;'>✅ Updated admin password</p>";
    }
    
    // Update products table to add more fields for admin management
    $productColumns = [
        'sku' => "ADD COLUMN sku VARCHAR(100) UNIQUE AFTER id",
        'brand' => "ADD COLUMN brand VARCHAR(100) AFTER category",
        'weight' => "ADD COLUMN weight DECIMAL(8,2) AFTER stock",
        'dimensions' => "ADD COLUMN dimensions VARCHAR(100) AFTER weight",
        'warranty' => "ADD COLUMN warranty VARCHAR(100) AFTER dimensions",
        'status' => "ADD COLUMN status ENUM('active', 'inactive', 'out_of_stock') DEFAULT 'active' AFTER featured",
        'meta_title' => "ADD COLUMN meta_title VARCHAR(200) AFTER status",
        'meta_description' => "ADD COLUMN meta_description TEXT AFTER meta_title"
    ];
    
    $currentColumns = $db->query("DESCRIBE products")->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($productColumns as $columnName => $alterSQL) {
        if (!in_array($columnName, $currentColumns)) {
            try {
                $db->exec("ALTER TABLE products " . $alterSQL);
                echo "<p style='color: green;'>✅ Added $columnName column to products table</p>";
            } catch (PDOException $e) {
                echo "<p style='color: orange;'>⚠️ Could not add $columnName: " . $e->getMessage() . "</p>";
            }
        }
    }
    
    // Update existing products with SKUs if they don't have them
    $updateSkus = $db->query("SELECT id FROM products WHERE sku IS NULL OR sku = ''");
    $productsToUpdate = $updateSkus->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($productsToUpdate as $productId) {
        $sku = 'EH' . str_pad($productId, 6, '0', STR_PAD_LEFT);
        $updateSku = $db->prepare("UPDATE products SET sku = ? WHERE id = ?");
        $updateSku->execute([$sku, $productId]);
    }
    
    if (count($productsToUpdate) > 0) {
        echo "<p style='color: green;'>✅ Updated " . count($productsToUpdate) . " products with SKUs</p>";
    }
    
    echo "<h3>Setup Complete!</h3>";
    echo "<p style='color: green; font-weight: bold;'>🎉 Admin system is ready!</p>";
    echo "<p><strong>Next steps:</strong></p>";
    echo "<ul>";
    echo "<li>✅ Login as admin: <a href='login.php'>login.php</a></li>";
    echo "<li>✅ Access admin dashboard: <a href='admin/dashboard.php'>admin/dashboard.php</a></li>";
    echo "<li>⚠️ <strong>Important:</strong> Delete this file (setup_admin.php) after running</li>";
    echo "</ul>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'><strong>❌ Error:</strong> " . $e->getMessage() . "</p>";
}
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 800px;
    margin: 20px auto;
    padding: 20px;
    background: #f5f5f5;
}
h2, h3 { color: #333; }
ul { background: white; padding: 15px; border-radius: 5px; }
p { margin: 5px 0; padding: 5px; }
a { color: #007cba; text-decoration: none; }
a:hover { text-decoration: underline; }
</style>
