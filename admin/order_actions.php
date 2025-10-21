<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

require_once '../config/Database.php';
require_once '../classes/Order.php';

$database = new Database();
$db = $database->getConnection();
$order = new Order($db);

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

if ($action === 'update_status') {
    $orderId = $_POST['order_id'] ?? 0;
    $status = $_POST['status'] ?? '';
    
    $validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
    
    if (!$orderId || !in_array($status, $validStatuses)) {
        echo json_encode(['success' => false, 'error' => 'Invalid parameters']);
        exit();
    }
    
    if ($order->updateOrderStatus($orderId, $status)) {
        echo json_encode(['success' => true, 'message' => 'Order status updated successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update order status']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid action']);
}
