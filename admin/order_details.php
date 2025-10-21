<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    http_response_code(403);
    exit('Unauthorized');
}

require_once '../config/Database.php';
require_once '../classes/Order.php';

$database = new Database();
$db = $database->getConnection();
$order = new Order($db);

$orderId = $_GET['id'] ?? 0;

if (!$orderId) {
    echo '<p>Invalid order ID</p>';
    exit();
}

// Get order details
$orderData = $order->getOrderById($orderId);
$orderItems = $order->getOrderItems($orderId);

if (!$orderData) {
    echo '<p>Order not found</p>';
    exit();
}

$shippingAddress = json_decode($orderData['shipping_address'], true);
$billingAddress = json_decode($orderData['billing_address'], true);
?>

<div class="order-details">
    <div class="order-header">
        <div>
            <h4>Order #<?php echo $order->generateOrderNumber($orderData['id']); ?></h4>
            <p>Placed on <?php echo date('F j, Y \a\t g:i A', strtotime($orderData['created_at'])); ?></p>
        </div>
        <div>
            <span class="status status-<?php echo $orderData['status']; ?>">
                <?php echo ucfirst($orderData['status']); ?>
            </span>
        </div>
    </div>

    <div class="order-section">
        <h5>Customer Information</h5>
        <div class="info-grid">
            <div class="info-item">
                <strong>Name:</strong>
                <span><?php echo htmlspecialchars($orderData['customer_name']); ?></span>
            </div>
            <div class="info-item">
                <strong>Email:</strong>
                <span><?php echo htmlspecialchars($orderData['customer_email']); ?></span>
            </div>
        </div>
    </div>

    <div class="order-section">
        <h5>Payment Information</h5>
        <div class="info-grid">
            <div class="info-item">
                <strong>Payment Method:</strong>
                <span><?php echo ucfirst($orderData['payment_method']); ?></span>
            </div>
            <div class="info-item">
                <strong>Payment Status:</strong>
                <span class="payment-status payment-<?php echo $orderData['payment_status']; ?>">
                    <?php echo ucfirst($orderData['payment_status']); ?>
                </span>
            </div>
            <div class="info-item">
                <strong>Subtotal:</strong>
                <span>₹<?php echo number_format($orderData['total_amount'] - $orderData['shipping_amount'], 2); ?></span>
            </div>
            <div class="info-item">
                <strong>Shipping:</strong>
                <span><?php echo $orderData['shipping_amount'] > 0 ? '₹' . number_format($orderData['shipping_amount'], 2) : 'Free'; ?></span>
            </div>
            <div class="info-item">
                <strong>Total Amount:</strong>
                <span class="total-amount">₹<?php echo number_format($orderData['total_amount'], 2); ?></span>
            </div>
        </div>
    </div>

    <div class="order-section">
        <h5>Shipping Address</h5>
        <div class="address-box">
            <p><strong><?php echo htmlspecialchars($shippingAddress['full_name']); ?></strong></p>
            <p><?php echo htmlspecialchars($shippingAddress['address'] ?? $shippingAddress['address_line_1'] ?? ''); ?></p>
            <p><?php echo htmlspecialchars($shippingAddress['city'] . ', ' . $shippingAddress['state'] . ' ' . $shippingAddress['postal_code']); ?></p>
            <p>Phone: <?php echo htmlspecialchars($shippingAddress['phone']); ?></p>
        </div>
    </div>

    <div class="order-section">
        <h5>Order Items</h5>
        <table class="items-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orderItems as $item): ?>
                    <tr>
                        <td>
                            <div class="product-info">
                                <?php if (!empty($item['image'])): ?>
                                    <img src="../<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="product-thumb">
                                <?php endif; ?>
                                <div>
                                    <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                                    <small><?php echo htmlspecialchars($item['category']); ?></small>
                                </div>
                            </div>
                        </td>
                        <td>₹<?php echo number_format($item['price'], 2); ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td><strong>₹<?php echo number_format($item['total'], 2); ?></strong></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.order-details {
    color: var(--text-primary);
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--border-color);
}

.order-header h4 {
    margin: 0 0 0.5rem 0;
    font-size: 1.5rem;
}

.order-header p {
    margin: 0;
    color: var(--text-secondary);
}

.order-section {
    margin-bottom: 2rem;
}

.order-section h5 {
    margin: 0 0 1rem 0;
    color: var(--text-primary);
    font-size: 1.1rem;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.info-item strong {
    color: var(--text-secondary);
    font-size: 0.9rem;
}

.total-amount {
    font-size: 1.2rem;
    color: var(--primary-color);
    font-weight: 700;
}

.address-box {
    background: var(--dark-bg);
    padding: 1rem;
    border-radius: 8px;
    border: 1px solid var(--border-color);
}

.address-box p {
    margin: 0.25rem 0;
}

.items-table {
    width: 100%;
    border-collapse: collapse;
}

.items-table th,
.items-table td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid var(--border-color);
}

.items-table th {
    background: var(--dark-bg);
    font-weight: 600;
    color: var(--text-secondary);
    font-size: 0.9rem;
    text-transform: uppercase;
}

.product-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.product-thumb {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 6px;
}

.product-info div {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.product-info small {
    color: var(--text-secondary);
    font-size: 0.85rem;
}
</style>
