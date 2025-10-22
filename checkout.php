<?php
session_start();

// Suppress errors for production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=checkout.php");
    exit();
}

require_once 'config/Database.php';
require_once 'classes/Cart.php';
require_once 'classes/Order.php';
require_once 'classes/Product.php';
require_once 'classes/Page.php';

$database = new Database();
$db = $database->getConnection();
$cart = new Cart($db);
$order = new Order($db);
$product = new Product($db);
$page = new Page("Checkout - ElectroHub", "Complete your order", "checkout, payment, order");

$userId = $_SESSION['user_id'];
$cartItems = $cart->getCartItems($userId);
$cartTotal = $cart->getCartTotal($userId);
$cartCount = $cart->getCartCount($userId);

// Redirect if cart is empty
if (empty($cartItems)) {
    header("Location: cart.php");
    exit();
}

$message = '';
$messageType = '';

// Handle order submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_order'])) {
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
            'full_name' => $_POST['billing_name'] ?? '',
            'address' => $_POST['billing_address1'] ?? '',
            'city' => $_POST['billing_city'] ?? '',
            'state' => $_POST['billing_state'] ?? '',
            'postal_code' => $_POST['billing_postal'] ?? '',
            'phone' => $_POST['billing_phone'] ?? ''
        ],
        'payment_method' => $_POST['payment_method'] ?? 'cod'
    ];

    // Use shipping address for billing if same_as_shipping is checked
    if (isset($_POST['same_as_shipping'])) {
        $orderData['billing_address'] = $orderData['shipping_address'];
    }

    try {
        $orderId = $order->createOrder($userId, $orderData, $cartItems);
        
        // Clear cart after successful order
        $cart->clearCart($userId);
        
        // Redirect to order confirmation
        header("Location: order_confirmation.php?order_id=" . $orderId);
        exit();
        
    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = 'error';
    }
}

$shipping = $cartTotal >= 500 ? 0 : 50;
$finalTotal = $cartTotal + $shipping;

$page->renderHeader();
?>

<div class="checkout-container">
    <div class="container">
        <div class="checkout-header">
            <h1>Checkout</h1>
            <div class="checkout-steps">
                <div class="step active">
                    <span class="step-number">1</span>
                    <span class="step-text">Shipping</span>
                </div>
                <div class="step active">
                    <span class="step-number">2</span>
                    <span class="step-text">Payment</span>
                </div>
                <div class="step">
                    <span class="step-number">3</span>
                    <span class="step-text">Confirmation</span>
                </div>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="checkout-form">
            <div class="checkout-content">
                <div class="checkout-main">
                    <!-- Shipping Information -->
                    <div class="checkout-section">
                        <h2>Shipping Information</h2>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="shipping_name">Full Name *</label>
                                <input type="text" id="shipping_name" name="shipping_name" class="form-control" 
                                       value="<?php echo htmlspecialchars($_SESSION['full_name'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="shipping_phone">Phone Number *</label>
                                <input type="tel" id="shipping_phone" name="shipping_phone" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="shipping_address1">Address *</label>
                            <input type="text" id="shipping_address1" name="shipping_address1" class="form-control" 
                                   placeholder="Street address, apartment, suite, building, floor, etc." required>
                        </div>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="shipping_city">City *</label>
                                <input type="text" id="shipping_city" name="shipping_city" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="shipping_state">State *</label>
                                <input type="text" id="shipping_state" name="shipping_state" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="shipping_postal">Postal Code *</label>
                                <input type="text" id="shipping_postal" name="shipping_postal" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <!-- Billing Information -->
                    <div class="checkout-section">
                        <h2>Billing Information</h2>
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="same_as_shipping" id="same_as_shipping" checked onchange="toggleBillingAddress()">
                                <span class="checkmark"></span>
                                Same as shipping address
                            </label>
                        </div>
                        
                        <div id="billing_fields" style="display: none;">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="billing_name">Full Name *</label>
                                    <input type="text" id="billing_name" name="billing_name" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label for="billing_phone">Phone Number *</label>
                                    <input type="tel" id="billing_phone" name="billing_phone" class="form-control">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="billing_address1">Address *</label>
                                <input type="text" id="billing_address1" name="billing_address1" class="form-control" 
                                       placeholder="Street address, apartment, suite, building, floor, etc.">
                            </div>
                            
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="billing_city">City *</label>
                                    <input type="text" id="billing_city" name="billing_city" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label for="billing_state">State *</label>
                                    <input type="text" id="billing_state" name="billing_state" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label for="billing_postal">Postal Code *</label>
                                    <input type="text" id="billing_postal" name="billing_postal" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="checkout-section">
                        <h2>Payment Method</h2>
                        <div class="payment-methods">
                            <label class="payment-option">
                                <input type="radio" name="payment_method" value="cod" checked>
                                <div class="payment-card">
                                    <div class="payment-icon">
                                        <i class="fas fa-money-bill-wave"></i>
                                    </div>
                                    <div class="payment-info">
                                        <h4>Cash on Delivery</h4>
                                        <p>Pay when you receive your order</p>
                                    </div>
                                </div>
                            </label>
                            
                            <label class="payment-option">
                                <input type="radio" name="payment_method" value="razorpay">
                                <div class="payment-card">
                                    <div class="payment-icon">
                                        <i class="fas fa-credit-card"></i>
                                    </div>
                                    <div class="payment-info">
                                        <h4>Online Payment</h4>
                                        <p>Credit/Debit Card, UPI, Net Banking, Wallets</p>
                                        <small style="color: #6366f1; font-weight: 600;">Powered by Razorpay</small>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="checkout-sidebar">
                    <div class="order-summary">
                        <h3>Order Summary</h3>
                        
                        <div class="summary-items">
                            <?php foreach ($cartItems as $item): ?>
                                <div class="summary-item">
                                    <div class="item-image">
                                        <?php if (!empty($item['image'])): ?>
                                            <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                        <?php else: ?>
                                            <div class="placeholder-image">
                                                <i class="fas fa-image"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="item-details">
                                        <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                                        <p>Qty: <?php echo $item['quantity']; ?></p>
                                        <p class="item-price">₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="summary-totals">
                            <div class="summary-row">
                                <span>Subtotal (<?php echo $cartCount; ?> items)</span>
                                <span>₹<?php echo number_format($cartTotal, 2); ?></span>
                            </div>
                            
                            <div class="summary-row">
                                <span>Shipping</span>
                                <span><?php echo $shipping > 0 ? '₹' . number_format($shipping, 2) : 'Free'; ?></span>
                            </div>
                            
                            <?php if ($cartTotal < 500): ?>
                                <div class="summary-note">
                                    <small>Add ₹<?php echo number_format(500 - $cartTotal, 2); ?> more for free shipping</small>
                                </div>
                            <?php endif; ?>
                            
                            <div class="summary-divider"></div>
                            
                            <div class="summary-row total">
                                <span>Total</span>
                                <span>₹<?php echo number_format($finalTotal, 2); ?></span>
                            </div>
                        </div>
                        
                        <button type="submit" name="place_order" class="btn btn-primary btn-block btn-lg" id="place_order_btn">
                            Place Order
                        </button>
                        
                        <div class="security-info">
                            <i class="fas fa-lock"></i>
                            <span>Your payment information is secure and encrypted</span>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Razorpay JavaScript SDK -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<style>
.checkout-container {
    min-height: calc(100vh - 80px);
    padding: 2rem 0;
    background: var(--dark-bg);
}

.checkout-header {
    text-align: center;
    margin-bottom: 3rem;
}

.checkout-header h1 {
    font-size: 2.5rem;
    margin-bottom: 2rem;
    background: var(--gradient-1);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.checkout-steps {
    display: flex;
    justify-content: center;
    gap: 2rem;
    margin-bottom: 2rem;
}

.step {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--text-secondary);
}

.step.active {
    color: var(--primary-color);
}

.step-number {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: var(--border-color);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
}

.step.active .step-number {
    background: var(--primary-color);
    color: white;
}

.checkout-content {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 3rem;
}

.checkout-section {
    background: var(--dark-card);
    border-radius: 16px;
    border: 1px solid var(--border-color);
    padding: 2rem;
    margin-bottom: 2rem;
}

.checkout-section h2 {
    margin: 0 0 1.5rem 0;
    color: var(--text-primary);
    font-size: 1.5rem;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    color: var(--text-primary);
    font-weight: 500;
}

.form-control {
    width: 100%;
    padding: 0.875rem;
    background: var(--dark-bg);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    color: var(--text-primary);
    font-size: 1rem;
    transition: border-color 0.2s;
}

.form-control:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    cursor: pointer;
    color: var(--text-primary);
}

.checkbox-label input[type="checkbox"] {
    width: 18px;
    height: 18px;
}

.payment-methods {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.payment-option {
    cursor: pointer;
}

.payment-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.5rem;
    background: var(--dark-bg);
    border: 2px solid var(--border-color);
    border-radius: 12px;
    transition: all 0.2s;
}

.payment-option input[type="radio"]:checked + .payment-card {
    border-color: var(--primary-color);
    background: rgba(99, 102, 241, 0.05);
}

.payment-icon {
    width: 48px;
    height: 48px;
    background: var(--primary-color);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
}

.payment-info h4 {
    margin: 0 0 0.25rem 0;
    color: var(--text-primary);
}

.payment-info p {
    margin: 0;
    color: var(--text-secondary);
    font-size: 0.9rem;
}

.order-summary {
    background: var(--dark-card);
    border-radius: 16px;
    border: 1px solid var(--border-color);
    padding: 2rem;
    position: sticky;
    top: 2rem;
}

.order-summary h3 {
    margin: 0 0 1.5rem 0;
    color: var(--text-primary);
}

.summary-items {
    margin-bottom: 1.5rem;
}

.summary-item {
    display: flex;
    gap: 1rem;
    padding: 1rem 0;
    border-bottom: 1px solid var(--border-color);
}

.summary-item:last-child {
    border-bottom: none;
}

.summary-item .item-image {
    width: 60px;
    height: 60px;
}

.summary-item .item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 8px;
}

.summary-item .placeholder-image {
    width: 100%;
    height: 100%;
    background: var(--border-color);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-secondary);
}

.summary-item .item-details h4 {
    margin: 0 0 0.25rem 0;
    color: var(--text-primary);
    font-size: 1rem;
}

.summary-item .item-details p {
    margin: 0;
    color: var(--text-secondary);
    font-size: 0.9rem;
}

.summary-item .item-price {
    color: var(--primary-color);
    font-weight: 600;
}

.summary-totals {
    border-top: 1px solid var(--border-color);
    padding-top: 1rem;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.75rem;
    color: var(--text-secondary);
}

.summary-row.total {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--text-primary);
    padding-top: 0.75rem;
    border-top: 1px solid var(--border-color);
    margin-top: 0.75rem;
}

.summary-note {
    background: rgba(99, 102, 241, 0.1);
    padding: 0.75rem;
    border-radius: 8px;
    margin-bottom: 1rem;
    text-align: center;
}

.summary-note small {
    color: var(--primary-color);
}

.summary-divider {
    height: 1px;
    background: var(--border-color);
    margin: 1rem 0;
}

.security-info {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    justify-content: center;
    margin-top: 1rem;
    color: var(--text-secondary);
    font-size: 0.9rem;
}

.security-info i {
    color: var(--success-color);
}

.alert {
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 2rem;
}

.alert-error {
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid rgba(239, 68, 68, 0.3);
    color: var(--danger-color);
}

@media (max-width: 768px) {
    .checkout-content {
        grid-template-columns: 1fr;
    }
    
    .checkout-steps {
        flex-direction: column;
        align-items: center;
        gap: 1rem;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
function toggleBillingAddress() {
    const checkbox = document.getElementById('same_as_shipping');
    const billingFields = document.getElementById('billing_fields');
    
    if (checkbox.checked) {
        billingFields.style.display = 'none';
        // Clear billing fields
        billingFields.querySelectorAll('input').forEach(input => {
            input.removeAttribute('required');
        });
    } else {
        billingFields.style.display = 'block';
        // Make billing fields required
        billingFields.querySelectorAll('input[id$="_name"], input[id$="_address1"], input[id$="_city"], input[id$="_state"], input[id$="_postal"], input[id$="_phone"]').forEach(input => {
            input.setAttribute('required', 'required');
        });
    }
}

// Initialize billing address toggle
document.addEventListener('DOMContentLoaded', function() {
    toggleBillingAddress();
});

// Handle form submission for Razorpay payments
document.getElementById('place_order_btn').addEventListener('click', function(e) {
    const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
    
    if (paymentMethod === 'razorpay') {
        e.preventDefault();
        processRazorpayPayment();
    }
    // For COD, let the form submit normally
});

function processRazorpayPayment() {
    const form = document.querySelector('.checkout-form');
    const formData = new FormData(form);
    formData.append('action', 'create_order');
    
    // Show loading
    const btn = document.getElementById('place_order_btn');
    const originalText = btn.textContent;
    btn.textContent = 'Processing...';
    btn.disabled = true;
    
    fetch('payment_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Initialize Razorpay checkout
            const options = {
                ...data.payment_data,
                handler: function(response) {
                    verifyPayment(response);
                },
                modal: {
                    ondismiss: function() {
                        // Reset button
                        btn.textContent = originalText;
                        btn.disabled = false;
                        
                        // Notify payment handler about cancellation
                        fetch('payment_handler.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: 'action=payment_failed'
                        });
                    }
                }
            };
            
            const rzp = new Razorpay(options);
            rzp.open();
        } else {
            alert('Error: ' + data.error);
            btn.textContent = originalText;
            btn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while processing payment');
        btn.textContent = originalText;
        btn.disabled = false;
    });
}

function verifyPayment(response) {
    const formData = new FormData();
    formData.append('action', 'verify_payment');
    formData.append('razorpay_order_id', response.razorpay_order_id);
    formData.append('razorpay_payment_id', response.razorpay_payment_id);
    formData.append('razorpay_signature', response.razorpay_signature);
    
    fetch('payment_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Redirect to order confirmation
            window.location.href = 'order_confirmation.php?order_id=' + data.order_id;
        } else {
            alert('Payment verification failed: ' + data.error);
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Payment verification failed');
        location.reload();
    });
}
</script>

<?php
$page->renderFooter();
?>
