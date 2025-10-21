<?php
// Script to fix the order_items table structure

require_once __DIR__ . '/../config/Database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo "Fixing order_items table structure...\n\n";
    
    // Check if 'total' column exists
    $checkQuery = "SHOW COLUMNS FROM `order_items` LIKE 'total'";
    $stmt = $db->query($checkQuery);
    $columnExists = $stmt->rowCount() > 0;
    
    if (!$columnExists) {
        echo "Adding 'total' column... ";
        try {
            $db->exec("ALTER TABLE `order_items` ADD COLUMN `total` decimal(10,2) NOT NULL AFTER `price`");
            echo "✓ Done\n";
        } catch (PDOException $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
        }
    } else {
        echo "'total' column already exists. ✓\n";
    }
    
    // Check if 'created_at' column exists
    $checkQuery = "SHOW COLUMNS FROM `order_items` LIKE 'created_at'";
    $stmt = $db->query($checkQuery);
    $columnExists = $stmt->rowCount() > 0;
    
    if (!$columnExists) {
        echo "Adding 'created_at' column... ";
        try {
            $db->exec("ALTER TABLE `order_items` ADD COLUMN `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP");
            echo "✓ Done\n";
        } catch (PDOException $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
        }
    } else {
        echo "'created_at' column already exists. ✓\n";
    }
    
    echo "\n" . str_repeat("-", 60) . "\n";
    echo "Final order_items table structure:\n";
    echo str_repeat("-", 60) . "\n";
    
    $stmt = $db->query('DESCRIBE order_items');
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo sprintf("%-25s %-30s\n", $row['Field'], $row['Type']);
    }
    
    echo "\n✓ Order items table structure fixed successfully!\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
