<?php
// Comprehensive database verification script

require_once __DIR__ . '/../config/Database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo "=== DATABASE VERIFICATION ===\n\n";
    
    $tables = ['users', 'products', 'categories', 'cart', 'orders', 'order_items'];
    
    foreach ($tables as $table) {
        echo str_repeat("=", 60) . "\n";
        echo "Table: $table\n";
        echo str_repeat("=", 60) . "\n";
        
        try {
            $stmt = $db->query("DESCRIBE $table");
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo sprintf("  %-25s %-30s\n", $row['Field'], $row['Type']);
            }
            echo "\n";
        } catch (PDOException $e) {
            echo "  ✗ Error: Table does not exist or cannot be accessed\n\n";
        }
    }
    
    echo str_repeat("=", 60) . "\n";
    echo "✓ Database verification complete!\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
