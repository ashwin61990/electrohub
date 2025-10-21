<?php
/**
 * Database Update Script
 * This file will update the users table to add missing columns
 * Run this file once to fix the database schema
 */

require_once 'config/Database.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

if (!$db) {
    die("Database connection failed!");
}

echo "<h2>Database Update Script</h2>";
echo "<p>Updating users table schema...</p>";

try {
    // Check if users table exists
    $checkTable = $db->query("SHOW TABLES LIKE 'users'");
    
    if ($checkTable->rowCount() == 0) {
        echo "<p style='color: red;'>❌ Users table doesn't exist. Creating it...</p>";
        
        // Create users table with all required columns
        $createTable = "
        CREATE TABLE users (
            id INT PRIMARY KEY AUTO_INCREMENT,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            full_name VARCHAR(100) NOT NULL,
            phone VARCHAR(20),
            address TEXT,
            city VARCHAR(100),
            country VARCHAR(100),
            postal_code VARCHAR(20),
            is_active TINYINT(1) DEFAULT 1,
            email_verified TINYINT(1) DEFAULT 0,
            last_login TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_email (email),
            INDEX idx_username (username)
        )";
        
        $db->exec($createTable);
        echo "<p style='color: green;'>✅ Users table created successfully!</p>";
        
    } else {
        echo "<p style='color: blue;'>ℹ️ Users table exists. Checking for missing columns...</p>";
        
        // Get current table structure
        $columns = $db->query("DESCRIBE users")->fetchAll(PDO::FETCH_COLUMN);
        
        // Define required columns and their SQL
        $requiredColumns = [
            'phone' => "ADD COLUMN phone VARCHAR(20) AFTER full_name",
            'address' => "ADD COLUMN address TEXT AFTER phone",
            'city' => "ADD COLUMN city VARCHAR(100) AFTER address",
            'country' => "ADD COLUMN country VARCHAR(100) AFTER city",
            'postal_code' => "ADD COLUMN postal_code VARCHAR(20) AFTER country",
            'is_active' => "ADD COLUMN is_active TINYINT(1) DEFAULT 1 AFTER postal_code",
            'email_verified' => "ADD COLUMN email_verified TINYINT(1) DEFAULT 0 AFTER is_active",
            'last_login' => "ADD COLUMN last_login TIMESTAMP NULL AFTER email_verified",
            'updated_at' => "ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at"
        ];
        
        $columnsAdded = 0;
        
        foreach ($requiredColumns as $columnName => $alterSQL) {
            if (!in_array($columnName, $columns)) {
                try {
                    $db->exec("ALTER TABLE users " . $alterSQL);
                    echo "<p style='color: green;'>✅ Added column: $columnName</p>";
                    $columnsAdded++;
                } catch (PDOException $e) {
                    echo "<p style='color: red;'>❌ Failed to add column $columnName: " . $e->getMessage() . "</p>";
                }
            } else {
                echo "<p style='color: gray;'>⚪ Column $columnName already exists</p>";
            }
        }
        
        // Add indexes if they don't exist
        try {
            $db->exec("ALTER TABLE users ADD INDEX idx_email (email)");
            echo "<p style='color: green;'>✅ Added email index</p>";
        } catch (PDOException $e) {
            echo "<p style='color: gray;'>⚪ Email index already exists or failed to add</p>";
        }
        
        try {
            $db->exec("ALTER TABLE users ADD INDEX idx_username (username)");
            echo "<p style='color: green;'>✅ Added username index</p>";
        } catch (PDOException $e) {
            echo "<p style='color: gray;'>⚪ Username index already exists or failed to add</p>";
        }
        
        if ($columnsAdded > 0) {
            echo "<p style='color: green;'><strong>✅ Database updated successfully! Added $columnsAdded columns.</strong></p>";
        } else {
            echo "<p style='color: blue;'><strong>ℹ️ Database is already up to date!</strong></p>";
        }
    }
    
    // Verify the final table structure
    echo "<h3>Final Table Structure:</h3>";
    echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse;'>";
    echo "<tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    
    $tableInfo = $db->query("DESCRIBE users")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($tableInfo as $column) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($column['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<br><p style='color: green; font-weight: bold;'>🎉 Database update completed successfully!</p>";
    echo "<p><strong>Next steps:</strong></p>";
    echo "<ul>";
    echo "<li>✅ Your registration and login should now work properly</li>";
    echo "<li>✅ You can now test user registration at: <a href='register.php'>register.php</a></li>";
    echo "<li>✅ You can test login at: <a href='login.php'>login.php</a></li>";
    echo "<li>⚠️ <strong>Important:</strong> Delete this file (update_database.php) after running it for security</li>";
    echo "</ul>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'><strong>❌ Database Error:</strong> " . $e->getMessage() . "</p>";
    echo "<p>Please check your database connection settings in config/Database.php</p>";
} catch (Exception $e) {
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
h2, h3 {
    color: #333;
}
table {
    width: 100%;
    background: white;
    margin: 10px 0;
}
th {
    background: #007cba;
    color: white;
    padding: 8px;
}
td {
    padding: 8px;
    border: 1px solid #ddd;
}
p {
    margin: 5px 0;
    padding: 5px;
}
ul {
    background: white;
    padding: 15px;
    border-radius: 5px;
}
</style>
