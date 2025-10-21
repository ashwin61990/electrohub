<?php
session_start();

require_once 'config/Database.php';
require_once 'classes/Page.php';

$page = new Page("Contact Us - ElectroHub", "Get in touch with us", "contact, support, help");

$message = '';
$messageType = '';

// Handle contact form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_contact'])) {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $messageText = $_POST['message'] ?? '';
    
    if (!empty($name) && !empty($email) && !empty($subject) && !empty($messageText)) {
        // In a real application, you would send an email or save to database
        $message = 'Thank you for contacting us! We will get back to you soon.';
        $messageType = 'success';
    } else {
        $message = 'Please fill in all fields.';
        $messageType = 'error';
    }
}

$page->renderHeader();
?>

<div class="contact-page">
    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <h1>Get in <span class="gradient-text">Touch</span></h1>
            <p>We'd love to hear from you. Send us a message and we'll respond as soon as possible.</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="contact-content">
            <!-- Contact Form -->
            <div class="contact-form-section">
                <h2>Send us a Message</h2>
                <form method="POST" action="" class="contact-form">
                    <div class="form-group">
                        <label for="name">Your Name *</label>
                        <input type="text" id="name" name="name" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Your Email *</label>
                        <input type="email" id="email" name="email" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="subject">Subject *</label>
                        <input type="text" id="subject" name="subject" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="message">Message *</label>
                        <textarea id="message" name="message" class="form-control" rows="6" required></textarea>
                    </div>

                    <button type="submit" name="submit_contact" class="btn btn-primary btn-block">
                        <i class="fas fa-paper-plane"></i> Send Message
                    </button>
                </form>
            </div>

            <!-- Contact Info -->
            <div class="contact-info-section">
                <h2>Contact Information</h2>
                
                <div class="info-cards">
                    <div class="info-card">
                        <div class="info-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="info-content">
                            <h3>Address</h3>
                            <p>123 Tech Street, Digital City<br>Mumbai, Maharashtra 400001<br>India</p>
                        </div>
                    </div>

                    <div class="info-card">
                        <div class="info-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="info-content">
                            <h3>Phone</h3>
                            <p>+91 234 567 8900<br>Mon-Sat: 9:00 AM - 6:00 PM</p>
                        </div>
                    </div>

                    <div class="info-card">
                        <div class="info-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="info-content">
                            <h3>Email</h3>
                            <p>info@electrohub.com<br>support@electrohub.com</p>
                        </div>
                    </div>

                    <div class="info-card">
                        <div class="info-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="info-content">
                            <h3>Business Hours</h3>
                            <p>Monday - Saturday<br>9:00 AM - 6:00 PM IST</p>
                        </div>
                    </div>
                </div>

                <div class="social-links">
                    <h3>Follow Us</h3>
                    <div class="social-icons">
                        <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
            </div>
        </div>

        <!-- FAQ Section -->
        <div class="faq-section">
            <h2>Frequently Asked Questions</h2>
            <div class="faq-grid">
                <div class="faq-item">
                    <h3><i class="fas fa-question-circle"></i> What are your shipping options?</h3>
                    <p>We offer free shipping on orders over ₹500. Standard delivery takes 3-5 business days.</p>
                </div>
                <div class="faq-item">
                    <h3><i class="fas fa-question-circle"></i> What is your return policy?</h3>
                    <p>We offer a 30-day money-back guarantee on all products. Items must be in original condition.</p>
                </div>
                <div class="faq-item">
                    <h3><i class="fas fa-question-circle"></i> Do you offer warranty?</h3>
                    <p>Yes, all products come with manufacturer warranty. Duration varies by product.</p>
                </div>
                <div class="faq-item">
                    <h3><i class="fas fa-question-circle"></i> How can I track my order?</h3>
                    <p>You'll receive a tracking number via email once your order ships. You can also check your account.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.contact-page {
    min-height: calc(100vh - 80px);
    padding: 2rem 0;
    background: var(--dark-bg);
}

.page-header {
    text-align: center;
    margin-bottom: 3rem;
}

.page-header h1 {
    font-size: 3rem;
    margin-bottom: 1rem;
    color: var(--text-primary);
}

.gradient-text {
    background: var(--gradient-1);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.page-header p {
    font-size: 1.2rem;
    color: var(--text-secondary);
}

.alert {
    padding: 1rem 1.5rem;
    border-radius: 8px;
    margin-bottom: 2rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.alert-success {
    background: rgba(16, 185, 129, 0.1);
    border: 1px solid var(--success-color);
    color: var(--success-color);
}

.alert-error {
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid var(--danger-color);
    color: var(--danger-color);
}

.contact-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 3rem;
    margin-bottom: 4rem;
}

.contact-form-section,
.contact-info-section {
    background: var(--dark-card);
    border: 1px solid var(--border-color);
    border-radius: 16px;
    padding: 2.5rem;
}

.contact-form-section h2,
.contact-info-section h2 {
    font-size: 1.75rem;
    margin-bottom: 2rem;
    color: var(--text-primary);
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

.form-control {
    width: 100%;
    padding: 0.875rem;
    background: var(--dark-bg);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    color: var(--text-primary);
    font-size: 1rem;
    transition: border-color 0.2s;
    font-family: inherit;
}

.form-control:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

textarea.form-control {
    resize: vertical;
    min-height: 150px;
}

.btn-block {
    width: 100%;
}

.info-cards {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.info-card {
    display: flex;
    gap: 1.5rem;
    padding: 1.5rem;
    background: var(--dark-bg);
    border-radius: 12px;
    border: 1px solid var(--border-color);
    transition: all 0.3s ease;
}

.info-card:hover {
    border-color: var(--primary-color);
    transform: translateX(5px);
}

.info-icon {
    width: 50px;
    height: 50px;
    background: var(--gradient-1);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
    flex-shrink: 0;
}

.info-content h3 {
    font-size: 1.1rem;
    margin-bottom: 0.5rem;
    color: var(--text-primary);
}

.info-content p {
    color: var(--text-secondary);
    line-height: 1.6;
    margin: 0;
}

.social-links {
    text-align: center;
    padding-top: 2rem;
    border-top: 1px solid var(--border-color);
}

.social-links h3 {
    font-size: 1.25rem;
    margin-bottom: 1rem;
    color: var(--text-primary);
}

.social-icons {
    display: flex;
    gap: 1rem;
    justify-content: center;
}

.social-icon {
    width: 45px;
    height: 45px;
    background: var(--dark-bg);
    border: 1px solid var(--border-color);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-secondary);
    text-decoration: none;
    transition: all 0.3s ease;
}

.social-icon:hover {
    background: var(--primary-color);
    border-color: var(--primary-color);
    color: white;
    transform: translateY(-3px);
}

.faq-section {
    background: var(--dark-card);
    border: 1px solid var(--border-color);
    border-radius: 16px;
    padding: 3rem;
}

.faq-section h2 {
    font-size: 2rem;
    margin-bottom: 2rem;
    text-align: center;
    color: var(--text-primary);
}

.faq-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
}

.faq-item {
    padding: 1.5rem;
    background: var(--dark-bg);
    border-radius: 12px;
    border: 1px solid var(--border-color);
}

.faq-item h3 {
    font-size: 1.1rem;
    margin-bottom: 0.75rem;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.faq-item h3 i {
    color: var(--primary-color);
}

.faq-item p {
    color: var(--text-secondary);
    line-height: 1.6;
    margin: 0;
}

@media (max-width: 968px) {
    .contact-content {
        grid-template-columns: 1fr;
    }
    
    .page-header h1 {
        font-size: 2rem;
    }
    
    .faq-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php
$page->renderFooter();
?>
