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
$page = new Page("Products - ElectroHub", "Browse our complete collection of electronic accessories", "products, electronics, accessories");

// Get filter parameters
$selectedCategory = $_GET['category'] ?? '';
$searchQuery = $_GET['search'] ?? '';
$sortBy = $_GET['sort'] ?? 'newest';

// Get all products
if ($selectedCategory) {
    $products = $product->getProductsByCategory($selectedCategory);
} elseif ($searchQuery) {
    $products = $product->searchProducts($searchQuery);
} else {
    $products = $product->getAllProducts(100);
}

// Sort products
if ($sortBy == 'price_low') {
    usort($products, function($a, $b) { return $a['price'] - $b['price']; });
} elseif ($sortBy == 'price_high') {
    usort($products, function($a, $b) { return $b['price'] - $a['price']; });
} elseif ($sortBy == 'name') {
    usort($products, function($a, $b) { return strcmp($a['name'], $b['name']); });
}

// Get all categories for filter
$categories = $category->getAllCategories();

// Render header
$page->renderHeader();
?>

<div class="products-page">
    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <h1>All Products</h1>
            <p>Discover our complete collection of premium electronic accessories</p>
        </div>

        <!-- Filters and Sort -->
        <div class="products-controls">
            <div class="filters">
                <div class="filter-group">
                    <label>Category:</label>
                    <select id="categoryFilter" class="filter-select" onchange="filterProducts()">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo urlencode($cat['name']); ?>" 
                                    <?php echo $selectedCategory == $cat['name'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Sort By:</label>
                    <select id="sortFilter" class="filter-select" onchange="filterProducts()">
                        <option value="newest" <?php echo $sortBy == 'newest' ? 'selected' : ''; ?>>Newest First</option>
                        <option value="price_low" <?php echo $sortBy == 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                        <option value="price_high" <?php echo $sortBy == 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                        <option value="name" <?php echo $sortBy == 'name' ? 'selected' : ''; ?>>Name: A to Z</option>
                    </select>
                </div>

                <div class="filter-group search-group">
                    <input type="text" id="searchInput" class="search-input" 
                           placeholder="Search products..." 
                           value="<?php echo htmlspecialchars($searchQuery); ?>">
                    <button class="btn btn-primary" onclick="searchProducts()">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>
            </div>

            <div class="results-count">
                <span><?php echo count($products); ?> Products Found</span>
            </div>
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
                    <p>Try adjusting your filters or search query.</p>
                    <a href="products.php" class="btn btn-primary">View All Products</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.products-page {
    min-height: calc(100vh - 80px);
    padding: 2rem 0;
    background: var(--dark-bg);
}

.page-header {
    text-align: center;
    margin-bottom: 3rem;
}

.page-header h1 {
    font-size: 2.5rem;
    margin-bottom: 1rem;
    background: var(--gradient-1);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.page-header p {
    color: var(--text-secondary);
    font-size: 1.1rem;
}

.products-controls {
    background: var(--dark-card);
    border-radius: 16px;
    border: 1px solid var(--border-color);
    padding: 2rem;
    margin-bottom: 3rem;
}

.filters {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.filter-group label {
    color: var(--text-primary);
    font-weight: 600;
    font-size: 0.9rem;
}

.filter-select,
.search-input {
    padding: 0.75rem;
    background: var(--dark-bg);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    color: var(--text-primary);
    font-size: 1rem;
    transition: border-color 0.2s;
}

.filter-select:focus,
.search-input:focus {
    outline: none;
    border-color: var(--primary-color);
}

.search-group {
    display: flex;
    flex-direction: row;
    align-items: flex-end;
    gap: 0.5rem;
}

.search-group .search-input {
    flex: 1;
}

.search-group .btn {
    padding: 0.75rem 1.5rem;
    white-space: nowrap;
}

.results-count {
    text-align: center;
    padding-top: 1rem;
    border-top: 1px solid var(--border-color);
}

.results-count span {
    color: var(--text-secondary);
    font-size: 0.95rem;
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

@media (max-width: 768px) {
    .filters {
        grid-template-columns: 1fr;
    }
    
    .products-grid {
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
    }
    
    .search-group {
        flex-direction: column;
        align-items: stretch;
    }
}
</style>

<script>
function filterProducts() {
    const category = document.getElementById('categoryFilter').value;
    const sort = document.getElementById('sortFilter').value;
    const search = document.getElementById('searchInput').value;
    
    let url = 'products.php?';
    if (category) url += 'category=' + category + '&';
    if (sort) url += 'sort=' + sort + '&';
    if (search) url += 'search=' + encodeURIComponent(search);
    
    window.location.href = url;
}

function searchProducts() {
    filterProducts();
}

// Allow Enter key to search
document.getElementById('searchInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        searchProducts();
    }
});
</script>

<?php
$page->renderFooter();
?>
