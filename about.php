<?php
session_start();

require_once 'config/Database.php';
require_once 'classes/Page.php';

$page = new Page("About Us - ElectroHub", "Learn more about ElectroHub and our mission", "about, electronics, company");

$page->renderHeader();
?>

<div class="about-page">
    <div class="container">
        <!-- Hero Section -->
        <div class="about-hero">
            <h1>About <span class="gradient-text">ElectroHub</span></h1>
            <p class="lead">Your trusted destination for premium electronic accessories</p>
        </div>

        <!-- Story Section -->
        <div class="about-section">
            <div class="section-content">
                <div class="content-text">
                    <h2>Our Story</h2>
                    <p>Founded with a passion for technology and innovation, ElectroHub has been serving customers with high-quality electronic accessories since our inception. We believe that the right accessories can enhance your digital lifestyle and make technology more accessible to everyone.</p>
                    <p>What started as a small venture has grown into a trusted name in the electronics industry, serving thousands of satisfied customers across the country.</p>
                </div>
                <div class="content-image">
                    <div class="image-placeholder">
                        <i class="fas fa-store"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mission Section -->
        <div class="about-section reverse">
            <div class="section-content">
                <div class="content-image">
                    <div class="image-placeholder">
                        <i class="fas fa-bullseye"></i>
                    </div>
                </div>
                <div class="content-text">
                    <h2>Our Mission</h2>
                    <p>At ElectroHub, our mission is to provide cutting-edge electronic accessories that combine quality, affordability, and innovation. We strive to:</p>
                    <ul>
                        <li><i class="fas fa-check-circle"></i> Offer premium products at competitive prices</li>
                        <li><i class="fas fa-check-circle"></i> Provide exceptional customer service</li>
                        <li><i class="fas fa-check-circle"></i> Stay ahead of technology trends</li>
                        <li><i class="fas fa-check-circle"></i> Build lasting relationships with our customers</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Values Section -->
        <div class="values-section">
            <h2>Our Values</h2>
            <div class="values-grid">
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-award"></i>
                    </div>
                    <h3>Quality First</h3>
                    <p>We carefully select and test every product to ensure it meets our high standards.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Customer Focus</h3>
                    <p>Your satisfaction is our priority. We're here to help you find the perfect products.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-lightbulb"></i>
                    </div>
                    <h3>Innovation</h3>
                    <p>We stay updated with the latest technology to bring you cutting-edge accessories.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>Trust & Security</h3>
                    <p>Shop with confidence knowing your data and transactions are secure.</p>
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="about-cta">
            <h2>Ready to Upgrade Your Tech?</h2>
            <p>Explore our collection of premium electronic accessories</p>
            <div class="cta-buttons">
                <a href="products.php" class="btn btn-primary">Shop Now</a>
                <a href="contact.php" class="btn btn-outline">Contact Us</a>
            </div>
        </div>
    </div>
</div>

<style>
.about-page {
    min-height: calc(100vh - 80px);
    padding: 2rem 0;
    background: var(--dark-bg);
}

.about-hero {
    text-align: center;
    padding: 4rem 0;
    margin-bottom: 4rem;
}

.about-hero h1 {
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

.lead {
    font-size: 1.25rem;
    color: var(--text-secondary);
}

.about-section {
    margin-bottom: 4rem;
}

.section-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4rem;
    align-items: center;
}

.about-section.reverse .section-content {
    direction: rtl;
}

.about-section.reverse .content-text {
    direction: ltr;
}

.content-text h2 {
    font-size: 2rem;
    margin-bottom: 1.5rem;
    color: var(--text-primary);
}

.content-text p {
    color: var(--text-secondary);
    line-height: 1.8;
    margin-bottom: 1rem;
    font-size: 1.1rem;
}

.content-text ul {
    list-style: none;
    padding: 0;
    margin: 1.5rem 0;
}

.content-text li {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.75rem 0;
    color: var(--text-secondary);
    font-size: 1.05rem;
}

.content-text li i {
    color: var(--success-color);
    font-size: 1.2rem;
}

.content-image {
    display: flex;
    justify-content: center;
    align-items: center;
}

.image-placeholder {
    width: 100%;
    max-width: 400px;
    aspect-ratio: 1;
    background: var(--dark-card);
    border: 1px solid var(--border-color);
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 6rem;
    color: var(--primary-color);
}

.values-section {
    padding: 4rem 0;
    text-align: center;
}

.values-section h2 {
    font-size: 2.5rem;
    margin-bottom: 3rem;
    color: var(--text-primary);
}

.values-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
}

.value-card {
    background: var(--dark-card);
    border: 1px solid var(--border-color);
    border-radius: 16px;
    padding: 2.5rem 2rem;
    transition: all 0.3s ease;
}

.value-card:hover {
    transform: translateY(-8px);
    border-color: var(--primary-color);
    box-shadow: 0 20px 40px rgba(99, 102, 241, 0.2);
}

.value-icon {
    width: 80px;
    height: 80px;
    background: var(--gradient-1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
    font-size: 2rem;
    color: white;
}

.value-card h3 {
    font-size: 1.5rem;
    margin-bottom: 1rem;
    color: var(--text-primary);
}

.value-card p {
    color: var(--text-secondary);
    line-height: 1.6;
}

.about-cta {
    text-align: center;
    padding: 4rem 2rem;
    background: var(--dark-card);
    border-radius: 20px;
    border: 1px solid var(--border-color);
    margin: 4rem 0;
}

.about-cta h2 {
    font-size: 2.5rem;
    margin-bottom: 1rem;
    color: var(--text-primary);
}

.about-cta p {
    font-size: 1.2rem;
    color: var(--text-secondary);
    margin-bottom: 2rem;
}

.cta-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

@media (max-width: 768px) {
    .about-hero h1 {
        font-size: 2rem;
    }
    
    .section-content {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    
    .about-section.reverse .section-content {
        direction: ltr;
    }
    
    .values-grid {
        grid-template-columns: 1fr;
    }
    
    .cta-buttons {
        flex-direction: column;
        align-items: center;
    }
    
    .cta-buttons .btn {
        width: 100%;
        max-width: 300px;
    }
}
</style>

<?php
$page->renderFooter();
?>
