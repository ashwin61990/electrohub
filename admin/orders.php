<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: ../login.php");
    exit();
}

require_once '../config/Database.php';
require_once '../classes/Order.php';

$database = new Database();
$db = $database->getConnection();
$order = new Order($db);

// Get all orders
$orders = $order->getAllOrders();

include 'includes/header.php';
?>

<div class="orders-table-container">
            <div class="table-header">
                <h2>All Orders</h2>
                <div class="table-actions">
                    <input type="text" id="searchOrders" placeholder="Search orders..." class="search-input">
                    <select id="statusFilter" class="filter-select">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="shipped">Shipped</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($orders)): ?>
                            <?php foreach ($orders as $orderItem): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo $order->generateOrderNumber($orderItem['id']); ?></strong>
                                    </td>
                                    <td>
                                        <div class="customer-info">
                                            <strong><?php echo htmlspecialchars($orderItem['customer_name'] ?? 'N/A'); ?></strong>
                                            <small><?php echo htmlspecialchars($orderItem['customer_email'] ?? ''); ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="date-info">
                                            <strong><?php echo date('M j, Y', strtotime($orderItem['created_at'])); ?></strong>
                                            <small><?php echo date('g:i A', strtotime($orderItem['created_at'])); ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>₹<?php echo number_format($orderItem['total_amount'], 2); ?></strong>
                                        <?php if ($orderItem['shipping_amount'] > 0): ?>
                                            <small>(+₹<?php echo number_format($orderItem['shipping_amount'], 2); ?> shipping)</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="payment-info">
                                            <span class="payment-method"><?php echo ucfirst($orderItem['payment_method']); ?></span>
                                            <span class="payment-status payment-<?php echo $orderItem['payment_status']; ?>">
                                                <?php echo ucfirst($orderItem['payment_status']); ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="status status-<?php echo $orderItem['status']; ?>">
                                            <?php echo ucfirst($orderItem['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="actions">
                                            <button onclick="viewOrder(<?php echo $orderItem['id']; ?>)" class="btn btn-sm btn-primary" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button onclick="updateOrderStatus(<?php echo $orderItem['id']; ?>)" class="btn btn-sm btn-warning" title="Update Status">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">
                                    <div class="no-data">
                                        <i class="fas fa-shopping-bag"></i>
                                        <h3>No Orders Found</h3>
                                        <p>Orders will appear here once customers start placing them.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
</div>

<!-- Order Details Modal -->
<div id="orderModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Order Details</h3>
            <span class="close">&times;</span>
        </div>
        <div class="modal-body" id="orderModalBody">
            <!-- Order details will be loaded here -->
        </div>
    </div>
</div>

<style>
.orders-table-container {
    background: var(--dark-card);
    border-radius: 12px;
    border: 1px solid var(--border-color);
    overflow: hidden;
}

.table-header {
    padding: 1.5rem;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.table-header h2 {
    margin: 0;
    color: var(--text-primary);
}

.table-actions {
    display: flex;
    gap: 1rem;
    align-items: center;
}

.search-input, .filter-select {
    padding: 0.5rem;
    border: 1px solid var(--border-color);
    border-radius: 6px;
    background: var(--dark-bg);
    color: var(--text-primary);
}

.customer-info, .date-info {
    display: flex;
    flex-direction: column;
}

.customer-info small, .date-info small {
    color: var(--text-secondary);
    font-size: 0.85rem;
}

.payment-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.payment-method {
    font-size: 0.85rem;
    color: var(--text-secondary);
    text-transform: capitalize;
}

.payment-status {
    font-size: 0.8rem;
    padding: 0.2rem 0.5rem;
    border-radius: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.payment-pending {
    background: rgba(245, 158, 11, 0.2);
    color: var(--warning-color);
}

.payment-paid {
    background: rgba(16, 185, 129, 0.2);
    color: var(--success-color);
}

.payment-failed {
    background: rgba(239, 68, 68, 0.2);
    color: var(--error-color);
}

.status {
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-pending {
    background: rgba(245, 158, 11, 0.2);
    color: var(--warning-color);
}

.status-processing {
    background: rgba(59, 130, 246, 0.2);
    color: var(--info-color);
}

.status-shipped {
    background: rgba(16, 185, 129, 0.2);
    color: var(--success-color);
}

.status-delivered {
    background: rgba(34, 197, 94, 0.2);
    color: #22c55e;
}

.status-cancelled {
    background: rgba(239, 68, 68, 0.2);
    color: var(--error-color);
}

.no-data {
    padding: 3rem;
    text-align: center;
    color: var(--text-secondary);
}

.no-data i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.no-data h3 {
    margin: 0 0 0.5rem 0;
    color: var(--text-primary);
}

.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
}

.modal-content {
    background: var(--dark-card);
    margin: 5% auto;
    padding: 0;
    border-radius: 12px;
    width: 90%;
    max-width: 800px;
    max-height: 80vh;
    overflow-y: auto;
}

.modal-header {
    padding: 1.5rem;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
    color: var(--text-primary);
}

.close {
    color: var(--text-secondary);
    font-size: 1.5rem;
    font-weight: bold;
    cursor: pointer;
}

.close:hover {
    color: var(--text-primary);
}

.modal-body {
    padding: 1.5rem;
}

@media (max-width: 768px) {
    .table-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .table-actions {
        width: 100%;
        flex-direction: column;
    }
    
    .search-input, .filter-select {
        width: 100%;
    }
}
</style>

<script>
function viewOrder(orderId) {
    // Fetch order details and show in modal
    fetch(`order_details.php?id=${orderId}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('orderModalBody').innerHTML = html;
            document.getElementById('orderModal').style.display = 'block';
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load order details');
        });
}

function updateOrderStatus(orderId) {
    const newStatus = prompt('Enter new status (pending, processing, shipped, delivered, cancelled):');
    if (newStatus && ['pending', 'processing', 'shipped', 'delivered', 'cancelled'].includes(newStatus)) {
        // Update order status
        const formData = new FormData();
        formData.append('action', 'update_status');
        formData.append('order_id', orderId);
        formData.append('status', newStatus);
        
        fetch('order_actions.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Failed to update status: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to update status');
        });
    }
}

// Close modal
document.querySelector('.close').onclick = function() {
    document.getElementById('orderModal').style.display = 'none';
}

window.onclick = function(event) {
    const modal = document.getElementById('orderModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}

// Search and filter functionality
document.getElementById('searchOrders').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('.admin-table tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

document.getElementById('statusFilter').addEventListener('change', function() {
    const filterValue = this.value;
    const rows = document.querySelectorAll('.admin-table tbody tr');
    
    rows.forEach(row => {
        if (!filterValue) {
            row.style.display = '';
        } else {
            const statusCell = row.querySelector('.status');
            if (statusCell && statusCell.classList.contains(`status-${filterValue}`)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>
