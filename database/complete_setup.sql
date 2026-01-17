-- =============================================================================
-- PROGRESSIVE BAR - COMPLETE DATABASE SETUP SCRIPT
-- =============================================================================
-- Database: TiDB Cloud (MySQL-compatible)
-- Currency: Nigerian Naira (₦)
-- Generated: 2025-01-17
-- 
-- INSTRUCTIONS:
-- 1. Connect to TiDB Cloud console
-- 2. Select your database (test)
-- 3. Run this entire script
-- =============================================================================

-- =============================================================================
-- STEP 1: DROP EXISTING TABLES (Clean Slate)
-- =============================================================================
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS admins;
DROP TABLE IF EXISTS menu_items;

-- =============================================================================
-- STEP 2: CREATE TABLES
-- =============================================================================

-- Menu Items Table
-- IMPORTANT: id is VARCHAR(50) to match frontend IDs like "cocktail-001"
CREATE TABLE menu_items (
    id VARCHAR(50) PRIMARY KEY,
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
CREATE TABLE orders (
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
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'admin',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE INDEX idx_email (email)
);

-- =============================================================================
-- STEP 3: INSERT ADMIN USER
-- =============================================================================
-- Email: admin@progressivebar.com
-- Password: admin123
-- Password Hash generated with PHP password_hash('admin123', PASSWORD_BCRYPT)
INSERT INTO admins (email, password_hash, name, role) VALUES 
('admin@progressivebar.com', '$2y$12$EzzkNc8xdt/FjcmhnRQ5eOokU4zH2QA0w4VgO7/geSWFSMbySHUHS', 'Bar Admin', 'admin');

-- =============================================================================
-- STEP 4: INSERT MENU ITEMS (26 items - All prices in Nigerian Naira)
-- =============================================================================

-- ===== COCKTAILS (6 items) =====
INSERT INTO menu_items (id, name, description, price, category, image, available, preparation_time, tags) VALUES
('cocktail-001', 'Progressive Sunset', 'Tequila, blood orange, grapefruit, agave, chili rim', 8500.00, 'cocktails', 'https://images.unsplash.com/photo-1536935338788-846bb9981813?w=600&q=80', 1, 5, '["signature", "spicy", "tequila"]');

INSERT INTO menu_items (id, name, description, price, category, image, available, preparation_time, tags) VALUES
('cocktail-002', 'Midnight Reverie', 'Vodka, blue curaçao, butterfly pea flower, lime, elderflower', 7500.00, 'cocktails', 'https://images.unsplash.com/photo-1514362545857-3bc16c4c7d1b?w=600&q=80', 1, 5, '["signature", "vodka", "floral"]');

INSERT INTO menu_items (id, name, description, price, category, image, available, preparation_time, tags) VALUES
('cocktail-003', 'Smoked Old Fashioned', 'Bourbon, maple, aromatic bitters, orange peel, hickory smoke', 9500.00, 'cocktails', 'https://images.unsplash.com/photo-1470337458703-46ad1756a187?w=600&q=80', 1, 6, '["premium", "whiskey", "smoky"]');

INSERT INTO menu_items (id, name, description, price, category, image, available, preparation_time, tags) VALUES
('cocktail-004', 'Velvet Martini', 'Grey Goose, Lillet Blanc, lavender, lemon twist', 8500.00, 'cocktails', 'https://images.unsplash.com/photo-1575023782549-62ca0d244b39?w=600&q=80', 1, 4, '["classic", "vodka", "elegant"]');

INSERT INTO menu_items (id, name, description, price, category, image, available, preparation_time, tags) VALUES
('cocktail-005', 'Garden Mojito', 'Bacardi, fresh mint, cucumber, lime, soda', 6500.00, 'cocktails', 'https://images.unsplash.com/photo-1551538827-9c037cb4f32a?w=600&q=80', 1, 4, '["refreshing", "rum", "light"]');

INSERT INTO menu_items (id, name, description, price, category, image, available, preparation_time, tags) VALUES
('cocktail-006', 'Espresso Martini', 'Absolut, Kahlúa, fresh espresso, vanilla', 8000.00, 'cocktails', 'https://images.unsplash.com/photo-1545438102-799c3991ffb2?w=600&q=80', 1, 5, '["popular", "coffee", "vodka"]');

-- ===== PREMIUM (4 items) =====
INSERT INTO menu_items (id, name, description, price, category, image, available, preparation_time, tags) VALUES
('premium-001', 'Macallan 12yr', 'Single malt scotch, neat or on the rocks', 15000.00, 'premium', 'https://images.unsplash.com/photo-1527281400683-1aae777175f8?w=600&q=80', 1, 1, '["whiskey", "scotch", "neat"]');

INSERT INTO menu_items (id, name, description, price, category, image, available, preparation_time, tags) VALUES
('premium-002', 'Clase Azul Reposado', 'Premium aged tequila, served neat', 25000.00, 'premium', 'https://images.unsplash.com/photo-1569529465841-dfecdab7503b?w=600&q=80', 1, 1, '["tequila", "premium", "neat"]');

INSERT INTO menu_items (id, name, description, price, category, image, available, preparation_time, tags) VALUES
('premium-003', 'Hennessy XO', 'Extra old cognac with rich, complex flavors', 35000.00, 'premium', 'https://images.unsplash.com/photo-1619451050621-83cb7aada2d7?w=600&q=80', 1, 1, '["cognac", "premium", "aged"]');

INSERT INTO menu_items (id, name, description, price, category, image, available, preparation_time, tags) VALUES
('premium-004', 'Grey Goose Vodka', 'French wheat vodka, chilled or with your choice of mixer', 12000.00, 'premium', 'https://images.unsplash.com/photo-1607622750671-6cd9a99eabd1?w=600&q=80', 1, 1, '["vodka", "premium", "french"]');

-- ===== WINE (4 items) =====
INSERT INTO menu_items (id, name, description, price, category, image, available, preparation_time, tags) VALUES
('wine-001', 'Caymus Cabernet', 'Napa Valley, full-bodied red with bold flavors', 18000.00, 'wine', 'https://images.unsplash.com/photo-1510812431401-41d2bd2722f3?w=600&q=80', 1, 2, '["red", "california", "bold"]');

INSERT INTO menu_items (id, name, description, price, category, image, available, preparation_time, tags) VALUES
('wine-002', 'Whispering Angel Rosé', 'Provence, France - crisp and refreshing', 12000.00, 'wine', 'https://images.unsplash.com/photo-1558001373-7b93ee48ffa0?w=600&q=80', 1, 2, '["rosé", "french", "light"]');

INSERT INTO menu_items (id, name, description, price, category, image, available, preparation_time, tags) VALUES
('wine-003', 'Cloudy Bay Sauvignon', 'New Zealand white with citrus and tropical notes', 14000.00, 'wine', 'https://images.unsplash.com/photo-1566754436219-f1c066d8a0be?w=600&q=80', 1, 2, '["white", "new zealand", "crisp"]');

INSERT INTO menu_items (id, name, description, price, category, image, available, preparation_time, tags) VALUES
('wine-004', 'Veuve Clicquot', 'Champagne, France - elegant and celebratory', 85000.00, 'wine', 'https://images.unsplash.com/photo-1592483648228-b35146a4330c?w=600&q=80', 1, 2, '["champagne", "french", "celebration"]');

-- ===== BEER (4 items) =====
INSERT INTO menu_items (id, name, description, price, category, image, available, preparation_time, tags) VALUES
('beer-001', 'Craft IPA Flight', 'Four local IPA samples - hoppy and refreshing', 6500.00, 'beer', 'https://images.unsplash.com/photo-1535958636474-b021ee887b13?w=600&q=80', 1, 3, '["craft", "local", "flight"]');

INSERT INTO menu_items (id, name, description, price, category, image, available, preparation_time, tags) VALUES
('beer-002', 'Japanese Lager', 'Asahi Super Dry, ice cold and crisp', 3500.00, 'beer', 'https://images.unsplash.com/photo-1608270586620-248524c67de9?w=600&q=80', 1, 1, '["lager", "imported", "crisp"]');

INSERT INTO menu_items (id, name, description, price, category, image, available, preparation_time, tags) VALUES
('beer-003', 'Belgian Wheat', 'Hoegaarden with orange and coriander notes', 4000.00, 'beer', 'https://images.unsplash.com/photo-1571613316887-6f8d5cbf7ef7?w=600&q=80', 1, 1, '["wheat", "belgian", "citrus"]');

INSERT INTO menu_items (id, name, description, price, category, image, available, preparation_time, tags) VALUES
('beer-004', 'Guinness Draught', 'Irish stout with creamy texture and roasted flavor', 4500.00, 'beer', 'https://images.unsplash.com/photo-1594739797384-35ba3f8e3be0?w=600&q=80', 1, 2, '["stout", "irish", "creamy"]');

-- ===== SNACKS (5 items) =====
INSERT INTO menu_items (id, name, description, price, category, image, available, preparation_time, tags) VALUES
('snack-001', 'Truffle Fries', 'Hand-cut fries, truffle oil, parmesan, fresh herbs', 5500.00, 'snacks', 'https://images.unsplash.com/photo-1630384060421-cb20d0e0649d?w=600&q=80', 1, 8, '["vegetarian", "shareable"]');

INSERT INTO menu_items (id, name, description, price, category, image, available, preparation_time, tags) VALUES
('snack-002', 'Wagyu Sliders', 'Three mini wagyu burgers, caramelized onion, special sauce', 12000.00, 'snacks', 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=600&q=80', 1, 12, '["premium", "beef", "popular"]');

INSERT INTO menu_items (id, name, description, price, category, image, available, preparation_time, tags) VALUES
('snack-003', 'Charcuterie Board', 'Cured meats, artisan cheeses, olives, fresh bread', 15000.00, 'snacks', 'https://images.unsplash.com/photo-1626200419199-391ae4be7a41?w=600&q=80', 1, 5, '["shareable", "premium"]');

INSERT INTO menu_items (id, name, description, price, category, image, available, preparation_time, tags) VALUES
('snack-004', 'Crispy Calamari', 'Lightly fried, lemon aioli, marinara dipping sauce', 8500.00, 'snacks', 'https://images.unsplash.com/photo-1599487488170-d11ec9c172f0?w=600&q=80', 1, 10, '["seafood", "shareable"]');

INSERT INTO menu_items (id, name, description, price, category, image, available, preparation_time, tags) VALUES
('snack-005', 'Loaded Nachos', 'Tortilla chips, cheese, jalapeños, guacamole, sour cream', 7500.00, 'snacks', 'https://images.unsplash.com/photo-1513456852971-30c0b8199d4d?w=600&q=80', 1, 8, '["shareable", "spicy"]');

-- ===== MOCKTAILS (4 items) =====
INSERT INTO menu_items (id, name, description, price, category, image, available, preparation_time, tags) VALUES
('mocktail-001', 'Virgin Mojito', 'Fresh mint, lime, soda - refreshingly alcohol-free', 3500.00, 'mocktails', 'https://images.unsplash.com/photo-1622483767028-3f66f32aef97?w=600&q=80', 1, 3, '["non-alcoholic", "refreshing"]');

INSERT INTO menu_items (id, name, description, price, category, image, available, preparation_time, tags) VALUES
('mocktail-002', 'Sunset Spritz', 'Orange, grapefruit, sparkling water, grenadine', 4000.00, 'mocktails', 'https://images.unsplash.com/photo-1560508179-b2c9a3f8e92b?w=600&q=80', 1, 3, '["non-alcoholic", "citrus"]');

INSERT INTO menu_items (id, name, description, price, category, image, available, preparation_time, tags) VALUES
('mocktail-003', 'Berry Bliss', 'Mixed berries, lemon, mint, sparkling water', 4000.00, 'mocktails', 'https://images.unsplash.com/photo-1497534446932-c925b458314e?w=600&q=80', 1, 3, '["non-alcoholic", "fruity"]');

INSERT INTO menu_items (id, name, description, price, category, image, available, preparation_time, tags) VALUES
('mocktail-004', 'Ginger Fizz', 'Fresh ginger, lime, honey, soda water', 3500.00, 'mocktails', 'https://images.unsplash.com/photo-1544145945-f90425340c7e?w=600&q=80', 1, 3, '["non-alcoholic", "spicy"]');

-- =============================================================================
-- STEP 5: VERIFY DATA
-- =============================================================================
SELECT 'VERIFICATION: Tables Created' AS status;
SELECT COUNT(*) AS admin_count FROM admins;
SELECT COUNT(*) AS menu_item_count FROM menu_items;
SELECT id, name, price, category FROM menu_items ORDER BY category, id;

-- =============================================================================
-- SUMMARY
-- =============================================================================
-- Tables: 3 (menu_items, orders, admins)
-- Menu Items: 26 total
--   - Cocktails: 6 items (cocktail-001 to cocktail-006)
--   - Premium: 4 items (premium-001 to premium-004)
--   - Wine: 4 items (wine-001 to wine-004)
--   - Beer: 4 items (beer-001 to beer-004)
--   - Snacks: 5 items (snack-001 to snack-005)
--   - Mocktails: 4 items (mocktail-001 to mocktail-004)
-- Admin: 1 user (admin@progressivebar.com / admin123)
-- =============================================================================
