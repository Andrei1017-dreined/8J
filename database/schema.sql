-- 8J Cellphone Accessories Integrated Management System
-- Database Schema for MySQL 8.x / MariaDB
-- Designed for BS Computer Science Thesis - Tanauan Institute, Inc.

CREATE DATABASE IF NOT EXISTS `8j_ims_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `8j_ims_db`;

-- 1. USERS & ROLES
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `fullname` VARCHAR(120) NOT NULL,
    `email` VARCHAR(120) UNIQUE NOT NULL,
    `username` VARCHAR(60) UNIQUE NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'staff', 'technician', 'customer') DEFAULT 'customer',
    `contact_number` VARCHAR(30) NULL,
    `address` TEXT NULL,
    `status` ENUM('active', 'inactive') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. CATEGORIES (Accessories & Device Categories)
CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(80) NOT NULL,
    `type` ENUM('accessory', 'unit', 'part') DEFAULT 'accessory',
    `description` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 3. PRODUCTS (Accessories & Parts Inventory)
CREATE TABLE IF NOT EXISTS `products` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `category_id` INT NULL,
    `sku` VARCHAR(60) UNIQUE NOT NULL,
    `name` VARCHAR(150) NOT NULL,
    `brand` VARCHAR(80) NULL,
    `cost_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `selling_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `stock_quantity` INT NOT NULL DEFAULT 0,
    `reorder_level` INT NOT NULL DEFAULT 5,
    `image` VARCHAR(255) NULL DEFAULT 'default_product.png',
    `description` TEXT NULL,
    `status` ENUM('available', 'low_stock', 'out_of_stock', 'archived') DEFAULT 'available',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 4. PHONE UNITS INVENTORY (Tracked individually by IMEI / Serial)
CREATE TABLE IF NOT EXISTS `phone_units` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `brand` VARCHAR(60) NOT NULL,
    `model_name` VARCHAR(100) NOT NULL,
    `storage_capacity` VARCHAR(30) NOT NULL, -- e.g. 64GB, 128GB, 256GB
    `color` VARCHAR(40) NULL,
    `imei_1` VARCHAR(30) UNIQUE NOT NULL,
    `imei_2` VARCHAR(30) NULL,
    `unit_condition` ENUM('Brand New', 'Pre-Owned Grade A', 'Pre-Owned Grade B', 'Pre-Owned Grade C') DEFAULT 'Pre-Owned Grade A',
    `battery_health` INT NULL, -- percentage e.g. 88
    `buy_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `selling_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `status` ENUM('in_stock', 'reserved', 'sold', 'traded', 'under_repair') DEFAULT 'in_stock',
    `source` ENUM('supplier', 'customer_buyout', 'customer_trade') DEFAULT 'supplier',
    `notes` TEXT NULL,
    `image` VARCHAR(255) NULL DEFAULT 'default_phone.png',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 5. APPRAISAL MATRIX (Base guide for valuing second-hand phones)
CREATE TABLE IF NOT EXISTS `appraisal_matrix` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `brand` VARCHAR(60) NOT NULL,
    `model_name` VARCHAR(100) NOT NULL,
    `storage_capacity` VARCHAR(30) NOT NULL,
    `base_market_value` DECIMAL(10,2) NOT NULL,
    `grade_a_pct` DECIMAL(5,2) DEFAULT 100.00, -- % of base
    `grade_b_pct` DECIMAL(5,2) DEFAULT 80.00,
    `grade_c_pct` DECIMAL(5,2) DEFAULT 65.00,
    `screen_scratch_deduction` DECIMAL(10,2) DEFAULT 800.00,
    `screen_crack_deduction` DECIMAL(10,2) DEFAULT 2500.00,
    `battery_low_deduction` DECIMAL(10,2) DEFAULT 1200.00, -- if < 80%
    `camera_issue_deduction` DECIMAL(10,2) DEFAULT 1500.00,
    `biometrics_issue_deduction` DECIMAL(10,2) DEFAULT 1500.00,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 6. APPRAISAL RECORDS
CREATE TABLE IF NOT EXISTS `appraisal_records` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `customer_name` VARCHAR(120) NOT NULL,
    `contact_number` VARCHAR(30) NOT NULL,
    `brand` VARCHAR(60) NOT NULL,
    `model_name` VARCHAR(100) NOT NULL,
    `storage_capacity` VARCHAR(30) NOT NULL,
    `imei` VARCHAR(30) NULL,
    `cosmetic_grade` ENUM('Grade A', 'Grade B', 'Grade C') NOT NULL,
    `screen_condition` ENUM('Flawless', 'Minor Scratches', 'Cracked / Lines') NOT NULL,
    `battery_health` INT NOT NULL,
    `face_or_fingerprint_working` TINYINT(1) DEFAULT 1,
    `cameras_working` TINYINT(1) DEFAULT 1,
    `speakers_mic_working` TINYINT(1) DEFAULT 1,
    `icloud_google_clean` TINYINT(1) DEFAULT 1, -- Must be clean
    `initial_market_val` DECIMAL(10,2) NOT NULL,
    `total_deductions` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `final_appraised_value` DECIMAL(10,2) NOT NULL,
    `technician_id` INT NULL,
    `status` ENUM('pending', 'accepted_for_trade', 'accepted_for_buyout', 'rejected') DEFAULT 'pending',
    `remarks` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`technician_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 7. BUYING OF UNITS (Buyouts from Walk-in sellers with Anti-Fencing docs)
CREATE TABLE IF NOT EXISTS `unit_buyouts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `transaction_no` VARCHAR(40) UNIQUE NOT NULL,
    `seller_name` VARCHAR(120) NOT NULL,
    `seller_contact` VARCHAR(30) NOT NULL,
    `seller_address` TEXT NOT NULL,
    `valid_id_type` VARCHAR(60) NOT NULL, -- e.g. UMID, PhilSys, Driver's License
    `valid_id_number` VARCHAR(80) NOT NULL,
    `id_photo_front` VARCHAR(255) NULL,
    `appraisal_record_id` INT NULL,
    `phone_unit_id` INT NULL, -- links to newly created phone_units entry
    `amount_paid` DECIMAL(10,2) NOT NULL,
    `payment_method` ENUM('cash', 'gcash', 'bank_transfer') DEFAULT 'cash',
    `anti_fencing_declaration_agreed` TINYINT(1) DEFAULT 1,
    `handled_by_staff_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`appraisal_record_id`) REFERENCES `appraisal_records`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`phone_unit_id`) REFERENCES `phone_units`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`handled_by_staff_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB;

-- 8. TRADING / SWAP TRANSACTIONS
CREATE TABLE IF NOT EXISTS `trading_transactions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `trade_code` VARCHAR(40) UNIQUE NOT NULL,
    `customer_name` VARCHAR(120) NOT NULL,
    `customer_contact` VARCHAR(30) NOT NULL,
    `trade_in_appraisal_id` INT NOT NULL,
    `incoming_unit_id` INT NULL, -- newly created pre-owned unit
    `outgoing_unit_id` INT NOT NULL, -- shop unit being given to customer
    `appraised_trade_value` DECIMAL(10,2) NOT NULL,
    `target_unit_price` DECIMAL(10,2) NOT NULL,
    `top_up_difference` DECIMAL(10,2) NOT NULL, -- target_unit_price - appraised_trade_value
    `cash_received_from_customer` DECIMAL(10,2) DEFAULT 0.00,
    `cash_paid_to_customer` DECIMAL(10,2) DEFAULT 0.00,
    `handled_by_staff_id` INT NOT NULL,
    `status` ENUM('completed', 'cancelled') DEFAULT 'completed',
    `remarks` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`trade_in_appraisal_id`) REFERENCES `appraisal_records`(`id`),
    FOREIGN KEY (`incoming_unit_id`) REFERENCES `phone_units`(`id`),
    FOREIGN KEY (`outgoing_unit_id`) REFERENCES `phone_units`(`id`),
    FOREIGN KEY (`handled_by_staff_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB;

-- 9. SALES & BILLING (POS Transactions)
CREATE TABLE IF NOT EXISTS `sales` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `invoice_no` VARCHAR(40) UNIQUE NOT NULL,
    `customer_id` INT NULL,
    `customer_name` VARCHAR(120) DEFAULT 'Walk-in Customer',
    `subtotal` DECIMAL(10,2) NOT NULL,
    `discount_amount` DECIMAL(10,2) DEFAULT 0.00,
    `total_amount` DECIMAL(10,2) NOT NULL,
    `amount_tendered` DECIMAL(10,2) NOT NULL,
    `change_due` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `payment_method` ENUM('cash', 'gcash', 'card', 'split') DEFAULT 'cash',
    `payment_reference` VARCHAR(80) NULL,
    `cashier_id` INT NOT NULL,
    `notes` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`customer_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`cashier_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `sale_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `sale_id` INT NOT NULL,
    `item_type` ENUM('product', 'phone_unit') NOT NULL,
    `product_id` INT NULL,
    `phone_unit_id` INT NULL,
    `item_name` VARCHAR(150) NOT NULL,
    `unit_price` DECIMAL(10,2) NOT NULL,
    `quantity` INT NOT NULL DEFAULT 1,
    `subtotal` DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (`sale_id`) REFERENCES `sales`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`phone_unit_id`) REFERENCES `phone_units`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 10. ONLINE ORDERS & STORE PICK-UP
CREATE TABLE IF NOT EXISTS `online_orders` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_code` VARCHAR(40) UNIQUE NOT NULL,
    `customer_id` INT NOT NULL,
    `total_amount` DECIMAL(10,2) NOT NULL,
    `pickup_target_date` DATE NOT NULL,
    `pickup_status` ENUM('pending_confirmation', 'ready_for_pickup', 'claimed', 'expired', 'cancelled') DEFAULT 'pending_confirmation',
    `claim_verification_code` VARCHAR(10) NOT NULL,
    `claimed_at` DATETIME NULL,
    `handled_by_staff_id` INT NULL,
    `notes` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`customer_id`) REFERENCES `users`(`id`),
    FOREIGN KEY (`handled_by_staff_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `online_order_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT NOT NULL,
    `item_type` ENUM('product', 'phone_unit') NOT NULL,
    `product_id` INT NULL,
    `phone_unit_id` INT NULL,
    `item_name` VARCHAR(150) NOT NULL,
    `unit_price` DECIMAL(10,2) NOT NULL,
    `quantity` INT NOT NULL DEFAULT 1,
    `subtotal` DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (`order_id`) REFERENCES `online_orders`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`phone_unit_id`) REFERENCES `phone_units`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 11. REPAIR & SERVICE SCHEDULING
CREATE TABLE IF NOT EXISTS `repair_job_orders` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `job_order_no` VARCHAR(40) UNIQUE NOT NULL,
    `customer_id` INT NULL,
    `customer_name` VARCHAR(120) NOT NULL,
    `customer_contact` VARCHAR(30) NOT NULL,
    `device_brand` VARCHAR(60) NOT NULL,
    `device_model` VARCHAR(100) NOT NULL,
    `device_imei_or_serial` VARCHAR(50) NULL,
    `passcode_or_pattern` VARCHAR(50) NULL, -- with customer consent for testing
    `reported_issues` TEXT NOT NULL,
    `diagnostic_notes` TEXT NULL,
    `estimated_cost` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `final_cost` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `parts_cost` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `labor_fee` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `deposit_paid` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `technician_id` INT NULL,
    `status` ENUM('scheduled', 'received', 'diagnosing', 'waiting_parts', 'repairing', 'ready_for_pickup', 'completed', 'unrepairable', 'cancelled') DEFAULT 'scheduled',
    `scheduled_date` DATE NULL,
    `completed_at` DATETIME NULL,
    `warranty_days` INT DEFAULT 30,
    `warranty_expiry` DATE NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`customer_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`technician_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 12. AUDIT LOGS
CREATE TABLE IF NOT EXISTS `audit_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NULL,
    `action` VARCHAR(100) NOT NULL,
    `module` VARCHAR(60) NOT NULL,
    `details` TEXT NULL,
    `ip_address` VARCHAR(45) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;
