<?php
// Migration script to add shipping_amount column to orders table

require_once __DIR__ . '/../config/Database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Check if column exists
    $checkQuery = "SHOW COLUMNS FROM `orders` LIKE 'shipping_amount'";
    $stmt = $db->query($checkQuery);
    $columnExists = $stmt->rowCount() > 0;
    
    if (!$columnExists) {
        echo "Adding shipping_amount column to orders table...\n";
        
        $alterQuery = "ALTER TABLE `orders` 
                      ADD COLUMN `shipping_amount` decimal(10,2) NOT NULL DEFAULT 0.00 AFTER `total_amount`";
        $db->exec($alterQuery);
        
        echo "✓ Successfully added shipping_amount column!\n";
    } else {
        echo "✓ shipping_amount column already exists.\n";
    }
    
    echo "\nMigration completed successfully!\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
