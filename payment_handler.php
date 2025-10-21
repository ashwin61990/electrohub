<?php
session_start();

require_once 'config/Database.php';
require_once 'classes/RazorpayPayment.php';
require_once 'classes/Order.php';
require_once 'classes/Cart.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

$database = new Database();
$db = $database->getConnection();
$razorpay = new RazorpayPayment();
$order = new Order($db);
$cart = new Cart($db);

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'create_order':
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['error' => 'User not logged in']);
            exit();
        }
        
        $userId = $_SESSION['user_id'];
        $cartItems = $cart->getCartItems($userId);
        $cartTotal = $cart->getCartTotal($userId);
        
        if (empty($cartItems)) {
            echo json_encode(['error' => 'Cart is empty']);
            exit();
        }
        
        // Calculate total with shipping
        $shipping = $cartTotal >= 500 ? 0 : 50;
        $finalTotal = $cartTotal + $shipping;
        
        // Generate order receipt
        $orderReceipt = 'EH_' . time() . '_' . $userId;
        
        // Customer details
        $customerDetails = [
            'name' => $_SESSION['full_name'] ?? 'Customer',
            'email' => $_SESSION['email'] ?? '',
            'phone' => $_POST['shipping_phone'] ?? ''
        ];
        
        // Prepare order data from form
        $orderData = [
            'shipping_address' => [
                'full_name' => $_POST['shipping_name'] ?? '',
                'address' => $_POST['shipping_address1'] ?? '',
                'city' => $_POST['shipping_city'] ?? '',
                'state' => $_POST['shipping_state'] ?? '',
                'postal_code' => $_POST['shipping_postal'] ?? '',
                'phone' => $_POST['shipping_phone'] ?? ''
            ],
            'billing_address' => [
                'full_name' => $_POST['billing_name'] ?? $_POST['shipping_name'] ?? '',
                'address' => $_POST['billing_address1'] ?? $_POST['shipping_address1'] ?? '',
                'city' => $_POST['billing_city'] ?? $_POST['shipping_city'] ?? '',
                'state' => $_POST['billing_state'] ?? $_POST['shipping_state'] ?? '',
                'postal_code' => $_POST['billing_postal'] ?? $_POST['shipping_postal'] ?? '',
                'phone' => $_POST['billing_phone'] ?? $_POST['shipping_phone'] ?? ''
            ],
            'payment_method' => 'razorpay'
        ];
        
        // Use shipping address for billing if same_as_shipping is checked
        if (isset($_POST['same_as_shipping'])) {
            $orderData['billing_address'] = $orderData['shipping_address'];
        }
        
        // Create Razorpay order
        $razorpayOrder = $razorpay->createOrder($finalTotal, $orderReceipt, $customerDetails);
        
        if ($razorpayOrder['success']) {
            // Store order details in session for later processing
            $_SESSION['pending_order'] = [
                'razorpay_order_id' => $razorpayOrder['order_id'],
                'amount' => $finalTotal,
                'cart_items' => $cartItems,
                'shipping_amount' => $shipping,
                'customer_details' => $customerDetails,
                'order_data' => $orderData
            ];
            
            // Generate payment form data
            $paymentData = $razorpay->generatePaymentData(
                $razorpayOrder['order_id'],
                $finalTotal,
                $customerDetails,
                ['description' => 'ElectroHub Order - ' . $orderReceipt]
            );
            
            echo json_encode([
                'success' => true,
                'payment_data' => $paymentData,
                'order_id' => $razorpayOrder['order_id']
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => $razorpayOrder['error']
            ]);
        }
        break;
        
    case 'verify_payment':
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['pending_order'])) {
            echo json_encode(['error' => 'Invalid session']);
            exit();
        }
        
        $razorpayOrderId = $_POST['razorpay_order_id'] ?? '';
        $razorpayPaymentId = $_POST['razorpay_payment_id'] ?? '';
        $razorpaySignature = $_POST['razorpay_signature'] ?? '';
        
        if (empty($razorpayOrderId) || empty($razorpayPaymentId) || empty($razorpaySignature)) {
            echo json_encode(['error' => 'Missing payment parameters']);
            exit();
        }
        
        // Verify payment signature
        $isValidSignature = $razorpay->verifyPayment($razorpayOrderId, $razorpayPaymentId, $razorpaySignature);
        
        if ($isValidSignature) {
            try {
                $userId = $_SESSION['user_id'];
                $pendingOrder = $_SESSION['pending_order'];
                
                // Use the order data from session
                $orderData = $pendingOrder['order_data'];
                
                // Create order in database
                $orderId = $order->createOrder($userId, $orderData, $pendingOrder['cart_items']);
                
                // Update order with payment details
                $updateQuery = "UPDATE orders SET 
                               payment_status = 'paid',
                               order_number = ?,
                               notes = ?
                               WHERE id = ?";
                
                $orderNumber = $order->generateOrderNumber($orderId);
                $paymentNotes = json_encode([
                    'razorpay_order_id' => $razorpayOrderId,
                    'razorpay_payment_id' => $razorpayPaymentId,
                    'payment_method' => 'razorpay'
                ]);
                
                $stmt = $db->prepare($updateQuery);
                $stmt->execute([$orderNumber, $paymentNotes, $orderId]);
                
                // Clear cart and session
                $cart->clearCart($userId);
                unset($_SESSION['pending_order']);
                
                echo json_encode([
                    'success' => true,
                    'order_id' => $orderId,
                    'payment_id' => $razorpayPaymentId,
                    'message' => 'Payment successful'
                ]);
                
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'error' => 'Failed to process order: ' . $e->getMessage()
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'Payment verification failed'
            ]);
        }
        break;
        
    case 'payment_failed':
        // Handle payment failure
        if (isset($_SESSION['pending_order'])) {
            unset($_SESSION['pending_order']);
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Payment cancelled'
        ]);
        break;
        
    default:
        echo json_encode(['error' => 'Invalid action']);
        break;
}
?>
