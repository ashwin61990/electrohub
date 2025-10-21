<?php

class Product {
    private $conn;
    private $table_name = "products";

    public $id;
    public $name;
    public $description;
    public $price;
    public $image;
    public $category;
    public $stock;
    public $rating;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all products
    public function getAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Get featured products
    public function getFeatured($limit = 8) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE featured = 1 ORDER BY id DESC LIMIT :limit";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    // Get products by category
    public function getByCategory($category, $limit = 4) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE category = :category ORDER BY id DESC LIMIT :limit";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":category", $category);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    // Get single product
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    // Get featured products (returns array)
    public function getFeaturedProducts($limit = 8) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE featured = 1 AND status = 'active' ORDER BY id DESC LIMIT :limit";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get all active products (returns array)
    public function getAllProducts($limit = 20) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE status = 'active' ORDER BY id DESC LIMIT :limit";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Update stock quantity
    public function updateStock($id, $quantity) {
        $query = "UPDATE " . $this->table_name . " SET stock = stock - :quantity WHERE id = :id AND stock >= :quantity";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":quantity", $quantity);
        return $stmt->execute();
    }

    // Get product stock
    public function getStock($id) {
        $query = "SELECT stock FROM " . $this->table_name . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result ? $result['stock'] : 0;
    }

    // Get product count by category
    public function getProductCountByCategory($category) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE category = :category AND status = 'active'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":category", $category);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result ? $result['count'] : 0;
    }
}
