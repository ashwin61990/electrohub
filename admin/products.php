<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
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

$message = '';
$messageType = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Handle image upload
    $imageUrl = $_POST['existing_image'] ?? '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/products/';
        
        // Create upload directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $file = $_FILES['image'];
        $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($fileExt, $allowedTypes) && $file['size'] <= 5 * 1024 * 1024) {
            $newFileName = uniqid('product_', true) . '.' . $fileExt;
            $uploadPath = $uploadDir . $newFileName;
            
            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                $imageUrl = 'uploads/products/' . $newFileName;
            }
        }
    }
    
    if ($action == 'add') {
        $productData = [
            'sku' => $_POST['sku'] ?? '',
            'name' => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? '',
            'price' => $_POST['price'] ?? 0,
            'image' => $imageUrl,
            'category' => $_POST['category'] ?? '',
            'brand' => $_POST['brand'] ?? '',
            'stock' => $_POST['stock'] ?? 0,
            'weight' => $_POST['weight'] ?? null,
            'dimensions' => $_POST['dimensions'] ?? '',
            'warranty' => $_POST['warranty'] ?? '',
            'featured' => isset($_POST['featured']) ? 1 : 0,
            'status' => $_POST['status'] ?? 'active',
            'meta_title' => $_POST['meta_title'] ?? '',
            'meta_description' => $_POST['meta_description'] ?? ''
        ];
        
        if ($admin->addProduct($productData)) {
            $message = 'Product added successfully!';
            $messageType = 'success';
        } else {
            $message = 'Failed to add product. Please try again.';
            $messageType = 'error';
        }
    } elseif ($action == 'edit') {
        $productId = $_POST['product_id'] ?? 0;
        $productData = [
            'sku' => $_POST['sku'] ?? '',
            'name' => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? '',
            'price' => $_POST['price'] ?? 0,
            'image' => $imageUrl,
            'category' => $_POST['category'] ?? '',
            'brand' => $_POST['brand'] ?? '',
            'stock' => $_POST['stock'] ?? 0,
            'weight' => $_POST['weight'] ?? null,
            'dimensions' => $_POST['dimensions'] ?? '',
            'warranty' => $_POST['warranty'] ?? '',
            'featured' => isset($_POST['featured']) ? 1 : 0,
            'status' => $_POST['status'] ?? 'active',
            'meta_title' => $_POST['meta_title'] ?? '',
            'meta_description' => $_POST['meta_description'] ?? ''
        ];
        
        if ($admin->updateProduct($productId, $productData)) {
            $message = 'Product updated successfully!';
            $messageType = 'success';
        } else {
            $message = 'Failed to update product. Please try again.';
            $messageType = 'error';
        }
    } elseif ($action == 'delete') {
        $productId = $_POST['product_id'] ?? 0;
        if ($admin->deleteProduct($productId)) {
            $message = 'Product deleted successfully!';
            $messageType = 'success';
        } else {
            $message = 'Failed to delete product. Please try again.';
            $messageType = 'error';
        }
    }
}

// Get products for listing
$search = $_GET['search'] ?? '';
$page = $_GET['page'] ?? 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$products = $admin->getAllProducts($limit, $offset, $search);
$totalProducts = $admin->getTotalProductsCount($search);
$totalPages = ceil($totalProducts / $limit);

// Get categories for dropdown
$categories = $admin->getCategories();

// Get product for editing if edit action
$editProduct = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $editProduct = $admin->getProduct($_GET['id']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products Management - ElectroHub Admin</title>
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
                <a href="dashboard.php" class="nav-item">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="products.php" class="nav-item active">
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
                    <h1>Products Management</h1>
                </div>
                <div class="header-right">
                    <button class="btn btn-primary" onclick="showAddForm()">
                        <i class="fas fa-plus"></i> Add Product
                    </button>
                </div>
            </header>

            <!-- Content -->
            <div class="dashboard-content">
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <!-- Add/Edit Product Form -->
                <div id="productForm" class="admin-form" style="<?php echo ($editProduct || isset($_GET['action']) && $_GET['action'] == 'add') ? 'display: block;' : 'display: none;'; ?>">
                    <h3><?php echo $editProduct ? 'Edit Product' : 'Add New Product'; ?></h3>
                    <form method="POST" action="" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="<?php echo $editProduct ? 'edit' : 'add'; ?>">
                        <?php if ($editProduct): ?>
                            <input type="hidden" name="product_id" value="<?php echo $editProduct['id']; ?>">
                            <input type="hidden" name="existing_image" value="<?php echo htmlspecialchars($editProduct['image'] ?? ''); ?>">
                        <?php endif; ?>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="sku">SKU</label>
                                <input type="text" id="sku" name="sku" class="form-control" 
                                       value="<?php echo htmlspecialchars($editProduct['sku'] ?? ''); ?>" 
                                       placeholder="Leave empty to auto-generate">
                            </div>
                            <div class="form-group">
                                <label for="name">Product Name *</label>
                                <input type="text" id="name" name="name" class="form-control" 
                                       value="<?php echo htmlspecialchars($editProduct['name'] ?? ''); ?>" 
                                       required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" class="form-control" rows="4"><?php echo htmlspecialchars($editProduct['description'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="price">Price (₹) *</label>
                                <input type="number" id="price" name="price" class="form-control" 
                                       value="<?php echo $editProduct['price'] ?? ''; ?>" 
                                       step="0.01" min="0" required placeholder="Enter price in INR">
                            </div>
                            <div class="form-group">
                                <label for="category">Category *</label>
                                <select id="category" name="category" class="form-control" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo htmlspecialchars($category['name']); ?>" 
                                                <?php echo ($editProduct['category'] ?? '') == $category['name'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="brand">Brand</label>
                                <input type="text" id="brand" name="brand" class="form-control" 
                                       value="<?php echo htmlspecialchars($editProduct['brand'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="stock">Stock Quantity</label>
                                <input type="number" id="stock" name="stock" class="form-control" 
                                       value="<?php echo $editProduct['stock'] ?? '0'; ?>" 
                                       min="0">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="weight">Weight (kg)</label>
                                <input type="number" id="weight" name="weight" class="form-control" 
                                       value="<?php echo $editProduct['weight'] ?? ''; ?>" 
                                       step="0.01" min="0">
                            </div>
                            <div class="form-group">
                                <label for="dimensions">Dimensions</label>
                                <input type="text" id="dimensions" name="dimensions" class="form-control" 
                                       value="<?php echo htmlspecialchars($editProduct['dimensions'] ?? ''); ?>" 
                                       placeholder="L x W x H">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="warranty">Warranty</label>
                                <input type="text" id="warranty" name="warranty" class="form-control" 
                                       value="<?php echo htmlspecialchars($editProduct['warranty'] ?? ''); ?>" 
                                       placeholder="e.g., 1 Year">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select id="status" name="status" class="form-control">
                                    <option value="active" <?php echo ($editProduct['status'] ?? 'active') == 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo ($editProduct['status'] ?? '') == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="image">Product Image</label>
                                <div class="image-upload-container">
                                    <input type="file" id="image" name="image" class="form-control" 
                                           accept="image/*" onchange="previewImage(this)">
                                    <div class="upload-help">
                                        <small>Supported formats: JPG, PNG, GIF, WebP (Max: 5MB)</small>
                                    </div>
                                    <?php if ($editProduct && !empty($editProduct['image'])): ?>
                                        <div class="current-image">
                                            <label>Current Image:</label>
                                            <img src="../<?php echo htmlspecialchars($editProduct['image']); ?>" 
                                                 alt="Current product image" class="current-image-preview">
                                        </div>
                                    <?php endif; ?>
                                    <div id="imagePreview" class="image-preview" style="display: none;">
                                        <label>New Image Preview:</label>
                                        <img id="previewImg" src="" alt="Image preview">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="featured" value="1" 
                                       <?php echo ($editProduct['featured'] ?? 0) ? 'checked' : ''; ?>>
                                Featured Product
                            </label>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="meta_title">Meta Title (SEO)</label>
                                <input type="text" id="meta_title" name="meta_title" class="form-control" 
                                       value="<?php echo htmlspecialchars($editProduct['meta_title'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="meta_description">Meta Description (SEO)</label>
                            <textarea id="meta_description" name="meta_description" class="form-control" rows="3"><?php echo htmlspecialchars($editProduct['meta_description'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?php echo $editProduct ? 'Update Product' : 'Add Product'; ?>
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="hideForm()">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Search and Filter -->
                <div class="search-section">
                    <form method="GET" action="" class="search-form">
                        <input type="text" name="search" class="form-control" 
                               value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="Search products by name, SKU, or category...">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Search
                        </button>
                        <?php if ($search): ?>
                            <a href="products.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        <?php endif; ?>
                    </form>
                </div>

                <!-- Products Table -->
                <div class="admin-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>SKU</th>
                                <th>Product Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($products)): ?>
                                <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td>
                                            <div class="product-image-cell">
                                                <?php if (!empty($product['image'])): ?>
                                                    <img src="../<?php echo htmlspecialchars($product['image']); ?>" 
                                                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                                         class="table-product-image">
                                                <?php else: ?>
                                                    <div class="no-image">
                                                        <i class="fas fa-image"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($product['sku'] ?? 'N/A'); ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                                            <?php if ($product['featured']): ?>
                                                <span class="badge featured">Featured</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($product['category']); ?></td>
                                        <td>₹<?php echo number_format($product['price'], 2); ?></td>
                                        <td>
                                            <?php if ($product['stock'] > 0): ?>
                                                <span class="stock in-stock"><?php echo $product['stock']; ?></span>
                                            <?php else: ?>
                                                <span class="stock out-of-stock">Out of Stock</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="status status-<?php echo $product['status']; ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $product['status'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="?action=edit&id=<?php echo $product['id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button onclick="deleteProduct(<?php echo $product['id']; ?>)" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center">No products found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                               class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Delete Form -->
    <form id="deleteForm" method="POST" action="" style="display: none;">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="product_id" id="deleteProductId">
    </form>

    <script>
        function showAddForm() {
            document.getElementById('productForm').style.display = 'block';
            document.getElementById('productForm').scrollIntoView({ behavior: 'smooth' });
        }

        function hideForm() {
            document.getElementById('productForm').style.display = 'none';
        }

        function deleteProduct(id) {
            if (confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
                document.getElementById('deleteProductId').value = id;
                document.getElementById('deleteForm').submit();
            }
        }

        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            const previewImg = document.getElementById('previewImg');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    preview.style.display = 'block';
                };
                
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.style.display = 'none';
            }
        }

        // Auto-hide alerts
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 300);
                }, 5000);
            });
        });
    </script>

    <style>
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            transition: opacity 0.3s;
        }
        
        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: var(--admin-success);
        }
        
        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: var(--admin-danger);
        }

        .search-section {
            background: var(--admin-card);
            border: 1px solid var(--admin-border);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .search-form {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .search-form input {
            flex: 1;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .btn-sm {
            padding: 0.5rem;
            font-size: 0.75rem;
        }

        .badge {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .badge.featured {
            background: var(--admin-warning);
            color: white;
        }

        .status {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .status-active {
            background: rgba(16, 185, 129, 0.2);
            color: var(--admin-success);
        }

        .status-inactive {
            background: rgba(239, 68, 68, 0.2);
            color: var(--admin-danger);
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 1.5rem;
        }

        .page-link {
            padding: 0.5rem 1rem;
            background: var(--admin-card);
            border: 1px solid var(--admin-border);
            color: var(--admin-text);
            text-decoration: none;
            border-radius: 6px;
            transition: all 0.2s;
        }

        .page-link:hover,
        .page-link.active {
            background: var(--admin-primary);
            border-color: var(--admin-primary);
            color: white;
        }

        .text-center {
            text-align: center;
            padding: 2rem;
            color: var(--admin-text-muted);
        }

        /* Image Upload Styles */
        .image-upload-container {
            border: 2px dashed var(--admin-border);
            border-radius: 8px;
            padding: 1rem;
            text-align: center;
            transition: border-color 0.3s;
        }

        .image-upload-container:hover {
            border-color: var(--admin-primary);
        }

        .upload-help {
            margin-top: 0.5rem;
        }

        .upload-help small {
            color: var(--admin-text-muted);
        }

        .current-image,
        .image-preview {
            margin-top: 1rem;
            text-align: left;
        }

        .current-image label,
        .image-preview label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--admin-text);
        }

        .current-image-preview,
        .image-preview img {
            max-width: 200px;
            max-height: 150px;
            border-radius: 8px;
            border: 1px solid var(--admin-border);
            object-fit: cover;
        }

        .image-preview {
            background: rgba(15, 23, 42, 0.5);
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
        }

        input[type="file"] {
            padding: 0.75rem;
            background: var(--admin-dark);
            border: 1px solid var(--admin-border);
            border-radius: 8px;
            color: var(--admin-text);
            width: 100%;
            cursor: pointer;
        }

        input[type="file"]::-webkit-file-upload-button {
            background: var(--admin-primary);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            margin-right: 1rem;
        }

        input[type="file"]::-webkit-file-upload-button:hover {
            background: #4f46e5;
        }

        /* Table Image Styles */
        .product-image-cell {
            width: 60px;
            height: 60px;
        }

        .table-product-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid var(--admin-border);
        }

        .no-image {
            width: 50px;
            height: 50px;
            background: var(--admin-border);
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--admin-text-muted);
        }
    </style>
</body>
</html>
