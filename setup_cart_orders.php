<?php
/**
 * Cart and Orders Setup Script
 * Creates cart and orders tables for the e-commerce functionality
 */

require_once 'config/Database.php';

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    die("Database connection failed!");
}

echo "<h2>Cart and Orders Setup Script</h2>";

try {
    // Create cart table
    $cartTable = "
    CREATE TABLE IF NOT EXISTS `cart` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `user_id` int(11) NOT NULL,
      `product_id` int(11) NOT NULL,
      `quantity` int(11) NOT NULL DEFAULT 1,
      `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      KEY `user_id` (`user_id`),
      KEY `product_id` (`product_id`),
      UNIQUE KEY `user_product` (`user_id`, `product_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $db->exec($cartTable);
    echo "<p style='color: green;'>✅ Cart table created successfully</p>";

    // Create orders table
    $ordersTable = "
    CREATE TABLE IF NOT EXISTS `orders` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `user_id` int(11) NOT NULL,
      `order_number` varchar(20) DEFAULT NULL,
      `total_amount` decimal(10,2) NOT NULL,
      `shipping_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
      `status` enum('pending','processing','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
      `payment_method` varchar(50) NOT NULL,
      `payment_status` enum('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
      `shipping_address` text NOT NULL,
      `billing_address` text NOT NULL,
      `notes` text,
      `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      KEY `user_id` (`user_id`),
      KEY `status` (`status`),
      KEY `payment_status` (`payment_status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $db->exec($ordersTable);
    echo "<p style='color: green;'>✅ Orders table created successfully</p>";

    // Create order_items table
    $orderItemsTable = "
    CREATE TABLE IF NOT EXISTS `order_items` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `order_id` int(11) NOT NULL,
      `product_id` int(11) NOT NULL,
      `quantity` int(11) NOT NULL,
      `price` decimal(10,2) NOT NULL,
      `total` decimal(10,2) NOT NULL,
      `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      KEY `order_id` (`order_id`),
      KEY `product_id` (`product_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $db->exec($orderItemsTable);
    echo "<p style='color: green;'>✅ Order items table created successfully</p>";

    // Add foreign key constraints (if they don't exist)
    try {
        $db->exec("ALTER TABLE `cart` ADD CONSTRAINT `cart_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE");
        echo "<p style='color: green;'>✅ Cart user foreign key added</p>";
    } catch (Exception $e) {
        echo "<p style='color: gray;'>⚪ Cart user foreign key already exists</p>";
    }

    try {
        $db->exec("ALTER TABLE `cart` ADD CONSTRAINT `cart_product_fk` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE");
        echo "<p style='color: green;'>✅ Cart product foreign key added</p>";
    } catch (Exception $e) {
        echo "<p style='color: gray;'>⚪ Cart product foreign key already exists</p>";
    }

    try {
        $db->exec("ALTER TABLE `orders` ADD CONSTRAINT `orders_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE");
        echo "<p style='color: green;'>✅ Orders user foreign key added</p>";
    } catch (Exception $e) {
        echo "<p style='color: gray;'>⚪ Orders user foreign key already exists</p>";
    }

    try {
        $db->exec("ALTER TABLE `order_items` ADD CONSTRAINT `order_items_order_fk` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE");
        echo "<p style='color: green;'>✅ Order items order foreign key added</p>";
    } catch (Exception $e) {
        echo "<p style='color: gray;'>⚪ Order items order foreign key already exists</p>";
    }

    try {
        $db->exec("ALTER TABLE `order_items` ADD CONSTRAINT `order_items_product_fk` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE");
        echo "<p style='color: green;'>✅ Order items product foreign key added</p>";
    } catch (Exception $e) {
        echo "<p style='color: gray;'>⚪ Order items product foreign key already exists</p>";
    }

    echo "<br><h3 style='color: green;'>🎉 Cart and Orders setup completed successfully!</h3>";
    echo "<p><strong>Next steps:</strong></p>";
    echo "<ul>";
    echo "<li>Add some products through the admin panel</li>";
    echo "<li>Test the cart functionality on the homepage</li>";
    echo "<li>Test the checkout process</li>";
    echo "<li>Verify stock management works correctly</li>";
    echo "</ul>";

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
