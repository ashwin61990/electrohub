<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup - ElectroHub</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 2rem;
            min-height: 100vh;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 2rem;
        }
        .step {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border-left: 4px solid #667eea;
        }
        .success {
            color: #28a745;
            font-weight: 600;
        }
        .error {
            color: #dc3545;
            font-weight: 600;
        }
        .info {
            color: #17a2b8;
            font-weight: 600;
        }
        .btn {
            background: #667eea;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 0.5rem 0.5rem 0.5rem 0;
        }
        .btn:hover {
            background: #5a6fd8;
        }
        pre {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 ElectroHub Database Setup</h1>
        
        <?php
        require_once 'config/Database.php';

        $database = new Database();
        $db = $database->getConnection();

        if (!$db) {
            echo '<div class="step"><p class="error">❌ Database connection failed! Please check your database configuration.</p></div>';
            exit;
        }

        echo '<div class="step"><p class="success">✅ Database connection successful!</p></div>';

        if (isset($_GET['setup']) && $_GET['setup'] == 'run') {
            echo '<div class="step"><h3>Running Database Setup...</h3>';
            
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
                echo "<p class='success'>✅ Cart table created successfully</p>";

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
                echo "<p class='success'>✅ Orders table created successfully</p>";

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
                echo "<p class='success'>✅ Order items table created successfully</p>";

                // Add some sample categories if none exist
                $checkCategories = $db->query("SELECT COUNT(*) as count FROM categories")->fetch();
                if ($checkCategories['count'] == 0) {
                    $categories = [
                        ['Audio', 'Audio accessories and devices'],
                        ['Accessories', 'General electronic accessories'],
                        ['Cables', 'Various types of cables'],
                        ['Chargers', 'Charging devices and adapters']
                    ];
                    
                    $insertCategory = $db->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
                    foreach ($categories as $cat) {
                        $insertCategory->execute($cat);
                    }
                    echo "<p class='success'>✅ Sample categories added</p>";
                } else {
                    echo "<p class='info'>ℹ️ Categories already exist</p>";
                }

                echo '<h3 class="success">🎉 Database setup completed successfully!</h3>';
                echo '<p><strong>Next steps:</strong></p>';
                echo '<ul>';
                echo '<li>Go to <a href="admin/dashboard.php">Admin Dashboard</a> (Login: ElectroHub@gmail.com / admin123)</li>';
                echo '<li>Add some products through the admin panel</li>';
                echo '<li>Test the cart functionality on the <a href="index.php">homepage</a></li>';
                echo '<li>Test the checkout process</li>';
                echo '</ul>';

            } catch (Exception $e) {
                echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
            }
            
            echo '</div>';
        } else {
            ?>
            <div class="step">
                <h3>Database Setup Required</h3>
                <p>This will create the necessary tables for the cart and order system:</p>
                <ul>
                    <li><strong>cart</strong> - Stores user cart items</li>
                    <li><strong>orders</strong> - Stores order information</li>
                    <li><strong>order_items</strong> - Stores individual order items</li>
                    <li><strong>categories</strong> - Sample categories (if none exist)</li>
                </ul>
                <a href="?setup=run" class="btn">🚀 Run Database Setup</a>
            </div>
            
            <div class="step">
                <h3>Current Database Status</h3>
                <?php
                try {
                    // Check existing tables
                    $tables = ['users', 'products', 'categories', 'cart', 'orders', 'order_items'];
                    foreach ($tables as $table) {
                        $result = $db->query("SHOW TABLES LIKE '$table'");
                        if ($result->rowCount() > 0) {
                            echo "<p class='success'>✅ Table '$table' exists</p>";
                        } else {
                            echo "<p class='error'>❌ Table '$table' missing</p>";
                        }
                    }
                } catch (Exception $e) {
                    echo "<p class='error'>Error checking tables: " . $e->getMessage() . "</p>";
                }
                ?>
            </div>
            <?php
        }
        ?>
        
        <div class="step">
            <h3>Quick Links</h3>
            <a href="index.php" class="btn">🏠 Homepage</a>
            <a href="admin/dashboard.php" class="btn">👨‍💼 Admin Dashboard</a>
            <a href="setup_admin.php" class="btn">🔧 Admin Setup</a>
        </div>
    </div>
</body>
</html>
