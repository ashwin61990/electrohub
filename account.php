<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'config/Database.php';
require_once 'classes/User.php';
require_once 'classes/Page.php';

$database = new Database();
$db = $database->getConnection();
$user = new User($db);
$page = new Page("My Account - ElectroHub", "Manage your account settings", "account, profile, settings");

// Get user details
$userDetails = $user->getById($_SESSION['user_id']);

$page->renderHeader();
?>

<div class="account-container">
    <div class="container">
        <div class="account-wrapper">
            <div class="account-header">
                <h1>My Account</h1>
                <p>Welcome back, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</p>
            </div>

            <div class="account-grid">
                <div class="account-card">
                    <div class="card-header">
                        <i class="fas fa-user"></i>
                        <h3>Profile Information</h3>
                    </div>
                    <div class="card-content">
                        <div class="info-item">
                            <strong>Full Name:</strong>
                            <span><?php echo htmlspecialchars($userDetails['full_name'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Username:</strong>
                            <span><?php echo htmlspecialchars($userDetails['username'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Email:</strong>
                            <span><?php echo htmlspecialchars($userDetails['email'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Phone:</strong>
                            <span><?php echo htmlspecialchars($userDetails['phone'] ?? 'Not provided'); ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Member Since:</strong>
                            <span><?php echo date('F j, Y', strtotime($userDetails['created_at'])); ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Last Login:</strong>
                            <span><?php echo $userDetails['last_login'] ? date('F j, Y g:i A', strtotime($userDetails['last_login'])) : 'N/A'; ?></span>
                        </div>
                    </div>
                    <div class="card-actions">
                        <a href="edit-profile.php" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Edit Profile
                        </a>
                    </div>
                </div>

                <div class="account-card">
                    <div class="card-header">
                        <i class="fas fa-shopping-bag"></i>
                        <h3>Recent Orders</h3>
                    </div>
                    <div class="card-content">
                        <p class="empty-state">
                            <i class="fas fa-box-open"></i>
                            No orders yet. <a href="index.php">Start shopping!</a>
                        </p>
                    </div>
                </div>

                <div class="account-card">
                    <div class="card-header">
                        <i class="fas fa-heart"></i>
                        <h3>Wishlist</h3>
                    </div>
                    <div class="card-content">
                        <p class="empty-state">
                            <i class="fas fa-heart-broken"></i>
                            Your wishlist is empty. <a href="index.php">Add some items!</a>
                        </p>
                    </div>
                </div>

                <div class="account-card">
                    <div class="card-header">
                        <i class="fas fa-cog"></i>
                        <h3>Account Settings</h3>
                    </div>
                    <div class="card-content">
                        <div class="settings-list">
                            <a href="change-password.php" class="setting-item">
                                <i class="fas fa-key"></i>
                                <span>Change Password</span>
                                <i class="fas fa-chevron-right"></i>
                            </a>
                            <a href="addresses.php" class="setting-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <span>Manage Addresses</span>
                                <i class="fas fa-chevron-right"></i>
                            </a>
                            <a href="notifications.php" class="setting-item">
                                <i class="fas fa-bell"></i>
                                <span>Notification Settings</span>
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="logout-section">
                <div class="logout-card">
                    <h3>Account Actions</h3>
                    <p>Need to sign out or delete your account?</p>
                    <div class="action-buttons">
                        <a href="logout.php" class="btn btn-logout">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                        <a href="#" class="btn btn-danger" onclick="confirmDelete()">
                            <i class="fas fa-trash"></i> Delete Account
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.account-container {
    min-height: calc(100vh - 80px);
    padding: 40px 0;
    background: var(--dark-bg);
}

.account-wrapper {
    max-width: 1200px;
    margin: 0 auto;
}

.account-header {
    text-align: center;
    margin-bottom: 3rem;
}

.account-header h1 {
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
    background: var(--gradient-1);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.account-header p {
    color: var(--text-secondary);
    font-size: 1.1rem;
}

.account-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 2rem;
    margin-bottom: 3rem;
}

.account-card {
    background: var(--dark-card);
    border-radius: 16px;
    border: 1px solid var(--border-color);
    overflow: hidden;
    transition: transform 0.3s, box-shadow 0.3s;
}

.account-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
}

.card-header {
    padding: 1.5rem;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    align-items: center;
    gap: 1rem;
}

.card-header i {
    font-size: 1.5rem;
    color: var(--primary-color);
}

.card-header h3 {
    font-size: 1.25rem;
    margin: 0;
}

.card-content {
    padding: 1.5rem;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid rgba(51, 65, 85, 0.3);
}

.info-item:last-child {
    border-bottom: none;
}

.info-item strong {
    color: var(--text-primary);
    font-weight: 600;
}

.info-item span {
    color: var(--text-secondary);
}

.card-actions {
    padding: 1.5rem;
    border-top: 1px solid var(--border-color);
}

.empty-state {
    text-align: center;
    color: var(--text-secondary);
    padding: 2rem 0;
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    display: block;
    opacity: 0.5;
}

.empty-state a {
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 600;
}

.settings-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.setting-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: rgba(15, 23, 42, 0.5);
    border-radius: 8px;
    color: var(--text-primary);
    text-decoration: none;
    transition: all 0.3s;
}

.setting-item:hover {
    background: rgba(99, 102, 241, 0.1);
    transform: translateX(4px);
}

.setting-item i:first-child {
    color: var(--primary-color);
}

.setting-item i:last-child {
    margin-left: auto;
    color: var(--text-secondary);
}

.logout-section {
    display: flex;
    justify-content: center;
}

.logout-card {
    background: var(--dark-card);
    border-radius: 16px;
    border: 1px solid var(--border-color);
    padding: 2rem;
    text-align: center;
    max-width: 400px;
    width: 100%;
}

.logout-card h3 {
    margin-bottom: 0.5rem;
    color: var(--text-primary);
}

.logout-card p {
    color: var(--text-secondary);
    margin-bottom: 1.5rem;
}

.action-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
}

.btn-logout {
    background: var(--gradient-1);
    color: white;
}

.btn-logout:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(99, 102, 241, 0.4);
}

.btn-danger {
    background: transparent;
    color: #ef4444;
    border: 2px solid #ef4444;
}

.btn-danger:hover {
    background: #ef4444;
    color: white;
}

@media (max-width: 768px) {
    .account-grid {
        grid-template-columns: 1fr;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .account-header h1 {
        font-size: 2rem;
    }
}
</style>

<script>
function confirmDelete() {
    if (confirm('Are you sure you want to delete your account? This action cannot be undone.')) {
        alert('Account deletion feature will be implemented soon.');
    }
}
</script>

<?php
$page->renderFooter();
?>
