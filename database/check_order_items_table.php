<?php
require_once __DIR__ . '/../config/Database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo "Order Items table structure:\n";
    echo str_repeat("-", 60) . "\n";
    
    $stmt = $db->query('DESCRIBE order_items');
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo sprintf("%-25s %-30s\n", $row['Field'], $row['Type']);
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
