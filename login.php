<?php
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// If user is already logged in, redirect to homepage
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

try {
    require_once 'config/Database.php';
    require_once 'classes/User.php';
    require_once 'classes/Admin.php';
    require_once 'classes/Page.php';

    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception("Database connection failed");
    }
    
    $user = new User($db);
    $page = new Page("Login - ElectroHub", "Login to your account", "login, signin, account");
} catch (Exception $e) {
    error_log("Login page error: " . $e->getMessage());
    die("Application error: " . $e->getMessage() . "<br><br>Please check the server configuration.");
}

$error = '';
$registration_success = isset($_SESSION['registration_success']);
unset($_SESSION['registration_success']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    if (empty($email)) {
        $error = "Email or username is required";
    } elseif (empty($password)) {
        $error = "Password is required";
    } else {
        $user->email = $email;
        $user->password = $password;

        $login_result = $user->login();

        if ($login_result === true) {
            // Set session variables
            $_SESSION['user_id'] = $user->id;
            $_SESSION['username'] = $user->username;
            $_SESSION['email'] = $user->email;
            $_SESSION['full_name'] = $user->full_name;

            // Set remember me cookie if checked
            if ($remember) {
                $token = bin2hex(random_bytes(32));
                setcookie('remember_token', $token, time() + (86400 * 30), "/"); // 30 days
            }

            // Check if user is admin and redirect accordingly
            $admin = new Admin($db);
            if ($admin->isAdmin($user->id)) {
                $_SESSION['is_admin'] = true;
                header("Location: admin/dashboard.php");
            } else {
                $redirect = $_GET['redirect'] ?? 'index.php';
                header("Location: " . $redirect);
            }
            exit();
        } elseif ($login_result === "inactive") {
            $error = "Your account has been deactivated. Please contact support.";
        } else {
            $error = "Invalid email/username or password";
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
                    <i class="fas fa-sign-in-alt"></i>
                </div>
                <h1>Welcome Back</h1>
                <p>Login to your ElectroHub account</p>
            </div>

            <?php if ($registration_success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <strong>Registration Successful!</strong>
                        <p>Please login with your credentials</p>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <div>
                        <strong>Login Failed</strong>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="auth-form" id="loginForm" novalidate>
                <div class="form-group">
                    <label for="email">Email or Username <span class="required">*</span></label>
                    <div class="input-wrapper">
                        <i class="fas fa-user"></i>
                        <input 
                            type="text" 
                            id="email" 
                            name="email" 
                            placeholder="Enter your email or username"
                            value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                            required
                            autofocus
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
                            placeholder="Enter your password"
                            required
                        >
                        <button type="button" class="toggle-password" data-target="password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <span class="validation-message"></span>
                </div>

                <div class="form-options">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember" id="remember">
                        <span>Remember me</span>
                    </label>
                    <a href="forgot-password.php" class="forgot-link">Forgot Password?</a>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>

            <div class="auth-footer">
                <p>Don't have an account? <a href="register.php">Register here</a></p>
            </div>
        </div>

        <div class="auth-benefits">
            <h3>Member Benefits</h3>
            <div class="benefit-item">
                <i class="fas fa-bolt"></i>
                <div>
                    <h4>Fast Checkout</h4>
                    <p>Save time with saved addresses</p>
                </div>
            </div>
            <div class="benefit-item">
                <i class="fas fa-history"></i>
                <div>
                    <h4>Order History</h4>
                    <p>Track all your purchases</p>
                </div>
            </div>
            <div class="benefit-item">
                <i class="fas fa-heart"></i>
                <div>
                    <h4>Wishlist</h4>
                    <p>Save items for later</p>
                </div>
            </div>
            <div class="benefit-item">
                <i class="fas fa-gift"></i>
                <div>
                    <h4>Rewards Program</h4>
                    <p>Earn points on every purchase</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/auth-validation.js"></script>

<?php
$page->renderFooter();
?>
