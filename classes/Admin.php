<?php

class Admin {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Check if user is admin
    public function isAdmin($userId) {
        $query = "SELECT is_admin FROM users WHERE id = :id AND is_admin = 1 LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $userId);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }
    
    // Get dashboard statistics
    public function getDashboardStats() {
        $stats = [];
        
        // Total users
        $userQuery = "SELECT COUNT(*) as total FROM users WHERE is_admin = 0";
        $userStmt = $this->conn->prepare($userQuery);
        $userStmt->execute();
        $stats['total_users'] = $userStmt->fetch()['total'];
        
        // Total products
        $productQuery = "SELECT COUNT(*) as total FROM products";
        $productStmt = $this->conn->prepare($productQuery);
        $productStmt->execute();
        $stats['total_products'] = $productStmt->fetch()['total'];
        
        // Active products
        $activeQuery = "SELECT COUNT(*) as total FROM products WHERE status = 'active'";
        $activeStmt = $this->conn->prepare($activeQuery);
        $activeStmt->execute();
        $stats['active_products'] = $activeStmt->fetch()['total'];
        
        // Out of stock products
        $stockQuery = "SELECT COUNT(*) as total FROM products WHERE stock = 0 OR status = 'out_of_stock'";
        $stockStmt = $this->conn->prepare($stockQuery);
        $stockStmt->execute();
        $stats['out_of_stock'] = $stockStmt->fetch()['total'];
        
        // Total categories
        $categoryQuery = "SELECT COUNT(*) as total FROM categories";
        $categoryStmt = $this->conn->prepare($categoryQuery);
        $categoryStmt->execute();
        $stats['total_categories'] = $categoryStmt->fetch()['total'];
        
        // Recent users (last 30 days)
        $recentQuery = "SELECT COUNT(*) as total FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) AND is_admin = 0";
        $recentStmt = $this->conn->prepare($recentQuery);
        $recentStmt->execute();
        $stats['recent_users'] = $recentStmt->fetch()['total'];
        
        return $stats;
    }
    
    // Get recent users
    public function getRecentUsers($limit = 10) {
        $query = "SELECT id, username, email, full_name, created_at, last_login, is_active 
                  FROM users 
                  WHERE is_admin = 0 
                  ORDER BY created_at DESC 
                  LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get all products for management
    public function getAllProducts($limit = 50, $offset = 0, $search = '') {
        $searchCondition = '';
        if (!empty($search)) {
            $searchCondition = "WHERE name LIKE :search OR sku LIKE :search OR category LIKE :search";
        }
        
        $query = "SELECT * FROM products 
                  $searchCondition 
                  ORDER BY created_at DESC 
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        
        if (!empty($search)) {
            $searchTerm = "%$search%";
            $stmt->bindParam(":search", $searchTerm);
        }
        
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get total products count for pagination
    public function getTotalProductsCount($search = '') {
        $searchCondition = '';
        if (!empty($search)) {
            $searchCondition = "WHERE name LIKE :search OR sku LIKE :search OR category LIKE :search";
        }
        
        $query = "SELECT COUNT(*) as total FROM products $searchCondition";
        $stmt = $this->conn->prepare($query);
        
        if (!empty($search)) {
            $searchTerm = "%$search%";
            $stmt->bindParam(":search", $searchTerm);
        }
        
        $stmt->execute();
        return $stmt->fetch()['total'];
    }
    
    // Add new product
    public function addProduct($data) {
        try {
            $query = "INSERT INTO products 
                      (sku, name, description, price, image, category, brand, stock, weight, dimensions, warranty, rating, featured, status, meta_title, meta_description) 
                      VALUES 
                      (:sku, :name, :description, :price, :image, :category, :brand, :stock, :weight, :dimensions, :warranty, :rating, :featured, :status, :meta_title, :meta_description)";
            
            $stmt = $this->conn->prepare($query);
            
            // Generate SKU if not provided
            if (empty($data['sku'])) {
                $data['sku'] = $this->generateSKU();
            }
            
            $result = $stmt->execute([
                ':sku' => $data['sku'],
                ':name' => $data['name'],
                ':description' => $data['description'],
                ':price' => $data['price'],
                ':image' => $data['image'] ?? '',
                ':category' => $data['category'],
                ':brand' => $data['brand'] ?? '',
                ':stock' => $data['stock'] ?? 0,
                ':weight' => $data['weight'] ?? null,
                ':dimensions' => $data['dimensions'] ?? '',
                ':warranty' => $data['warranty'] ?? '',
                ':rating' => $data['rating'] ?? 0,
                ':featured' => $data['featured'] ?? 0,
                ':status' => $data['status'] ?? 'active',
                ':meta_title' => $data['meta_title'] ?? '',
                ':meta_description' => $data['meta_description'] ?? ''
            ]);
            
            if (!$result) {
                error_log("Failed to add product: " . print_r($stmt->errorInfo(), true));
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Database error in addProduct: " . $e->getMessage());
            return false;
        }
    }
    
    // Update product
    public function updateProduct($id, $data) {
        $query = "UPDATE products SET 
                  sku = :sku, name = :name, description = :description, price = :price, 
                  image = :image, category = :category, brand = :brand, stock = :stock, 
                  weight = :weight, dimensions = :dimensions, warranty = :warranty, 
                  rating = :rating, featured = :featured, status = :status, 
                  meta_title = :meta_title, meta_description = :meta_description,
                  updated_at = CURRENT_TIMESTAMP
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $data['id'] = $id;
        return $stmt->execute([
            ':id' => $data['id'],
            ':sku' => $data['sku'],
            ':name' => $data['name'],
            ':description' => $data['description'],
            ':price' => $data['price'],
            ':image' => $data['image'] ?? '',
            ':category' => $data['category'],
            ':brand' => $data['brand'] ?? '',
            ':stock' => $data['stock'] ?? 0,
            ':weight' => $data['weight'] ?? null,
            ':dimensions' => $data['dimensions'] ?? '',
            ':warranty' => $data['warranty'] ?? '',
            ':rating' => $data['rating'] ?? 0,
            ':featured' => $data['featured'] ?? 0,
            ':status' => $data['status'] ?? 'active',
            ':meta_title' => $data['meta_title'] ?? '',
            ':meta_description' => $data['meta_description'] ?? ''
        ]);
    }
    
    // Delete product
    public function deleteProduct($id) {
        $query = "DELETE FROM products WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }
    
    // Get single product
    public function getProduct($id) {
        $query = "SELECT * FROM products WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Generate unique SKU
    private function generateSKU() {
        do {
            $sku = 'EH' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
            $check = $this->conn->prepare("SELECT id FROM products WHERE sku = :sku");
            $check->execute([':sku' => $sku]);
        } while ($check->rowCount() > 0);
        
        return $sku;
    }
    
    // Get all users for management
    public function getAllUsers($limit = 50, $offset = 0) {
        $query = "SELECT id, username, email, full_name, phone, is_active, email_verified, created_at, last_login 
                  FROM users 
                  WHERE is_admin = 0 
                  ORDER BY created_at DESC 
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Toggle user status
    public function toggleUserStatus($userId) {
        $query = "UPDATE users SET is_active = NOT is_active WHERE id = :id AND is_admin = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $userId);
        return $stmt->execute();
    }
    
    // Get categories for dropdown
    public function getCategories() {
        $query = "SELECT * FROM categories ORDER BY name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
