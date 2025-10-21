-- Create Database
CREATE DATABASE IF NOT EXISTS electronics_store;
USE electronics_store;

-- Categories Table
CREATE TABLE IF NOT EXISTS categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    icon VARCHAR(50),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products Table
CREATE TABLE IF NOT EXISTS products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    image VARCHAR(255),
    category VARCHAR(100),
    stock INT DEFAULT 0,
    rating DECIMAL(2, 1) DEFAULT 0,
    featured TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert Sample Categories
INSERT INTO categories (name, icon, description) VALUES
('Audio', 'fa-headphones', 'Premium audio accessories including headphones, earbuds, and speakers'),
('Chargers', 'fa-charging-station', 'Fast charging solutions for all your devices'),
('Cables', 'fa-plug', 'High-quality cables for data transfer and charging'),
('Accessories', 'fa-laptop', 'Essential accessories for your electronic devices');

-- Insert Sample Products
INSERT INTO products (name, description, price, image, category, stock, rating, featured) VALUES
('Wireless Earbuds Pro', 'Premium wireless earbuds with active noise cancellation and 24-hour battery life', 79.99, 'earbuds.jpg', 'Audio', 50, 4.5, 1),
('Fast Charging Cable', 'USB-C to USB-C cable with 100W power delivery support', 19.99, 'cable.jpg', 'Cables', 100, 4.8, 1),
('Portable Power Bank', '20000mAh power bank with dual USB ports and fast charging', 49.99, 'powerbank.jpg', 'Chargers', 75, 4.6, 1),
('Bluetooth Speaker', 'Waterproof portable speaker with 360-degree sound', 89.99, 'speaker.jpg', 'Audio', 40, 4.7, 1),
('Wireless Mouse', 'Ergonomic wireless mouse with precision tracking', 29.99, 'mouse.jpg', 'Accessories', 60, 4.4, 1),
('USB-C Hub', '7-in-1 USB-C hub with HDMI, USB 3.0, and SD card reader', 39.99, 'hub.jpg', 'Accessories', 45, 4.5, 1),
('Smart Watch Charger', 'Magnetic charging dock for smart watches', 24.99, 'watch-charger.jpg', 'Chargers', 80, 4.3, 1),
('Gaming Headset', 'Professional gaming headset with 7.1 surround sound', 129.99, 'headset.jpg', 'Audio', 30, 4.9, 1),
('Lightning Cable', 'MFi certified lightning cable with braided design', 15.99, 'lightning.jpg', 'Cables', 120, 4.7, 0),
('Wireless Charger', 'Qi-certified wireless charging pad with fast charge', 34.99, 'wireless-charger.jpg', 'Chargers', 55, 4.6, 0),
('Phone Stand', 'Adjustable aluminum phone stand for desk', 22.99, 'phone-stand.jpg', 'Accessories', 90, 4.5, 0),
('HDMI Cable', '4K HDMI 2.1 cable with high-speed ethernet', 18.99, 'hdmi.jpg', 'Cables', 110, 4.8, 0),
('Webcam HD', '1080p HD webcam with built-in microphone', 59.99, 'webcam.jpg', 'Accessories', 35, 4.4, 0),
('Laptop Sleeve', 'Premium leather laptop sleeve with magnetic closure', 44.99, 'laptop-sleeve.jpg', 'Accessories', 65, 4.6, 0),
('Car Charger', 'Dual USB car charger with quick charge 3.0', 16.99, 'car-charger.jpg', 'Chargers', 95, 4.5, 0),
('Keyboard Wireless', 'Slim wireless keyboard with rechargeable battery', 54.99, 'keyboard.jpg', 'Accessories', 42, 4.7, 0);

-- Users Table (Enhanced for authentication)
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(100),
    country VARCHAR(100),
    postal_code VARCHAR(20),
    is_active TINYINT(1) DEFAULT 1,
    email_verified TINYINT(1) DEFAULT 0,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_username (username)
);

-- Orders Table (for future e-commerce functionality)
CREATE TABLE IF NOT EXISTS orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    total_amount DECIMAL(10, 2) NOT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Order Items Table
CREATE TABLE IF NOT EXISTS order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT,
    product_id INT,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Cart Table
CREATE TABLE IF NOT EXISTS cart (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    product_id INT,
    quantity INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);
