<?php
session_start();

require_once 'config/Database.php';
require_once 'classes/Cart.php';
require_once 'classes/Product.php';

$database = new Database();
$db = $database->getConnection();
$cart = new Cart($db);
$product = new Product($db);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'add':
        $productId = $_POST['product_id'] ?? 0;
        $quantity = $_POST['quantity'] ?? 1;
        
        if (!$productId) {
            echo json_encode(['error' => 'Product ID is required']);
            exit();
        }
        
        // Check if product exists and has stock
        $productData = $product->getById($productId);
        if (!$productData) {
            echo json_encode(['error' => 'Product not found']);
            exit();
        }
        
        if ($productData['stock'] < $quantity) {
            echo json_encode(['error' => 'Insufficient stock. Only ' . $productData['stock'] . ' items available.']);
            exit();
        }
        
        if (isset($_SESSION['user_id'])) {
            // User is logged in
            $userId = $_SESSION['user_id'];
            
            // Check current cart quantity
            $cartItems = $cart->getCartItems($userId);
            $currentQuantity = 0;
            foreach ($cartItems as $item) {
                if ($item['id'] == $productId) {
                    $currentQuantity = $item['quantity'];
                    break;
                }
            }
            
            if (($currentQuantity + $quantity) > $productData['stock']) {
                echo json_encode(['error' => 'Cannot add more items. Stock limit exceeded.']);
                exit();
            }
            
            if ($cart->addItem($userId, $productId, $quantity)) {
                $cartCount = $cart->getCartCount($userId);
                echo json_encode([
                    'success' => true,
                    'message' => 'Product added to cart',
                    'cart_count' => $cartCount
                ]);
            } else {
                echo json_encode(['error' => 'Failed to add product to cart']);
            }
        } else {
            // Guest user - use session
            Cart::addToGuestCart($productId, $quantity);
            $guestCart = Cart::getGuestCart();
            $cartCount = array_sum($guestCart);
            
            echo json_encode([
                'success' => true,
                'message' => 'Product added to cart',
                'cart_count' => $cartCount
            ]);
        }
        break;
        
    case 'remove':
        $cartId = $_POST['cart_id'] ?? 0;
        $productId = $_POST['product_id'] ?? 0;
        
        if (isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];
            if ($cart->removeItem($cartId, $userId)) {
                $cartCount = $cart->getCartCount($userId);
                echo json_encode([
                    'success' => true,
                    'message' => 'Product removed from cart',
                    'cart_count' => $cartCount
                ]);
            } else {
                echo json_encode(['error' => 'Failed to remove product from cart']);
            }
        } else {
            Cart::removeFromGuestCart($productId);
            $guestCart = Cart::getGuestCart();
            $cartCount = array_sum($guestCart);
            
            echo json_encode([
                'success' => true,
                'message' => 'Product removed from cart',
                'cart_count' => $cartCount
            ]);
        }
        break;
        
    case 'update':
        $cartId = $_POST['cart_id'] ?? 0;
        $productId = $_POST['product_id'] ?? 0;
        $quantity = $_POST['quantity'] ?? 1;
        
        if (isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];
            
            // Check stock
            $productData = $product->getById($productId);
            if ($quantity > $productData['stock']) {
                echo json_encode(['error' => 'Insufficient stock']);
                exit();
            }
            
            if ($cart->updateQuantity($cartId, $userId, $quantity)) {
                $cartCount = $cart->getCartCount($userId);
                $cartTotal = $cart->getCartTotal($userId);
                echo json_encode([
                    'success' => true,
                    'message' => 'Cart updated',
                    'cart_count' => $cartCount,
                    'cart_total' => $cartTotal
                ]);
            } else {
                echo json_encode(['error' => 'Failed to update cart']);
            }
        } else {
            if ($quantity <= 0) {
                Cart::removeFromGuestCart($productId);
            } else {
                $_SESSION['guest_cart'][$productId] = $quantity;
            }
            
            $guestCart = Cart::getGuestCart();
            $cartCount = array_sum($guestCart);
            
            echo json_encode([
                'success' => true,
                'message' => 'Cart updated',
                'cart_count' => $cartCount
            ]);
        }
        break;
        
    case 'get_count':
        if (isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];
            $cartCount = $cart->getCartCount($userId);
        } else {
            $guestCart = Cart::getGuestCart();
            $cartCount = array_sum($guestCart);
        }
        
        echo json_encode(['cart_count' => $cartCount]);
        break;
        
    default:
        echo json_encode(['error' => 'Invalid action']);
        break;
}
?>
