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

// Get category name from URL
$categoryName = $_GET['name'] ?? '';

if (empty($categoryName)) {
    header("Location: categories.php");
    exit();
}

$page = new Page(htmlspecialchars($categoryName) . " - ElectroHub", "Browse " . htmlspecialchars($categoryName) . " products", "electronics, " . htmlspecialchars($categoryName));

// Get products in this category
$products = $product->getProductsByCategory($categoryName);

// Get product count
$productCount = count($products);

// Render header
$page->renderHeader();
?>

<div class="category-page">
    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="breadcrumb">
                <a href="index.php">Home</a>
                <i class="fas fa-chevron-right"></i>
                <a href="categories.php">Categories</a>
                <i class="fas fa-chevron-right"></i>
                <span><?php echo htmlspecialchars($categoryName); ?></span>
            </div>
            <h1><?php echo htmlspecialchars($categoryName); ?></h1>
            <p><?php echo $productCount; ?> product<?php echo $productCount != 1 ? 's' : ''; ?> found</p>
        </div>

        <!-- Products Grid -->
        <div class="products-grid">
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $prod): ?>
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
                            <div class="product-overlay">
                                <button class="overlay-btn"><i class="fas fa-eye"></i> Quick View</button>
                                <button class="overlay-btn"><i class="fas fa-heart"></i> Wishlist</button>
                            </div>
                        </div>
                        <div class="product-info">
                            <span class="product-category"><?php echo htmlspecialchars($prod['category']); ?></span>
                            <h3 class="product-name"><?php echo htmlspecialchars($prod['name']); ?></h3>
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
                    <h3>No Products Found</h3>
                    <p>There are no products in this category yet.</p>
                    <div class="no-products-actions">
                        <a href="categories.php" class="btn btn-primary">Browse Other Categories</a>
                        <a href="products.php" class="btn btn-outline">View All Products</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.category-page {
    min-height: calc(100vh - 80px);
    padding: 2rem 0;
    background: var(--dark-bg);
}

.page-header {
    margin-bottom: 3rem;
}

.breadcrumb {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1rem;
    font-size: 0.9rem;
}

.breadcrumb a {
    color: var(--text-secondary);
    text-decoration: none;
    transition: color 0.2s;
}

.breadcrumb a:hover {
    color: var(--primary-color);
}

.breadcrumb i {
    color: var(--text-secondary);
    font-size: 0.7rem;
}

.breadcrumb span {
    color: var(--text-primary);
    font-weight: 600;
}

.page-header h1 {
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
    background: var(--gradient-1);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.page-header p {
    color: var(--text-secondary);
    font-size: 1.1rem;
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 2rem;
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

.product-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.8);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    opacity: 0;
    transition: opacity 0.3s;
}

.product-card:hover .product-overlay {
    opacity: 1;
}

.overlay-btn {
    background: white;
    color: var(--dark-bg);
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.overlay-btn:hover {
    background: var(--primary-color);
    color: white;
    transform: translateY(-2px);
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

.product-footer {
    display: flex;
    flex-direction: column;
    gap: 1rem;
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
    padding: 0.75rem 1rem;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    width: 100%;
    font-size: 0.95rem;
    white-space: nowrap;
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
    margin: 0 0 2rem 0;
    color: var(--text-secondary);
    font-size: 1.1rem;
}

.no-products-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

@media (max-width: 768px) {
    .products-grid {
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
    }
    
    .no-products-actions {
        flex-direction: column;
        align-items: center;
    }
    
    .no-products-actions .btn {
        width: 100%;
        max-width: 300px;
    }
}
</style>

<?php
$page->renderFooter();
?>
