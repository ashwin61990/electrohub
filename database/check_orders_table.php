<?php
require_once __DIR__ . '/../config/Database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo "Orders table structure:\n";
    echo str_repeat("-", 60) . "\n";
    
    $stmt = $db->query('DESCRIBE orders');
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo sprintf("%-25s %-30s\n", $row['Field'], $row['Type']);
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
