-- =======================================================
-- GrihGO Digital & Tech — Part 4 Master Admin Panel & CRM Migration
-- Target: MySQL 8.0+ / MariaDB 10.4+ / InnoDB
-- =======================================================

USE `grihgo_db`;

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Table: admins
CREATE TABLE IF NOT EXISTS `admins` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NULL,
  `full_name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('super_admin', 'admin', 'manager', 'staff') NOT NULL DEFAULT 'admin',
  `status` ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_admins_email` (`email`),
  INDEX `idx_admins_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Table: admin_activity_logs (Section 50)
CREATE TABLE IF NOT EXISTS `admin_activity_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `admin_id` INT NULL,
  `admin_name` VARCHAR(150) NULL,
  `action` VARCHAR(100) NOT NULL,
  `entity_type` VARCHAR(100) NULL,
  `entity_id` INT NULL,
  `description` TEXT NULL,
  `ip_address` VARCHAR(50) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_logs_admin` (`admin_id`),
  INDEX `idx_logs_action` (`action`),
  INDEX `idx_logs_date` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Table: testimonials
CREATE TABLE IF NOT EXISTS `testimonials` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `client_name` VARCHAR(150) NOT NULL,
  `company_name` VARCHAR(150) NULL,
  `designation` VARCHAR(150) NULL,
  `rating` TINYINT NOT NULL DEFAULT 5,
  `content` TEXT NOT NULL,
  `image_url` VARCHAR(255) NULL,
  `status` ENUM('published', 'draft') NOT NULL DEFAULT 'published',
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Table: portfolio
CREATE TABLE IF NOT EXISTS `portfolio` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(200) NOT NULL,
  `category` VARCHAR(100) NOT NULL,
  `client_name` VARCHAR(150) NULL,
  `industry` VARCHAR(100) NULL,
  `description` TEXT NULL,
  `solution` TEXT NULL,
  `technology` VARCHAR(255) NULL,
  `impact` TEXT NULL,
  `image_url` VARCHAR(255) NULL,
  `project_url` VARCHAR(255) NULL,
  `status` ENUM('published', 'draft') NOT NULL DEFAULT 'published',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Table: blog_posts
CREATE TABLE IF NOT EXISTS `blog_posts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL UNIQUE,
  `excerpt` TEXT NULL,
  `content` TEXT NOT NULL,
  `author_name` VARCHAR(100) NOT NULL DEFAULT 'GrihGO Editorial Team',
  `featured_image` VARCHAR(255) NULL,
  `category` VARCHAR(100) DEFAULT 'Technology',
  `tags` VARCHAR(255) NULL,
  `meta_title` VARCHAR(255) NULL,
  `meta_desc` TEXT NULL,
  `status` ENUM('draft', 'published', 'archived') NOT NULL DEFAULT 'published',
  `published_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_blog_slug` (`slug`),
  INDEX `idx_blog_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ensure invoice financial breakdown columns
ALTER TABLE `invoices` ADD COLUMN `subtotal` DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER `due_date`;
ALTER TABLE `invoices` ADD COLUMN `tax_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER `subtotal`;
ALTER TABLE `invoices` ADD COLUMN `discount_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER `tax_amount`;

SET FOREIGN_KEY_CHECKS = 1;
