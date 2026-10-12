-- =======================================================
-- GrihGO Digital & Tech — Master Database Schema
-- Version: 2.0 (Part 2 - Backend, Database & Lead Architecture)
-- Target: MySQL 8.0+ / MariaDB 10.4+ / InnoDB
-- Charset: utf8mb4 / utf8mb4_unicode_ci
-- =======================================================

CREATE DATABASE IF NOT EXISTS `grihgo_db` 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `grihgo_db`;

SET FOREIGN_KEY_CHECKS = 0;

-- -------------------------------------------------------
-- 1. Table: users (Prepares Client Portal & Admin Auth)
-- -------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('client', 'admin', 'staff') NOT NULL DEFAULT 'client',
  `status` ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
  `remember_token` VARCHAR(100) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_users_email` (`email`),
  INDEX `idx_users_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 2. Table: admins (For Part 4 Admin Panel)
-- -------------------------------------------------------
DROP TABLE IF EXISTS `admins`;
CREATE TABLE `admins` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NULL,
  `full_name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `role` ENUM('super_admin', 'manager', 'sales', 'support') NOT NULL DEFAULT 'manager',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_admins_email` (`email`),
  CONSTRAINT `fk_admins_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 3. Table: clients (For Part 3 Client Portal)
-- -------------------------------------------------------
DROP TABLE IF EXISTS `clients`;
CREATE TABLE `clients` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NULL,
  `company_name` VARCHAR(150) NOT NULL,
  `contact_person` VARCHAR(150) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `phone` VARCHAR(50) NOT NULL,
  `address` TEXT NULL,
  `gst_number` VARCHAR(30) NULL,
  `status` ENUM('active', 'inactive', 'lead') NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_clients_company` (`company_name`),
  CONSTRAINT `fk_clients_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 4. Table: services (Active Service Catalog)
-- -------------------------------------------------------
DROP TABLE IF EXISTS `services`;
CREATE TABLE `services` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `name` VARCHAR(150) NOT NULL,
  `short_desc` TEXT NOT NULL,
  `full_desc` TEXT NULL,
  `icon` VARCHAR(50) NOT NULL DEFAULT '⚡',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_services_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 5. Table: products (Active Product Catalog - 14 Systems)
-- -------------------------------------------------------
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `name` VARCHAR(150) NOT NULL,
  `category` VARCHAR(50) NOT NULL,
  `short_desc` TEXT NOT NULL,
  `full_desc` TEXT NULL,
  `icon` VARCHAR(50) NOT NULL DEFAULT '💻',
  `badge` VARCHAR(50) NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_products_slug` (`slug`),
  INDEX `idx_products_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 6. Table: appointments (Active Appointment Booking System)
-- -------------------------------------------------------
DROP TABLE IF EXISTS `appointments`;
CREATE TABLE `appointments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(150) NOT NULL,
  `business_name` VARCHAR(150) NULL,
  `email` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(50) NOT NULL,
  `service` VARCHAR(100) NOT NULL,
  `budget_range` VARCHAR(50) NULL,
  `preferred_date` DATE NULL,
  `preferred_time` VARCHAR(50) NULL,
  `message` TEXT NULL,
  `status` ENUM('Pending', 'Confirmed', 'Rescheduled', 'Completed', 'Cancelled') NOT NULL DEFAULT 'Pending',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_appointments_status` (`status`),
  INDEX `idx_appointments_email` (`email`),
  INDEX `idx_appointments_date` (`preferred_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 7. Table: leads (Central Lead CRM Data Engine)
-- -------------------------------------------------------
DROP TABLE IF EXISTS `leads`;
CREATE TABLE `leads` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `business_name` VARCHAR(150) NULL,
  `phone` VARCHAR(50) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `service` VARCHAR(150) NULL,
  `source` ENUM('Website', 'Appointment', 'Contact Form', 'Request Demo', 'Service Enquiry') NOT NULL DEFAULT 'Website',
  `appointment_id` INT NULL,
  `status` ENUM('New', 'Contacted', 'Interested', 'Proposal Sent', 'Negotiation', 'Converted', 'Lost') NOT NULL DEFAULT 'New',
  `assigned_to` INT NULL,
  `notes` TEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_leads_status` (`status`),
  INDEX `idx_leads_source` (`source`),
  INDEX `idx_leads_email` (`email`),
  CONSTRAINT `fk_leads_appointment` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_leads_assigned_user` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 8. Table: contact_messages (Active Contact Inquiries)
-- -------------------------------------------------------
DROP TABLE IF EXISTS `contact_messages`;
CREATE TABLE `contact_messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `business_name` VARCHAR(150) NULL,
  `email` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(50) NOT NULL,
  `service` VARCHAR(100) NULL,
  `message` TEXT NOT NULL,
  `status` ENUM('New', 'Read', 'Replied', 'Closed') NOT NULL DEFAULT 'New',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_contact_status` (`status`),
  INDEX `idx_contact_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 9. Table: notifications (Admin & System Notification Queue)
-- -------------------------------------------------------
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `type` VARCHAR(50) NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `message` TEXT NOT NULL,
  `reference_id` INT NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_notif_read` (`is_read`),
  INDEX `idx_notif_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 10. Table: portfolio (Ready for Case Studies & Admin)
-- -------------------------------------------------------
DROP TABLE IF EXISTS `portfolio`;
CREATE TABLE `portfolio` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(200) NOT NULL,
  `category` VARCHAR(100) NOT NULL,
  `client_name` VARCHAR(150) NULL,
  `description` TEXT NULL,
  `image_url` VARCHAR(255) NULL,
  `live_url` VARCHAR(255) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 11. Table: testimonials (Client Social Proof)
-- -------------------------------------------------------
DROP TABLE IF EXISTS `testimonials`;
CREATE TABLE `testimonials` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `client_name` VARCHAR(150) NOT NULL,
  `company_position` VARCHAR(150) NULL,
  `company_name` VARCHAR(150) NULL,
  `rating` TINYINT NOT NULL DEFAULT 5,
  `content` TEXT NOT NULL,
  `avatar_url` VARCHAR(255) NULL,
  `is_featured` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 12. Table: blog_posts (Technology Insights & Articles)
-- -------------------------------------------------------
DROP TABLE IF EXISTS `blog_posts`;
CREATE TABLE `blog_posts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL UNIQUE,
  `excerpt` TEXT NULL,
  `content` TEXT NOT NULL,
  `author_name` VARCHAR(100) NOT NULL DEFAULT 'GrihGO Editorial Team',
  `featured_image` VARCHAR(255) NULL,
  `status` ENUM('draft', 'published') NOT NULL DEFAULT 'published',
  `published_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_blog_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 13. Table: settings (Application & Business Configuration)
-- -------------------------------------------------------
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(100) NOT NULL UNIQUE,
  `setting_value` TEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_settings_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- =======================================================
-- SEED DATA (Services, Products & Business Settings)
-- =======================================================

-- Services Seed Data (6 Core IT Capabilities)
INSERT INTO `services` (`slug`, `name`, `short_desc`, `icon`, `is_active`, `sort_order`) VALUES
('website-development', 'Website Development', 'High-speed corporate portals, web applications, and responsive conversion engines engineered with clean code and modern UX.', '🌐', 1, 1),
('application-development', 'Application Development', 'Scalable iOS, Android, and cross-platform mobile applications crafted for enterprise performance.', '📱', 1, 2),
('software-development', 'Custom Software Development', 'Tailored business software, microservices, and database systems built around unique operational workflows.', '💻', 1, 3),
('ai-solutions', 'AI Solutions & Chatbots', 'Conversational AI, smart business automation, predictive workflows, and intelligent customer agents.', '🤖', 1, 4),
('digital-marketing', 'Digital Marketing & Growth', 'Performance marketing, multi-channel customer acquisition campaigns, and brand visibility management.', '📈', 1, 5),
('seo', 'Search Engine Optimization (SEO)', 'Technical SEO audits, high-intent keyword ranking, content strategy, and organic authority acceleration.', '🔍', 1, 6)
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

-- Products Seed Data (14 Ready SaaS & Business Systems)
INSERT INTO `products` (`slug`, `name`, `category`, `short_desc`, `icon`, `badge`, `is_active`, `sort_order`) VALUES
('whatsapp-crm', 'GrihGO WhatsApp CRM', 'crm', 'Centralized multi-agent WhatsApp team inbox, automated lead routing, and broadcast campaigns on official business numbers.', '💬', 'Flagship SaaS', 1, 1),
('billing-software', 'GST Billing Software', 'finance', 'Comprehensive invoicing, GST tax compliance, multi-rate tax calculations, and instant digital billing receipts.', '🧾', 'GST Ready', 1, 2),
('crm', 'Enterprise CRM', 'crm', 'Complete sales pipeline visualization, lead scoring, deal velocity tracking, and automated client follow-ups.', '📊', 'High Demand', 1, 3),
('inventory', 'Inventory Management', 'ops', 'Real-time multi-warehouse stock tracking, automated re-order thresholds, barcode scanning, and supply audits.', '📦', 'Multi-Warehouse', 1, 4),
('erp', 'Enterprise ERP', 'finance', 'Unified multi-branch platform synchronizing finance, supply chains, sales orders, and departmental operations.', '⚙️', 'Enterprise Grade', 1, 5),
('hr-payroll', 'HR Management & Payroll', 'ops', 'Automated salary disbursals, statutory tax deductions, biometric attendance sync, and employee self-service.', '👥', 'Automated Compliance', 1, 6),
('school-management', 'School Management System', 'sector', 'Integrated student admissions, fee collections, exam gradebooks, teacher scheduling, and parent mobile portals.', '🎓', 'Education Tech', 1, 7),
('hospital-management', 'Hospital Management Suite', 'sector', 'Digital patient EHR, OPD/IPD billing, pharmacy inventory, lab report dispatch, and doctor appointment slots.', '🏥', 'Healthcare Tech', 1, 8),
('restaurant-management', 'Restaurant POS & Management', 'sector', 'Interactive POS terminal, Kitchen Order Ticket (KOT) automation, table reservations, and recipe food costing.', '🍽️', 'F&B Solution', 1, 9),
('real-estate-crm', 'Real Estate CRM', 'crm', 'Property unit inventory mapping, buyer site visit schedules, broker commissions, and payment milestones.', '🏢', 'Realty Suite', 1, 10),
('lead-management', 'Lead Management Platform', 'crm', 'Multi-channel inquiry ingestion, automatic lead assignment, conversion analytics, and spam filtering.', '🎯', 'Growth Engine', 1, 11),
('project-management', 'Project Management Suite', 'ops', 'Agile Kanban boards, Gantt timelines, milestone resource allocation, client approvals, and time logging.', '📋', 'Productivity', 1, 12),
('customer-support', 'Customer Support System', 'crm', 'Omnichannel ticket desk, SLA tracking, knowledge base builder, and automated satisfaction rating surveys.', '🎧', 'Omnichannel Desk', 1, 13),
('appointment-management', 'Appointment Management', 'ops', 'Self-service client booking calendar, automated SMS/WhatsApp reminders, staff scheduling, and buffer buffers.', '📅', 'Smart Scheduler', 1, 14)
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

-- System Settings Seed Data
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('company_name', 'GrihGO Digital & Tech'),
('company_tagline', 'Technology • Innovation • Growth'),
('company_email', 'contact@grihgodigital.com'),
('company_phone', '+91 99999 99999'),
('company_address', 'GrihGO Tech Park, Innovation Zone, Sector 62, Metro City — 201301'),
('whatsapp_number', '919999999999'),
('notification_email', 'admin@grihgodigital.com'),
('environment', 'development')
ON DUPLICATE KEY UPDATE `setting_value`=VALUES(`setting_value`);
