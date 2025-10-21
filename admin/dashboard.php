<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin'])) {
    header("Location: ../login.php");
    exit();
}

require_once '../config/Database.php';
require_once '../classes/Admin.php';

$database = new Database();
$db = $database->getConnection();
$admin = new Admin($db);

// Verify admin status
if (!$admin->isAdmin($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Get dashboard statistics
$stats = $admin->getDashboardStats();
$recentUsers = $admin->getRecentUsers(5);
$recentProducts = $admin->getAllProducts(5);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - ElectroHub</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <i class="fas fa-bolt"></i>
                    <span>ElectroHub Admin</span>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item active">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="products.php" class="nav-item">
                    <i class="fas fa-box"></i>
                    <span>Products</span>
                </a>
                <a href="categories.php" class="nav-item">
                    <i class="fas fa-tags"></i>
                    <span>Categories</span>
                </a>
                <a href="users.php" class="nav-item">
                    <i class="fas fa-users"></i>
                    <span>Users</span>
                </a>
                <a href="orders.php" class="nav-item">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Orders</span>
                </a>
                <a href="analytics.php" class="nav-item">
                    <i class="fas fa-chart-bar"></i>
                    <span>Analytics</span>
                </a>
                <a href="settings.php" class="nav-item">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
            </nav>
            
            <div class="sidebar-footer">
                <a href="../index.php" class="nav-item">
                    <i class="fas fa-home"></i>
                    <span>View Site</span>
                </a>
                <a href="../logout.php" class="nav-item logout">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <!-- Top Bar -->
            <header class="admin-header">
                <div class="header-left">
                    <button class="sidebar-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1>Dashboard</h1>
                </div>
                <div class="header-right">
                    <div class="admin-user">
                        <span>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                        <i class="fas fa-user-shield"></i>
                    </div>
                </div>
            </header>

            <!-- Dashboard Content -->
            <div class="dashboard-content">
                <!-- Statistics Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon users">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo number_format($stats['total_users']); ?></h3>
                            <p>Total Users</p>
                            <small>+<?php echo $stats['recent_users']; ?> this month</small>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon products">
                            <i class="fas fa-box"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo number_format($stats['total_products']); ?></h3>
                            <p>Total Products</p>
                            <small><?php echo $stats['active_products']; ?> active</small>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon categories">
                            <i class="fas fa-tags"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo number_format($stats['total_categories']); ?></h3>
                            <p>Categories</p>
                            <small>Product categories</small>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon stock">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo number_format($stats['out_of_stock']); ?></h3>
                            <p>Out of Stock</p>
                            <small>Need attention</small>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="quick-actions">
                    <h2>Quick Actions</h2>
                    <div class="actions-grid">
                        <a href="products.php?action=add" class="action-card">
                            <i class="fas fa-plus"></i>
                            <span>Add Product</span>
                        </a>
                        <a href="categories.php?action=add" class="action-card">
                            <i class="fas fa-tag"></i>
                            <span>Add Category</span>
                        </a>
                        <a href="users.php" class="action-card">
                            <i class="fas fa-user-plus"></i>
                            <span>Manage Users</span>
                        </a>
                        <a href="orders.php" class="action-card">
                            <i class="fas fa-shopping-bag"></i>
                            <span>View Orders</span>
                        </a>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="dashboard-grid">
                    <!-- Recent Users -->
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3>Recent Users</h3>
                            <a href="users.php" class="view-all">View All</a>
                        </div>
                        <div class="card-content">
                            <?php if (!empty($recentUsers)): ?>
                                <div class="user-list">
                                    <?php foreach ($recentUsers as $user): ?>
                                        <div class="user-item">
                                            <div class="user-avatar">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <div class="user-info">
                                                <strong><?php echo htmlspecialchars($user['full_name']); ?></strong>
                                                <small><?php echo htmlspecialchars($user['email']); ?></small>
                                                <span class="user-date"><?php echo date('M j, Y', strtotime($user['created_at'])); ?></span>
                                            </div>
                                            <div class="user-status">
                                                <?php if ($user['is_active']): ?>
                                                    <span class="status active">Active</span>
                                                <?php else: ?>
                                                    <span class="status inactive">Inactive</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="empty-state">No users found</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Recent Products -->
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3>Recent Products</h3>
                            <a href="products.php" class="view-all">View All</a>
                        </div>
                        <div class="card-content">
                            <?php if (!empty($recentProducts)): ?>
                                <div class="product-list">
                                    <?php foreach ($recentProducts as $product): ?>
                                        <div class="product-item">
                                            <div class="product-image">
                                                <i class="fas fa-image"></i>
                                            </div>
                                            <div class="product-info">
                                                <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                                                <small>SKU: <?php echo htmlspecialchars($product['sku'] ?? 'N/A'); ?></small>
                                                <span class="product-price">₹<?php echo number_format($product['price'], 2); ?></span>
                                            </div>
                                            <div class="product-stock">
                                                <?php if ($product['stock'] > 0): ?>
                                                    <span class="stock in-stock"><?php echo $product['stock']; ?> in stock</span>
                                                <?php else: ?>
                                                    <span class="stock out-of-stock">Out of stock</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="empty-state">No products found</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- System Info -->
                <div class="system-info">
                    <h2>System Information</h2>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">PHP Version:</span>
                            <span class="info-value"><?php echo phpversion(); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Server Time:</span>
                            <span class="info-value"><?php echo date('Y-m-d H:i:s'); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Database:</span>
                            <span class="info-value">MySQL Connected</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Admin Session:</span>
                            <span class="info-value">Active</span>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="../assets/js/admin.js"></script>
</body>
</html>
