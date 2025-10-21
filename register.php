<?php
session_start();

// If user is already logged in, redirect to homepage
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require_once 'config/Database.php';
require_once 'classes/User.php';
require_once 'classes/Page.php';

$database = new Database();
$db = $database->getConnection();
$user = new User($db);
$page = new Page("Register - ElectroHub", "Create your account to start shopping", "register, signup, create account");

$errors = [];
$success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $full_name = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($full_name)) {
        $errors[] = "Full name is required";
    }

    if (empty($username)) {
        $errors[] = "Username is required";
    } elseif (!User::validateUsername($username)) {
        $errors[] = "Username must be 3-50 characters and contain only letters, numbers, and underscores";
    } else {
        $user->username = $username;
        if ($user->usernameExists()) {
            $errors[] = "Username already exists";
        }
    }

    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!User::validateEmail($email)) {
        $errors[] = "Invalid email format";
    } else {
        $user->email = $email;
        if ($user->emailExists()) {
            $errors[] = "Email already registered";
        }
    }

    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (!User::validatePassword($password)) {
        $errors[] = "Password must be at least 8 characters with uppercase, lowercase, and number";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }

    // If no errors, register user
    if (empty($errors)) {
        $user->full_name = $full_name;
        $user->username = $username;
        $user->email = $email;
        $user->phone = $phone;
        $user->password = $password;

        if ($user->register()) {
            $success = true;
            $_SESSION['registration_success'] = true;
        } else {
            $errors[] = "Registration failed. Please try again.";
        }
    }
}

$page->renderHeader();
?>

<div class="auth-container">
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <h1>Create Account</h1>
                <p>Join ElectroHub and start shopping today</p>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <strong>Registration Successful!</strong>
                        <p>Your account has been created. <a href="login.php">Login now</a></p>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <div>
                        <strong>Registration Failed</strong>
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!$success): ?>
            <form method="POST" action="" class="auth-form" id="registerForm" novalidate>
                <div class="form-group">
                    <label for="full_name">Full Name <span class="required">*</span></label>
                    <div class="input-wrapper">
                        <i class="fas fa-user"></i>
                        <input 
                            type="text" 
                            id="full_name" 
                            name="full_name" 
                            placeholder="Enter your full name"
                            value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>"
                            required
                        >
                    </div>
                    <span class="validation-message"></span>
                </div>

                <div class="form-group">
                    <label for="username">Username <span class="required">*</span></label>
                    <div class="input-wrapper">
                        <i class="fas fa-at"></i>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            placeholder="Choose a username"
                            value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                            required
                        >
                    </div>
                    <span class="validation-message"></span>
                    <small class="input-hint">3-50 characters, letters, numbers, and underscores only</small>
                </div>

                <div class="form-group">
                    <label for="email">Email Address <span class="required">*</span></label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope"></i>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            placeholder="Enter your email"
                            value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                            required
                        >
                    </div>
                    <span class="validation-message"></span>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <div class="input-wrapper">
                        <i class="fas fa-phone"></i>
                        <input 
                            type="tel" 
                            id="phone" 
                            name="phone" 
                            placeholder="Enter your phone number"
                            value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"
                        >
                    </div>
                    <span class="validation-message"></span>
                </div>

                <div class="form-group">
                    <label for="password">Password <span class="required">*</span></label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            placeholder="Create a strong password"
                            required
                        >
                        <button type="button" class="toggle-password" data-target="password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <span class="validation-message"></span>
                    <div class="password-strength">
                        <div class="strength-bar">
                            <div class="strength-fill"></div>
                        </div>
                        <span class="strength-text">Password strength</span>
                    </div>
                    <small class="input-hint">At least 8 characters with uppercase, lowercase, and number</small>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password <span class="required">*</span></label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input 
                            type="password" 
                            id="confirm_password" 
                            name="confirm_password" 
                            placeholder="Re-enter your password"
                            required
                        >
                        <button type="button" class="toggle-password" data-target="confirm_password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <span class="validation-message"></span>
                </div>

                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="terms" required>
                        <span>I agree to the <a href="terms.php">Terms & Conditions</a> and <a href="privacy.php">Privacy Policy</a></span>
                    </label>
                    <span class="validation-message"></span>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-user-plus"></i> Create Account
                </button>
            </form>
            <?php endif; ?>

            <div class="auth-footer">
                <p>Already have an account? <a href="login.php">Login here</a></p>
            </div>
        </div>

        <div class="auth-benefits">
            <h3>Why Join ElectroHub?</h3>
            <div class="benefit-item">
                <i class="fas fa-shipping-fast"></i>
                <div>
                    <h4>Free Shipping</h4>
                    <p>On orders over $50</p>
                </div>
            </div>
            <div class="benefit-item">
                <i class="fas fa-tags"></i>
                <div>
                    <h4>Exclusive Deals</h4>
                    <p>Member-only discounts</p>
                </div>
            </div>
            <div class="benefit-item">
                <i class="fas fa-headset"></i>
                <div>
                    <h4>24/7 Support</h4>
                    <p>Dedicated customer service</p>
                </div>
            </div>
            <div class="benefit-item">
                <i class="fas fa-undo"></i>
                <div>
                    <h4>Easy Returns</h4>
                    <p>30-day return policy</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/auth-validation.js"></script>

<?php
$page->renderFooter();
?>
