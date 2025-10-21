<?php

class Page {
    private $title;
    private $description;
    private $keywords;

    public function __construct($title = "ElectroHub - Premium Electronic Accessories", $description = "", $keywords = "") {
        $this->title = $title;
        $this->description = $description;
        $this->keywords = $keywords;
    }

    public function renderHeader() {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta name="description" content="<?php echo htmlspecialchars($this->description); ?>">
            <meta name="keywords" content="<?php echo htmlspecialchars($this->keywords); ?>">
            <title><?php echo htmlspecialchars($this->title); ?></title>
            <link rel="stylesheet" href="assets/css/style.css">
            <link rel="stylesheet" href="assets/css/auth.css">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
            <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
        </head>
        <body>
            <nav class="navbar">
                <div class="container">
                    <div class="nav-wrapper">
                        <div class="logo">
                            <i class="fas fa-bolt"></i>
                            <span>ElectroHub</span>
                        </div>
                        <ul class="nav-menu">
                            <li><a href="index.php" class="active">Home</a></li>
                            <li><a href="products.php">Products</a></li>
                            <li><a href="categories.php">Categories</a></li>
                            <li><a href="about.php">About</a></li>
                            <li><a href="contact.php">Contact</a></li>
                        </ul>
                        <div class="nav-icons">
                            <a href="search.php" class="icon-btn"><i class="fas fa-search"></i></a>
                            <a href="cart.php" class="icon-btn">
                                <i class="fas fa-shopping-cart"></i>
                                <span class="badge">0</span>
                            </a>
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <div class="user-dropdown">
                                    <button class="icon-btn dropdown-toggle" title="<?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Account'); ?>">
                                        <i class="fas fa-user"></i>
                                    </button>
                                    <div class="dropdown-menu">
                                        <div class="dropdown-header">
                                            <strong><?php echo htmlspecialchars($_SESSION['full_name'] ?? 'User'); ?></strong>
                                            <small><?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?></small>
                                        </div>
                                        <a href="account.php" class="dropdown-item">
                                            <i class="fas fa-user"></i> My Account
                                        </a>
                                        <a href="orders.php" class="dropdown-item">
                                            <i class="fas fa-shopping-bag"></i> My Orders
                                        </a>
                                        <a href="wishlist.php" class="dropdown-item">
                                            <i class="fas fa-heart"></i> Wishlist
                                        </a>
                                        <div class="dropdown-divider"></div>
                                        <a href="logout.php" class="dropdown-item logout">
                                            <i class="fas fa-sign-out-alt"></i> Logout
                                        </a>
                                    </div>
                                </div>
                            <?php else: ?>
                                <a href="login.php" class="icon-btn" title="Login">
                                    <i class="fas fa-sign-in-alt"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                        <div class="hamburger">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                    </div>
                </div>
            </nav>
        <?php
    }

    public function renderFooter() {
        ?>
            <footer class="footer">
                <div class="container">
                    <div class="footer-content">
                        <div class="footer-section">
                            <h3><i class="fas fa-bolt"></i> ElectroHub</h3>
                            <p>Your one-stop shop for premium electronic accessories. Quality products at competitive prices.</p>
                            <div class="social-links">
                                <a href="#"><i class="fab fa-facebook"></i></a>
                                <a href="#"><i class="fab fa-twitter"></i></a>
                                <a href="#"><i class="fab fa-instagram"></i></a>
                                <a href="#"><i class="fab fa-youtube"></i></a>
                            </div>
                        </div>
                        <div class="footer-section">
                            <h4>Quick Links</h4>
                            <ul>
                                <li><a href="about.php">About Us</a></li>
                                <li><a href="products.php">Products</a></li>
                                <li><a href="contact.php">Contact</a></li>
                                <li><a href="faq.php">FAQ</a></li>
                            </ul>
                        </div>
                        <div class="footer-section">
                            <h4>Customer Service</h4>
                            <ul>
                                <li><a href="shipping.php">Shipping Info</a></li>
                                <li><a href="returns.php">Returns</a></li>
                                <li><a href="warranty.php">Warranty</a></li>
                                <li><a href="support.php">Support</a></li>
                            </ul>
                        </div>
                        <div class="footer-section">
                            <h4>Contact Us</h4>
                            <ul class="contact-info">
                                <li><i class="fas fa-phone"></i> +1 234 567 8900</li>
                                <li><i class="fas fa-envelope"></i> info@electrohub.com</li>
                                <li><i class="fas fa-map-marker-alt"></i> 123 Tech Street, Digital City</li>
                            </ul>
                        </div>
                    </div>
                    <div class="footer-bottom">
                        <p>&copy; <?php echo date('Y'); ?> ElectroHub. All rights reserved.</p>
                    </div>
                </div>
            </footer>
            <script src="assets/js/main.js"></script>
        </body>
        </html>
        <?php
    }
}
