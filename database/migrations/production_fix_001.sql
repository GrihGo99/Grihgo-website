-- =======================================================
-- GrihGO Digital & Tech — Production Schema Fix & Optimization
-- Migration: production_fix_001.sql
-- Version: 5.0 (Final Production Deployment & Integration)
-- Target: MySQL 8.0+ / MariaDB 10.4+ / InnoDB / utf8mb4
-- =======================================================

USE `grihgo_db`;

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Table: clients (Ensure client_code exists with indexing)
ALTER TABLE `clients` 
  ADD COLUMN IF NOT EXISTS `client_code` VARCHAR(50) NULL AFTER `user_id`;

UPDATE `clients` 
  SET `client_code` = CONCAT('CLT-', LPAD(id, 4, '0')) 
  WHERE `client_code` IS NULL OR `client_code` = '';

ALTER TABLE `clients` 
  ADD INDEX IF NOT EXISTS `idx_clients_code` (`client_code`);

-- 2. Table: leads (Ensure full_name, budget, message compatibility columns)
ALTER TABLE `leads` 
  ADD COLUMN IF NOT EXISTS `full_name` VARCHAR(150) NULL AFTER `id`,
  ADD COLUMN IF NOT EXISTS `budget` VARCHAR(100) NULL AFTER `service`,
  ADD COLUMN IF NOT EXISTS `message` TEXT NULL AFTER `budget`;

UPDATE `leads` 
  SET `full_name` = `name` 
  WHERE `full_name` IS NULL AND `name` IS NOT NULL;

UPDATE `leads` 
  SET `message` = `notes` 
  WHERE `message` IS NULL AND `notes` IS NOT NULL;

-- 3. Table: appointments (Ensure appointment_date, appointment_time compatibility)
ALTER TABLE `appointments` 
  ADD COLUMN IF NOT EXISTS `appointment_date` DATE NULL AFTER `preferred_date`,
  ADD COLUMN IF NOT EXISTS `appointment_time` VARCHAR(50) NULL AFTER `preferred_time`;

UPDATE `appointments` 
  SET `appointment_date` = `preferred_date` 
  WHERE `appointment_date` IS NULL AND `preferred_date` IS NOT NULL;

UPDATE `appointments` 
  SET `appointment_time` = `preferred_time` 
  WHERE `appointment_time` IS NULL AND `preferred_time` IS NOT NULL;

-- 4. Table: projects (Ensure project_name, manager_id, end_date compatibility)
ALTER TABLE `projects` 
  ADD COLUMN IF NOT EXISTS `project_name` VARCHAR(200) NULL AFTER `id`,
  ADD COLUMN IF NOT EXISTS `manager_id` INT NULL AFTER `progress`,
  ADD COLUMN IF NOT EXISTS `end_date` DATE NULL AFTER `expected_completion`;

UPDATE `projects` 
  SET `project_name` = `name` 
  WHERE `project_name` IS NULL AND `name` IS NOT NULL;

UPDATE `projects` 
  SET `manager_id` = `assigned_to` 
  WHERE `manager_id` IS NULL AND `assigned_to` IS NOT NULL;

UPDATE `projects` 
  SET `end_date` = `expected_completion` 
  WHERE `end_date` IS NULL AND `expected_completion` IS NOT NULL;

-- 5. Table: project_tasks (Ensure task_name compatibility)
ALTER TABLE `project_tasks` 
  ADD COLUMN IF NOT EXISTS `task_name` VARCHAR(200) NULL AFTER `id`;

UPDATE `project_tasks` 
  SET `task_name` = `name` 
  WHERE `task_name` IS NULL AND `name` IS NOT NULL;

-- 6. Table: invoices (Ensure amount, tax compatibility columns)
ALTER TABLE `invoices` 
  ADD COLUMN IF NOT EXISTS `amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER `due_date`,
  ADD COLUMN IF NOT EXISTS `tax` DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER `amount`;

UPDATE `invoices` 
  SET `amount` = COALESCE(`subtotal`, `total_amount`, 0.00) 
  WHERE `amount` = 0.00;

UPDATE `invoices` 
  SET `tax` = COALESCE(`tax_amount`, 0.00) 
  WHERE `tax` = 0.00;

-- 7. Table: notifications (Ensure user_id, user_type, link compatibility)
ALTER TABLE `notifications` 
  ADD COLUMN IF NOT EXISTS `user_id` INT NULL AFTER `id`,
  ADD COLUMN IF NOT EXISTS `user_type` VARCHAR(50) NOT NULL DEFAULT 'client' AFTER `user_id`,
  ADD COLUMN IF NOT EXISTS `link` VARCHAR(255) NULL AFTER `message`;

UPDATE `notifications` 
  SET `user_id` = `client_id` 
  WHERE `user_id` IS NULL AND `client_id` IS NOT NULL;

-- 8. Table: documents (Ensure document_name compatibility)
ALTER TABLE `documents` 
  ADD COLUMN IF NOT EXISTS `document_name` VARCHAR(255) NULL AFTER `id`;

UPDATE `documents` 
  SET `document_name` = `name` 
  WHERE `document_name` IS NULL AND `name` IS NOT NULL;

SET FOREIGN_KEY_CHECKS = 1;
