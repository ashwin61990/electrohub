<?php
session_start();

// Include required files
require_once 'config/Database.php';
require_once 'classes/Product.php';
require_once 'classes/Category.php';
require_once 'classes/Page.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize objects
$product = new Product($db);
$category = new Category($db);
$page = new Page("ElectroHub - Premium Electronic Accessories", "Shop the latest electronic accessories including headphones, chargers, cables, and more.", "electronics, accessories, headphones, chargers, cables");

// Render header
$page->renderHeader();

// Get real products from database
$featuredProducts = $product->getFeaturedProducts(6);
$allProducts = $product->getAllProducts(12);
$categories = $category->getAllCategories();

// Add icons and product counts to categories
$categoryIcons = [
    'Audio' => 'fa-headphones',
    'Accessories' => 'fa-laptop',
    'Cables' => 'fa-plug',
    'Chargers' => 'fa-charging-station',
    'Mobile Accessories' => 'fa-mobile-alt',
    'Computer Accessories' => 'fa-desktop',
    'Gaming' => 'fa-gamepad',
    'Storage' => 'fa-hdd'
];

// If no categories exist, create some default ones for display
if (empty($categories)) {
    $categories = [
        ['name' => 'Audio', 'icon' => 'fa-headphones', 'count' => 0],
        ['name' => 'Accessories', 'icon' => 'fa-laptop', 'count' => 0],
        ['name' => 'Cables', 'icon' => 'fa-plug', 'count' => 0],
        ['name' => 'Chargers', 'icon' => 'fa-charging-station', 'count' => 0]
    ];
} else {
    // Get product counts for each category
    foreach ($categories as &$cat) {
        $cat['icon'] = $categoryIcons[$cat['name']] ?? 'fa-tag';
        $cat['count'] = $product->getProductCountByCategory($cat['name']);
    }
}
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <div class="hero-content">
            <div class="hero-text">
                <h1 class="hero-title">Premium Electronic <span class="gradient-text">Accessories</span></h1>
                <p class="hero-subtitle">Discover cutting-edge technology and accessories that enhance your digital lifestyle</p>
                <div class="hero-buttons">
                    <a href="products.php" class="btn btn-primary">Shop Now</a>
                    <a href="categories.php" class="btn btn-secondary">Browse Categories</a>
                </div>
                <div class="hero-features">
                    <div class="feature-item">
                        <i class="fas fa-shipping-fast"></i>
                        <span>Free Shipping</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-shield-alt"></i>
                        <span>Secure Payment</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-undo"></i>
                        <span>Easy Returns</span>
                    </div>
                </div>
            </div>
            <div class="hero-image">
                <div class="floating-card card-1">
                    <i class="fas fa-headphones"></i>
                    <span>Premium Audio</span>
                </div>
                <div class="floating-card card-2">
                    <i class="fas fa-bolt"></i>
                    <span>Fast Charging</span>
                </div>
                <div class="floating-card card-3">
                    <i class="fas fa-wifi"></i>
                    <span>Wireless Tech</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="categories-section">
    <div class="container">
        <div class="section-header">
            <h2>Shop by Category</h2>
            <p>Explore our wide range of electronic accessories</p>
        </div>
        <div class="categories-grid">
            <?php foreach ($categories as $cat): ?>
            <div class="category-card">
                <div class="category-icon">
                    <i class="fas <?php echo $cat['icon']; ?>"></i>
                </div>
                <h3><?php echo htmlspecialchars($cat['name']); ?></h3>
                <p><?php echo $cat['count']; ?> Products</p>
                <a href="category.php?name=<?php echo urlencode($cat['name']); ?>" class="category-link">
                    Browse <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Products Section -->
<section class="products-section">
    <div class="container">
        <div class="section-header">
            <h2>Featured Products</h2>
            <p>Check out our best-selling electronic accessories</p>
        </div>
        <div class="products-grid">
            <?php if (!empty($featuredProducts)): ?>
                <?php foreach ($featuredProducts as $prod): ?>
            <div class="product-card">
                <?php if ($prod['featured']): ?>
                    <div class="product-badge">Featured</div>
                <?php endif; ?>
                <div class="product-image">
                    <?php if (!empty($prod['image'])): ?>
                        <img src="<?php echo htmlspecialchars($prod['image']); ?>" alt="<?php echo htmlspecialchars($prod['name']); ?>">
                    <?php else: ?>
                        <div class="placeholder-image">
                            <i class="fas fa-image"></i>
                        </div>
                    <?php endif; ?>
                    </div>
                    <div class="product-overlay">
                        <button class="overlay-btn"><i class="fas fa-eye"></i> Quick View</button>
                        <button class="overlay-btn"><i class="fas fa-heart"></i> Wishlist</button>
                    </div>
                </div>
                <div class="product-info">
                    <span class="product-category"><?php echo htmlspecialchars($prod['category']); ?></span>
                    <h3 class="product-name"><?php echo htmlspecialchars($prod['name']); ?></h3>
                    <div class="product-rating">
                        <?php 
                        $rating = $prod['rating'] ?? 0;
                        $fullStars = floor($rating);
                        $halfStar = ($rating - $fullStars) >= 0.5;
                        for ($i = 0; $i < $fullStars; $i++): ?>
                            <i class="fas fa-star"></i>
                        <?php endfor; ?>
                        <?php if ($halfStar): ?>
                            <i class="fas fa-star-half-alt"></i>
                        <?php endif; ?>
                        <?php for ($i = $fullStars + ($halfStar ? 1 : 0); $i < 5; $i++): ?>
                            <i class="far fa-star"></i>
                        <?php endfor; ?>
                        <span>(<?php echo number_format($rating, 1); ?>)</span>
                    </div>
                    <div class="product-footer">
                        <span class="product-price">₹<?php echo number_format($prod['price'], 2); ?></span>
                        <button class="btn-add-cart" onclick="addToCart(<?php echo $prod['id']; ?>, '<?php echo htmlspecialchars($prod['name']); ?>', <?php echo $prod['price']; ?>, '<?php echo htmlspecialchars($prod['image']); ?>')">
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </button>
                    </div>
                    <?php if ($prod['stock'] <= 0): ?>
                        <div class="out-of-stock-overlay">Out of Stock</div>
                    <?php endif; ?>
                </div>
            </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-products">
                    <div class="no-products-icon">
                        <i class="fas fa-box-open"></i>
                    </div>
                    <h3>No Featured Products Yet</h3>
                    <p>Products will appear here once they are added through the admin panel.</p>
                </div>
            <?php endif; ?>
        </div>
        <div class="section-footer">
            <a href="products.php" class="btn btn-outline">View All Products</a>
        </div>
    </div>
</section>

<!-- Promo Banner -->
<section class="promo-banner">
    <div class="container">
        <div class="promo-content">
            <div class="promo-text">
                <span class="promo-label">Limited Time Offer</span>
                <h2>Get 25% Off on All Wireless Products</h2>
                <p>Upgrade your tech with our premium wireless accessories. Offer valid until end of month.</p>
                <a href="products.php?category=wireless" class="btn btn-primary">Shop Wireless</a>
            </div>
            <div class="promo-image">
                <div class="promo-circle"></div>
                <i class="fas fa-broadcast-tower"></i>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features-section">
    <div class="container">
        <div class="features-grid">
            <div class="feature-box">
                <div class="feature-icon">
                    <i class="fas fa-truck"></i>
                </div>
                <h3>Free Shipping</h3>
                <p>Free delivery on orders over $50</p>
            </div>
            <div class="feature-box">
                <div class="feature-icon">
                    <i class="fas fa-lock"></i>
                </div>
                <h3>Secure Payment</h3>
                <p>100% secure payment processing</p>
            </div>
            <div class="feature-box">
                <div class="feature-icon">
                    <i class="fas fa-headset"></i>
                </div>
                <h3>24/7 Support</h3>
                <p>Dedicated customer support team</p>
            </div>
            <div class="feature-box">
                <div class="feature-icon">
                    <i class="fas fa-award"></i>
                </div>
                <h3>Quality Guarantee</h3>
                <p>30-day money-back guarantee</p>
            </div>
        </div>
    </div>
</section>

<!-- Newsletter Section -->
<section class="newsletter-section">
    <div class="container">
        <div class="newsletter-content">
            <div class="newsletter-text">
                <h2>Subscribe to Our Newsletter</h2>
                <p>Get the latest updates on new products and exclusive offers</p>
            </div>
            <form class="newsletter-form">
                <input type="email" placeholder="Enter your email address" required>
                <button type="submit" class="btn btn-primary">Subscribe</button>
            </form>
        </div>
    </div>
</section>

<style>
/* Fix category grid alignment */
.categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
    margin-top: 3rem;
}

.category-card {
    background: var(--dark-card);
    border-radius: 16px;
    border: 1px solid var(--border-color);
    padding: 2rem;
    text-align: center;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    min-height: 200px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

.category-card:hover {
    transform: translateY(-8px);
    border-color: var(--primary-color);
    box-shadow: 0 20px 40px rgba(99, 102, 241, 0.2);
}

.category-icon {
    width: 80px;
    height: 80px;
    background: var(--gradient-1);
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem auto;
    font-size: 2rem;
    color: white;
}

.category-card h3 {
    font-size: 1.5rem;
    margin: 0 0 0.5rem 0;
    color: var(--text-primary);
}

.category-card p {
    color: var(--text-secondary);
    margin: 0 0 1.5rem 0;
    font-size: 1rem;
}

.category-link {
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s;
}

.category-link:hover {
    gap: 0.75rem;
}

/* Fix products grid alignment */
.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 2rem;
    margin-top: 3rem;
}

.product-card {
    background: var(--dark-card);
    border-radius: 16px;
    border: 1px solid var(--border-color);
    overflow: hidden;
    transition: all 0.3s ease;
    position: relative;
    display: flex;
    flex-direction: column;
    height: 100%;
}

.product-card:hover {
    transform: translateY(-8px);
    border-color: var(--primary-color);
    box-shadow: 0 20px 40px rgba(99, 102, 241, 0.15);
}

.product-image {
    position: relative;
    height: 200px;
    overflow: hidden;
    background: var(--dark-bg);
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.product-card:hover .product-image img {
    transform: scale(1.05);
}

.placeholder-image {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--border-color);
    color: var(--text-secondary);
    font-size: 3rem;
}

.product-badge {
    position: absolute;
    top: 1rem;
    left: 1rem;
    background: var(--primary-color);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    z-index: 2;
}

.product-info {
    padding: 1.5rem;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.product-category {
    color: var(--text-secondary);
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.5rem;
}

.product-name {
    font-size: 1.2rem;
    margin: 0 0 1rem 0;
    color: var(--text-primary);
    line-height: 1.4;
}

.product-rating {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    margin-bottom: 1rem;
    color: var(--warning-color);
}

.product-rating span {
    color: var(--text-secondary);
    font-size: 0.9rem;
    margin-left: 0.5rem;
}

.product-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: auto;
}

.product-price {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--primary-color);
}

.btn-add-cart {
    background: var(--primary-color);
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-add-cart:hover {
    background: #4f46e5;
    transform: translateY(-2px);
}

.out-of-stock-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 1.1rem;
    z-index: 3;
}

.no-products {
    grid-column: 1 / -1;
    text-align: center;
    padding: 4rem 2rem;
    background: var(--dark-card);
    border-radius: 16px;
    border: 1px solid var(--border-color);
}

.no-products-icon {
    font-size: 4rem;
    color: var(--text-secondary);
    margin-bottom: 1.5rem;
}

.no-products h3 {
    margin: 0 0 1rem 0;
    color: var(--text-primary);
    font-size: 1.5rem;
}

.no-products p {
    margin: 0;
    color: var(--text-secondary);
    font-size: 1.1rem;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .categories-grid {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
    }
    
    .products-grid {
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
    }
    
    .category-card {
        padding: 1.5rem;
        min-height: 180px;
    }
    
    .category-icon {
        width: 60px;
        height: 60px;
        font-size: 1.5rem;
    }
}
</style>

<?php
// Render footer
$page->renderFooter();
?>
