<?php

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $conn;

    public function __construct() {
        // Check if we're on AWS or local environment
        if (isset($_SERVER['RDS_HOSTNAME'])) {
            // AWS RDS Configuration
            $this->host = $_SERVER['RDS_HOSTNAME'];
            $this->db_name = $_SERVER['RDS_DB_NAME'];
            $this->username = $_SERVER['RDS_USERNAME'];
            $this->password = $_SERVER['RDS_PASSWORD'];
        } else {
            // Local/Development Configuration
            $this->host = "localhost";
            $this->db_name = "electronics_store";
            $this->username = "root";
            $this->password = "";
        }
    }

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                ]
            );
        } catch(PDOException $e) {
            // Log error instead of displaying it
            error_log("Database Connection Error: " . $e->getMessage());
            throw new Exception("Database connection failed. Please try again later.");
        }

        return $this->conn;
    }
}
