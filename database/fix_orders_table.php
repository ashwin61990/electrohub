<?php
// Script to fix the orders table structure

require_once __DIR__ . '/../config/Database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo "Fixing orders table structure...\n\n";
    
    // Check and add missing columns
    $columnsToAdd = [
        'order_number' => "ALTER TABLE `orders` ADD COLUMN `order_number` varchar(20) DEFAULT NULL AFTER `user_id`",
        'payment_method' => "ALTER TABLE `orders` ADD COLUMN `payment_method` varchar(50) NOT NULL DEFAULT 'cod' AFTER `status`",
        'payment_status' => "ALTER TABLE `orders` ADD COLUMN `payment_status` enum('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending' AFTER `payment_method`",
        'shipping_address' => "ALTER TABLE `orders` ADD COLUMN `shipping_address` text NOT NULL AFTER `payment_status`",
        'billing_address' => "ALTER TABLE `orders` ADD COLUMN `billing_address` text NOT NULL AFTER `shipping_address`",
        'notes' => "ALTER TABLE `orders` ADD COLUMN `notes` text AFTER `billing_address`",
        'updated_at' => "ALTER TABLE `orders` ADD COLUMN `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`"
    ];
    
    foreach ($columnsToAdd as $columnName => $query) {
        // Check if column exists
        $checkQuery = "SHOW COLUMNS FROM `orders` LIKE '$columnName'";
        $stmt = $db->query($checkQuery);
        $columnExists = $stmt->rowCount() > 0;
        
        if (!$columnExists) {
            echo "Adding column: $columnName... ";
            try {
                $db->exec($query);
                echo "✓ Done\n";
            } catch (PDOException $e) {
                echo "✗ Error: " . $e->getMessage() . "\n";
            }
        } else {
            echo "Column $columnName already exists. ✓\n";
        }
    }
    
    // Update status column to enum if it's varchar
    echo "\nUpdating status column to enum... ";
    try {
        $db->exec("ALTER TABLE `orders` MODIFY COLUMN `status` enum('pending','processing','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending'");
        echo "✓ Done\n";
    } catch (PDOException $e) {
        echo "✗ Error: " . $e->getMessage() . "\n";
    }
    
    echo "\n" . str_repeat("-", 60) . "\n";
    echo "Final orders table structure:\n";
    echo str_repeat("-", 60) . "\n";
    
    $stmt = $db->query('DESCRIBE orders');
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo sprintf("%-25s %-30s\n", $row['Field'], $row['Type']);
    }
    
    echo "\n✓ Orders table structure fixed successfully!\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
