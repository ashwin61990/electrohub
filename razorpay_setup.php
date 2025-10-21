<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Razorpay Setup - ElectroHub</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 2rem;
            min-height: 100vh;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 2rem;
        }
        .step {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border-left: 4px solid #667eea;
        }
        .success {
            color: #28a745;
            font-weight: 600;
        }
        .error {
            color: #dc3545;
            font-weight: 600;
        }
        .info {
            color: #17a2b8;
            font-weight: 600;
        }
        .warning {
            color: #ffc107;
            font-weight: 600;
            background: #fff3cd;
            padding: 1rem;
            border-radius: 8px;
            border: 1px solid #ffeaa7;
        }
        .btn {
            background: #667eea;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 0.5rem 0.5rem 0.5rem 0;
        }
        .btn:hover {
            background: #5a6fd8;
        }
        pre {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            overflow-x: auto;
            border: 1px solid #dee2e6;
        }
        .code-block {
            background: #2d3748;
            color: #e2e8f0;
            padding: 1rem;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            margin: 1rem 0;
        }
        .highlight {
            background: #fff3cd;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-family: monospace;
        }
        ol {
            padding-left: 2rem;
        }
        li {
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Razorpay Payment Integration Setup</h1>
        
        <div class="warning">
            ⚠️ <strong>Important:</strong> This integration is set up for TEST MODE. You'll need to get your actual Razorpay credentials to use it.
        </div>
        
        <div class="step">
            <h3>📋 Step 1: Create Razorpay Account</h3>
            <ol>
                <li>Go to <a href="https://razorpay.com" target="_blank">https://razorpay.com</a></li>
                <li>Click "Sign Up" and create a free account</li>
                <li>Complete the verification process</li>
                <li>You'll get access to the dashboard with test credentials</li>
            </ol>
        </div>
        
        <div class="step">
            <h3>🔑 Step 2: Get Test Credentials</h3>
            <ol>
                <li>Login to your Razorpay Dashboard</li>
                <li>Go to <strong>Settings → API Keys</strong></li>
                <li>In the <strong>Test Mode</strong> section, you'll see:</li>
                <ul>
                    <li><strong>Key ID:</strong> Starts with <span class="highlight">rzp_test_</span></li>
                    <li><strong>Key Secret:</strong> Click "Generate" to create one</li>
                </ul>
            </ol>
        </div>
        
        <div class="step">
            <h3>⚙️ Step 3: Update Configuration</h3>
            <p>Edit the file: <span class="highlight">config/Razorpay.php</span></p>
            <div class="code-block">
const TEST_KEY_ID = 'rzp_test_YOUR_ACTUAL_KEY_ID';
const TEST_KEY_SECRET = 'YOUR_ACTUAL_KEY_SECRET';
            </div>
            <p>Replace the placeholder values with your actual test credentials.</p>
        </div>
        
        <div class="step">
            <h3>🧪 Step 4: Test Payment</h3>
            <p>Use these test card details for testing:</p>
            <div class="code-block">
<strong>Test Card Number:</strong> 4111 1111 1111 1111
<strong>Expiry:</strong> Any future date (e.g., 12/25)
<strong>CVV:</strong> Any 3 digits (e.g., 123)
<strong>Name:</strong> Any name

<strong>Test UPI ID:</strong> success@razorpay
<strong>Test Net Banking:</strong> Select any bank and use "success" as password
            </div>
        </div>
        
        <div class="step">
            <h3>📊 Current Configuration Status</h3>
            <?php
            require_once 'config/Razorpay.php';
            $config = RazorpayConfig::getConfig();
            ?>
            
            <p><strong>Environment:</strong> 
                <span class="<?php echo $config['test_mode'] ? 'info' : 'success'; ?>">
                    <?php echo $config['test_mode'] ? 'TEST MODE' : 'LIVE MODE'; ?>
                </span>
            </p>
            
            <p><strong>Key ID:</strong> 
                <span class="highlight"><?php echo $config['key_id']; ?></span>
                <?php if ($config['key_id'] === 'rzp_test_1DP5mmOlF5G5ag'): ?>
                    <span class="warning">⚠️ Using placeholder credentials</span>
                <?php else: ?>
                    <span class="success">✅ Custom credentials configured</span>
                <?php endif; ?>
            </p>
            
            <p><strong>Currency:</strong> <span class="highlight"><?php echo $config['currency']; ?></span></p>
            <p><strong>Company:</strong> <span class="highlight"><?php echo $config['company_name']; ?></span></p>
        </div>
        
        <div class="step">
            <h3>🔗 Test the Integration</h3>
            <p>Once you've updated the credentials:</p>
            <ol>
                <li>Go to the <a href="index.php">Homepage</a> and add products to cart</li>
                <li>Proceed to <a href="checkout.php">Checkout</a></li>
                <li>Select "Online Payment" option</li>
                <li>Use the test credentials above to complete payment</li>
            </ol>
            
            <a href="test_payment.php" class="btn">🧪 Test Payment</a>
            <a href="checkout.php" class="btn">🛒 Test Checkout</a>
            <a href="admin/dashboard.php" class="btn">👨‍💼 Admin Dashboard</a>
        </div>
        
        <div class="step">
            <h3>📝 Integration Features</h3>
            <ul>
                <li>✅ <strong>Multiple Payment Methods:</strong> Cards, UPI, Net Banking, Wallets</li>
                <li>✅ <strong>Secure Payment Processing:</strong> PCI DSS compliant</li>
                <li>✅ <strong>Payment Verification:</strong> Signature verification for security</li>
                <li>✅ <strong>Order Management:</strong> Automatic order creation on successful payment</li>
                <li>✅ <strong>Stock Management:</strong> Inventory updated after payment</li>
                <li>✅ <strong>Test Mode:</strong> Safe testing environment</li>
                <li>✅ <strong>Mobile Responsive:</strong> Works on all devices</li>
            </ul>
        </div>
        
        <div class="step">
            <h3>🚨 Important Notes</h3>
            <ul>
                <li><strong>Test Mode:</strong> No real money is charged in test mode</li>
                <li><strong>Webhooks:</strong> For production, set up webhooks for payment notifications</li>
                <li><strong>KYC:</strong> Complete KYC verification before going live</li>
                <li><strong>SSL Certificate:</strong> Required for live payments</li>
                <li><strong>Pricing:</strong> Razorpay charges 2% + GST per transaction</li>
            </ul>
        </div>
        
        <div class="step">
            <h3>🔧 Quick Links</h3>
            <a href="index.php" class="btn">🏠 Homepage</a>
            <a href="setup_database.php" class="btn">🗄️ Database Setup</a>
            <a href="admin/dashboard.php" class="btn">👨‍💼 Admin Panel</a>
            <a href="https://razorpay.com/docs/" target="_blank" class="btn">📚 Razorpay Docs</a>
        </div>
    </div>
</body>
</html>
