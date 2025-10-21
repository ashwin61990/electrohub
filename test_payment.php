<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'config/Database.php';
require_once 'classes/RazorpayPayment.php';
require_once 'classes/Page.php';

$database = new Database();
$db = $database->getConnection();
$page = new Page("Test Payment - ElectroHub", "Test Razorpay payment integration", "payment, test, razorpay");

$page->renderHeader();
?>

<div class="test-payment-container">
    <div class="container">
        <div class="test-header">
            <h1>🧪 Payment Integration Test</h1>
            <p>Test the Razorpay payment integration with sample data</p>
        </div>
        
        <div class="test-grid">
            <div class="test-card">
                <h3>💳 Test Card Details</h3>
                <div class="test-details">
                    <div class="detail-row">
                        <span>Card Number:</span>
                        <code>4111 1111 1111 1111</code>
                    </div>
                    <div class="detail-row">
                        <span>Expiry:</span>
                        <code>12/25</code>
                    </div>
                    <div class="detail-row">
                        <span>CVV:</span>
                        <code>123</code>
                    </div>
                    <div class="detail-row">
                        <span>Name:</span>
                        <code>Test User</code>
                    </div>
                </div>
            </div>
            
            <div class="test-card">
                <h3>📱 Test UPI</h3>
                <div class="test-details">
                    <div class="detail-row">
                        <span>UPI ID:</span>
                        <code>success@razorpay</code>
                    </div>
                    <div class="detail-row">
                        <span>PIN:</span>
                        <code>Any 4 digits</code>
                    </div>
                </div>
            </div>
            
            <div class="test-card">
                <h3>🏦 Test Net Banking</h3>
                <div class="test-details">
                    <div class="detail-row">
                        <span>Bank:</span>
                        <code>Any bank</code>
                    </div>
                    <div class="detail-row">
                        <span>Password:</span>
                        <code>success</code>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="test-payment-form">
            <h3>Test Payment Form</h3>
            <form id="testPaymentForm">
                <div class="form-group">
                    <label>Amount (₹)</label>
                    <input type="number" id="testAmount" value="100" min="1" step="0.01">
                </div>
                
                <div class="form-group">
                    <label>Customer Name</label>
                    <input type="text" id="testName" value="<?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Test User'); ?>">
                </div>
                
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" id="testEmail" value="<?php echo htmlspecialchars($_SESSION['email'] ?? 'test@example.com'); ?>">
                </div>
                
                <div class="form-group">
                    <label>Phone</label>
                    <input type="tel" id="testPhone" value="9999999999">
                </div>
                
                <button type="button" id="testPayBtn" class="btn btn-primary">
                    🚀 Test Payment
                </button>
            </form>
        </div>
        
        <div class="test-results" id="testResults" style="display: none;">
            <h3>Test Results</h3>
            <div id="resultContent"></div>
        </div>
        
        <div class="test-links">
            <a href="index.php" class="btn btn-outline">🏠 Homepage</a>
            <a href="checkout.php" class="btn btn-outline">🛒 Real Checkout</a>
            <a href="razorpay_setup.php" class="btn btn-outline">⚙️ Setup Guide</a>
        </div>
    </div>
</div>

<!-- Razorpay JavaScript SDK -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<style>
.test-payment-container {
    min-height: calc(100vh - 80px);
    padding: 3rem 0;
    background: var(--dark-bg);
}

.test-header {
    text-align: center;
    margin-bottom: 3rem;
}

.test-header h1 {
    font-size: 2.5rem;
    margin-bottom: 1rem;
    background: var(--gradient-1);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.test-header p {
    color: var(--text-secondary);
    font-size: 1.2rem;
}

.test-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    margin-bottom: 3rem;
}

.test-card {
    background: var(--dark-card);
    border-radius: 16px;
    border: 1px solid var(--border-color);
    padding: 2rem;
}

.test-card h3 {
    margin: 0 0 1.5rem 0;
    color: var(--text-primary);
    font-size: 1.3rem;
}

.test-details {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.detail-row span {
    color: var(--text-secondary);
    font-weight: 500;
}

.detail-row code {
    background: var(--primary-color);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 6px;
    font-family: 'Courier New', monospace;
    font-size: 0.9rem;
}

.test-payment-form {
    background: var(--dark-card);
    border-radius: 16px;
    border: 1px solid var(--border-color);
    padding: 2rem;
    margin-bottom: 3rem;
    max-width: 500px;
    margin-left: auto;
    margin-right: auto;
}

.test-payment-form h3 {
    margin: 0 0 2rem 0;
    color: var(--text-primary);
    text-align: center;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    color: var(--text-primary);
    font-weight: 500;
}

.form-group input {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    background: var(--dark-bg);
    color: var(--text-primary);
    font-size: 1rem;
}

.form-group input:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.test-results {
    background: var(--dark-card);
    border-radius: 16px;
    border: 1px solid var(--border-color);
    padding: 2rem;
    margin-bottom: 3rem;
}

.test-results h3 {
    margin: 0 0 1rem 0;
    color: var(--text-primary);
}

.test-links {
    text-align: center;
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

.success-result {
    color: var(--success-color);
    background: rgba(16, 185, 129, 0.1);
    padding: 1rem;
    border-radius: 8px;
    border: 1px solid rgba(16, 185, 129, 0.2);
}

.error-result {
    color: var(--error-color);
    background: rgba(239, 68, 68, 0.1);
    padding: 1rem;
    border-radius: 8px;
    border: 1px solid rgba(239, 68, 68, 0.2);
}

@media (max-width: 768px) {
    .test-grid {
        grid-template-columns: 1fr;
    }
    
    .test-links {
        flex-direction: column;
        align-items: center;
    }
}
</style>

<script>
document.getElementById('testPayBtn').addEventListener('click', function() {
    const amount = document.getElementById('testAmount').value;
    const name = document.getElementById('testName').value;
    const email = document.getElementById('testEmail').value;
    const phone = document.getElementById('testPhone').value;
    
    if (!amount || !name || !email || !phone) {
        alert('Please fill all fields');
        return;
    }
    
    // Create test order
    const formData = new FormData();
    formData.append('action', 'test_payment');
    formData.append('amount', amount);
    formData.append('name', name);
    formData.append('email', email);
    formData.append('phone', phone);
    
    const btn = this;
    const originalText = btn.textContent;
    btn.textContent = 'Creating Order...';
    btn.disabled = true;
    
    fetch('test_payment_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Initialize Razorpay checkout
            const options = {
                ...data.payment_data,
                handler: function(response) {
                    showResult('success', 'Payment Successful!', {
                        'Payment ID': response.razorpay_payment_id,
                        'Order ID': response.razorpay_order_id,
                        'Signature': response.razorpay_signature.substring(0, 20) + '...'
                    });
                },
                modal: {
                    ondismiss: function() {
                        btn.textContent = originalText;
                        btn.disabled = false;
                        showResult('error', 'Payment Cancelled', {
                            'Status': 'User cancelled the payment'
                        });
                    }
                }
            };
            
            const rzp = new Razorpay(options);
            rzp.open();
            
            btn.textContent = originalText;
            btn.disabled = false;
        } else {
            showResult('error', 'Order Creation Failed', {
                'Error': data.error
            });
            btn.textContent = originalText;
            btn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showResult('error', 'Network Error', {
            'Error': 'Failed to connect to server'
        });
        btn.textContent = originalText;
        btn.disabled = false;
    });
});

function showResult(type, title, details) {
    const resultsDiv = document.getElementById('testResults');
    const contentDiv = document.getElementById('resultContent');
    
    let html = `<div class="${type}-result">`;
    html += `<h4>${title}</h4>`;
    
    if (details) {
        html += '<div class="result-details">';
        for (const [key, value] of Object.entries(details)) {
            html += `<p><strong>${key}:</strong> ${value}</p>`;
        }
        html += '</div>';
    }
    
    html += '</div>';
    
    contentDiv.innerHTML = html;
    resultsDiv.style.display = 'block';
    resultsDiv.scrollIntoView({ behavior: 'smooth' });
}
</script>

<?php
$page->renderFooter();
?>
