-- Progressive Bar Database Schema for MySQL (MAMP)
-- Run this in phpMyAdmin or MySQL CLI

CREATE DATABASE IF NOT EXISTS progressive_bar;
USE progressive_bar;

-- Menu Items Table
CREATE TABLE IF NOT EXISTS menu_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    category VARCHAR(100) NOT NULL,
    image VARCHAR(500),
    available TINYINT(1) DEFAULT 1,
    preparation_time INT DEFAULT 5,
    tags JSON,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_available (available)
);

-- Orders Table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    table_number VARCHAR(10) NOT NULL,
    items JSON NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    tax DECIMAL(10, 2) NOT NULL,
    total DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'confirmed', 'preparing', 'ready', 'delivered', 'cancelled') DEFAULT 'pending',
    estimated_wait_time INT DEFAULT 5,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_table_number (table_number),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
);

-- Admins Table
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'admin',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE INDEX idx_email (email)
);

-- Insert default admin user (password: admin123)
INSERT INTO admins (email, password_hash, name, role) VALUES 
('admin@progressivebar.com', '$2y$12$EzzkNc8xdt/FjcmhnRQ5eOokU4zH2QA0w4VgO7/geSWFSMbySHUHS', 'Bar Admin', 'admin');

-- Insert sample menu items
INSERT INTO menu_items (name, description, price, category, image, available, preparation_time, tags) VALUES
-- Cocktails
('Progressive Sunset', 'Tequila, blood orange, grapefruit, agave, chili rim', 16.00, 'cocktails', '/images/sunset.jpg', 1, 5, '["signature", "spicy", "tequila"]'),
('Midnight Reverie', 'Vodka, blue curaçao, butterfly pea flower, lime, elderflower', 15.00, 'cocktails', '/images/midnight.jpg', 1, 5, '["signature", "vodka", "floral"]'),
('Smoked Old Fashioned', 'Bourbon, maple, aromatic bitters, orange peel, hickory smoke', 18.00, 'cocktails', '/images/old-fashioned.jpg', 1, 6, '["premium", "whiskey", "smoky"]'),
('Velvet Martini', 'Grey Goose, Lillet Blanc, lavender, lemon twist', 17.00, 'cocktails', '/images/martini.jpg', 1, 4, '["classic", "vodka", "elegant"]'),
('Garden Mojito', 'Bacardi, fresh mint, cucumber, lime, soda', 14.00, 'cocktails', '/images/mojito.jpg', 1, 4, '["refreshing", "rum", "light"]'),
('Espresso Martini', 'Absolut, Kahlúa, fresh espresso, vanilla', 16.00, 'cocktails', '/images/espresso-martini.jpg', 1, 5, '["popular", "coffee", "vodka"]'),

-- Premium
('Macallan 12yr', 'Single malt scotch, neat or on the rocks', 22.00, 'premium', '/images/macallan.jpg', 1, 1, '["whiskey", "scotch", "neat"]'),
('Clase Azul Reposado', 'Premium aged tequila, served neat', 32.00, 'premium', '/images/clase-azul.jpg', 1, 1, '["tequila", "premium", "neat"]'),

-- Wine
('Caymus Cabernet', 'Napa Valley, full-bodied red', 28.00, 'wine', '/images/caymus.jpg', 1, 2, '["red", "california", "bold"]'),
('Whispering Angel Rosé', 'Provence, France - crisp and refreshing', 16.00, 'wine', '/images/rose.jpg', 1, 2, '["rosé", "french", "light"]'),

-- Beer
('Craft IPA Flight', 'Four local IPA samples', 14.00, 'beer', '/images/ipa-flight.jpg', 1, 3, '["craft", "local", "flight"]'),
('Japanese Lager', 'Asahi Super Dry, ice cold', 8.00, 'beer', '/images/asahi.jpg', 1, 1, '["lager", "imported", "crisp"]'),

-- Snacks
('Truffle Fries', 'Hand-cut fries, truffle oil, parmesan, herbs', 12.00, 'snacks', '/images/truffle-fries.jpg', 1, 8, '["vegetarian", "shareable"]'),
('Wagyu Sliders', 'Three mini wagyu burgers, caramelized onion, special sauce', 22.00, 'snacks', '/images/sliders.jpg', 1, 12, '["premium", "beef", "popular"]'),
('Charcuterie Board', 'Cured meats, artisan cheeses, olives, bread', 28.00, 'snacks', '/images/charcuterie.jpg', 1, 5, '["shareable", "premium"]'),
('Crispy Calamari', 'Lightly fried, lemon aioli, marinara', 16.00, 'snacks', '/images/calamari.jpg', 1, 10, '["seafood", "shareable"]'),

-- Mocktails
('Virgin Mojito', 'Fresh mint, lime, soda, no alcohol', 8.00, 'mocktails', '/images/virgin-mojito.jpg', 1, 3, '["non-alcoholic", "refreshing"]'),
('Sunset Spritz', 'Orange, grapefruit, sparkling water, no alcohol', 9.00, 'mocktails', '/images/sunset-spritz.jpg', 1, 3, '["non-alcoholic", "citrus"]');
