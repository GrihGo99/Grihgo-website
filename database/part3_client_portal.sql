-- =======================================================
-- GrihGO Digital & Tech — Part 3 Client Portal Schema Extension
-- Version: 3.0 (Client Portal & Authentication Architecture)
-- Target: MySQL 8.0+ / MariaDB 10.4+ / InnoDB
-- =======================================================

-- Select the target database in phpMyAdmin before importing this file.

SET FOREIGN_KEY_CHECKS = 0;

-- Ensure users table has phone column
ALTER TABLE `users` ADD COLUMN `phone` VARCHAR(50) NULL AFTER `email`;

-- Ensure clients table has profile_image column
ALTER TABLE `clients` ADD COLUMN `profile_image` VARCHAR(255) NULL AFTER `address`;

-- Ensure notifications table has client_id column
ALTER TABLE `notifications` ADD COLUMN `client_id` INT NULL AFTER `id`;
ALTER TABLE `notifications` ADD INDEX `idx_notif_client` (`client_id`);

-- Ensure appointments table has client_id column
ALTER TABLE `appointments` ADD COLUMN `client_id` INT NULL AFTER `id`;
ALTER TABLE `appointments` ADD INDEX `idx_appointments_client` (`client_id`);

-- -------------------------------------------------------
-- Table: projects
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `projects` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `client_id` INT NOT NULL,
  `name` VARCHAR(200) NOT NULL,
  `service` VARCHAR(150) NOT NULL,
  `description` TEXT NULL,
  `start_date` DATE NOT NULL,
  `expected_completion` DATE NULL,
  `status` ENUM('Planning', 'In Progress', 'Testing', 'Review', 'Completed', 'On Hold') NOT NULL DEFAULT 'In Progress',
  `progress` INT NOT NULL DEFAULT 0,
  `assigned_team` VARCHAR(150) DEFAULT 'GrihGO Engineering Team',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_projects_client` (`client_id`),
  INDEX `idx_projects_status` (`status`),
  CONSTRAINT `fk_projects_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- Table: project_tasks
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `project_tasks` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `project_id` INT NOT NULL,
  `name` VARCHAR(200) NOT NULL,
  `description` TEXT NULL,
  `assigned_team` VARCHAR(150) DEFAULT 'GrihGO Tech Core',
  `due_date` DATE NULL,
  `completed_date` DATE NULL,
  `priority` ENUM('Low', 'Medium', 'High', 'Urgent') NOT NULL DEFAULT 'Medium',
  `status` ENUM('Pending', 'In Progress', 'Completed', 'Blocked') NOT NULL DEFAULT 'Pending',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_tasks_project` (`project_id`),
  INDEX `idx_tasks_status` (`status`),
  CONSTRAINT `fk_tasks_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- Table: invoices
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `invoices` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `invoice_number` VARCHAR(50) NOT NULL UNIQUE,
  `client_id` INT NOT NULL,
  `project_id` INT NULL,
  `invoice_date` DATE NOT NULL,
  `due_date` DATE NOT NULL,
  `total_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `paid_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `status` ENUM('Paid', 'Pending', 'Partially Paid', 'Overdue', 'Cancelled') NOT NULL DEFAULT 'Pending',
  `notes` TEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_invoices_client` (`client_id`),
  INDEX `idx_invoices_status` (`status`),
  CONSTRAINT `fk_invoices_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_invoices_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- Table: invoice_items
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `invoice_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `invoice_id` INT NOT NULL,
  `description` VARCHAR(255) NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `unit_price` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `total` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  INDEX `idx_items_invoice` (`invoice_id`),
  CONSTRAINT `fk_items_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- Table: payments
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `payments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `invoice_id` INT NOT NULL,
  `client_id` INT NOT NULL,
  `amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `payment_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `payment_method` VARCHAR(50) NOT NULL DEFAULT 'Bank Transfer (NEFT/RTGS)',
  `transaction_id` VARCHAR(100) NOT NULL,
  `status` ENUM('Success', 'Pending', 'Failed', 'Refunded') NOT NULL DEFAULT 'Success',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_payments_client` (`client_id`),
  INDEX `idx_payments_invoice` (`invoice_id`),
  CONSTRAINT `fk_payments_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_payments_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- Table: documents
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `documents` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `client_id` INT NOT NULL,
  `project_id` INT NULL,
  `name` VARCHAR(255) NOT NULL,
  `type` VARCHAR(50) NOT NULL DEFAULT 'Contract',
  `file_path` VARCHAR(255) NOT NULL,
  `file_size` VARCHAR(50) DEFAULT '1.2 MB',
  `uploaded_by` VARCHAR(100) DEFAULT 'GrihGO Operations',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_docs_client` (`client_id`),
  INDEX `idx_docs_project` (`project_id`),
  CONSTRAINT `fk_docs_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_docs_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- Table: support_tickets
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `support_tickets` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `ticket_number` VARCHAR(50) NOT NULL UNIQUE,
  `client_id` INT NOT NULL,
  `subject` VARCHAR(255) NOT NULL,
  `category` ENUM('Technical Issue', 'Billing', 'Project', 'Website', 'Software', 'CRM', 'General', 'Other') NOT NULL DEFAULT 'General',
  `priority` ENUM('Low', 'Medium', 'High', 'Urgent') NOT NULL DEFAULT 'Medium',
  `status` ENUM('Open', 'In Progress', 'Waiting for Client', 'Resolved', 'Closed') NOT NULL DEFAULT 'Open',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_tickets_client` (`client_id`),
  INDEX `idx_tickets_status` (`status`),
  CONSTRAINT `fk_tickets_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- Table: ticket_messages
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ticket_messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `ticket_id` INT NOT NULL,
  `sender_type` ENUM('client', 'staff', 'admin') NOT NULL DEFAULT 'client',
  `sender_id` INT NOT NULL,
  `sender_name` VARCHAR(150) NOT NULL,
  `message` TEXT NOT NULL,
  `attachment_path` VARCHAR(255) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_ticket_msgs_ticket` (`ticket_id`),
  CONSTRAINT `fk_ticket_msgs_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `support_tickets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- Table: messages (Direct Client <-> GrihGO Team Communications)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `client_id` INT NOT NULL,
  `project_id` INT NULL,
  `ticket_id` INT NULL,
  `sender_type` ENUM('client', 'staff', 'admin') NOT NULL DEFAULT 'client',
  `sender_name` VARCHAR(150) NOT NULL,
  `message` TEXT NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_messages_client` (`client_id`),
  CONSTRAINT `fk_messages_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
