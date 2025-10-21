<?php
session_start();

// Include required files
require_once 'config/Database.php';
require_once 'classes/Category.php';
require_once 'classes/Product.php';
require_once 'classes/Page.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize objects
$category = new Category($db);
$product = new Product($db);
$page = new Page("Categories - ElectroHub", "Browse all product categories", "categories, electronics, accessories");

// Get all categories
$categories = $category->getAllCategories();

// Add product counts to categories
foreach ($categories as &$cat) {
    $cat['count'] = $product->getProductCountByCategory($cat['name']);
}

// Category icons mapping
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

// Render header
$page->renderHeader();
?>

<div class="categories-page">
    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <h1>Browse Categories</h1>
            <p>Explore our wide range of electronic accessories by category</p>
        </div>

        <!-- Categories Grid -->
        <div class="categories-grid">
            <?php if (!empty($categories)): ?>
                <?php foreach ($categories as $cat): ?>
                    <a href="category.php?name=<?php echo urlencode($cat['name']); ?>" class="category-card">
                        <div class="category-icon">
                            <i class="fas <?php echo $categoryIcons[$cat['name']] ?? 'fa-tag'; ?>"></i>
                        </div>
                        <h3><?php echo htmlspecialchars($cat['name']); ?></h3>
                        <p class="product-count"><?php echo $cat['count']; ?> Products</p>
                        <?php if (!empty($cat['description'])): ?>
                            <p class="category-description"><?php echo htmlspecialchars($cat['description']); ?></p>
                        <?php endif; ?>
                        <span class="browse-link">
                            Browse <i class="fas fa-arrow-right"></i>
                        </span>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-categories">
                    <div class="no-categories-icon">
                        <i class="fas fa-tags"></i>
                    </div>
                    <h3>No Categories Found</h3>
                    <p>Categories will appear here once they are added.</p>
                    <a href="products.php" class="btn btn-primary">View All Products</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.categories-page {
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

.categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 2rem;
}

.category-card {
    background: var(--dark-card);
    border-radius: 16px;
    border: 1px solid var(--border-color);
    padding: 2.5rem;
    text-align: center;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-decoration: none;
    min-height: 280px;
}

.category-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: var(--gradient-1);
    transform: scaleX(0);
    transition: transform 0.3s ease;
}

.category-card:hover::before {
    transform: scaleX(1);
}

.category-card:hover {
    transform: translateY(-8px);
    border-color: var(--primary-color);
    box-shadow: 0 20px 40px rgba(99, 102, 241, 0.2);
}

.category-icon {
    width: 100px;
    height: 100px;
    background: var(--gradient-1);
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1.5rem;
    font-size: 2.5rem;
    color: white;
    transition: transform 0.3s ease;
}

.category-card:hover .category-icon {
    transform: scale(1.1) rotate(5deg);
}

.category-card h3 {
    font-size: 1.5rem;
    margin: 0 0 0.5rem 0;
    color: var(--text-primary);
}

.product-count {
    color: var(--primary-color);
    font-weight: 600;
    margin: 0 0 1rem 0;
    font-size: 1.1rem;
}

.category-description {
    color: var(--text-secondary);
    margin: 0 0 1.5rem 0;
    font-size: 0.95rem;
    line-height: 1.5;
    flex: 1;
}

.browse-link {
    color: var(--primary-color);
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s;
    margin-top: auto;
}

.category-card:hover .browse-link {
    gap: 0.75rem;
}

.no-categories {
    grid-column: 1 / -1;
    text-align: center;
    padding: 4rem 2rem;
    background: var(--dark-card);
    border-radius: 16px;
    border: 1px solid var(--border-color);
}

.no-categories-icon {
    font-size: 4rem;
    color: var(--text-secondary);
    margin-bottom: 1.5rem;
}

.no-categories h3 {
    margin: 0 0 1rem 0;
    color: var(--text-primary);
    font-size: 1.5rem;
}

.no-categories p {
    margin: 0 0 2rem 0;
    color: var(--text-secondary);
    font-size: 1.1rem;
}

@media (max-width: 768px) {
    .categories-grid {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 1.5rem;
    }
    
    .category-card {
        padding: 2rem;
        min-height: 250px;
    }
    
    .category-icon {
        width: 80px;
        height: 80px;
        font-size: 2rem;
    }
}
</style>

<?php
$page->renderFooter();
?>
