-- A2Z Handicraft Database Schema & Seed Data
-- Database: a2z_handicraft

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------
-- Table structure for `users` (Admin Authentication)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(60) NOT NULL UNIQUE,
  `email` VARCHAR(120) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(30) DEFAULT NULL,
  `role` ENUM('admin', 'manager', 'editor') NOT NULL DEFAULT 'admin',
  `avatar` VARCHAR(255) DEFAULT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `failed_attempts` INT NOT NULL DEFAULT 0,
  `lockout_until` DATETIME DEFAULT NULL,
  `last_login_at` DATETIME DEFAULT NULL,
  `last_login_ip` VARCHAR(45) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `site_settings`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `site_settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `key_name` VARCHAR(100) NOT NULL UNIQUE,
  `value` LONGTEXT DEFAULT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `banners`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `banners` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `subtitle` VARCHAR(255) DEFAULT NULL,
  `tagline` VARCHAR(255) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `image` VARCHAR(255) NOT NULL,
  `button_text` VARCHAR(100) DEFAULT 'Show All Products',
  `button_link` VARCHAR(255) DEFAULT 'categories.php',
  `rating_text` VARCHAR(100) DEFAULT '4.9/5 Review',
  `sort_order` INT NOT NULL DEFAULT 0,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `categories`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `slug` VARCHAR(150) NOT NULL UNIQUE,
  `image` VARCHAR(255) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `sort_order` INT NOT NULL DEFAULT 0,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  -- Yoast SEO Fields
  `meta_title` VARCHAR(255) DEFAULT NULL,
  `meta_description` TEXT DEFAULT NULL,
  `meta_keywords` VARCHAR(255) DEFAULT NULL,
  `canonical_url` VARCHAR(255) DEFAULT NULL,
  `og_title` VARCHAR(255) DEFAULT NULL,
  `og_description` TEXT DEFAULT NULL,
  `og_image` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `subcategories`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `subcategories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `slug` VARCHAR(150) NOT NULL UNIQUE,
  `image` VARCHAR(255) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  -- Yoast SEO Fields
  `meta_title` VARCHAR(255) DEFAULT NULL,
  `meta_description` TEXT DEFAULT NULL,
  `meta_keywords` VARCHAR(255) DEFAULT NULL,
  `canonical_url` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX (`category_id`),
  CONSTRAINT `fk_subcat_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `products`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT NOT NULL,
  `subcategory_id` INT DEFAULT NULL,
  `name` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL UNIQUE,
  `sku` VARCHAR(100) DEFAULT NULL,
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `sale_price` DECIMAL(10,2) DEFAULT NULL,
  `sizes_pricing` TEXT DEFAULT NULL,
  `short_description` TEXT DEFAULT NULL,
  `description` LONGTEXT DEFAULT NULL,
  `featured_image` VARCHAR(255) DEFAULT NULL,
  `gallery_images` LONGTEXT DEFAULT NULL,
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `is_bestseller` TINYINT(1) NOT NULL DEFAULT 0,
  `in_stock` TINYINT(1) NOT NULL DEFAULT 1,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `views` INT NOT NULL DEFAULT 0,
  -- Yoast SEO Fields
  `meta_title` VARCHAR(255) DEFAULT NULL,
  `meta_description` TEXT DEFAULT NULL,
  `meta_keywords` VARCHAR(255) DEFAULT NULL,
  `canonical_url` VARCHAR(255) DEFAULT NULL,
  `og_title` VARCHAR(255) DEFAULT NULL,
  `og_description` TEXT DEFAULT NULL,
  `og_image` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX (`category_id`),
  INDEX (`subcategory_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `leads` (Contact & Inquiries)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `leads` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(50) NOT NULL,
  `subject` VARCHAR(255) DEFAULT NULL,
  `message` TEXT NOT NULL,
  `product_id` INT DEFAULT NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `status` ENUM('new', 'contacted', 'resolved', 'spam') NOT NULL DEFAULT 'new',
  `notes` TEXT DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `testimonials`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `testimonials` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `designation` VARCHAR(150) DEFAULT NULL,
  `rating` INT NOT NULL DEFAULT 5,
  `content` TEXT NOT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `reviews`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `reviews` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT DEFAULT NULL,
  `author_name` VARCHAR(150) NOT NULL,
  `author_email` VARCHAR(150) DEFAULT NULL,
  `rating` INT NOT NULL DEFAULT 5,
  `title` VARCHAR(255) DEFAULT NULL,
  `comment` TEXT NOT NULL,
  `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'approved',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `faqs`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `faqs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `question` VARCHAR(255) NOT NULL,
  `answer` TEXT NOT NULL,
  `category` VARCHAR(100) DEFAULT 'General',
  `sort_order` INT NOT NULL DEFAULT 0,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `about_section`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `about_section` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `subtitle` VARCHAR(255) DEFAULT 'About Us',
  `image` VARCHAR(255) DEFAULT 'images/about.jpeg',
  `content_p1` TEXT DEFAULT NULL,
  `content_p2` TEXT DEFAULT NULL,
  `content_p3` TEXT DEFAULT NULL,
  `experience_years` VARCHAR(50) DEFAULT '15+ Years',
  `happy_customers` VARCHAR(50) DEFAULT '10,000+',
  `products_count` VARCHAR(50) DEFAULT '500+',
  `craftsmen_count` VARCHAR(50) DEFAULT '50+',
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `login_attempts` (Rate Limiting)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `ip_address` VARCHAR(45) NOT NULL,
  `username` VARCHAR(100) NOT NULL,
  `attempted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (`ip_address`, `attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `activity_logs` (Audit Trail)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT DEFAULT NULL,
  `action` VARCHAR(100) NOT NULL,
  `details` TEXT DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- --------------------------------------------------------
-- Initial Seed Data
-- --------------------------------------------------------

-- Admin User: admin / admin123
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `phone`, `role`, `status`)
VALUES (1, 'admin', 'admin@a2zhandicraft.com', '$2y$10$DXBGb1d.UbwTpoSKdo4NV.yIEpikBusaCMY1qpfgRj0033dbXqdmu', 'Administrator', '+91-7983894672', 'admin', 1)
ON DUPLICATE KEY UPDATE `username` = `username`;

-- Site Settings
INSERT INTO `site_settings` (`key_name`, `value`) VALUES
('site_name', 'A2Z Handicraft'),
('site_tagline', 'Elevate Your Style With Handmade Fashion'),
('site_logo', 'images/logo.png'),
('site_favicon', 'images/logo.png'),
('footer_logo', 'images/footer-logo.png'),
('phone', '+91-7983894672'),
('whatsapp', '917983894672'),
('email', 'a2zhandicraft@gmail.com'),
('address', 'Main Hapur Road, Arifpur Saravni, Distt. Hapur, UP - 245101'),
('topbar_info', 'Exclusive Best Handmade Products - Your Perfect Choice!'),
('topbar_whatsapp_text', 'Bulk Order Now: +91-7983894672'),
('facebook_url', 'https://facebook.com/'),
('instagram_url', 'https://instagram.com/'),
('youtube_url', 'https://youtube.com/'),
('google_map_iframe', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3321.7140488191794!2d77.9146990755088!3d28.850016875546647!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x390c81006e5b797d%3A0x7f2601683ef1701!2sArifpur%20sarawini%20plant!5e1!3m2!1sen!2sin!4v1791610367406!5m2!1sen!2sin'),
('copyright_text', 'Copyright © 2026 A2Z HANDICRAFT. All Rights Reserved!'),
('meta_title', 'A2Z Handicraft - Handmade Fashion, Home & Event Decor Manufacturer'),
('meta_description', 'A2Z Handicraft is a trusted manufacturer and supplier of handcrafted decorative products, offering a wide range of beautifully designed items.'),
('meta_keywords', 'handicraft, wedding decor, event decoration, RDI hanging, dream catcher, flower bunch, fancy toran')
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);

-- Banner
INSERT INTO `banners` (`id`, `title`, `subtitle`, `tagline`, `description`, `image`, `button_text`, `button_link`, `rating_text`, `sort_order`, `status`)
VALUES (1, 'Elevate Your Style With Handmade Fashion', 'A2Z HANDICRAFT', 'Premium Quality Materials | Affordable Collection', 'A2Z Handicraft is a trusted manufacturer and supplier of handcrafted decorative products, offering a wide range of beautifully designed items for homes, offices, hotels, events, and interior décor.', 'images/banner.png', 'Show All Products', 'categories.php', '4.9/5 Review', 1, 1)
ON DUPLICATE KEY UPDATE `id` = `id`;

-- Categories
INSERT INTO `categories` (`id`, `name`, `slug`, `image`, `description`, `is_featured`, `sort_order`, `status`, `meta_title`, `meta_description`, `meta_keywords`) VALUES
(1, 'RDI Hanging', 'rdi-hanging', 'images/ca1.jpg', 'Exclusive handmade RDI Hanging decor for weddings, homes, and events.', 1, 1, 1, 'RDI Hanging - Handcrafted Wedding & Event Decor | A2Z Handicraft', 'Shop premium quality RDI Hanging decorative crafts at wholesale factory prices from A2Z Handicraft.', 'rdi hanging, wedding hanging, event decor'),
(2, 'Iron Frame', 'iron-frame', 'images/ca2.webp', 'Sturdy and ornate handcrafted iron frames for floral and decorative backdrops.', 1, 2, 1, 'Handcrafted Iron Frames - A2Z Handicraft', 'Durable decorative iron frames suitable for stage decor, floral installations and backdrops.', 'iron frame, backdrop frame, stage decor'),
(3, 'Artificial Flower Patta', 'artificial-flower-patta', 'images/ca3.jpeg', 'Vibrant artificial flower patta and leaf strings for decoration.', 1, 3, 1, 'Artificial Flower Patta Collection | A2Z Handicraft', 'High quality realistic artificial flower patta for festive and wedding styling.', 'flower patta, artificial leaves, floral decor'),
(4, 'Dream Catcher', 'dream-catcher', 'images/ca4.webp', 'Artistic handcrafted dream catchers in exquisite boho and traditional styles.', 1, 4, 1, 'Boho & Traditional Dream Catchers | A2Z Handicraft', 'Unique handcrafted dream catchers made with premium threads, beads and feathers.', 'dream catcher, boho decor, wall hanging'),
(5, 'Flower Bunch', 'flower-bunch', 'images/ca5.webp', 'Rich floral bunches crafted for vases, stage setups and festive gifting.', 1, 5, 1, 'Handmade Flower Bunches | A2Z Handicraft', 'Explore colorful floral bunches crafted with elegance for decor and gifts.', 'flower bunch, artificial flowers, bouquet decor'),
(6, 'Net Line Hanging', 'net-line-hanging', 'images/ca6.webp', 'Delicate net line hangings designed for ceilings, walkways, and stages.', 1, 6, 1, 'Net Line Hanging Decor | A2Z Handicraft', 'Lightweight net line hangings perfect for wedding entrance and canopy setups.', 'net line hanging, canopy decor'),
(7, 'Ring Hanging', 'ring-hanging', 'images/ca7.webp', 'Round circular floral and metallic ring hangings for elegant backdrops.', 1, 7, 1, 'Circular Ring Hanging Decor | A2Z Handicraft', 'Stylish circular ring hangings for modern festive and party decorations.', 'ring hanging, circular hoop decor'),
(8, 'Fancy Toran', 'fancy-toran', 'images/ca8.jpg', 'Traditional handcrafted door torans for Diwali, weddings, and housewarmings.', 1, 8, 1, 'Handcrafted Fancy Torans for Door & Walls | A2Z Handicraft', 'Welcome positivity and elegance with handcrafted traditional door torans.', 'fancy toran, door hanging, diwali toran')
ON DUPLICATE KEY UPDATE `id` = `id`;

-- Subcategories
INSERT INTO `subcategories` (`id`, `category_id`, `name`, `slug`, `image`, `description`, `sort_order`, `status`, `meta_title`, `meta_description`) VALUES
(1, 1, 'Kite Hanging', 'kite-hanging', 'images/ca1.jpg', 'Kite shaped decorative hangings for haldi and mehendi setups.', 1, 1, 'Kite Hanging Decor', 'Buy handcrafted kite hangings for festive occasions.'),
(2, 1, 'Net Chakra Hanging', 'net-chakra-hanging', 'images/ca6.webp', 'Chakra designed net hangings for ceiling decor.', 2, 1, 'Net Chakra Hanging', 'Artistic net chakra hangings for events.'),
(3, 1, 'Tokri Jumer', 'tokri-jumer', 'images/ca8.jpg', 'Basket style traditional jumer chandeliers.', 3, 1, 'Tokri Jumer Handicrafts', 'Traditional basket jumer for grand venue decor.'),
(4, 7, 'Artificial Ring Hanging', 'artificial-ring-hanging', 'images/ca2.webp', 'Rings decorated with artificial flowers and bells.', 1, 1, 'Artificial Ring Hanging', 'Floral ring hangings for celebrations.'),
(5, 3, 'Genda Lines', 'genda-lines', 'images/ca5.webp', 'Marigold garland strings in multiple lengths.', 1, 1, 'Marigold Genda Lines', 'Festive marigold garland strings for backdrop.')
ON DUPLICATE KEY UPDATE `id` = `id`;

-- Products
INSERT INTO `products` (`id`, `category_id`, `subcategory_id`, `name`, `slug`, `sku`, `price`, `sale_price`, `sizes_pricing`, `short_description`, `description`, `featured_image`, `gallery_images`, `is_featured`, `is_bestseller`, `in_stock`, `status`, `meta_title`, `meta_description`, `meta_keywords`) VALUES
(1, 1, 1, 'RDI-01 Kite Hanging', 'rdi-01-kite-hanging', 'RDI-01', 300.00, 350.00, '11×7 Inches = ₹300\n13×9 Inches = ₹360\n16×12 Inches = ₹420\n19×15 Inches = ₹540\n22×18 Inches= ₹660\n25×21 Inches = ₹780', 'Handcrafted kite hanging decor with colorful tassels and intricate handiwork.', 'The RDI-01 Kite Hanging is a beautifully crafted piece that embodies grace, simplicity, and everlasting charm. Designed with a sleek and modern touch, it enhances your natural style while adding a subtle hint of luxury. Perfect for everyday wear, wedding setups, or special festive occasions. Made with high-quality, durable materials that retain their brilliance over time.', 'images/ca1.jpg', '[\"images/ca1.jpg\",\"images/ca2.webp\",\"images/ca3.jpeg\"]', 1, 1, 1, 1, 'RDI-01 Kite Hanging - Handcrafted Decor | A2Z Handicraft', 'Buy RDI-01 Kite Hanging at best price from A2Z Handicraft. Available in multiple sizes.', 'kite hanging, rdi hanging, festive decor'),
(2, 7, 4, 'Ring Hanging Floral Chandelier', 'ring-hanging-floral-chandelier', 'RNG-02', 250.00, 290.00, '10 Inch = ₹250\n12 Inch = ₹320\n15 Inch = ₹450\n18 Inch = ₹600', 'Elegant circular ring hanging crafted with premium faux flowers and beads.', 'Our Ring Hanging Floral Chandelier is intricately assembled by skilled artisans. Lightweight, easy to hang and creates an enchanting visual atmosphere for weddings, stages, walkways, or home living rooms.', 'images/p1.jpeg', '[\"images/p1.jpeg\",\"images/ca7.webp\"]', 1, 1, 1, 1, 'Ring Hanging Floral Chandelier | A2Z Handicraft', 'Gorgeous ring hanging floral chandelier for events and home decor.', 'ring hanging, floral chandelier, circular decor'),
(3, 7, 4, 'Artificial Ring Hanging Royal Gold', 'artificial-ring-hanging-royal-gold', 'ARH-03', 280.00, 320.00, 'Standard Size = ₹280\nLarge Size = ₹380', 'Golden finish ring hanging with bells and silk flower attachments.', 'Designed with royal touches, this artificial ring hanging is ideal for temple decoration, pooja rooms, wedding mandaps, and festive entrance decor.', 'images/p2.jpeg', '[\"images/p2.jpeg\"]', 1, 1, 1, 1, 'Artificial Ring Hanging Royal Gold | A2Z Handicraft', 'Golden finish floral ring hanging for auspicious celebrations.', 'artificial ring hanging, gold decor'),
(4, 1, 2, 'Net Chakra Hanging Mandala', 'net-chakra-hanging-mandala', 'NCH-04', 320.00, 380.00, '14 Inch Diameter = ₹320\n18 Inch Diameter = ₹420\n24 Inch Diameter = ₹550', 'Artistic net chakra mandala hanging for ceiling and stage decor.', 'The Net Chakra Hanging creates a stunning layered geometric focal point. Perfect for banquet halls, wedding photo booths, and festive celebrations.', 'images/p3.jpeg', '[\"images/p3.jpeg\"]', 1, 1, 1, 1, 'Net Chakra Hanging Mandala | A2Z Handicraft', 'Handmade net chakra hanging mandala for wedding stages and ceiling styling.', 'chakra hanging, net decor, mandala decor'),
(5, 3, 5, 'Genda Lines Marigold Garland', 'genda-lines-marigold-garland', 'GLD-05', 180.00, 220.00, '5 Feet = ₹180\n8 Feet = ₹260\n10 Feet = ₹320', 'Evergreen realistic artificial marigold genda phool garland lines.', 'Made of washable, reusable premium synthetic fabric that replicates the authentic color and fullness of fresh Indian marigold flowers.', 'images/p4.jpeg', '[\"images/p4.jpeg\"]', 1, 1, 1, 1, 'Genda Lines Marigold Garland | A2Z Handicraft', 'Reusable artificial marigold genda lines garland for Diwali and Haldi decor.', 'genda lines, marigold garland, haldi decor'),
(6, 1, 3, 'Traditional Tokri Jumer Decor', 'traditional-tokri-jumer-decor', 'TTJ-06', 450.00, 520.00, 'Single Tier = ₹450\nDouble Tier = ₹680\nTriple Tier = ₹950', 'Traditional handcrafted cane and bamboo basket jumer with hanging pearls.', 'Add heritage royal charm to your venue with the Traditional Tokri Jumer. Crafted meticulously with hanging tassels, pearls, and mirror work.', 'images/p5.jpg', '[\"images/p5.jpg\"]', 1, 1, 1, 1, 'Traditional Tokri Jumer Decor | A2Z Handicraft', 'Heritage cane and bead tokri jumer chandelier for luxury Indian event decor.', 'tokri jumer, cane chandelier, traditional decor')
ON DUPLICATE KEY UPDATE `id` = `id`;

-- About Section
INSERT INTO `about_section` (`id`, `title`, `subtitle`, `image`, `content_p1`, `content_p2`, `content_p3`, `experience_years`, `happy_customers`, `products_count`, `craftsmen_count`) VALUES
(1, 'Discover Why Customers Love Our Handicraft Products Collection', 'About Us', 'images/about.jpeg', 'A2Z Handicraft is a trusted manufacturer and supplier of high-quality handicraft products designed to bring creativity, traditional craftsmanship, and elegance into homes, offices, hotels, events, and commercial spaces. We offer a wide range of beautifully crafted handicraft items that combine artistic designs with excellent finishing and durable quality.', 'Our product collection includes RDI Hanging, Fancy Toran, Dream Catchers, Flower Bunches, decorative items, wall décor, and other handcrafted products. We focus on creating attractive designs that suit modern décor requirements while maintaining the charm of traditional Indian craftsmanship.', 'As an experienced handicraft manufacturer and supplier, we are committed to maintaining consistent quality, competitive pricing, and timely delivery. Our skilled craftsmen pay close attention to every detail, ensuring that each product meets our quality standards before reaching our customers.', '15+ Years', '10,000+', '500+', '50+')
ON DUPLICATE KEY UPDATE `id` = `id`;

-- Testimonials
INSERT INTO `testimonials` (`id`, `name`, `designation`, `rating`, `content`, `image`, `sort_order`, `status`) VALUES
(1, 'Pooja Sharma', 'Wedding Planner, Delhi', 5, 'The RDI hangings and flower bunches we ordered for our grand wedding project were absolutely breathtaking! Superior craftsmanship and timely wholesale delivery.', 'images/icon-google.svg', 1, 1),
(2, 'Rajesh Agarwal', 'Event Decorator, Jaipur', 5, 'A2Z Handicraft provides unmatched quality at direct factory prices. The customer service on WhatsApp is prompt and their product sizes are accurate.', 'images/icon-google.svg', 2, 1),
(3, 'Sunita Verma', 'Interior Stylist, Lucknow', 5, 'The traditional torans, kite hangings and dream catchers exceeded my clients expectations. Highly recommended for bulk handicraft supplies.', 'images/icon-google.svg', 3, 1)
ON DUPLICATE KEY UPDATE `id` = `id`;

-- Reviews
INSERT INTO `reviews` (`id`, `product_id`, `author_name`, `author_email`, `rating`, `title`, `comment`, `status`) VALUES
(1, 1, 'Aman Verma', 'aman@example.com', 5, 'Outstanding Quality', 'Ordered 20 pieces for a mehendi function, everyone appreciated the vibrant colors and strong stitching!', 'approved'),
(2, 2, 'Kavita Joshi', 'kavita@example.com', 5, 'Very Beautiful Ring Hanging', 'Loved the finish and size. Very reasonable price compared to local markets.', 'approved'),
(3, 5, 'Vikas Malhotra', 'vikas@example.com', 5, 'Realistic Marigolds', 'Garlands look so natural in photographs! Reusable and very durable.', 'approved')
ON DUPLICATE KEY UPDATE `id` = `id`;

-- FAQs
INSERT INTO `faqs` (`id`, `question`, `answer`, `category`, `sort_order`, `status`) VALUES
(1, 'What kind of handicraft products does A2Z Handicraft manufacture?', 'We specialize in event decorations, wedding hangings (RDI Hanging, Kite Hanging, Ring Hanging), artificial floral decor (Flower Patta, Genda Lines), Fancy Torans, Dream Catchers, and wooden/iron decorative accessories.', 'Products', 1, 1),
(2, 'Do you accept bulk and wholesale orders?', 'Yes, we are direct manufacturers and suppliers. We cater to bulk wholesale orders for event planners, decorators, retail shops, and corporate buyers with special discounted rates.', 'Orders', 2, 1),
(3, 'Can I customize products according to specific event themes or sizes?', 'Absolutely! Many of our products offer customizable dimensions, color combinations, and design variations. You can connect directly via WhatsApp (+91-7983894672) for custom requirements.', 'Customization', 3, 1),
(4, 'How are bulk orders shipped and how long does delivery take?', 'We ship across India using trusted transport and courier networks. Delivery generally takes 3 to 7 business days depending on order size and destination.', 'Shipping', 4, 1)
ON DUPLICATE KEY UPDATE `id` = `id`;

-- Initial Lead
INSERT INTO `leads` (`id`, `name`, `email`, `phone`, `subject`, `message`, `status`, `is_read`) VALUES
(1, 'Nikhil Sharma', 'nikhil.event@gmail.com', '+91 9876543210', 'Bulk Inquiry for Wedding Decor Hangings', 'Hi, I need 100 sets of RDI Hanging and 50 Marigold Genda Lines for an upcoming destination wedding in November. Please share catalog and best wholesale rates.', 'new', 0)
ON DUPLICATE KEY UPDATE `id` = `id`;
