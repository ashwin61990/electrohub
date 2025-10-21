<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'config/Database.php';
require_once 'classes/Order.php';
require_once 'classes/Page.php';

$database = new Database();
$db = $database->getConnection();
$order = new Order($db);
$page = new Page("Order Confirmation - ElectroHub", "Your order has been placed successfully", "order, confirmation, success");

$orderId = $_GET['order_id'] ?? 0;
$userId = $_SESSION['user_id'];

// Get order details
$orderData = $order->getOrder($orderId, $userId);
if (!$orderData) {
    header("Location: index.php");
    exit();
}

$orderItems = $order->getOrderItems($orderId);
$orderNumber = $order->generateOrderNumber($orderId);

$page->renderHeader();
?>

<div class="confirmation-container">
    <div class="container">
        <div class="confirmation-content">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            
            <h1>Order Placed Successfully!</h1>
            <p class="confirmation-message">
                Thank you for your order. We've received your order and will process it shortly.
            </p>
            
            <div class="order-details-card">
                <div class="order-header">
                    <h2>Order Details</h2>
                    <div class="order-meta">
                        <span class="order-number">Order #<?php echo $orderNumber; ?></span>
                        <span class="order-date"><?php echo date('M j, Y g:i A', strtotime($orderData['created_at'])); ?></span>
                    </div>
                </div>
                
                <div class="order-status">
                    <div class="status-badge status-<?php echo $orderData['status']; ?>">
                        <?php echo ucfirst($orderData['status']); ?>
                    </div>
                    <div class="payment-status">
                        Payment: <span class="payment-<?php echo $orderData['payment_status']; ?>">
                            <?php echo ucfirst($orderData['payment_status']); ?>
                        </span>
                    </div>
                </div>
                
                <div class="order-items">
                    <h3>Items Ordered</h3>
                    <?php foreach ($orderItems as $item): ?>
                        <div class="order-item">
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
                                <p class="item-category"><?php echo htmlspecialchars($item['category']); ?></p>
                                <div class="item-pricing">
                                    <span class="quantity">Qty: <?php echo $item['quantity']; ?></span>
                                    <span class="price">₹<?php echo number_format($item['price'], 2); ?> each</span>
                                    <span class="total">₹<?php echo number_format($item['total'], 2); ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="order-summary">
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span>₹<?php echo number_format($orderData['total_amount'] - $orderData['shipping_amount'], 2); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Shipping</span>
                        <span><?php echo $orderData['shipping_amount'] > 0 ? '₹' . number_format($orderData['shipping_amount'], 2) : 'Free'; ?></span>
                    </div>
                    <div class="summary-row total">
                        <span>Total</span>
                        <span>₹<?php echo number_format($orderData['total_amount'], 2); ?></span>
                    </div>
                </div>
                
                <div class="address-info">
                    <div class="address-section">
                        <h4>Shipping Address</h4>
                        <?php 
                        $shippingAddress = json_decode($orderData['shipping_address'], true);
                        ?>
                        <div class="address">
                            <p><strong><?php echo htmlspecialchars($shippingAddress['full_name']); ?></strong></p>
                            <p><?php echo htmlspecialchars($shippingAddress['address_line_1']); ?></p>
                            <?php if (!empty($shippingAddress['address_line_2'])): ?>
                                <p><?php echo htmlspecialchars($shippingAddress['address_line_2']); ?></p>
                            <?php endif; ?>
                            <p><?php echo htmlspecialchars($shippingAddress['city'] . ', ' . $shippingAddress['state'] . ' ' . $shippingAddress['postal_code']); ?></p>
                            <p>Phone: <?php echo htmlspecialchars($shippingAddress['phone']); ?></p>
                        </div>
                    </div>
                    
                    <div class="address-section">
                        <h4>Payment Method</h4>
                        <div class="payment-method">
                            <?php
                            $paymentMethods = [
                                'cod' => 'Cash on Delivery',
                                'upi' => 'UPI Payment',
                                'card' => 'Credit/Debit Card'
                            ];
                            ?>
                            <p><?php echo $paymentMethods[$orderData['payment_method']] ?? 'Unknown'; ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="next-steps">
                <h3>What's Next?</h3>
                <div class="steps-grid">
                    <div class="step-card">
                        <div class="step-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <h4>Order Confirmation</h4>
                        <p>You'll receive an email confirmation shortly with your order details.</p>
                    </div>
                    
                    <div class="step-card">
                        <div class="step-icon">
                            <i class="fas fa-box"></i>
                        </div>
                        <h4>Processing</h4>
                        <p>We'll prepare your order for shipment within 1-2 business days.</p>
                    </div>
                    
                    <div class="step-card">
                        <div class="step-icon">
                            <i class="fas fa-truck"></i>
                        </div>
                        <h4>Shipping</h4>
                        <p>Your order will be shipped and you'll receive tracking information.</p>
                    </div>
                    
                    <div class="step-card">
                        <div class="step-icon">
                            <i class="fas fa-home"></i>
                        </div>
                        <h4>Delivery</h4>
                        <p>Your order will be delivered to your specified address.</p>
                    </div>
                </div>
            </div>
            
            <div class="action-buttons">
                <a href="account.php" class="btn btn-primary">View Order History</a>
                <a href="index.php" class="btn btn-outline">Continue Shopping</a>
            </div>
        </div>
    </div>
</div>

<style>
.confirmation-container {
    min-height: calc(100vh - 80px);
    padding: 3rem 0;
    background: var(--dark-bg);
}

.confirmation-content {
    max-width: 800px;
    margin: 0 auto;
    text-align: center;
}

.success-icon {
    font-size: 5rem;
    color: var(--success-color);
    margin-bottom: 2rem;
}

.confirmation-content h1 {
    font-size: 2.5rem;
    margin-bottom: 1rem;
    background: var(--gradient-1);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.confirmation-message {
    font-size: 1.2rem;
    color: var(--text-secondary);
    margin-bottom: 3rem;
}

.order-details-card {
    background: var(--dark-card);
    border-radius: 16px;
    border: 1px solid var(--border-color);
    padding: 2rem;
    margin-bottom: 3rem;
    text-align: left;
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--border-color);
}

.order-header h2 {
    margin: 0;
    color: var(--text-primary);
}

.order-meta {
    text-align: right;
}

.order-number {
    display: block;
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--primary-color);
    margin-bottom: 0.25rem;
}

.order-date {
    color: var(--text-secondary);
    font-size: 0.9rem;
}

.order-status {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.status-badge {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
}

.status-pending {
    background: rgba(245, 158, 11, 0.2);
    color: var(--warning-color);
}

.status-processing {
    background: rgba(59, 130, 246, 0.2);
    color: var(--info-color);
}

.status-shipped {
    background: rgba(16, 185, 129, 0.2);
    color: var(--success-color);
}

.payment-status {
    color: var(--text-secondary);
}

.payment-pending {
    color: var(--warning-color);
}

.payment-paid {
    color: var(--success-color);
}

.order-items h3 {
    margin: 0 0 1.5rem 0;
    color: var(--text-primary);
}

.order-item {
    display: flex;
    gap: 1rem;
    padding: 1rem 0;
    border-bottom: 1px solid var(--border-color);
}

.order-item:last-child {
    border-bottom: none;
}

.item-image {
    width: 80px;
    height: 80px;
}

.item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 8px;
}

.placeholder-image {
    width: 100%;
    height: 100%;
    background: var(--border-color);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-secondary);
}

.item-details {
    flex: 1;
}

.item-details h4 {
    margin: 0 0 0.5rem 0;
    color: var(--text-primary);
}

.item-category {
    color: var(--text-secondary);
    font-size: 0.9rem;
    margin: 0 0 0.75rem 0;
}

.item-pricing {
    display: flex;
    gap: 1rem;
    align-items: center;
}

.quantity {
    color: var(--text-secondary);
}

.price {
    color: var(--text-secondary);
}

.total {
    color: var(--primary-color);
    font-weight: 600;
    margin-left: auto;
}

.order-summary {
    border-top: 1px solid var(--border-color);
    padding-top: 1rem;
    margin-top: 2rem;
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

.address-info {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid var(--border-color);
}

.address-section h4 {
    margin: 0 0 1rem 0;
    color: var(--text-primary);
}

.address p {
    margin: 0 0 0.25rem 0;
    color: var(--text-secondary);
}

.payment-method p {
    margin: 0;
    color: var(--text-secondary);
}

.next-steps {
    margin-bottom: 3rem;
}

.next-steps h3 {
    margin-bottom: 2rem;
    color: var(--text-primary);
}

.steps-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    text-align: center;
}

.step-card {
    background: var(--dark-card);
    border-radius: 12px;
    border: 1px solid var(--border-color);
    padding: 1.5rem;
}

.step-icon {
    width: 60px;
    height: 60px;
    background: var(--primary-color);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem auto;
    font-size: 1.5rem;
    color: white;
}

.step-card h4 {
    margin: 0 0 0.75rem 0;
    color: var(--text-primary);
}

.step-card p {
    margin: 0;
    color: var(--text-secondary);
    font-size: 0.9rem;
}

.action-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
}

@media (max-width: 768px) {
    .order-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }

    .order-meta {
        text-align: left;
    }

    .order-status {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }

    .address-info {
        grid-template-columns: 1fr;
    }

    .steps-grid {
        grid-template-columns: 1fr;
    }

    .action-buttons {
        flex-direction: column;
    }

    .item-pricing {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.25rem;
    }

    .total {
        margin-left: 0;
    }
}
</style>

<?php
$page->renderFooter();
?>
