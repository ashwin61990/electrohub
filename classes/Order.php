<?php

class Order {
    private $conn;
    private $table_name = "orders";
    private $items_table = "order_items";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create new order
    public function createOrder($userId, $orderData, $cartItems) {
        try {
            $this->conn->beginTransaction();

            // Calculate totals
            $subtotal = 0;
            foreach ($cartItems as $item) {
                $subtotal += $item['price'] * $item['quantity'];
            }
            
            $shipping = $subtotal >= 500 ? 0 : 50;
            $total = $subtotal + $shipping;

            // Insert order
            $orderQuery = "INSERT INTO " . $this->table_name . " 
                          (user_id, total_amount, shipping_amount, status, shipping_address, billing_address, payment_method, payment_status) 
                          VALUES (:user_id, :total, :shipping, 'pending', :shipping_address, :billing_address, :payment_method, 'pending')";
            
            $orderStmt = $this->conn->prepare($orderQuery);
            $orderStmt->execute([
                ':user_id' => $userId,
                ':total' => $total,
                ':shipping' => $shipping,
                ':shipping_address' => json_encode($orderData['shipping_address']),
                ':billing_address' => json_encode($orderData['billing_address']),
                ':payment_method' => $orderData['payment_method']
            ]);

            $orderId = $this->conn->lastInsertId();

            // Insert order items and update stock
            $itemQuery = "INSERT INTO " . $this->items_table . " 
                         (order_id, product_id, quantity, price, total) 
                         VALUES (:order_id, :product_id, :quantity, :price, :total)";
            $itemStmt = $this->conn->prepare($itemQuery);

            $stockQuery = "UPDATE products SET stock = stock - :quantity WHERE id = :product_id AND stock >= :min_quantity";
            $stockStmt = $this->conn->prepare($stockQuery);

            foreach ($cartItems as $item) {
                // Insert order item
                $itemStmt->execute([
                    ':order_id' => $orderId,
                    ':product_id' => $item['id'],
                    ':quantity' => $item['quantity'],
                    ':price' => $item['price'],
                    ':total' => $item['price'] * $item['quantity']
                ]);

                // Update stock
                $stockResult = $stockStmt->execute([
                    ':quantity' => $item['quantity'],
                    ':product_id' => $item['id'],
                    ':min_quantity' => $item['quantity']
                ]);

                if (!$stockResult || $stockStmt->rowCount() == 0) {
                    throw new Exception("Insufficient stock for product: " . $item['name']);
                }
            }

            $this->conn->commit();
            return $orderId;

        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Order creation error: " . $e->getMessage());
            throw new Exception("Failed to process order: " . $e->getMessage());
        }
    }

    // Get order by ID
    public function getOrder($orderId, $userId = null) {
        $query = "SELECT o.*, u.full_name, u.email 
                  FROM " . $this->table_name . " o
                  JOIN users u ON o.user_id = u.id
                  WHERE o.id = :order_id";
        
        if ($userId) {
            $query .= " AND o.user_id = :user_id";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":order_id", $orderId);
        if ($userId) {
            $stmt->bindParam(":user_id", $userId);
        }
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get order items
    public function getOrderItems($orderId) {
        $query = "SELECT oi.*, p.name, p.image, p.category
                  FROM " . $this->items_table . " oi
                  JOIN products p ON oi.product_id = p.id
                  WHERE oi.order_id = :order_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":order_id", $orderId);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get user orders
    public function getUserOrders($userId, $limit = 20) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE user_id = :user_id 
                  ORDER BY created_at DESC 
                  LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $userId);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Update order status
    public function updateStatus($orderId, $status) {
        $query = "UPDATE " . $this->table_name . " 
                  SET status = :status, updated_at = CURRENT_TIMESTAMP 
                  WHERE id = :order_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":order_id", $orderId);
        return $stmt->execute();
    }

    // Update payment status
    public function updatePaymentStatus($orderId, $paymentStatus) {
        $query = "UPDATE " . $this->table_name . " 
                  SET payment_status = :payment_status, updated_at = CURRENT_TIMESTAMP 
                  WHERE id = :order_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":payment_status", $paymentStatus);
        $stmt->bindParam(":order_id", $orderId);
        return $stmt->execute();
    }

    // Generate order number
    public function generateOrderNumber($orderId) {
        return 'EH' . str_pad($orderId, 6, '0', STR_PAD_LEFT);
    }

    // Get all orders for admin
    public function getAllOrders($limit = 50) {
        $query = "SELECT o.*, u.full_name as customer_name, u.email as customer_email 
                  FROM " . $this->table_name . " o 
                  LEFT JOIN users u ON o.user_id = u.id 
                  ORDER BY o.created_at DESC 
                  LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get order by ID for admin
    public function getOrderById($orderId) {
        $query = "SELECT o.*, u.full_name as customer_name, u.email as customer_email 
                  FROM " . $this->table_name . " o 
                  LEFT JOIN users u ON o.user_id = u.id 
                  WHERE o.id = :order_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':order_id', $orderId);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Update order status
    public function updateOrderStatus($orderId, $status) {
        $query = "UPDATE " . $this->table_name . " 
                  SET status = :status, updated_at = CURRENT_TIMESTAMP 
                  WHERE id = :order_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':order_id', $orderId);
        
        return $stmt->execute();
    }

    // Get order statistics
    public function getOrderStats() {
        $stats = [];
        
        // Total orders
        $totalQuery = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $totalStmt = $this->conn->prepare($totalQuery);
        $totalStmt->execute();
        $stats['total_orders'] = $totalStmt->fetch()['total'];
        
        // Pending orders
        $pendingQuery = "SELECT COUNT(*) as pending FROM " . $this->table_name . " WHERE status = 'pending'";
        $pendingStmt = $this->conn->prepare($pendingQuery);
        $pendingStmt->execute();
        $stats['pending_orders'] = $pendingStmt->fetch()['pending'];
        
        // Total revenue
        $revenueQuery = "SELECT SUM(total_amount) as revenue FROM " . $this->table_name . " WHERE payment_status = 'paid'";
        $revenueStmt = $this->conn->prepare($revenueQuery);
        $revenueStmt->execute();
        $stats['total_revenue'] = $revenueStmt->fetch()['revenue'] ?: 0;
        
        return $stats;
    }
}
