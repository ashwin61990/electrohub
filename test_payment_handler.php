<?php
session_start();

require_once 'classes/RazorpayPayment.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

$action = $_POST['action'] ?? '';

if ($action === 'test_payment') {
    $amount = floatval($_POST['amount'] ?? 0);
    $name = $_POST['name'] ?? 'Test User';
    $email = $_POST['email'] ?? 'test@example.com';
    $phone = $_POST['phone'] ?? '9999999999';
    
    if ($amount <= 0) {
        echo json_encode(['error' => 'Invalid amount']);
        exit();
    }
    
    try {
        $razorpay = new RazorpayPayment();
        
        // Generate test order receipt
        $orderReceipt = 'TEST_' . time() . '_' . rand(1000, 9999);
        
        // Customer details
        $customerDetails = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone
        ];
        
        // Create Razorpay order
        $razorpayOrder = $razorpay->createOrder($amount, $orderReceipt, $customerDetails);
        
        if ($razorpayOrder['success']) {
            // Generate payment form data
            $paymentData = $razorpay->generatePaymentData(
                $razorpayOrder['order_id'],
                $amount,
                $customerDetails,
                ['description' => 'ElectroHub Test Payment - ' . $orderReceipt]
            );
            
            echo json_encode([
                'success' => true,
                'payment_data' => $paymentData,
                'order_id' => $razorpayOrder['order_id'],
                'receipt' => $orderReceipt
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => $razorpayOrder['error']
            ]);
        }
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Server error: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode(['error' => 'Invalid action']);
}
?>
