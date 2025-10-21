<?php
session_start();

require_once 'config/Database.php';
require_once 'classes/Cart.php';
require_once 'classes/Product.php';
require_once 'classes/Page.php';

$database = new Database();
$db = $database->getConnection();
$cart = new Cart($db);
$product = new Product($db);
$page = new Page("Shopping Cart - ElectroHub", "Review your selected items", "cart, shopping, checkout");

$cartItems = [];
$cartTotal = 0;
$cartCount = 0;

if (isset($_SESSION['user_id'])) {
    // Logged in user
    $userId = $_SESSION['user_id'];
    $cartItems = $cart->getCartItems($userId);
    $cartTotal = $cart->getCartTotal($userId);
    $cartCount = $cart->getCartCount($userId);
} else {
    // Guest user
    $guestCart = Cart::getGuestCart();
    if (!empty($guestCart)) {
        foreach ($guestCart as $productId => $quantity) {
            $productData = $product->getById($productId);
            if ($productData && $productData['status'] == 'active') {
                $productData['quantity'] = $quantity;
                $productData['cart_id'] = 'guest_' . $productId;
                $cartItems[] = $productData;
                $cartTotal += $productData['price'] * $quantity;
                $cartCount += $quantity;
            }
        }
    }
}

$page->renderHeader();
?>

<div class="cart-container">
    <div class="container">
        <div class="cart-header">
            <h1>Shopping Cart</h1>
            <p>Review your items and proceed to checkout</p>
        </div>

        <?php if (empty($cartItems)): ?>
            <div class="empty-cart">
                <div class="empty-cart-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h2>Your cart is empty</h2>
                <p>Looks like you haven't added any items to your cart yet.</p>
                <a href="index.php" class="btn btn-primary">Continue Shopping</a>
            </div>
        <?php else: ?>
            <div class="cart-content">
                <div class="cart-items">
                    <div class="cart-items-header">
                        <h3>Items in your cart (<?php echo $cartCount; ?>)</h3>
                    </div>
                    
                    <?php foreach ($cartItems as $item): ?>
                        <div class="cart-item" data-cart-id="<?php echo $item['cart_id']; ?>" data-product-id="<?php echo $item['id']; ?>">
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
                                <h4 class="item-name"><?php echo htmlspecialchars($item['name']); ?></h4>
                                <p class="item-category"><?php echo htmlspecialchars($item['category']); ?></p>
                                <p class="item-price">₹<?php echo number_format($item['price'], 2); ?></p>
                                
                                <?php if ($item['stock'] <= 0): ?>
                                    <p class="stock-warning">Out of Stock</p>
                                <?php elseif ($item['stock'] < 5): ?>
                                    <p class="stock-low">Only <?php echo $item['stock']; ?> left in stock</p>
                                <?php endif; ?>
                            </div>
                            
                            <div class="item-quantity">
                                <label>Quantity:</label>
                                <div class="quantity-controls">
                                    <button class="qty-btn" onclick="updateQuantity('<?php echo $item['cart_id']; ?>', <?php echo $item['id']; ?>, <?php echo $item['quantity'] - 1; ?>)">-</button>
                                    <input type="number" class="qty-input" value="<?php echo $item['quantity']; ?>" 
                                           min="1" max="<?php echo $item['stock']; ?>" 
                                           onchange="updateQuantity('<?php echo $item['cart_id']; ?>', <?php echo $item['id']; ?>, this.value)">
                                    <button class="qty-btn" onclick="updateQuantity('<?php echo $item['cart_id']; ?>', <?php echo $item['id']; ?>, <?php echo $item['quantity'] + 1; ?>)">+</button>
                                </div>
                            </div>
                            
                            <div class="item-total">
                                <p class="item-subtotal">₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></p>
                                <button class="remove-btn" onclick="removeFromCart('<?php echo $item['cart_id']; ?>', <?php echo $item['id']; ?>)">
                                    <i class="fas fa-trash"></i> Remove
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="cart-summary">
                    <div class="summary-card">
                        <h3>Order Summary</h3>
                        
                        <div class="summary-row">
                            <span>Subtotal (<?php echo $cartCount; ?> items)</span>
                            <span>₹<?php echo number_format($cartTotal, 2); ?></span>
                        </div>
                        
                        <div class="summary-row">
                            <span>Shipping</span>
                            <span><?php echo $cartTotal >= 500 ? 'Free' : '₹50.00'; ?></span>
                        </div>
                        
                        <?php if ($cartTotal < 500): ?>
                            <div class="summary-note">
                                <small>Add ₹<?php echo number_format(500 - $cartTotal, 2); ?> more for free shipping</small>
                            </div>
                        <?php endif; ?>
                        
                        <div class="summary-divider"></div>
                        
                        <div class="summary-row total">
                            <span>Total</span>
                            <span>₹<?php echo number_format($cartTotal + ($cartTotal >= 500 ? 0 : 50), 2); ?></span>
                        </div>
                        
                        <div class="checkout-actions">
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <a href="checkout.php" class="btn btn-primary btn-block">Proceed to Checkout</a>
                            <?php else: ?>
                                <a href="login.php?redirect=checkout.php" class="btn btn-primary btn-block">Login to Checkout</a>
                                <p class="login-note">Or <a href="register.php">create an account</a> to continue</p>
                            <?php endif; ?>
                            <a href="index.php" class="btn btn-outline btn-block">Continue Shopping</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.cart-container {
    min-height: calc(100vh - 80px);
    padding: 2rem 0;
    background: var(--dark-bg);
}

.cart-header {
    text-align: center;
    margin-bottom: 3rem;
}

.cart-header h1 {
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
    background: var(--gradient-1);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.cart-header p {
    color: var(--text-secondary);
    font-size: 1.1rem;
}

.empty-cart {
    text-align: center;
    padding: 4rem 2rem;
    background: var(--dark-card);
    border-radius: 16px;
    border: 1px solid var(--border-color);
    max-width: 500px;
    margin: 0 auto;
}

.empty-cart-icon {
    font-size: 4rem;
    color: var(--text-secondary);
    margin-bottom: 1.5rem;
}

.empty-cart h2 {
    margin-bottom: 1rem;
    color: var(--text-primary);
}

.empty-cart p {
    color: var(--text-secondary);
    margin-bottom: 2rem;
}

.cart-content {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 2rem;
}

.cart-items {
    background: var(--dark-card);
    border-radius: 16px;
    border: 1px solid var(--border-color);
    overflow: hidden;
}

.cart-items-header {
    padding: 1.5rem;
    border-bottom: 1px solid var(--border-color);
    background: rgba(15, 23, 42, 0.5);
}

.cart-items-header h3 {
    margin: 0;
    color: var(--text-primary);
}

.cart-item {
    display: grid;
    grid-template-columns: 100px 1fr auto auto;
    gap: 1.5rem;
    padding: 1.5rem;
    border-bottom: 1px solid var(--border-color);
    align-items: center;
}

.cart-item:last-child {
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

.item-details h4 {
    margin: 0 0 0.5rem 0;
    color: var(--text-primary);
    font-size: 1.1rem;
}

.item-category {
    color: var(--text-secondary);
    font-size: 0.9rem;
    margin: 0 0 0.5rem 0;
}

.item-price {
    color: var(--primary-color);
    font-weight: 600;
    font-size: 1.1rem;
    margin: 0;
}

.stock-warning {
    color: var(--danger-color);
    font-size: 0.85rem;
    margin: 0.25rem 0 0 0;
}

.stock-low {
    color: var(--warning-color);
    font-size: 0.85rem;
    margin: 0.25rem 0 0 0;
}

.item-quantity label {
    display: block;
    margin-bottom: 0.5rem;
    color: var(--text-secondary);
    font-size: 0.9rem;
}

.quantity-controls {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.qty-btn {
    width: 32px;
    height: 32px;
    border: 1px solid var(--border-color);
    background: var(--dark-card);
    color: var(--text-primary);
    border-radius: 6px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.qty-btn:hover {
    background: var(--primary-color);
    border-color: var(--primary-color);
    color: white;
}

.qty-input {
    width: 60px;
    height: 32px;
    text-align: center;
    border: 1px solid var(--border-color);
    background: var(--dark-bg);
    color: var(--text-primary);
    border-radius: 6px;
}

.item-total {
    text-align: right;
}

.item-subtotal {
    font-size: 1.2rem;
    font-weight: 600;
    color: var(--text-primary);
    margin: 0 0 1rem 0;
}

.remove-btn {
    background: none;
    border: none;
    color: var(--danger-color);
    cursor: pointer;
    font-size: 0.9rem;
    padding: 0.5rem;
    border-radius: 6px;
    transition: all 0.2s;
}

.remove-btn:hover {
    background: rgba(239, 68, 68, 0.1);
}

.cart-summary {
    position: sticky;
    top: 2rem;
    height: fit-content;
}

.summary-card {
    background: var(--dark-card);
    border-radius: 16px;
    border: 1px solid var(--border-color);
    padding: 1.5rem;
}

.summary-card h3 {
    margin: 0 0 1.5rem 0;
    color: var(--text-primary);
}

.summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    color: var(--text-secondary);
}

.summary-row.total {
    font-size: 1.2rem;
    font-weight: 600;
    color: var(--text-primary);
    padding-top: 1rem;
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

.checkout-actions {
    margin-top: 1.5rem;
}

.checkout-actions .btn {
    margin-bottom: 0.75rem;
}

.login-note {
    text-align: center;
    color: var(--text-secondary);
    font-size: 0.9rem;
    margin-top: 1rem;
}

.login-note a {
    color: var(--primary-color);
    text-decoration: none;
}

.login-note a:hover {
    text-decoration: underline;
}

@media (max-width: 768px) {
    .cart-content {
        grid-template-columns: 1fr;
    }
    
    .cart-item {
        grid-template-columns: 80px 1fr;
        gap: 1rem;
    }
    
    .item-quantity,
    .item-total {
        grid-column: 1 / -1;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid var(--border-color);
    }
    
    .item-total {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
}
</style>

<script>
function addToCart(productId, name, price, image) {
    fetch('cart_handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=add&product_id=${productId}&quantity=1`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            updateCartCount(data.cart_count);
        } else {
            showToast(data.error || 'Failed to add to cart', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred', 'error');
    });
}

function removeFromCart(cartId, productId) {
    if (!confirm('Are you sure you want to remove this item from your cart?')) {
        return;
    }
    
    fetch('cart_handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=remove&cart_id=${cartId}&product_id=${productId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            location.reload(); // Refresh to update totals
        } else {
            showToast(data.error || 'Failed to remove from cart', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred', 'error');
    });
}

function updateQuantity(cartId, productId, quantity) {
    if (quantity < 1) {
        removeFromCart(cartId, productId);
        return;
    }
    
    fetch('cart_handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=update&cart_id=${cartId}&product_id=${productId}&quantity=${quantity}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload(); // Refresh to update totals
        } else {
            showToast(data.error || 'Failed to update cart', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred', 'error');
    });
}

function updateCartCount(count) {
    const cartBadge = document.querySelector('.nav-icons .badge');
    if (cartBadge) {
        cartBadge.textContent = count;
    }
}

function showToast(message, type = 'info') {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'exclamation-triangle' : 'info'}-circle"></i>
        <span>${message}</span>
    `;
    
    // Add to page
    document.body.appendChild(toast);
    
    // Show toast
    setTimeout(() => toast.classList.add('show'), 100);
    
    // Hide and remove toast
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Load cart count on page load
document.addEventListener('DOMContentLoaded', function() {
    fetch('cart_handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=get_count'
    })
    .then(response => response.json())
    .then(data => {
        updateCartCount(data.cart_count);
    })
    .catch(error => console.error('Error loading cart count:', error));
});
</script>

<?php
$page->renderFooter();
?>
