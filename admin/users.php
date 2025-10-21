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

// Handle user status toggle
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'toggle_status' && isset($_POST['user_id'])) {
        $userId = $_POST['user_id'];
        if ($admin->toggleUserStatus($userId)) {
            $message = 'User status updated successfully!';
            $messageType = 'success';
        } else {
            $message = 'Failed to update user status.';
            $messageType = 'error';
        }
    }
}

// Get users for listing
$page = $_GET['page'] ?? 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$users = $admin->getAllUsers($limit, $offset);
$totalUsers = $admin->getDashboardStats()['total_users'];
$totalPages = ceil($totalUsers / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users Management - ElectroHub Admin</title>
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
                <a href="products.php" class="nav-item">
                    <i class="fas fa-box"></i>
                    <span>Products</span>
                </a>
                <a href="categories.php" class="nav-item">
                    <i class="fas fa-tags"></i>
                    <span>Categories</span>
                </a>
                <a href="users.php" class="nav-item active">
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
                    <h1>Users Management</h1>
                </div>
                <div class="header-right">
                    <div class="admin-user">
                        <span>Total Users: <?php echo number_format($totalUsers); ?></span>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <div class="dashboard-content">
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <!-- Users Statistics -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon users">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo number_format($totalUsers); ?></h3>
                            <p>Total Users</p>
                            <small>Registered customers</small>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon products">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div class="stat-info">
                            <?php
                            $activeUsers = 0;
                            foreach ($users as $user) {
                                if ($user['is_active']) $activeUsers++;
                            }
                            ?>
                            <h3><?php echo number_format($activeUsers); ?></h3>
                            <p>Active Users</p>
                            <small>Currently active</small>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon categories">
                            <i class="fas fa-envelope-open"></i>
                        </div>
                        <div class="stat-info">
                            <?php
                            $verifiedUsers = 0;
                            foreach ($users as $user) {
                                if ($user['email_verified']) $verifiedUsers++;
                            }
                            ?>
                            <h3><?php echo number_format($verifiedUsers); ?></h3>
                            <p>Verified</p>
                            <small>Email verified</small>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon stock">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="stat-info">
                            <?php
                            $recentUsers = 0;
                            $thirtyDaysAgo = date('Y-m-d H:i:s', strtotime('-30 days'));
                            foreach ($users as $user) {
                                if ($user['created_at'] >= $thirtyDaysAgo) $recentUsers++;
                            }
                            ?>
                            <h3><?php echo number_format($recentUsers); ?></h3>
                            <p>New This Month</p>
                            <small>Last 30 days</small>
                        </div>
                    </div>
                </div>

                <!-- Users Table -->
                <div class="admin-table">
                    <div class="table-header">
                        <h3>All Users</h3>
                        <div class="table-actions">
                            <input type="text" placeholder="Search users..." class="form-control search-input">
                            <button class="btn btn-primary">
                                <i class="fas fa-download"></i> Export
                            </button>
                        </div>
                    </div>
                    
                    <table>
                        <thead>
                            <tr>
                                <th>
                                    <input type="checkbox" id="selectAll">
                                </th>
                                <th>User</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th>Verified</th>
                                <th>Joined</th>
                                <th>Last Login</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($users)): ?>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="row-checkbox" value="<?php echo $user['id']; ?>">
                                        </td>
                                        <td>
                                            <div class="user-info">
                                                <div class="user-avatar">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($user['full_name']); ?></strong>
                                                    <small>@<?php echo htmlspecialchars($user['username']); ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><?php echo htmlspecialchars($user['phone'] ?: 'Not provided'); ?></td>
                                        <td>
                                            <?php if ($user['is_active']): ?>
                                                <span class="status status-active">Active</span>
                                            <?php else: ?>
                                                <span class="status status-inactive">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($user['email_verified']): ?>
                                                <span class="verified">
                                                    <i class="fas fa-check-circle"></i> Verified
                                                </span>
                                            <?php else: ?>
                                                <span class="unverified">
                                                    <i class="fas fa-times-circle"></i> Unverified
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                        <td>
                                            <?php if ($user['last_login']): ?>
                                                <?php echo date('M j, Y g:i A', strtotime($user['last_login'])); ?>
                                            <?php else: ?>
                                                <span class="text-muted">Never</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button onclick="viewUser(<?php echo $user['id']; ?>)" 
                                                        class="btn btn-sm btn-primary" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="action" value="toggle_status">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                    <button type="submit" 
                                                            class="btn btn-sm <?php echo $user['is_active'] ? 'btn-warning' : 'btn-success'; ?>" 
                                                            title="<?php echo $user['is_active'] ? 'Deactivate' : 'Activate'; ?> User">
                                                        <i class="fas fa-<?php echo $user['is_active'] ? 'ban' : 'check'; ?>"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center">No users found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Bulk Actions -->
                <div class="bulk-actions">
                    <span>Selected <span class="selected-count">0</span> users</span>
                    <div class="bulk-buttons">
                        <button class="btn btn-success">
                            <i class="fas fa-check"></i> Activate Selected
                        </button>
                        <button class="btn btn-warning">
                            <i class="fas fa-ban"></i> Deactivate Selected
                        </button>
                        <button class="btn btn-info">
                            <i class="fas fa-envelope"></i> Send Email
                        </button>
                        <button class="btn btn-danger">
                            <i class="fas fa-trash"></i> Delete Selected
                        </button>
                    </div>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="?page=<?php echo $i; ?>" 
                               class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- User Details Modal -->
    <div id="userModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>User Details</h3>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <div id="userDetails">
                    <!-- User details will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/admin.js"></script>
    <script>
        function viewUser(userId) {
            // This would typically fetch user details via AJAX
            const modal = document.getElementById('userModal');
            const userDetails = document.getElementById('userDetails');
            
            userDetails.innerHTML = `
                <div class="loading">
                    <i class="fas fa-spinner fa-spin"></i> Loading user details...
                </div>
            `;
            
            modal.style.display = 'block';
            
            // Simulate loading user details
            setTimeout(() => {
                userDetails.innerHTML = `
                    <div class="user-detail-grid">
                        <div class="detail-item">
                            <strong>User ID:</strong>
                            <span>${userId}</span>
                        </div>
                        <div class="detail-item">
                            <strong>Registration Date:</strong>
                            <span>March 15, 2024</span>
                        </div>
                        <div class="detail-item">
                            <strong>Total Orders:</strong>
                            <span>0</span>
                        </div>
                        <div class="detail-item">
                            <strong>Total Spent:</strong>
                            <span>₹0.00</span>
                        </div>
                    </div>
                    <div class="user-actions">
                        <button class="btn btn-primary">Send Email</button>
                        <button class="btn btn-warning">Reset Password</button>
                        <button class="btn btn-danger">Delete Account</button>
                    </div>
                `;
            }, 1000);
        }

        // Modal functionality
        document.querySelector('.modal-close').addEventListener('click', function() {
            document.getElementById('userModal').style.display = 'none';
        });

        window.addEventListener('click', function(e) {
            const modal = document.getElementById('userModal');
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });

        // Search functionality
        document.querySelector('.search-input').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('.admin-table tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    </script>

    <style>
        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem;
            border-bottom: 1px solid var(--admin-border);
        }

        .table-actions {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .table-actions .form-control {
            width: 250px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: var(--admin-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.875rem;
        }

        .verified {
            color: var(--admin-success);
        }

        .unverified {
            color: var(--admin-danger);
        }

        .text-muted {
            color: var(--admin-text-muted);
        }

        .bulk-buttons {
            display: flex;
            gap: 0.5rem;
        }

        /* Modal Styles */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: var(--admin-card);
            border-radius: 12px;
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem;
            border-bottom: 1px solid var(--admin-border);
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--admin-text-muted);
            cursor: pointer;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .user-detail-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem;
            background: rgba(15, 23, 42, 0.5);
            border-radius: 6px;
        }

        .user-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }

        .loading {
            text-align: center;
            padding: 2rem;
            color: var(--admin-text-muted);
        }

        @media (max-width: 768px) {
            .table-header {
                flex-direction: column;
                gap: 1rem;
                align-items: stretch;
            }

            .table-actions {
                flex-direction: column;
            }

            .table-actions .form-control {
                width: 100%;
            }

            .user-detail-grid {
                grid-template-columns: 1fr;
            }

            .user-actions {
                flex-direction: column;
            }
        }
    </style>
</body>
</html>
