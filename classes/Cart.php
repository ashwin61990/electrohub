<?php

class Cart {
    private $conn;
    private $table_name = "cart";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Add item to cart
    public function addItem($userId, $productId, $quantity = 1) {
        // Check if item already exists in cart
        $checkQuery = "SELECT id, quantity FROM " . $this->table_name . " WHERE user_id = :user_id AND product_id = :product_id";
        $checkStmt = $this->conn->prepare($checkQuery);
        $checkStmt->bindParam(":user_id", $userId);
        $checkStmt->bindParam(":product_id", $productId);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() > 0) {
            // Update existing item
            $existingItem = $checkStmt->fetch();
            $newQuantity = $existingItem['quantity'] + $quantity;
            
            $updateQuery = "UPDATE " . $this->table_name . " SET quantity = :quantity, updated_at = CURRENT_TIMESTAMP WHERE id = :id";
            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->bindParam(":quantity", $newQuantity);
            $updateStmt->bindParam(":id", $existingItem['id']);
            return $updateStmt->execute();
        } else {
            // Add new item
            $insertQuery = "INSERT INTO " . $this->table_name . " (user_id, product_id, quantity) VALUES (:user_id, :product_id, :quantity)";
            $insertStmt = $this->conn->prepare($insertQuery);
            $insertStmt->bindParam(":user_id", $userId);
            $insertStmt->bindParam(":product_id", $productId);
            $insertStmt->bindParam(":quantity", $quantity);
            return $insertStmt->execute();
        }
    }

    // Get cart items for user
    public function getCartItems($userId) {
        $query = "SELECT c.id as cart_id, c.quantity, c.created_at,
                         p.id, p.name, p.price, p.image, p.stock, p.category
                  FROM " . $this->table_name . " c
                  JOIN products p ON c.product_id = p.id
                  WHERE c.user_id = :user_id AND p.status = 'active'
                  ORDER BY c.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $userId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Update cart item quantity
    public function updateQuantity($cartId, $userId, $quantity) {
        if ($quantity <= 0) {
            return $this->removeItem($cartId, $userId);
        }
        
        $query = "UPDATE " . $this->table_name . " SET quantity = :quantity, updated_at = CURRENT_TIMESTAMP 
                  WHERE id = :cart_id AND user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":quantity", $quantity);
        $stmt->bindParam(":cart_id", $cartId);
        $stmt->bindParam(":user_id", $userId);
        return $stmt->execute();
    }

    // Remove item from cart
    public function removeItem($cartId, $userId) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :cart_id AND user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":cart_id", $cartId);
        $stmt->bindParam(":user_id", $userId);
        return $stmt->execute();
    }

    // Clear entire cart
    public function clearCart($userId) {
        $query = "DELETE FROM " . $this->table_name . " WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $userId);
        return $stmt->execute();
    }

    // Get cart total
    public function getCartTotal($userId) {
        $query = "SELECT SUM(c.quantity * p.price) as total
                  FROM " . $this->table_name . " c
                  JOIN products p ON c.product_id = p.id
                  WHERE c.user_id = :user_id AND p.status = 'active'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $userId);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result ? $result['total'] : 0;
    }

    // Get cart item count
    public function getCartCount($userId) {
        $query = "SELECT SUM(quantity) as count FROM " . $this->table_name . " WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $userId);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result ? $result['count'] : 0;
    }

    // Check if product is in cart
    public function isInCart($userId, $productId) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE user_id = :user_id AND product_id = :product_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $userId);
        $stmt->bindParam(":product_id", $productId);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    // Get cart for guest users (session-based)
    public static function getGuestCart() {
        return isset($_SESSION['guest_cart']) ? $_SESSION['guest_cart'] : [];
    }

    // Add to guest cart
    public static function addToGuestCart($productId, $quantity = 1) {
        if (!isset($_SESSION['guest_cart'])) {
            $_SESSION['guest_cart'] = [];
        }

        if (isset($_SESSION['guest_cart'][$productId])) {
            $_SESSION['guest_cart'][$productId] += $quantity;
        } else {
            $_SESSION['guest_cart'][$productId] = $quantity;
        }
    }

    // Remove from guest cart
    public static function removeFromGuestCart($productId) {
        if (isset($_SESSION['guest_cart'][$productId])) {
            unset($_SESSION['guest_cart'][$productId]);
        }
    }

    // Clear guest cart
    public static function clearGuestCart() {
        $_SESSION['guest_cart'] = [];
    }

    // Transfer guest cart to user cart
    public function transferGuestCart($userId) {
        $guestCart = self::getGuestCart();
        
        foreach ($guestCart as $productId => $quantity) {
            $this->addItem($userId, $productId, $quantity);
        }
        
        self::clearGuestCart();
    }
}
