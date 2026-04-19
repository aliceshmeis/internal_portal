-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Apr 19, 2026 at 02:35 PM
-- Server version: 8.4.7
-- PHP Version: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `internal_portal`
--

-- --------------------------------------------------------

--
-- Table structure for table `assets`
--

DROP TABLE IF EXISTS `assets`;
CREATE TABLE IF NOT EXISTS `assets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `asset_tag` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `campus_id` int NOT NULL,
  `building` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `floor` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `room` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `category` enum('Laptop','Printer','Network Equipment','Furniture','Other') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `serial_number` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('Available','In Use','Maintenance','Retired') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'Available',
  `assigned_to` int DEFAULT NULL,
  `po_item_id` int DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `purchase_cost` decimal(10,2) DEFAULT NULL,
  `warranty_expiry` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `expected_return_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `asset_tag` (`asset_tag`),
  KEY `idx_campus_id` (`campus_id`),
  KEY `idx_category` (`category`),
  KEY `idx_status` (`status`),
  KEY `idx_assigned_to` (`assigned_to`),
  KEY `fk_assets_po_item` (`po_item_id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assets`
--

INSERT INTO `assets` (`id`, `asset_tag`, `campus_id`, `building`, `floor`, `room`, `category`, `name`, `description`, `serial_number`, `status`, `assigned_to`, `po_item_id`, `purchase_date`, `purchase_cost`, `warranty_expiry`, `created_at`, `updated_at`, `expected_return_date`) VALUES
(1, 'AST-P20260212-31084B', 1, NULL, NULL, NULL, 'Printer', 'HP LaserJet 1020 (HR)', 'Printer in HR office', 'SN-PR-001', 'Available', NULL, NULL, '2026-02-01', 300.00, '2028-02-01', '2026-02-12 09:00:51', '2026-02-20 08:11:34', NULL),
(2, 'AST-P20260212-4EE64D', 1, NULL, NULL, NULL, 'Printer', 'andriod pp', 'Printer in HR office', 'SN-PR-002', 'In Use', 2, NULL, '2026-02-01', 250.00, '2028-02-01', '2026-02-12 09:52:36', '2026-02-17 08:27:01', NULL),
(3, 'TEST-C2-001', 1, NULL, NULL, NULL, 'Printer', 'Campus2 Test Printer', 'Test asset for campus 2', 'SN-C2-001', 'Available', NULL, NULL, NULL, NULL, NULL, '2026-02-12 10:06:19', '2026-02-22 20:33:12', NULL),
(4, 'AST-L20260213-812B2A', 1, NULL, NULL, NULL, 'Laptop', 'Dell XPS 15 Laptop', 'string', 'DLL-XPS-2024-001', 'Available', NULL, NULL, '2024-01-15', 1500.00, '2026-02-13', '2026-02-13 07:58:00', '2026-02-20 07:55:34', NULL),
(5, 'AST-O20260217-76E700', 1, NULL, NULL, NULL, 'Other', 'mouse hp', 'fast', 'SNhfgh', 'Available', NULL, NULL, '2026-02-11', 900.00, '2026-02-27', '2026-02-17 09:43:51', '2026-02-22 20:33:04', NULL),
(6, 'AST-L20260217-DDA06A', 1, NULL, NULL, NULL, 'Laptop', 'laptop apple', 'careful', 'app', 'Available', NULL, NULL, '2026-02-26', 900.00, '2026-02-12', '2026-02-17 10:03:25', '2026-02-20 07:55:30', NULL),
(7, 'AST-O20260220-CA9494', 1, NULL, NULL, NULL, 'Other', 'mouse hp', 'tyrew', 'ertyeytty', 'Available', 14, NULL, NULL, 5.00, '2026-02-01', '2026-02-20 08:17:32', '2026-02-20 08:17:32', NULL),
(8, 'AST-O20260220-289347', 1, 'B', '1', '101', 'Other', 'hp', 'yuiyuirt', 'yujyi', 'Available', 18, NULL, '2026-02-10', 9.00, '2026-02-28', '2026-02-20 08:21:06', '2026-02-20 08:21:06', NULL),
(9, 'AST-L20260223-8EB89F', 1, NULL, NULL, NULL, 'Laptop', 'mac 2025', 'Received via PO PO-20260222-BA6C39', NULL, 'Available', NULL, 4, NULL, NULL, NULL, '2026-02-23 05:58:00', '2026-02-23 05:58:00', NULL),
(10, 'AST-L20260223-8EBBA0', 1, NULL, NULL, NULL, 'Laptop', 'mac 2025', 'Received via PO PO-20260222-BA6C39', NULL, 'Available', NULL, 4, NULL, NULL, NULL, '2026-02-23 05:58:00', '2026-02-23 05:58:00', NULL),
(11, 'AST-L20260223-8EBD4C', 1, NULL, NULL, NULL, 'Laptop', 'mac 2025', 'Received via PO PO-20260222-BA6C39', NULL, 'Available', NULL, 4, NULL, NULL, NULL, '2026-02-23 05:58:00', '2026-02-23 05:58:00', NULL),
(12, 'AST-F20260223-D63B5E', 1, NULL, NULL, NULL, 'Furniture', 'campon 2025', 'Received via PO PO-20260223-66189B', NULL, 'Available', NULL, 6, NULL, NULL, NULL, '2026-02-23 07:55:57', '2026-02-23 07:55:57', NULL),
(13, 'AST-F20260223-D63CF1', 1, NULL, NULL, NULL, 'Furniture', 'campon 2025', 'Received via PO PO-20260223-66189B', NULL, 'Available', NULL, 6, NULL, NULL, NULL, '2026-02-23 07:55:57', '2026-02-23 07:55:57', NULL),
(14, 'AST-F20260223-D63DB0', 1, NULL, NULL, NULL, 'Furniture', 'campon 2025', 'Received via PO PO-20260223-66189B', NULL, 'Available', NULL, 6, NULL, NULL, NULL, '2026-02-23 07:55:57', '2026-02-23 07:55:57', NULL),
(15, 'AST-F20260223-D63E3D', 1, NULL, NULL, NULL, 'Furniture', 'campon 2025', 'Received via PO PO-20260223-66189B', NULL, 'Available', NULL, 6, NULL, NULL, NULL, '2026-02-23 07:55:57', '2026-02-23 07:55:57', NULL),
(16, 'AST-F20260223-D63FD8', 1, NULL, NULL, NULL, 'Furniture', 'campon 2025', 'Received via PO PO-20260223-66189B', NULL, 'Available', NULL, 6, NULL, NULL, NULL, '2026-02-23 07:55:57', '2026-02-23 07:55:57', NULL),
(17, 'AST-F20260223-D6408F', 1, NULL, NULL, NULL, 'Furniture', 'campon 2025', 'Received via PO PO-20260223-66189B', NULL, 'Available', NULL, 6, NULL, NULL, NULL, '2026-02-23 07:55:57', '2026-02-23 07:55:57', NULL),
(18, 'AST-F20260223-D6412A', 1, NULL, NULL, NULL, 'Furniture', 'campon 2025', 'Received via PO PO-20260223-66189B', NULL, 'Available', NULL, 6, NULL, NULL, NULL, '2026-02-23 07:55:57', '2026-02-23 07:55:57', NULL),
(19, 'AST-F20260223-D6419D', 1, NULL, NULL, NULL, 'Furniture', 'campon 2025', 'Received via PO PO-20260223-66189B', NULL, 'Available', NULL, 6, NULL, NULL, NULL, '2026-02-23 07:55:57', '2026-02-23 07:55:57', NULL),
(20, 'AST-F20260223-D64205', 1, NULL, NULL, NULL, 'Furniture', 'campon 2025', 'Received via PO PO-20260223-66189B', NULL, 'Available', NULL, 6, NULL, NULL, NULL, '2026-02-23 07:55:57', '2026-02-23 07:55:57', NULL),
(21, 'AST-F20260223-D6426A', 1, NULL, NULL, NULL, 'Furniture', 'campon 2025', 'Received via PO PO-20260223-66189B', NULL, 'Available', NULL, 6, NULL, NULL, NULL, '2026-02-23 07:55:57', '2026-02-23 07:55:57', NULL),
(22, 'AST-F20260223-D642CF', 1, NULL, NULL, NULL, 'Furniture', 'campon 2025', 'Received via PO PO-20260223-66189B', NULL, 'Available', NULL, 6, NULL, NULL, NULL, '2026-02-23 07:55:57', '2026-02-23 07:55:57', NULL),
(23, 'AST-F20260223-D64395', 1, NULL, NULL, NULL, 'Furniture', 'campon 2025', 'Received via PO PO-20260223-66189B', NULL, 'Available', NULL, 6, NULL, NULL, NULL, '2026-02-23 07:55:57', '2026-02-23 07:55:57', NULL),
(24, 'AST-F20260223-D64414', 1, NULL, NULL, NULL, 'Furniture', 'campon 2025', 'Received via PO PO-20260223-66189B', NULL, 'Available', NULL, 6, NULL, NULL, NULL, '2026-02-23 07:55:57', '2026-02-23 07:55:57', NULL),
(25, 'AST-F20260223-D644B5', 1, NULL, NULL, NULL, 'Furniture', 'campon 2025', 'Received via PO PO-20260223-66189B', NULL, 'Available', NULL, 6, NULL, NULL, NULL, '2026-02-23 07:55:57', '2026-02-23 07:55:57', NULL),
(26, 'AST-F20260223-D6456B', 1, NULL, NULL, NULL, 'Furniture', 'campon 2025', 'Received via PO PO-20260223-66189B', NULL, 'Available', NULL, 6, NULL, NULL, NULL, '2026-02-23 07:55:57', '2026-02-23 07:55:57', NULL),
(27, 'AST-F20260223-D645F1', 1, NULL, NULL, NULL, 'Furniture', 'campon 2025', 'Received via PO PO-20260223-66189B', NULL, 'Available', NULL, 6, NULL, NULL, NULL, '2026-02-23 07:55:57', '2026-02-23 07:55:57', NULL),
(28, 'AST-F20260223-D6466D', 1, NULL, NULL, NULL, 'Furniture', 'campon 2025', 'Received via PO PO-20260223-66189B', NULL, 'Available', NULL, 6, NULL, NULL, NULL, '2026-02-23 07:55:57', '2026-02-23 07:55:57', NULL),
(29, 'AST-F20260223-D646DD', 1, NULL, NULL, NULL, 'Furniture', 'campon 2025', 'Received via PO PO-20260223-66189B', NULL, 'Available', NULL, 6, NULL, NULL, NULL, '2026-02-23 07:55:57', '2026-02-23 07:55:57', NULL),
(30, 'AST-F20260223-D64751', 1, NULL, NULL, NULL, 'Furniture', 'campon 2025', 'Received via PO PO-20260223-66189B', NULL, 'Available', NULL, 6, NULL, NULL, NULL, '2026-02-23 07:55:57', '2026-02-23 07:55:57', NULL),
(31, 'AST-F20260223-D647D2', 1, NULL, NULL, NULL, 'Furniture', 'campon 2025', 'Received via PO PO-20260223-66189B', NULL, 'Available', NULL, 6, NULL, NULL, NULL, '2026-02-23 07:55:57', '2026-02-23 07:55:57', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `asset_assignments`
--

DROP TABLE IF EXISTS `asset_assignments`;
CREATE TABLE IF NOT EXISTS `asset_assignments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `asset_id` int NOT NULL,
  `user_id` int NOT NULL,
  `assigned_by` int NOT NULL COMMENT 'User who made the assignment (Admin/Asset Manager)',
  `assigned_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `returned_at` timestamp NULL DEFAULT NULL COMMENT 'When asset was returned (NULL = still assigned)',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT 'Reason for assignment/return',
  PRIMARY KEY (`id`),
  KEY `idx_asset_id` (`asset_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_assigned_by` (`assigned_by`),
  KEY `idx_assigned_at` (`assigned_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `campus_id` int DEFAULT NULL,
  `action` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `table_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `record_id` int DEFAULT NULL,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `user_agent` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_campus_id` (`campus_id`),
  KEY `idx_action` (`action`),
  KEY `idx_table_name` (`table_name`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `campus_id`, `action`, `table_name`, `record_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 1, 'approve_quotation', 'quotations', 3, '{\"status\": \"Pending\"}', '{\"status\": \"Approved\"}', NULL, NULL, '2026-02-28 17:49:45'),
(2, 1, 1, 'generate_po', 'purchase_orders', 8, '[]', '{\"po_number\": \"PO-20260228-E541B7\", \"quotation_id\": 3}', NULL, NULL, '2026-02-28 18:28:46'),
(3, 1, 1, 'approve_quotation', 'quotations', 2, '{\"status\": \"Pending\"}', '{\"status\": \"Approved\"}', NULL, NULL, '2026-02-28 18:37:36'),
(4, 1, 1, 'generate_po', 'purchase_orders', 9, '[]', '{\"po_number\": \"PO-20260228-0BD19F\", \"quotation_id\": 2}', NULL, NULL, '2026-02-28 18:37:36');

-- --------------------------------------------------------

--
-- Table structure for table `campuses`
--

DROP TABLE IF EXISTS `campuses`;
CREATE TABLE IF NOT EXISTS `campuses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `campus_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `campus_code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `location` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `campus_code` (`campus_code`),
  KEY `idx_campus_code` (`campus_code`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `campuses`
--

INSERT INTO `campuses` (`id`, `campus_name`, `campus_code`, `location`, `address`, `phone`, `is_active`, `created_at`) VALUES
(1, 'Beirut Campus', 'BRT', 'Beirut', 'Beirut, Lebanon', NULL, 1, '2026-02-16 07:22:57'),
(2, 'Bekaa Campus', 'BKA', 'Bekaa', 'Bekaa Valley, Lebanon', NULL, 1, '2026-02-16 07:22:57'),
(3, 'Saida Campus', 'SDA', 'Saida', 'Saida, South Lebanon', NULL, 1, '2026-02-16 07:22:57'),
(4, 'Nabatieh Campus', 'NBT', 'Nabatieh', 'Nabatieh, South Lebanon', NULL, 1, '2026-02-16 07:22:57'),
(5, 'Tripoli Campus', 'TRP', 'Tripoli', 'Tripoli, North Lebanon', NULL, 1, '2026-02-16 07:22:57'),
(6, 'Mount Lebanon Campus', 'MTL', 'Mount Lebanon', 'Mount Lebanon, Lebanon', NULL, 1, '2026-02-16 07:22:57'),
(7, 'Tyre Campus', 'TYR', 'Tyre', 'Tyre, South Lebanon', NULL, 1, '2026-02-16 07:22:57'),
(8, 'Rayak Campus', 'RYK', 'Rayak', 'Rayak, Bekaa Valley, Lebanon', NULL, 1, '2026-02-16 07:22:57'),
(9, 'Akkar Campus', 'AKR', 'Akkar', 'Akkar, North Lebanon', NULL, 1, '2026-02-16 07:22:57');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

DROP TABLE IF EXISTS `departments`;
CREATE TABLE IF NOT EXISTS `departments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `campus_id` int NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `type` enum('academic','administrative') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'academic',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_dept_per_campus` (`campus_id`,`name`),
  KEY `idx_campus_id` (`campus_id`)
) ENGINE=InnoDB AUTO_INCREMENT=123 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `campus_id`, `name`, `description`, `is_active`, `created_at`, `type`) VALUES
(1, 1, 'Engineering', 'Faculty of Engineering - Beirut Campus', 1, '2026-02-16 07:22:57', 'academic'),
(2, 1, 'Pharmacy', 'Faculty of Pharmacy - Beirut Campus', 1, '2026-02-16 07:22:57', 'academic'),
(3, 1, 'IT', 'Faculty of Information Technology - Beirut Campus', 1, '2026-02-16 07:22:57', 'academic'),
(4, 1, 'Arts & Sciences', 'Faculty of Arts & Sciences - Beirut Campus', 1, '2026-02-16 07:22:57', 'academic'),
(5, 2, 'Engineering', 'Faculty of Engineering - Bekaa Campus', 1, '2026-02-16 07:22:57', 'academic'),
(6, 2, 'Pharmacy', 'Faculty of Pharmacy - Bekaa Campus', 1, '2026-02-16 07:22:57', 'academic'),
(7, 2, 'IT', 'Faculty of Information Technology - Bekaa Campus', 1, '2026-02-16 07:22:57', 'academic'),
(8, 2, 'Arts & Sciences', 'Faculty of Arts & Sciences - Bekaa Campus', 1, '2026-02-16 07:22:57', 'academic'),
(9, 3, 'Engineering', 'Faculty of Engineering - Saida Campus', 1, '2026-02-16 07:22:57', 'academic'),
(10, 3, 'Pharmacy', 'Faculty of Pharmacy - Saida Campus', 1, '2026-02-16 07:22:57', 'academic'),
(11, 3, 'IT', 'Faculty of Information Technology - Saida Campus', 1, '2026-02-16 07:22:57', 'academic'),
(12, 3, 'Arts & Sciences', 'Faculty of Arts & Sciences - Saida Campus', 1, '2026-02-16 07:22:57', 'academic'),
(13, 4, 'Engineering', 'Faculty of Engineering - Nabatieh Campus', 1, '2026-02-16 07:22:57', 'academic'),
(14, 4, 'Pharmacy', 'Faculty of Pharmacy - Nabatieh Campus', 1, '2026-02-16 07:22:57', 'academic'),
(15, 4, 'IT', 'Faculty of Information Technology - Nabatieh Campus', 1, '2026-02-16 07:22:57', 'academic'),
(16, 4, 'Arts & Sciences', 'Faculty of Arts & Sciences - Nabatieh Campus', 1, '2026-02-16 07:22:57', 'academic'),
(17, 5, 'Engineering', 'Faculty of Engineering - Tripoli Campus', 1, '2026-02-16 07:22:57', 'academic'),
(18, 5, 'Pharmacy', 'Faculty of Pharmacy - Tripoli Campus', 1, '2026-02-16 07:22:57', 'academic'),
(19, 5, 'IT', 'Faculty of Information Technology - Tripoli Campus', 1, '2026-02-16 07:22:57', 'academic'),
(20, 5, 'Arts & Sciences', 'Faculty of Arts & Sciences - Tripoli Campus', 1, '2026-02-16 07:22:57', 'academic'),
(21, 6, 'Engineering', 'Faculty of Engineering - Mount Lebanon Campus', 1, '2026-02-16 07:22:57', 'academic'),
(22, 6, 'Pharmacy', 'Faculty of Pharmacy - Mount Lebanon Campus', 1, '2026-02-16 07:22:57', 'academic'),
(23, 6, 'IT', 'Faculty of Information Technology - Mount Lebanon Campus', 1, '2026-02-16 07:22:57', 'academic'),
(24, 6, 'Arts & Sciences', 'Faculty of Arts & Sciences - Mount Lebanon Campus', 1, '2026-02-16 07:22:57', 'academic'),
(25, 7, 'Engineering', 'Faculty of Engineering - Tyre Campus', 1, '2026-02-16 07:22:57', 'academic'),
(26, 7, 'Pharmacy', 'Faculty of Pharmacy - Tyre Campus', 1, '2026-02-16 07:22:57', 'academic'),
(27, 7, 'IT', 'Faculty of Information Technology - Tyre Campus', 1, '2026-02-16 07:22:57', 'academic'),
(28, 7, 'Arts & Sciences', 'Faculty of Arts & Sciences - Tyre Campus', 1, '2026-02-16 07:22:57', 'academic'),
(29, 8, 'Engineering', 'Faculty of Engineering - Rayak Campus', 1, '2026-02-16 07:22:57', 'academic'),
(30, 8, 'Pharmacy', 'Faculty of Pharmacy - Rayak Campus', 1, '2026-02-16 07:22:57', 'academic'),
(31, 8, 'IT', 'Faculty of Information Technology - Rayak Campus', 1, '2026-02-16 07:22:57', 'academic'),
(32, 8, 'Arts & Sciences', 'Faculty of Arts & Sciences - Rayak Campus', 1, '2026-02-16 07:22:57', 'academic'),
(33, 9, 'Engineering', 'Faculty of Engineering - Akkar Campus', 1, '2026-02-16 07:22:57', 'academic'),
(34, 9, 'Pharmacy', 'Faculty of Pharmacy - Akkar Campus', 1, '2026-02-16 07:22:57', 'academic'),
(35, 9, 'IT', 'Faculty of Information Technology - Akkar Campus', 1, '2026-02-16 07:22:57', 'academic'),
(36, 9, 'Arts & Sciences', 'Faculty of Arts & Sciences - Akkar Campus', 1, '2026-02-16 07:22:57', 'academic'),
(37, 1, 'School of Business', 'School of Business - Beirut Campus', 1, '2026-02-24 10:31:07', 'academic'),
(38, 2, 'School of Business', 'School of Business - Bekaa Campus', 1, '2026-02-24 10:31:07', 'academic'),
(39, 3, 'School of Business', 'School of Business - Saida Campus', 1, '2026-02-24 10:31:07', 'academic'),
(40, 4, 'School of Business', 'School of Business - Nabatieh Campus', 1, '2026-02-24 10:31:07', 'academic'),
(41, 5, 'School of Business', 'School of Business - Tripoli Campus', 1, '2026-02-24 10:31:07', 'academic'),
(42, 6, 'School of Business', 'School of Business - Mount Lebanon Campus', 1, '2026-02-24 10:31:07', 'academic'),
(43, 7, 'School of Business', 'School of Business - Tyre Campus', 1, '2026-02-24 10:31:07', 'academic'),
(44, 8, 'School of Business', 'School of Business - Rayak Campus', 1, '2026-02-24 10:31:07', 'academic'),
(45, 9, 'School of Business', 'School of Business - Akkar Campus', 1, '2026-02-24 10:31:07', 'academic'),
(46, 1, 'School of Education', 'School of Education - Beirut Campus', 1, '2026-02-24 10:31:07', 'academic'),
(47, 2, 'School of Education', 'School of Education - Bekaa Campus', 1, '2026-02-24 10:31:07', 'academic'),
(48, 3, 'School of Education', 'School of Education - Saida Campus', 1, '2026-02-24 10:31:07', 'academic'),
(49, 4, 'School of Education', 'School of Education - Nabatieh Campus', 1, '2026-02-24 10:31:07', 'academic'),
(50, 5, 'School of Education', 'School of Education - Tripoli Campus', 1, '2026-02-24 10:31:07', 'academic'),
(51, 6, 'School of Education', 'School of Education - Mount Lebanon Campus', 1, '2026-02-24 10:31:07', 'academic'),
(52, 7, 'School of Education', 'School of Education - Tyre Campus', 1, '2026-02-24 10:31:07', 'academic'),
(53, 8, 'School of Education', 'School of Education - Rayak Campus', 1, '2026-02-24 10:31:07', 'academic'),
(54, 9, 'School of Education', 'School of Education - Akkar Campus', 1, '2026-02-24 10:31:07', 'academic'),
(77, 1, 'Library', 'Library - Beirut Campus', 1, '2026-02-25 05:56:17', 'administrative'),
(78, 1, 'Admissions', 'Admissions - Beirut Campus', 1, '2026-02-25 05:56:17', 'administrative'),
(79, 1, 'Finance', 'Finance - Beirut Campus', 1, '2026-02-25 05:56:17', 'administrative'),
(80, 1, 'HR', 'HR - Beirut Campus', 1, '2026-02-25 05:56:17', 'administrative'),
(81, 1, 'Registrar', 'Registrar - Beirut Campus', 1, '2026-02-25 05:56:17', 'administrative'),
(82, 1, 'Student Affairs', 'Student Affairs - Beirut Campus', 1, '2026-02-25 05:56:17', 'administrative'),
(83, 2, 'Library', 'Library - Bekaa Campus', 1, '2026-02-25 05:56:17', 'administrative'),
(84, 2, 'Admissions', 'Admissions - Bekaa Campus', 1, '2026-02-25 05:56:17', 'administrative'),
(85, 2, 'Finance', 'Finance - Bekaa Campus', 1, '2026-02-25 05:56:17', 'administrative'),
(86, 2, 'Registrar', 'Registrar - Bekaa Campus', 1, '2026-02-25 05:56:17', 'administrative'),
(87, 2, 'Student Affairs', 'Student Affairs - Bekaa Campus', 1, '2026-02-25 05:56:17', 'administrative'),
(88, 3, 'Library', 'Library - Saida Campus', 1, '2026-02-25 05:56:17', 'administrative'),
(89, 3, 'Admissions', 'Admissions - Saida Campus', 1, '2026-02-25 05:56:17', 'administrative'),
(90, 3, 'Finance', 'Finance - Saida Campus', 1, '2026-02-25 05:56:17', 'administrative'),
(91, 3, 'Registrar', 'Registrar - Saida Campus', 1, '2026-02-25 05:56:17', 'administrative'),
(92, 3, 'Student Affairs', 'Student Affairs - Saida Campus', 1, '2026-02-25 05:56:17', 'administrative'),
(93, 4, 'Library', 'Library - Nabatieh Campus', 1, '2026-02-25 05:56:17', 'administrative'),
(94, 4, 'Admissions', 'Admissions - Nabatieh Campus', 1, '2026-02-25 05:56:17', 'administrative'),
(95, 4, 'Finance', 'Finance - Nabatieh Campus', 1, '2026-02-25 05:56:17', 'administrative'),
(96, 4, 'Registrar', 'Registrar - Nabatieh Campus', 1, '2026-02-25 05:56:17', 'administrative'),
(97, 4, 'Student Affairs', 'Student Affairs - Nabatieh Campus', 1, '2026-02-25 05:56:17', 'administrative'),
(98, 5, 'Library', 'Library - Tripoli Campus', 1, '2026-02-25 05:56:17', 'administrative'),
(99, 5, 'Admissions', 'Admissions - Tripoli Campus', 1, '2026-02-25 05:56:17', 'administrative'),
(100, 5, 'Finance', 'Finance - Tripoli Campus', 1, '2026-02-25 05:56:17', 'administrative'),
(101, 5, 'Registrar', 'Registrar - Tripoli Campus', 1, '2026-02-25 05:56:17', 'administrative'),
(102, 5, 'Student Affairs', 'Student Affairs - Tripoli Campus', 1, '2026-02-25 05:56:17', 'administrative'),
(103, 6, 'Library', 'Library - Mount Lebanon Campus', 1, '2026-02-25 05:56:17', 'administrative'),
(104, 6, 'Admissions', 'Admissions - Mount Lebanon Campus', 1, '2026-02-25 05:56:17', 'administrative'),
(105, 6, 'Finance', 'Finance - Mount Lebanon Campus', 1, '2026-02-25 05:56:17', 'administrative'),
(106, 6, 'Registrar', 'Registrar - Mount Lebanon Campus', 1, '2026-02-25 05:56:17', 'administrative'),
(107, 6, 'Student Affairs', 'Student Affairs - Mount Lebanon Campus', 1, '2026-02-25 05:56:17', 'administrative'),
(108, 7, 'Library', 'Library - Tyre Campus', 1, '2026-02-25 05:56:17', 'administrative'),
(109, 7, 'Admissions', 'Admissions - Tyre Campus', 1, '2026-02-25 05:56:17', 'administrative'),
(110, 7, 'Finance', 'Finance - Tyre Campus', 1, '2026-02-25 05:56:17', 'administrative'),
(111, 7, 'Registrar', 'Registrar - Tyre Campus', 1, '2026-02-25 05:56:17', 'administrative'),
(112, 7, 'Student Affairs', 'Student Affairs - Tyre Campus', 1, '2026-02-25 05:56:17', 'administrative'),
(113, 8, 'Library', 'Library - Rayak Campus', 1, '2026-02-25 05:56:17', 'administrative'),
(114, 8, 'Admissions', 'Admissions - Rayak Campus', 1, '2026-02-25 05:56:17', 'administrative'),
(115, 8, 'Finance', 'Finance - Rayak Campus', 1, '2026-02-25 05:56:17', 'administrative'),
(116, 8, 'Registrar', 'Registrar - Rayak Campus', 1, '2026-02-25 05:56:17', 'administrative'),
(117, 8, 'Student Affairs', 'Student Affairs - Rayak Campus', 1, '2026-02-25 05:56:17', 'administrative'),
(118, 9, 'Library', 'Library - Akkar Campus', 1, '2026-02-25 05:56:17', 'administrative'),
(119, 9, 'Admissions', 'Admissions - Akkar Campus', 1, '2026-02-25 05:56:17', 'administrative'),
(120, 9, 'Finance', 'Finance - Akkar Campus', 1, '2026-02-25 05:56:17', 'administrative'),
(121, 9, 'Registrar', 'Registrar - Akkar Campus', 1, '2026-02-25 05:56:17', 'administrative'),
(122, 9, 'Student Affairs', 'Student Affairs - Akkar Campus', 1, '2026-02-25 05:56:17', 'administrative');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `ticket_id` int DEFAULT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `ticket_id` (`ticket_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `ticket_id`, `type`, `message`, `is_read`, `created_at`) VALUES
(1, 8, 26, 'ticket_returned', 'Your ticket \"Access Request –\" was returned by your department head. Reason: fix 123', 0, '2026-03-15 22:30:08');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_orders`
--

DROP TABLE IF EXISTS `purchase_orders`;
CREATE TABLE IF NOT EXISTS `purchase_orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `quotation_id` int DEFAULT NULL,
  `po_number` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `campus_id` int NOT NULL,
  `supplier` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `total_amount` decimal(12,2) DEFAULT '0.00',
  `status` enum('Draft','Pending Approval','Approved','Rejected','Completed','Cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'Draft',
  `approval_status` enum('Pending','Approved','Rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'Pending',
  `created_by` int NOT NULL,
  `approved_by` int DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `rejection_reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `approved_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `po_number` (`po_number`),
  KEY `idx_campus_id` (`campus_id`),
  KEY `idx_status` (`status`),
  KEY `idx_approval_status` (`approval_status`),
  KEY `idx_created_by` (`created_by`),
  KEY `idx_approved_by` (`approved_by`),
  KEY `fk_po_quotation` (`quotation_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase_orders`
--

INSERT INTO `purchase_orders` (`id`, `quotation_id`, `po_number`, `campus_id`, `supplier`, `total_amount`, `status`, `approval_status`, `created_by`, `approved_by`, `notes`, `rejection_reason`, `created_at`, `approved_at`, `updated_at`) VALUES
(1, NULL, 'PO-20260213-D2EE19', 1, 'Tech Supplies Inc', 6150.00, 'Draft', 'Pending', 1, NULL, 'Urgent order for new office equipment', NULL, '2026-02-13 06:10:37', NULL, '2026-02-13 06:10:37'),
(2, NULL, 'PO-20260213-983C58', 1, 'Tech Supplies Inc', 6000.00, 'Draft', 'Pending', 1, NULL, 'string', NULL, '2026-02-13 08:05:13', NULL, '2026-02-13 08:05:13'),
(3, NULL, 'PO-20260222-BA6C39', 1, 'Tech dt', 9000.00, 'Completed', 'Approved', 33, 1, 'urgent if you can be fast', NULL, '2026-02-22 20:14:03', '2026-02-23 05:51:45', '2026-02-23 05:58:00'),
(4, NULL, 'PO-20260223-C772F5', 1, 'tech cc', 3000.00, 'Completed', 'Approved', 33, 1, NULL, NULL, '2026-02-23 07:50:04', '2026-02-23 07:50:41', '2026-02-23 07:51:00'),
(5, NULL, 'PO-20260223-66189B', 1, 'tech hb', 2000.00, 'Completed', 'Approved', 33, 1, NULL, NULL, '2026-02-23 07:54:46', '2026-02-23 07:55:19', '2026-02-23 07:55:57'),
(6, NULL, 'PO-20260225-3BC339', 2, 'tech cc', 9000.00, 'Draft', 'Pending', 33, NULL, 'gbhgf', NULL, '2026-02-25 05:22:27', NULL, '2026-02-25 05:22:27'),
(7, NULL, 'PO-20260225-91C832', 2, 'tech cc', 9000.00, 'Draft', 'Pending', 33, NULL, 'gbhgf', NULL, '2026-02-25 05:22:33', NULL, '2026-02-25 05:22:33'),
(8, 3, 'PO-20260228-E541B7', 1, 'tech cc', 3444.00, 'Approved', 'Approved', 1, NULL, 'Generated from Quotation #erferf', NULL, '2026-02-28 18:28:46', NULL, '2026-02-28 18:28:46'),
(9, 2, 'PO-20260228-0BD19F', 1, 'lucii', 9000.00, 'Completed', 'Approved', 1, NULL, 'Generated from Quotation #mmmm', NULL, '2026-02-28 18:37:36', NULL, '2026-02-28 18:42:23');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_order_items`
--

DROP TABLE IF EXISTS `purchase_order_items`;
CREATE TABLE IF NOT EXISTS `purchase_order_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `po_id` int NOT NULL,
  `item_type` enum('stock','asset') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'stock',
  `stock_id` int DEFAULT NULL,
  `asset_category` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `asset_brand` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `asset_model` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `item_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `quantity` int NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(12,2) NOT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_po_id` (`po_id`),
  KEY `fk_poi_stock` (`stock_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase_order_items`
--

INSERT INTO `purchase_order_items` (`id`, `po_id`, `item_type`, `stock_id`, `asset_category`, `asset_brand`, `asset_model`, `item_name`, `quantity`, `unit_price`, `total_price`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 'stock', NULL, NULL, NULL, NULL, 'Laptop Dell XPS 15', 5, 1200.00, 6000.00, 'Include warranty', '2026-02-22 21:26:19', '2026-02-22 21:26:19'),
(2, 1, 'stock', NULL, NULL, NULL, NULL, 'USB-C Cable', 10, 15.00, 150.00, NULL, '2026-02-22 21:26:19', '2026-02-22 21:26:19'),
(3, 2, 'stock', NULL, NULL, NULL, NULL, 'Laptop Dell XPS 15', 5, 1200.00, 6000.00, 'string', '2026-02-22 21:26:19', '2026-02-22 21:26:19'),
(4, 3, 'asset', NULL, 'Laptop', 'mac', '2025', 'maclaptop2025', 3, 3000.00, 9000.00, NULL, '2026-02-22 22:14:03', '2026-02-22 22:14:03'),
(5, 4, 'stock', 2, NULL, NULL, NULL, 'blue pens', 100, 30.00, 3000.00, NULL, '2026-02-23 09:50:04', '2026-02-23 09:50:04'),
(6, 5, 'asset', NULL, 'Furniture', 'campon', '2025', 'pillow', 20, 100.00, 2000.00, NULL, '2026-02-23 09:54:46', '2026-02-23 09:54:46'),
(7, 6, 'stock', 2, NULL, NULL, NULL, 'blue pens', 1, 9000.00, 9000.00, 'fdgh', '2026-02-25 07:22:27', '2026-02-25 07:22:27'),
(8, 7, 'stock', 2, NULL, NULL, NULL, 'blue pens', 1, 9000.00, 9000.00, 'fdgh', '2026-02-25 07:22:33', '2026-02-25 07:22:33');

--
-- Triggers `purchase_order_items`
--
DROP TRIGGER IF EXISTS `update_po_total_after_item_delete`;
DELIMITER $$
CREATE TRIGGER `update_po_total_after_item_delete` AFTER DELETE ON `purchase_order_items` FOR EACH ROW BEGIN
    UPDATE purchase_orders 
    SET total_amount = (
        SELECT COALESCE(SUM(total_price), 0) 
        FROM purchase_order_items 
        WHERE po_id = OLD.po_id
    )
    WHERE id = OLD.po_id;
END
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `update_po_total_after_item_insert`;
DELIMITER $$
CREATE TRIGGER `update_po_total_after_item_insert` AFTER INSERT ON `purchase_order_items` FOR EACH ROW BEGIN
    UPDATE purchase_orders 
    SET total_amount = (
        SELECT SUM(total_price) 
        FROM purchase_order_items 
        WHERE po_id = NEW.po_id
    )
    WHERE id = NEW.po_id;
END
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `update_po_total_after_item_update`;
DELIMITER $$
CREATE TRIGGER `update_po_total_after_item_update` AFTER UPDATE ON `purchase_order_items` FOR EACH ROW BEGIN
    UPDATE purchase_orders 
    SET total_amount = (
        SELECT SUM(total_price) 
        FROM purchase_order_items 
        WHERE po_id = NEW.po_id
    )
    WHERE id = NEW.po_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `quotations`
--

DROP TABLE IF EXISTS `quotations`;
CREATE TABLE IF NOT EXISTS `quotations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `quotation_number` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `supplier_id` int NOT NULL,
  `campus_id` int NOT NULL,
  `total_amount` decimal(12,2) NOT NULL,
  `quotation_date` date NOT NULL,
  `valid_until` date NOT NULL,
  `status` enum('Pending','Approved','Rejected','Expired') COLLATE utf8mb4_unicode_ci DEFAULT 'Pending',
  `file_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uploaded_at` timestamp NULL DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_by` int NOT NULL,
  `approved_by` int DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `supplier_id` (`supplier_id`),
  KEY `campus_id` (`campus_id`),
  KEY `created_by` (`created_by`),
  KEY `approved_by` (`approved_by`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `quotations`
--

INSERT INTO `quotations` (`id`, `quotation_number`, `supplier_id`, `campus_id`, `total_amount`, `quotation_date`, `valid_until`, `status`, `file_path`, `uploaded_at`, `notes`, `created_by`, `approved_by`, `approved_at`, `rejection_reason`, `created_at`, `updated_at`) VALUES
(1, 'mmmm', 1, 1, 9000.00, '2026-02-28', '2026-03-03', 'Expired', NULL, NULL, 'hbhyhb', 1, NULL, NULL, NULL, '2026-02-28 14:13:08', '2026-03-11 19:03:50'),
(2, 'mmmm', 1, 1, 9000.00, '2026-02-28', '2026-03-03', 'Approved', NULL, NULL, 'hbhyhb', 1, 1, '2026-02-28 18:37:36', NULL, '2026-02-28 14:19:13', '2026-02-28 18:37:36'),
(3, 'erferf', 2, 1, 3444.00, '2026-02-28', '2026-03-02', 'Approved', '/internal_portal/public/uploads/quotations/QUO-3-20260228-174933-0c85f022.pdf', '2026-02-28 17:49:33', 'erferf', 1, 1, '2026-02-28 17:49:45', NULL, '2026-02-28 14:26:04', '2026-02-28 17:49:45');

-- --------------------------------------------------------

--
-- Table structure for table `quotation_requests`
--

DROP TABLE IF EXISTS `quotation_requests`;
CREATE TABLE IF NOT EXISTS `quotation_requests` (
  `id` int NOT NULL AUTO_INCREMENT,
  `quotation_id` int DEFAULT NULL,
  `supplier_id` int NOT NULL,
  `campus_id` int NOT NULL,
  `requested_by` int NOT NULL,
  `requested_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `response_due_date` date DEFAULT NULL,
  `status` enum('Sent','Failed','Received') COLLATE utf8mb4_unicode_ci DEFAULT 'Sent',
  `email_subject` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_body` text COLLATE utf8mb4_unicode_ci,
  `error_message` text COLLATE utf8mb4_unicode_ci,
  `notes` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `supplier_id` (`supplier_id`),
  KEY `campus_id` (`campus_id`),
  KEY `requested_by` (`requested_by`),
  KEY `quotation_id` (`quotation_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `quotation_requests`
--

INSERT INTO `quotation_requests` (`id`, `quotation_id`, `supplier_id`, `campus_id`, `requested_by`, `requested_at`, `response_due_date`, `status`, `email_subject`, `email_body`, `error_message`, `notes`) VALUES
(1, 3, 2, 1, 1, '2026-02-28 17:11:39', '2026-03-04', 'Sent', 'Request for Quotation — Lebanese International University', '\r\n                <div style=\'font-family:Arial,sans-serif;max-width:600px;margin:0 auto;\'>\r\n                    <div style=\'background:#1e40af;padding:24px;border-radius:8px 8px 0 0;\'>\r\n                        <h2 style=\'color:#fff;margin:0;\'>Request for Quotation</h2>\r\n                        <p style=\'color:#bfdbfe;margin:4px 0 0;\'>Lebanese International University — Procurement</p>\r\n                    </div>\r\n                    <div style=\'background:#f8fafc;padding:24px;border:1px solid #e2e8f0;border-radius:0 0 8px 8px;\'>\r\n                        <p>Dear <strong>tech cc</strong>,</p>\r\n                        <p>We would like to request a formal quotation from your company for the items/services required by our institution.</p>\r\n                        <p><strong>Details:</strong><br>dfvdf</p>\r\n                        <div style=\'background:#fff;border:1px solid #e2e8f0;border-radius:6px;padding:16px;margin:16px 0;\'>\r\n                            <p style=\'margin:0;\'><strong>Response Required By:</strong> 04 Mar 2026</p>\r\n                        </div>\r\n                        <p>Please reply to this email with your quotation PDF and pricing details.</p>\r\n                        <p style=\'color:#64748b;font-size:13px;margin-top:24px;\'>\r\n                            This request was sent by the Procurement Office.<br>\r\n                            Lebanese International University\r\n                        </p>\r\n                    </div>\r\n                </div>\r\n            ', NULL, 'dfvdf');

-- --------------------------------------------------------

--
-- Table structure for table `stock`
--

DROP TABLE IF EXISTS `stock`;
CREATE TABLE IF NOT EXISTS `stock` (
  `id` int NOT NULL AUTO_INCREMENT,
  `campus_id` int NOT NULL,
  `item_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `category` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `quantity` int DEFAULT '0',
  `unit` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `minimum_threshold` int DEFAULT '10',
  `last_updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_stock_item_per_campus` (`campus_id`,`item_name`),
  KEY `idx_campus_id` (`campus_id`),
  KEY `idx_category` (`category`),
  KEY `idx_quantity` (`quantity`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stock`
--

INSERT INTO `stock` (`id`, `campus_id`, `item_name`, `category`, `quantity`, `unit`, `minimum_threshold`, `last_updated`, `created_at`) VALUES
(1, 1, 'A4 Paper', 'Office Supplies', 50, 'boxes', 20, '2026-02-13 08:18:08', '2026-02-13 08:18:08'),
(2, 1, 'blue pens', 'office supplies', 153, 'boxes', 10, '2026-02-23 07:51:00', '2026-02-18 11:39:06'),
(3, 1, 'HDMI Cables', 'Office Supplies', 9990, 'boxes', 988, '2026-02-20 08:29:59', '2026-02-20 08:29:59'),
(4, 1, 'USB Flash Drive 32GB', 'IT Accessories', 40, 'pieces', 10, '2026-02-20 08:33:14', '2026-02-20 08:33:14'),
(5, 2, 'Printer Ink Cartridge HP 305', 'Printer Supplies', 900, 'pieces', 10, '2026-02-20 08:41:22', '2026-02-20 08:41:22'),
(6, 1, 'hdmi3cables', 'Other', 110, 'box', 10, '2026-02-23 06:24:06', '2026-02-22 18:22:43'),
(7, 1, 'hdmi5cables', 'Other', 49, 'pack', 10, '2026-02-23 07:58:56', '2026-02-23 07:52:33');

-- --------------------------------------------------------

--
-- Table structure for table `subtask_comments`
--

DROP TABLE IF EXISTS `subtask_comments`;
CREATE TABLE IF NOT EXISTS `subtask_comments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `subtask_id` int NOT NULL,
  `user_id` int NOT NULL,
  `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `subtask_id` (`subtask_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subtask_comments`
--

INSERT INTO `subtask_comments` (`id`, `subtask_id`, `user_id`, `comment`, `created_at`) VALUES
(1, 1, 1, 'mhmd do this', '2026-02-27 07:51:22');

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

DROP TABLE IF EXISTS `suppliers`;
CREATE TABLE IF NOT EXISTS `suppliers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`id`, `name`, `email`, `phone`, `address`, `is_active`, `created_at`) VALUES
(1, 'lucii', '12232419@students.liu.edu.lb', '81050430', 'downtown', 1, '2026-02-28 14:09:04'),
(2, 'tech cc', 'aliceshmeis4@gmail.com', '81050430', 'erfrgtgtg', 1, '2026-02-28 14:25:42');

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

DROP TABLE IF EXISTS `tickets`;
CREATE TABLE IF NOT EXISTS `tickets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ticket_number` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `campus_id` int NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `building` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `floor` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `room` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ssid` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `category` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('Open','In Progress','Pending','Resolved','Closed','Returned') COLLATE utf8mb4_general_ci DEFAULT 'Open',
  `priority` enum('Low','Medium','High','Critical') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'Medium',
  `created_by` int NOT NULL,
  `assigned_to` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `closed_at` timestamp NULL DEFAULT NULL,
  `submitted_to_admin` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ticket_number` (`ticket_number`),
  KEY `idx_campus_id` (`campus_id`),
  KEY `idx_status` (`status`),
  KEY `idx_priority` (`priority`),
  KEY `idx_created_by` (`created_by`),
  KEY `idx_assigned_to` (`assigned_to`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tickets`
--

INSERT INTO `tickets` (`id`, `ticket_number`, `campus_id`, `title`, `description`, `building`, `floor`, `room`, `ssid`, `category`, `status`, `priority`, `created_by`, `assigned_to`, `created_at`, `updated_at`, `closed_at`, `submitted_to_admin`) VALUES
(1, 'TKT-20260212-AC7C95', 1, 'string', 'string', NULL, NULL, NULL, NULL, NULL, 'Open', 'Low', 1, 5, '2026-02-12 08:07:22', '2026-02-16 08:34:45', '2026-02-12 08:26:03', 0),
(2, 'TKT-20260212-F8CAA5', 1, 'WiFi not working in conference room', 'The WiFi connection keeps dropping in Conference Room B', NULL, NULL, NULL, NULL, NULL, 'Open', 'High', 1, 3, '2026-02-12 08:28:15', '2026-02-16 06:43:25', NULL, 0),
(3, 'TKT-20260213-B29743', 1, 'Broken printer in office', 'The printer on the 2nd floor is not working', NULL, NULL, NULL, NULL, NULL, 'In Progress', 'High', 1, 5, '2026-02-13 07:56:27', '2026-02-16 06:43:25', NULL, 0),
(4, 'TKT-20260213-5E8E94', 1, 'Broken printer in office', 'The printer on the 2nd floor is not working', NULL, NULL, NULL, NULL, NULL, 'Resolved', 'High', 1, 2, '2026-02-13 08:19:01', '2026-02-16 06:43:25', NULL, 0),
(5, 'TKT-20260213-5D0778', 1, 'Broken printer in office', 'The printer on the 2nd floor is not working', NULL, NULL, NULL, NULL, NULL, 'Pending', 'High', 1, 3, '2026-02-13 08:20:53', '2026-02-16 06:43:25', NULL, 0),
(7, 'TKT-20260216-97D1D3', 1, 'Printer not working on 2nd floor', 'The HP printer in the main office is showing paper jam error', NULL, NULL, NULL, NULL, NULL, 'Open', 'Medium', 1, NULL, '2026-02-16 07:41:13', '2026-02-16 07:41:13', NULL, 0),
(9, 'TKT-20260216-995C83', 1, 'network', 'not connected', NULL, NULL, NULL, NULL, NULL, 'Open', 'Medium', 1, NULL, '2026-02-16 08:10:01', '2026-02-16 08:10:01', NULL, 0),
(10, 'TKT-20260216-C41A9B', 1, 'network', 'not connected', NULL, NULL, NULL, NULL, NULL, 'Open', 'Medium', 1, NULL, '2026-02-16 08:10:20', '2026-02-16 08:10:20', NULL, 0),
(11, 'TKT-20260216-0837F0', 1, 'network', 'not connected', NULL, NULL, NULL, NULL, NULL, 'Open', 'Medium', 1, 3, '2026-02-16 08:21:04', '2026-02-16 08:21:04', NULL, 0),
(12, 'TKT-20260216-FD31B8', 1, 'string', 'need 2 more days', NULL, NULL, NULL, NULL, NULL, 'In Progress', 'High', 8, 2, '2026-02-16 09:21:19', '2026-02-26 07:41:30', NULL, 0),
(13, 'TKT-20260217-4223EB', 1, 'the cable is not connected to electricuity', 'fix it', NULL, NULL, NULL, NULL, NULL, 'Open', 'Medium', 1, 7, '2026-02-17 07:30:28', '2026-02-17 07:30:28', NULL, 0),
(14, 'TKT-20260217-BDE7C3', 1, 'IT & Software –', 'visual studio not working\n\n--- Additional Details ---\nSoftware: visual stidio', NULL, NULL, NULL, NULL, NULL, 'Open', 'High', 8, NULL, '2026-02-17 10:33:47', '2026-02-17 10:33:47', NULL, 0),
(15, 'TKT-20260217-1BE894', 1, 'IT & Software –', 'visual studio not working\n\n--- Additional Details ---\nSoftware: visual stidio', NULL, NULL, NULL, NULL, NULL, 'Open', 'High', 8, NULL, '2026-02-17 10:33:53', '2026-02-17 10:33:53', NULL, 0),
(16, 'TKT-20260217-2CB208', 1, 'IT & Software –', 'visual studio not working\n\n--- Additional Details ---\nSoftware: visual stidio\n\n[Resolution Notes] Issue resolved', NULL, NULL, NULL, NULL, NULL, 'Resolved', 'High', 8, 8, '2026-02-17 10:33:54', '2026-02-19 15:49:37', NULL, 0),
(17, 'TKT-20260217-34C274', 1, 'IT & Software –', 'visual studio not working\n\n--- Additional Details ---\nSoftware: visual stidio', NULL, NULL, NULL, NULL, NULL, 'Open', 'High', 8, NULL, '2026-02-17 10:33:55', '2026-02-17 10:33:55', NULL, 0),
(18, 'TKT-20260217-584ACE', 1, 'the cable is not connected to electricuity', 'jmkj', NULL, NULL, NULL, NULL, NULL, 'Open', 'Medium', 8, NULL, '2026-02-17 10:36:53', '2026-02-17 10:36:53', NULL, 0),
(19, 'TKT-20260217-A7DCD3', 1, 'the cable is not connected to electricuity', 'jmkj\n\n[Resolution Notes] Issue resolved', NULL, NULL, NULL, NULL, NULL, 'Closed', 'Medium', 8, 2, '2026-02-17 10:36:58', '2026-02-20 06:38:32', '2026-02-20 06:38:32', 0),
(20, 'TKT-20260220-08AAD8', 1, 'IT & Software –', 'Software malfunction causing unexpected errors and preventing the system from performing as intended.\n\n--- Details ---\nsoftware name: visual stidio\nerror message: bvghfcvgvjhvfhjv\nwhat happened: i was openingh it', 'B', '1', '101', NULL, 'IT & Software', 'Open', 'Medium', 8, NULL, '2026-02-20 10:12:00', '2026-02-20 10:12:00', NULL, 0),
(21, 'TKT-20260220-DA76CF', 1, 'IT & Software –', 'Software malfunction causing unexpected errors and preventing the system from performing as intended.\n\n--- Details ---\nsoftware name: visual stidio\nerror message: bvghfcvgvjhvfhjv\nwhat happened: i was openingh it\n\n[Resolution Notes] Issue resolved', 'B', '1', '101', NULL, 'IT & Software', 'Resolved', 'Medium', 8, NULL, '2026-02-20 10:12:13', '2026-02-27 08:29:18', NULL, 0),
(22, 'TKT-20260220-425141', 1, 'IT & Software –', 'Software malfunction causing unexpected errors and preventing the system from performing as intended.\n\n--- Details ---\nsoftware name: visual stidio\nerror message: bvghfcvgvjhvfhjv\nwhat happened: i was openingh it', 'B', '1', '101', NULL, 'IT & Software', 'In Progress', 'Medium', 8, 20, '2026-02-20 10:12:20', '2026-03-01 09:59:42', NULL, 0),
(23, 'TKT-20260220-7AECB1', 1, 'IT & Software –', 'tlkmlhlf\n\n--- Details ---\nsoftware name: microsoft\nerror message: bvghfcvgvjhvfhjv\nwhat happened: i was openingh it', 'B', '1', '101', NULL, 'IT & Software', 'In Progress', 'Medium', 8, NULL, '2026-02-20 11:10:31', '2026-03-01 11:53:35', NULL, 0),
(24, 'TKT-20260220-20F06D', 1, 'Network Problem –', 'no hgbh\n\n--- Details ---\nconnection type: WiFi\nssid: blionk\naffecting others: Yes', 'B', '1', '101', 'blionk', 'Network Problem', 'Open', 'High', 8, NULL, '2026-02-20 11:29:38', '2026-02-20 11:29:38', NULL, 0),
(25, 'TKT-20260220-A1D104', 1, 'Network Problem –', 'sdfgdg\n\n--- Details ---\nconnection type: WiFi\nssid: dfgfd', 'B', '1', '101', 'dfgfd', 'Network Problem', 'Open', 'High', 8, NULL, '2026-02-20 11:30:18', '2026-02-20 11:30:18', NULL, 0),
(26, 'TKT-20260220-840062', 1, 'Access Request –', 'lok\n\n--- Details ---\nsystem name: hgvj\naccess level: Read & Write\naccess reason: hgj', NULL, '1', '101', NULL, 'Access Request', 'Returned', 'Low', 8, 8, '2026-02-20 11:40:56', '2026-03-15 20:30:30', NULL, 0),
(27, 'TKT-20260220-C18CE6', 1, 'Network Problem –', 'jhhvj\n\n--- Details ---\nconnection type: Wired\naffecting others: Yes', NULL, NULL, '101', NULL, 'Network Problem', 'Open', 'High', 8, NULL, '2026-02-20 11:41:48', '2026-02-20 11:41:48', NULL, 0),
(28, 'TKT-20260220-864F10', 1, 'Printer Issue –', 'klj\n\n--- Details ---\nprinter id: 6\nprinter issue: No Ink/Toner\nurgent: Yes', 'B', NULL, '101', NULL, 'Printer Issue', 'Resolved', 'High', 8, 20, '2026-02-20 11:42:16', '2026-02-26 07:39:34', NULL, 0),
(29, 'TKT-20260221-A529BC', 2, 'IT & Software –', 'dfkngbi\n\n--- Details ---\nsoftware name: dfgg\nerror message: drgftg\nwhat happened: drgfdfg', 'A', '1', '101', NULL, 'IT & Software', 'Open', 'Medium', 16, NULL, '2026-02-21 10:38:02', '2026-02-21 10:38:02', NULL, 0),
(30, 'TKT-20260221-D7516F', 2, 'IT & Software –', 'jnsdjfcn\n\n--- Details ---\nsoftware name: dfjgnjfgndf\nerror message: gdfgd\nwhat happened: dfgdfg', 'A', '1', '202', NULL, 'IT & Software', 'Open', 'Medium', 16, NULL, '2026-02-21 10:38:53', '2026-02-21 10:38:53', NULL, 0),
(31, 'TKT-20260221-72D236', 2, 'Network Problem –', 'sjdfnjs\n\n--- Details ---\nconnection type: Wired\naffecting others: Yes', 'A', '1', '101', NULL, 'Network Problem', 'Open', 'High', 16, NULL, '2026-02-21 11:41:43', '2026-02-21 13:21:09', NULL, 0),
(32, 'TKT-20260221-34238E', 3, 'Hardware Issue –', 'hjbhjbk\n\n--- Details ---\nhardware issue: Keyboard / Mouse', 'A', '1', '101', NULL, 'Hardware Issue', 'In Progress', 'Medium', 19, 19, '2026-02-21 13:31:15', '2026-02-25 10:14:31', NULL, 0),
(33, 'TKT-20260222-35264E', 9, 'IT & Software –', 'kmkm\n\n--- Details ---\nsoftware name: word\nerror message: efwef\nwhat happened: sdeff', 'A', '1', '101', NULL, 'IT & Software', 'Open', 'Medium', 30, NULL, '2026-02-22 11:00:35', '2026-02-22 11:00:35', NULL, 0),
(34, 'TKT-20260222-D0CEC1', 9, 'Network Problem –', ',mm\n\n--- Details ---\nconnection type: WiFi\nssid: blink\naffecting others: Yes', 'b', '1', '101', 'blink', 'Network Problem', 'Open', 'High', 30, NULL, '2026-02-22 11:18:53', '2026-02-22 11:18:53', NULL, 0),
(35, 'TKT-20260223-751FDD', 2, 'IT & Software –', 'started yesterday\n\n--- Details ---\nsoftware name: microsoft\nerror message: bvghfcvgvjhvfhjv\nwhat happened: open it', 'B', '1', '101', NULL, 'IT & Software', 'Open', 'High', 42, NULL, '2026-02-23 09:31:19', '2026-02-23 09:38:57', NULL, 0),
(36, 'TKT-20260223-22399D', 2, 'IT & Software –', 'loi\n\n--- Details ---\nsoftware name: wamp\nerror message: bvghfcvgvjhvfhjv\nwhat happened: open it\n\n[Resolution Notes] Issue resolved', NULL, NULL, NULL, NULL, 'IT & Software', 'Closed', 'Medium', 43, 44, '2026-02-23 10:00:34', '2026-02-27 08:26:57', '2026-02-27 08:26:57', 0),
(37, 'TKT-20260225-7B922D', 2, 'IT & Systems –', 'this is not working', 'B', '1', '101', NULL, 'IT & Systems', 'Resolved', 'Medium', 43, 44, '2026-02-25 07:48:39', '2026-02-25 11:13:54', NULL, 0),
(38, 'TKT-20260225-0C6F59', 2, 'IT & Systems –', 'Blue screen error on office desktop', 'G', '1', '101', NULL, 'IT & Systems', 'Open', 'Medium', 43, NULL, '2026-02-25 08:06:40', '2026-02-25 08:06:40', NULL, 0),
(39, 'TKT-20260225-C5454A', 2, 'Registrar Services –', 'Cannot register for course (prerequisite error)', 'E', '1', '101', NULL, 'Registrar Services', 'Open', 'Medium', 43, NULL, '2026-02-25 08:07:24', '2026-02-25 08:07:24', NULL, 0),
(40, 'TKT-20260225-47DB7A', 2, 'Human Resources –', 'Salary not credited this month', 'C', '1', '101', NULL, 'Human Resources', 'Open', 'High', 43, NULL, '2026-02-25 08:07:48', '2026-02-25 08:07:48', NULL, 0),
(41, 'TKT-20260225-4E1BCD', 2, 'Finance –', 'Invoice showing incorrect amount', 'C', '1', '101', NULL, 'Finance', 'Open', 'Critical', 43, NULL, '2026-02-25 08:09:24', '2026-02-25 08:09:24', NULL, 0),
(42, 'TKT-20260225-1A28AB', 2, 'Academic Request –', 'Major change request', 'B', '1', '101', NULL, 'Academic Request', 'Pending', 'Low', 43, 46, '2026-02-25 08:10:41', '2026-02-26 05:18:41', NULL, 0),
(43, 'TKT-20260225-59B2D7', 2, 'Facilities –', 'Broken classroom chair', 'G', '1', '101', NULL, 'Facilities', 'Closed', 'Medium', 43, 46, '2026-02-25 08:11:01', '2026-02-25 10:09:13', '2026-02-25 10:09:13', 0),
(44, 'TKT-20260225-28AD4D', 3, 'IT & Systems –', 'why there is a block on the entry popint?', 'G', '6', '606', NULL, 'IT & Systems', 'Pending', 'Medium', 19, 48, '2026-02-25 10:16:18', '2026-02-25 10:31:35', NULL, 0),
(45, 'TKT-20260416-0EF5C4', 1, 'Admissions –', 'graduate paper of hassan', 'A', '1', '101', NULL, 'Admissions', 'Open', 'Medium', 20, 55, '2026-04-16 19:00:16', '2026-04-16 19:00:16', NULL, 0),
(46, 'TKT-20260416-76F0E6', 1, 'HR –', 'paper', 'b', '1', '1', NULL, 'HR', 'Open', 'Medium', 20, 55, '2026-04-16 19:04:55', '2026-04-16 19:04:55', NULL, 0),
(47, 'TKT-20260417-D5C31C', 1, 'HR –', 'papercctx', 'b', '1', '101', NULL, 'HR', 'Resolved', 'Medium', 150, 51, '2026-04-17 17:46:21', '2026-04-17 18:04:25', NULL, 1),
(48, 'TKT-20260417-ABD09C', 1, 'Finance –', 'cash withdraw', 'b', '1', '101', NULL, 'Finance', 'Open', 'Medium', 150, 51, '2026-04-17 19:22:50', '2026-04-17 19:22:50', NULL, 0),
(49, 'TKT-20260418-D9314A', 8, 'School of Business –', 'down pc2', 'c', '1', '101', NULL, 'School of Business', 'Open', 'Medium', 151, 134, '2026-04-18 08:04:45', '2026-04-18 08:04:45', NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `ticket_attachments`
--

DROP TABLE IF EXISTS `ticket_attachments`;
CREATE TABLE IF NOT EXISTS `ticket_attachments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ticket_id` int NOT NULL,
  `file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `file_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `file_size` int NOT NULL,
  `file_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `uploaded_by` int NOT NULL,
  `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ticket_id` (`ticket_id`),
  KEY `idx_uploaded_by` (`uploaded_by`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ticket_attachments`
--

INSERT INTO `ticket_attachments` (`id`, `ticket_id`, `file_name`, `file_path`, `file_size`, `file_type`, `uploaded_by`, `uploaded_at`) VALUES
(1, 24, 'Screenshot 2026-02-10 135438.png', '/internal_portal/storage/attachments/24/attach_699845a223698.png', 22240, 'image/png', 8, '2026-02-20 11:29:38'),
(2, 25, 'Screenshot 2026-02-10 135438.png', '/internal_portal/storage/attachments/25/attach_699845ca25307.png', 22240, 'image/png', 8, '2026-02-20 11:30:18'),
(3, 26, 'Screenshot 2026-02-10 135458.png', '/internal_portal/storage/attachments/26/attach_69984848483ea.png', 19960, 'image/png', 8, '2026-02-20 11:40:56'),
(4, 27, 'Screenshot 2026-02-10 135438.png', '/internal_portal/storage/attachments/27/attach_6998487c22648.png', 22240, 'image/png', 8, '2026-02-20 11:41:48'),
(5, 28, 'Screenshot 2026-02-10 135438.png', '/internal_portal/storage/attachments/28/attach_699848986b823.png', 22240, 'image/png', 8, '2026-02-20 11:42:16'),
(6, 29, 'Screenshot (1).png', '/internal_portal/storage/attachments/29/attach_69998b0a81d79.png', 943613, 'image/png', 16, '2026-02-21 10:38:02'),
(7, 31, 'Screenshot (1).png', '/internal_portal/storage/attachments/31/attach_699999f762d3f.png', 943613, 'image/png', 16, '2026-02-21 11:41:43'),
(8, 35, 'Screenshot 2026-02-10 135438.png', '/internal_portal/storage/attachments/35/attach_699c1e67608b3.png', 22240, 'image/png', 42, '2026-02-23 09:31:19'),
(9, 36, 'Screenshot 2026-02-10 142509.png', '/internal_portal/storage/attachments/36/attach_699c25422cbdc.png', 80902, 'image/png', 43, '2026-02-23 10:00:34'),
(10, 37, 'Screenshot 2026-02-10 135458.png', '/internal_portal/storage/attachments/37/attach_699ea957c78a1.png', 19960, 'image/png', 43, '2026-02-25 07:48:39'),
(11, 38, 'Screenshot 2026-02-10 142509.png', '/internal_portal/storage/attachments/38/attach_699ead90cef46.png', 80902, 'image/png', 43, '2026-02-25 08:06:40'),
(12, 28, 'Screenshot 2026-02-10 151135.png', '/internal_portal/storage/attachments/28/attach_699ff8481f331.png', 81135, 'image/png', 8, '2026-02-26 07:37:44'),
(13, 45, 'cccccc.pdf', '/internal_portal/storage/attachments/45/attach_69e131c120fb6.pdf', 943528, 'application/pdf', 20, '2026-04-16 19:00:17'),
(14, 46, 'kk.pdf', '/internal_portal/storage/attachments/46/attach_69e132d783ccf.pdf', 939649, 'application/pdf', 20, '2026-04-16 19:04:55'),
(15, 47, 'liulogo.png', '/internal_portal/storage/attachments/47/attach_69e271ed7dd19.png', 14899, 'image/png', 150, '2026-04-17 17:46:21');

-- --------------------------------------------------------

--
-- Table structure for table `ticket_comments`
--

DROP TABLE IF EXISTS `ticket_comments`;
CREATE TABLE IF NOT EXISTS `ticket_comments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ticket_id` int NOT NULL,
  `user_id` int NOT NULL,
  `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ticket_id` (`ticket_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ticket_comments`
--

INSERT INTO `ticket_comments` (`id`, `ticket_id`, `user_id`, `comment`, `created_at`) VALUES
(1, 19, 1, 'taken into consideration', '2026-02-19 19:22:44'),
(2, 22, 1, 'sfjmkl', '2026-02-20 10:51:23'),
(3, 28, 1, 'ok', '2026-02-20 12:15:33'),
(4, 31, 1, 'tfy', '2026-02-21 13:21:09'),
(5, 35, 34, 'I\'ll think', '2026-02-23 09:38:57'),
(6, 36, 43, 'okay', '2026-02-24 05:43:42'),
(7, 36, 43, 'okay', '2026-02-24 05:43:59'),
(8, 36, 43, 'okay', '2026-02-24 05:44:29'),
(9, 36, 43, '\'', '2026-02-24 05:45:30'),
(10, 36, 43, 'okay', '2026-02-24 05:47:25'),
(11, 36, 43, 'jlk', '2026-02-24 05:47:30'),
(12, 36, 44, 'done', '2026-02-24 05:48:37'),
(13, 28, 8, 'here is the missing paper', '2026-02-26 07:37:44'),
(14, 12, 8, 'done', '2026-02-26 07:41:30'),
(15, 22, 20, 'okay deal', '2026-03-01 09:59:42'),
(16, 26, 55, '[Head Return] fix 123', '2026-03-15 20:30:08'),
(17, 26, 55, 'fix 123', '2026-03-15 20:30:30'),
(18, 47, 51, 'urgent', '2026-04-17 18:01:53'),
(19, 47, 51, '[Submitted to Admin] Department head reviewed and forwarded this ticket.', '2026-04-17 18:02:01');

-- --------------------------------------------------------

--
-- Table structure for table `ticket_subtasks`
--

DROP TABLE IF EXISTS `ticket_subtasks`;
CREATE TABLE IF NOT EXISTS `ticket_subtasks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ticket_id` int NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` enum('Open','In Progress','Done') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Open',
  `priority` enum('Low','Medium','High','Critical') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Medium',
  `due_date` date DEFAULT NULL,
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ticket_id` (`ticket_id`),
  KEY `created_by` (`created_by`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ticket_subtasks`
--

INSERT INTO `ticket_subtasks` (`id`, `ticket_id`, `title`, `description`, `status`, `priority`, `due_date`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 21, 'hjnh', 'ghjhg', 'In Progress', 'Medium', '2026-02-27', 1, '2026-02-27 07:15:47', '2026-02-27 07:51:22'),
(2, 21, 'check its settings', 'open navigate like the descriptiohn i gave you', 'Open', 'Medium', '2026-02-28', 1, '2026-02-27 07:17:13', '2026-02-27 07:17:13'),
(3, 21, 'prepare school one has', 'go to it then update', 'Open', 'Medium', '2026-03-03', 1, '2026-02-27 08:22:29', '2026-02-27 08:22:29'),
(4, 22, 'prepare efjr', 'handle itt welll', 'Open', 'Medium', '2026-03-30', 1, '2026-03-01 10:00:40', '2026-03-01 10:00:40'),
(5, 22, 'test it', 'test it well', 'Open', 'Medium', '2026-04-30', 1, '2026-03-01 10:01:16', '2026-03-01 10:01:16'),
(6, 23, 'hbhbhj', 'hbhhjbvhj', 'Open', 'Medium', '2026-04-11', 1, '2026-03-01 10:06:02', '2026-03-01 10:06:02'),
(7, 23, 'gvgh', 'nvgvgh', 'Open', 'Medium', '2026-04-09', 1, '2026-03-01 10:06:35', '2026-03-01 10:06:35');

-- --------------------------------------------------------

--
-- Table structure for table `ticket_subtask_assignments`
--

DROP TABLE IF EXISTS `ticket_subtask_assignments`;
CREATE TABLE IF NOT EXISTS `ticket_subtask_assignments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `subtask_id` int NOT NULL,
  `user_id` int NOT NULL,
  `user_status` enum('Assigned','In Progress','Done') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Assigned',
  `assigned_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_subtask_user` (`subtask_id`,`user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ticket_subtask_assignments`
--

INSERT INTO `ticket_subtask_assignments` (`id`, `subtask_id`, `user_id`, `user_status`, `assigned_at`, `updated_at`) VALUES
(1, 1, 9, 'Assigned', '2026-02-27 07:15:47', '2026-02-27 07:15:47'),
(2, 1, 8, 'In Progress', '2026-02-27 07:15:47', '2026-02-27 07:23:26'),
(3, 2, 45, 'Assigned', '2026-02-27 07:17:13', '2026-02-27 07:17:13'),
(4, 2, 3, 'Assigned', '2026-02-27 07:17:13', '2026-02-27 07:17:13'),
(5, 3, 8, 'Assigned', '2026-02-27 08:22:29', '2026-02-27 08:22:29'),
(6, 4, 2, 'Assigned', '2026-03-01 10:00:40', '2026-03-01 10:00:40'),
(7, 5, 45, 'Assigned', '2026-03-01 10:01:16', '2026-03-01 10:01:16'),
(8, 6, 3, 'Assigned', '2026-03-01 10:06:02', '2026-03-01 10:06:02'),
(9, 7, 45, 'Assigned', '2026-03-01 10:06:35', '2026-03-01 10:06:35');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `google_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `login_method` enum('google','email') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'google',
  `email_verified` tinyint(1) DEFAULT '0',
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `profile_picture` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `role` enum('Admin','Staff','Asset Manager','Viewer') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `campus_id` int DEFAULT NULL,
  `department_id` int DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `is_head` tinyint(1) DEFAULT '0',
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `google_id` (`google_id`),
  KEY `idx_campus_id` (`campus_id`),
  KEY `idx_role` (`role`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_department_id` (`department_id`),
  KEY `idx_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=152 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `google_id`, `email`, `password`, `login_method`, `email_verified`, `name`, `profile_picture`, `role`, `campus_id`, `department_id`, `is_active`, `is_head`, `last_login`, `created_at`, `updated_at`) VALUES
(1, '108803309753251049295', 'aliceshmeis4@gmail.com', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Alice Shmeis', 'https://lh3.googleusercontent.com/a/ACg8ocILkVUqcEKUrRHqiTOPodO0Kk_KB3VmHzvUcw7L95s84U5gAw=s96-c', 'Admin', 1, 3, 1, 0, '2026-04-17 19:29:34', '2026-02-12 06:32:26', '2026-04-17 19:29:34'),
(2, NULL, 'sarah.johnson@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Sarah Johnson', NULL, 'Staff', 1, 2, 1, 0, '2026-03-01 10:03:50', '2026-02-16 06:43:25', '2026-03-15 19:54:43'),
(3, NULL, 'michael.chen@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Michael Chen', NULL, 'Staff', 1, 3, 1, 0, NULL, '2026-02-16 06:43:25', '2026-03-15 19:54:43'),
(4, NULL, 'emily.rodriguez@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Emily Rodriguez', NULL, 'Staff', 2, 8, 1, 0, NULL, '2026-02-16 06:43:25', '2026-03-15 19:54:43'),
(5, NULL, 'david.kim@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'David Kim', NULL, 'Staff', 5, 17, 1, 0, '2026-02-16 09:43:13', '2026-02-16 06:43:25', '2026-03-15 19:54:43'),
(6, NULL, 'lisa.martinez@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Lisa Martinez', NULL, 'Staff', 3, 10, 1, 0, NULL, '2026-02-16 06:43:25', '2026-03-15 19:54:43'),
(7, NULL, 'james.thompson@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'James Thompson', NULL, 'Asset Manager', 1, 3, 0, 0, NULL, '2026-02-16 06:43:25', '2026-03-15 19:54:43'),
(8, '109200483030056019346', '82230025@students.liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Mhamad Mawla', NULL, 'Staff', 1, 1, 1, 0, '2026-03-15 21:31:01', '2026-02-16 06:49:03', '2026-03-15 21:31:01'),
(9, '108174192366709840915', '12031004@students.liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'google', 1, 'Malak Mallah', NULL, 'Staff', 1, NULL, 1, 0, NULL, '2026-02-17 13:44:01', '2026-03-15 19:54:43'),
(10, NULL, 'alisabah@students.liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 0, 'ali sabah', NULL, 'Asset Manager', 1, NULL, 1, 0, NULL, '2026-02-18 06:41:24', '2026-03-15 19:54:43'),
(11, NULL, 'elissa@students.liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 0, 'Elissa', NULL, 'Asset Manager', 9, NULL, 1, 0, NULL, '2026-02-18 07:44:58', '2026-03-15 19:54:43'),
(12, NULL, 'asss@students.liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 0, 'asss', NULL, 'Asset Manager', 4, 13, 1, 0, NULL, '2026-02-18 08:31:46', '2026-03-15 19:54:43'),
(13, NULL, 'bubbly@students.liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 0, 'bubbly', NULL, 'Asset Manager', 2, 5, 1, 0, NULL, '2026-02-18 10:09:22', '2026-03-15 19:54:43'),
(14, NULL, 'malakmallah@students.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 0, 'malakmallah', NULL, 'Staff', 4, 16, 1, 0, '2026-02-27 07:27:19', '2026-02-19 08:19:20', '2026-03-15 19:54:43'),
(15, NULL, 'khaleelali@students.liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 0, 'khaleel ali', NULL, 'Staff', 2, 6, 1, 0, '2026-02-19 08:28:50', '2026-02-19 08:21:53', '2026-03-15 19:54:43'),
(16, NULL, 'leyahamzi@students.liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 0, 'Leya Hamzi', NULL, 'Staff', 8, 31, 1, 0, NULL, '2026-02-20 05:42:35', '2026-03-15 19:54:43'),
(17, NULL, 'tonybeiruti@students.liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 0, 'Tony Beiruti', NULL, 'Staff', 5, 19, 1, 0, NULL, '2026-02-20 05:45:15', '2026-03-15 19:54:43'),
(18, NULL, 'adamabboud@students.liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 0, 'Adam Abboud', NULL, 'Staff', 6, 23, 1, 0, NULL, '2026-02-20 06:55:56', '2026-03-15 19:54:43'),
(19, NULL, 'mariamabdallah@students.liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 0, 'mariam abdallah', NULL, 'Staff', 3, 9, 1, 0, '2026-02-25 11:09:39', '2026-02-21 13:29:37', '2026-03-15 19:54:43'),
(20, NULL, 'hanidaou@students.liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 0, 'hani daou', NULL, 'Staff', 1, 3, 1, 0, '2026-04-17 17:31:34', '2026-02-21 16:52:47', '2026-04-17 17:31:34'),
(21, NULL, 'batoul@students.liu.edu.com', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 0, 'Batoul', NULL, 'Staff', 7, 27, 1, 0, '2026-02-21 17:40:22', '2026-02-21 17:39:49', '2026-03-15 19:54:43'),
(22, NULL, 'aliahmad@students.liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 0, 'ali ahmad', NULL, 'Staff', 5, 19, 1, 0, NULL, '2026-02-21 17:41:41', '2026-03-15 19:54:43'),
(23, NULL, 'fatima@students.liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 0, 'fatima', NULL, 'Staff', 2, 7, 1, 0, NULL, '2026-02-21 17:42:12', '2026-03-15 19:54:43'),
(24, NULL, 'jaafar@students.liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 0, 'jaafar', NULL, 'Staff', 6, 23, 1, 0, NULL, '2026-02-21 17:42:55', '2026-03-15 19:54:43'),
(25, NULL, 'ella@students.liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 0, 'Ella', NULL, 'Staff', 4, 15, 1, 0, NULL, '2026-02-21 17:44:04', '2026-03-15 19:54:43'),
(26, NULL, 'bouba@students.liu.edu.com', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 0, 'bouba', NULL, 'Staff', 8, 31, 1, 0, '2026-02-21 17:50:45', '2026-02-21 17:44:59', '2026-03-15 19:54:43'),
(27, NULL, 'abbas@students.liu.edu.com', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 0, 'Abbas', NULL, 'Staff', 3, 11, 1, 0, NULL, '2026-02-21 17:45:48', '2026-03-15 19:54:43'),
(28, NULL, 'hawlohaidar@students.liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 0, 'hawlohaidar', NULL, 'Staff', 5, 19, 1, 0, NULL, '2026-02-21 17:46:52', '2026-03-15 19:54:43'),
(29, NULL, 'cynthia@students.liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 0, 'cynthia', NULL, 'Staff', 7, 27, 1, 0, NULL, '2026-02-21 17:47:42', '2026-03-15 19:54:43'),
(30, NULL, 'manal@students.liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 0, 'Manal', NULL, 'Staff', 9, 33, 1, 0, '2026-02-22 11:45:00', '2026-02-22 10:53:29', '2026-03-15 19:54:43'),
(31, NULL, 'haidar@students.liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 0, 'Haidar', NULL, 'Staff', 9, 35, 1, 0, '2026-02-22 14:22:23', '2026-02-22 13:21:07', '2026-03-15 19:54:43'),
(32, NULL, 'jad@students.liu.edu.com', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 0, 'jad', NULL, 'Staff', 6, 23, 1, 0, '2026-02-22 14:08:09', '2026-02-22 13:53:57', '2026-03-15 19:54:43'),
(33, NULL, 'rania@students.liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 0, 'Rania', NULL, 'Asset Manager', 1, 4, 1, 0, '2026-02-27 06:07:09', '2026-02-22 18:16:11', '2026-03-15 19:54:43'),
(34, NULL, 'admin.bekaa@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Admin Bekaa', NULL, 'Admin', 2, NULL, 1, 0, '2026-02-27 08:23:10', '2026-02-23 06:55:30', '2026-03-15 19:54:43'),
(35, NULL, 'admin.saida@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Admin Saida', NULL, 'Admin', 3, NULL, 1, 0, '2026-02-25 10:31:54', '2026-02-23 06:55:30', '2026-03-15 19:54:43'),
(36, NULL, 'admin.nabatieh@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Admin Nabatieh', NULL, 'Admin', 4, NULL, 1, 0, '2026-02-23 07:22:13', '2026-02-23 06:55:30', '2026-03-15 19:54:43'),
(37, NULL, 'admin.tripoli@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Admin Tripoli', NULL, 'Admin', 5, NULL, 1, 0, NULL, '2026-02-23 06:55:30', '2026-03-15 19:54:43'),
(38, NULL, 'admin.mountlebanon@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Admin Mount Lebanon', NULL, 'Admin', 6, NULL, 1, 0, '2026-02-23 07:22:50', '2026-02-23 06:55:30', '2026-03-15 19:54:43'),
(39, NULL, 'admin.tyre@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Admin Tyre', NULL, 'Admin', 7, NULL, 1, 0, NULL, '2026-02-23 06:55:30', '2026-03-15 19:54:43'),
(40, NULL, 'admin.rayak@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Admin Rayak', NULL, 'Admin', 8, NULL, 1, 0, '2026-04-18 07:57:49', '2026-02-23 06:55:30', '2026-04-18 07:57:49'),
(41, NULL, 'admin.akkar@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Admin Akkar', NULL, 'Admin', 9, NULL, 1, 0, '2026-02-23 07:23:09', '2026-02-23 06:55:30', '2026-03-15 19:54:43'),
(42, NULL, 'waseem@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 0, 'wasseem', NULL, 'Staff', 2, 5, 1, 0, '2026-02-23 09:29:50', '2026-02-23 09:29:10', '2026-03-15 19:54:43'),
(43, NULL, 'hala@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 0, 'Hala', NULL, 'Staff', 2, 5, 1, 0, '2026-02-26 05:18:58', '2026-02-23 09:59:06', '2026-03-15 19:54:43'),
(44, NULL, 'eyad@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 0, 'eyad', NULL, 'Staff', 2, 7, 1, 0, '2026-02-25 11:13:36', '2026-02-23 10:54:13', '2026-03-15 19:54:43'),
(45, NULL, 'jhonny@students.liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 0, 'jhonny', NULL, 'Staff', 1, 77, 1, 0, NULL, '2026-02-25 06:32:34', '2026-03-15 19:54:43'),
(46, NULL, 'nancy@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 0, 'nancy', NULL, 'Staff', 2, 86, 1, 0, '2026-02-26 05:17:38', '2026-02-25 08:16:09', '2026-03-15 19:54:43'),
(47, NULL, 'maha@students.liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 0, 'maha', NULL, 'Staff', 2, 84, 1, 0, NULL, '2026-02-25 08:19:18', '2026-03-15 19:54:43'),
(48, NULL, 'serine@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 0, 'serine', NULL, 'Staff', 3, 11, 1, 0, '2026-02-25 10:30:00', '2026-02-25 10:20:02', '2026-03-15 19:54:43'),
(49, NULL, 'superadmin@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 0, 'Super Admin', NULL, 'Admin', NULL, NULL, 1, 0, NULL, '2026-02-25 11:28:49', '2026-03-15 19:54:43'),
(50, NULL, 'head.admissions.c1@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Admissions (Beirut)', NULL, 'Staff', 1, 78, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(51, NULL, 'head.arts.c1@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Arts & Sciences (Beirut)', NULL, 'Staff', 1, 4, 1, 1, '2026-04-17 19:14:29', '2026-03-15 17:40:06', '2026-04-17 19:14:29'),
(52, NULL, 'head.engineering.c1@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Engineering (Beirut)', NULL, 'Staff', 1, 1, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(53, NULL, 'head.finance.c1@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Finance (Beirut)', NULL, 'Staff', 1, 79, 1, 1, '2026-04-17 19:41:43', '2026-03-15 17:40:06', '2026-04-17 19:41:43'),
(54, NULL, 'head.hr.c1@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of HR (Beirut)', NULL, 'Staff', 1, 80, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(55, NULL, 'head.it.c1@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of IT (Beirut)', NULL, 'Staff', 1, 3, 1, 1, '2026-03-15 21:28:48', '2026-03-15 17:40:06', '2026-03-15 21:28:48'),
(56, NULL, 'head.library.c1@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Library (Beirut)', NULL, 'Staff', 1, 77, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(57, NULL, 'head.pharmacy.c1@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Pharmacy (Beirut)', NULL, 'Staff', 1, 2, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(58, NULL, 'head.registrar.c1@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Registrar (Beirut)', NULL, 'Staff', 1, 81, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(59, NULL, 'head.business.c1@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of School of Business (Beirut)', NULL, 'Staff', 1, 37, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(60, NULL, 'head.education.c1@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of School of Education (Beirut)', NULL, 'Staff', 1, 46, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(61, NULL, 'head.studentaffairs.c1@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Student Affairs (Beirut)', NULL, 'Staff', 1, 82, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(62, NULL, 'head.admissions.c2@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Admissions (Bekaa)', NULL, 'Staff', 2, 84, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(63, NULL, 'head.arts.c2@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Arts & Sciences (Bekaa)', NULL, 'Staff', 2, 8, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(64, NULL, 'head.engineering.c2@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Engineering (Bekaa)', NULL, 'Staff', 2, 5, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(65, NULL, 'head.finance.c2@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Finance (Bekaa)', NULL, 'Staff', 2, 85, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(66, NULL, 'head.it.c2@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of IT (Bekaa)', NULL, 'Staff', 2, 7, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(67, NULL, 'head.library.c2@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Library (Bekaa)', NULL, 'Staff', 2, 83, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(68, NULL, 'head.pharmacy.c2@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Pharmacy (Bekaa)', NULL, 'Staff', 2, 6, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(69, NULL, 'head.registrar.c2@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Registrar (Bekaa)', NULL, 'Staff', 2, 86, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(70, NULL, 'head.business.c2@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of School of Business (Bekaa)', NULL, 'Staff', 2, 38, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(71, NULL, 'head.education.c2@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of School of Education (Bekaa)', NULL, 'Staff', 2, 47, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(72, NULL, 'head.studentaffairs.c2@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Student Affairs (Bekaa)', NULL, 'Staff', 2, 87, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(73, NULL, 'head.admissions.c3@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Admissions (Saida)', NULL, 'Staff', 3, 89, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(74, NULL, 'head.arts.c3@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Arts & Sciences (Saida)', NULL, 'Staff', 3, 12, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(75, NULL, 'head.engineering.c3@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Engineering (Saida)', NULL, 'Staff', 3, 9, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(76, NULL, 'head.finance.c3@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Finance (Saida)', NULL, 'Staff', 3, 90, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(77, NULL, 'head.it.c3@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of IT (Saida)', NULL, 'Staff', 3, 11, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(78, NULL, 'head.library.c3@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Library (Saida)', NULL, 'Staff', 3, 88, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(79, NULL, 'head.pharmacy.c3@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Pharmacy (Saida)', NULL, 'Staff', 3, 10, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(80, NULL, 'head.registrar.c3@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Registrar (Saida)', NULL, 'Staff', 3, 91, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(81, NULL, 'head.business.c3@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of School of Business (Saida)', NULL, 'Staff', 3, 39, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(82, NULL, 'head.education.c3@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of School of Education (Saida)', NULL, 'Staff', 3, 48, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(83, NULL, 'head.studentaffairs.c3@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Student Affairs (Saida)', NULL, 'Staff', 3, 92, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(84, NULL, 'head.admissions.c4@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Admissions (Nabatieh)', NULL, 'Staff', 4, 94, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(85, NULL, 'head.arts.c4@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Arts & Sciences (Nabatieh)', NULL, 'Staff', 4, 16, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(86, NULL, 'head.engineering.c4@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Engineering (Nabatieh)', NULL, 'Staff', 4, 13, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(87, NULL, 'head.finance.c4@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Finance (Nabatieh)', NULL, 'Staff', 4, 95, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(88, NULL, 'head.it.c4@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of IT (Nabatieh)', NULL, 'Staff', 4, 15, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(89, NULL, 'head.library.c4@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Library (Nabatieh)', NULL, 'Staff', 4, 93, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(90, NULL, 'head.pharmacy.c4@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Pharmacy (Nabatieh)', NULL, 'Staff', 4, 14, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(91, NULL, 'head.registrar.c4@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Registrar (Nabatieh)', NULL, 'Staff', 4, 96, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(92, NULL, 'head.business.c4@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of School of Business (Nabatieh)', NULL, 'Staff', 4, 40, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(93, NULL, 'head.education.c4@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of School of Education (Nabatieh)', NULL, 'Staff', 4, 49, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(94, NULL, 'head.studentaffairs.c4@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Student Affairs (Nabatieh)', NULL, 'Staff', 4, 97, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(95, NULL, 'head.admissions.c5@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Admissions (Tripoli)', NULL, 'Staff', 5, 99, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(96, NULL, 'head.arts.c5@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Arts & Sciences (Tripoli)', NULL, 'Staff', 5, 20, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(97, NULL, 'head.engineering.c5@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Engineering (Tripoli)', NULL, 'Staff', 5, 17, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(98, NULL, 'head.finance.c5@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Finance (Tripoli)', NULL, 'Staff', 5, 100, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(99, NULL, 'head.it.c5@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of IT (Tripoli)', NULL, 'Staff', 5, 19, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(100, NULL, 'head.library.c5@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Library (Tripoli)', NULL, 'Staff', 5, 98, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(101, NULL, 'head.pharmacy.c5@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Pharmacy (Tripoli)', NULL, 'Staff', 5, 18, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(102, NULL, 'head.registrar.c5@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Registrar (Tripoli)', NULL, 'Staff', 5, 101, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(103, NULL, 'head.business.c5@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of School of Business (Tripoli)', NULL, 'Staff', 5, 41, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(104, NULL, 'head.education.c5@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of School of Education (Tripoli)', NULL, 'Staff', 5, 50, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(105, NULL, 'head.studentaffairs.c5@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Student Affairs (Tripoli)', NULL, 'Staff', 5, 102, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(106, NULL, 'head.admissions.c6@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Admissions (Mount Lebanon)', NULL, 'Staff', 6, 104, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(107, NULL, 'head.arts.c6@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Arts & Sciences (Mount Lebanon)', NULL, 'Staff', 6, 24, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(108, NULL, 'head.engineering.c6@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Engineering (Mount Lebanon)', NULL, 'Staff', 6, 21, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(109, NULL, 'head.finance.c6@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Finance (Mount Lebanon)', NULL, 'Staff', 6, 105, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(110, NULL, 'head.it.c6@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of IT (Mount Lebanon)', NULL, 'Staff', 6, 23, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(111, NULL, 'head.library.c6@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Library (Mount Lebanon)', NULL, 'Staff', 6, 103, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(112, NULL, 'head.pharmacy.c6@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Pharmacy (Mount Lebanon)', NULL, 'Staff', 6, 22, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(113, NULL, 'head.registrar.c6@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Registrar (Mount Lebanon)', NULL, 'Staff', 6, 106, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(114, NULL, 'head.business.c6@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of School of Business (Mount Lebanon)', NULL, 'Staff', 6, 42, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(115, NULL, 'head.education.c6@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of School of Education (Mount Lebanon)', NULL, 'Staff', 6, 51, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(116, NULL, 'head.studentaffairs.c6@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Student Affairs (Mount Lebanon)', NULL, 'Staff', 6, 107, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(117, NULL, 'head.admissions.c7@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Admissions (Tyre)', NULL, 'Staff', 7, 109, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(118, NULL, 'head.arts.c7@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Arts & Sciences (Tyre)', NULL, 'Staff', 7, 28, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(119, NULL, 'head.engineering.c7@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Engineering (Tyre)', NULL, 'Staff', 7, 25, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(120, NULL, 'head.finance.c7@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Finance (Tyre)', NULL, 'Staff', 7, 110, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(121, NULL, 'head.it.c7@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of IT (Tyre)', NULL, 'Staff', 7, 27, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(122, NULL, 'head.library.c7@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Library (Tyre)', NULL, 'Staff', 7, 108, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(123, NULL, 'head.pharmacy.c7@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Pharmacy (Tyre)', NULL, 'Staff', 7, 26, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(124, NULL, 'head.registrar.c7@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Registrar (Tyre)', NULL, 'Staff', 7, 111, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(125, NULL, 'head.business.c7@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of School of Business (Tyre)', NULL, 'Staff', 7, 43, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(126, NULL, 'head.education.c7@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of School of Education (Tyre)', NULL, 'Staff', 7, 52, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(127, NULL, 'head.studentaffairs.c7@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Student Affairs (Tyre)', NULL, 'Staff', 7, 112, 1, 1, NULL, '2026-03-15 17:40:06', '2026-03-15 19:54:43'),
(128, NULL, 'head.admissions.c8@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Admissions (Rayak)', NULL, 'Staff', 8, 114, 1, 1, NULL, '2026-03-15 17:40:07', '2026-03-15 19:54:43'),
(129, NULL, 'head.arts.c8@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Arts & Sciences (Rayak)', NULL, 'Staff', 8, 32, 1, 1, NULL, '2026-03-15 17:40:07', '2026-03-15 19:54:43'),
(130, NULL, 'head.engineering.c8@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Engineering (Rayak)', NULL, 'Staff', 8, 29, 1, 1, NULL, '2026-03-15 17:40:07', '2026-03-15 19:54:43'),
(131, NULL, 'head.finance.c8@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Finance (Rayak)', NULL, 'Staff', 8, 115, 1, 1, NULL, '2026-03-15 17:40:07', '2026-03-15 19:54:43'),
(132, NULL, 'head.it.c8@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of IT (Rayak)', NULL, 'Staff', 8, 31, 1, 1, '2026-04-17 17:37:27', '2026-03-15 17:40:07', '2026-04-17 17:37:27'),
(133, NULL, 'head.library.c8@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Library (Rayak)', NULL, 'Staff', 8, 113, 1, 1, NULL, '2026-03-15 17:40:07', '2026-03-15 19:54:43'),
(134, NULL, 'head.pharmacy.c8@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Pharmacy (Rayak)', NULL, 'Staff', 8, 30, 1, 1, '2026-04-18 08:10:29', '2026-03-15 17:40:07', '2026-04-18 08:10:29'),
(135, NULL, 'head.registrar.c8@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Registrar (Rayak)', NULL, 'Staff', 8, 116, 1, 1, NULL, '2026-03-15 17:40:07', '2026-03-15 19:54:43'),
(136, NULL, 'head.business.c8@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of School of Business (Rayak)', NULL, 'Staff', 8, 44, 1, 1, NULL, '2026-03-15 17:40:07', '2026-03-15 19:54:43'),
(137, NULL, 'head.education.c8@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of School of Education (Rayak)', NULL, 'Staff', 8, 53, 1, 1, NULL, '2026-03-15 17:40:07', '2026-03-15 19:54:43'),
(138, NULL, 'head.studentaffairs.c8@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Student Affairs (Rayak)', NULL, 'Staff', 8, 117, 1, 1, NULL, '2026-03-15 17:40:07', '2026-03-15 19:54:43'),
(139, NULL, 'head.admissions.c9@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Admissions (Akkar)', NULL, 'Staff', 9, 119, 1, 1, NULL, '2026-03-15 17:40:07', '2026-03-15 19:54:43'),
(140, NULL, 'head.arts.c9@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Arts & Sciences (Akkar)', NULL, 'Staff', 9, 36, 1, 1, NULL, '2026-03-15 17:40:07', '2026-03-15 19:54:43'),
(141, NULL, 'head.engineering.c9@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Engineering (Akkar)', NULL, 'Staff', 9, 33, 1, 1, NULL, '2026-03-15 17:40:07', '2026-03-15 19:54:43'),
(142, NULL, 'head.finance.c9@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Finance (Akkar)', NULL, 'Staff', 9, 120, 1, 1, NULL, '2026-03-15 17:40:07', '2026-03-15 19:54:43'),
(143, NULL, 'head.it.c9@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of IT (Akkar)', NULL, 'Staff', 9, 35, 1, 1, NULL, '2026-03-15 17:40:07', '2026-03-15 19:54:43'),
(144, NULL, 'head.library.c9@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Library (Akkar)', NULL, 'Staff', 9, 118, 1, 1, NULL, '2026-03-15 17:40:07', '2026-03-15 19:54:43'),
(145, NULL, 'head.pharmacy.c9@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Pharmacy (Akkar)', NULL, 'Staff', 9, 34, 1, 1, NULL, '2026-03-15 17:40:07', '2026-03-15 19:54:43'),
(146, NULL, 'head.registrar.c9@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Registrar (Akkar)', NULL, 'Staff', 9, 121, 1, 1, NULL, '2026-03-15 17:40:07', '2026-03-15 19:54:43'),
(147, NULL, 'head.business.c9@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of School of Business (Akkar)', NULL, 'Staff', 9, 45, 1, 1, NULL, '2026-03-15 17:40:07', '2026-03-15 19:54:43'),
(148, NULL, 'head.education.c9@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of School of Education (Akkar)', NULL, 'Staff', 9, 54, 1, 1, NULL, '2026-03-15 17:40:07', '2026-03-15 19:54:43'),
(149, NULL, 'head.studentaffairs.c9@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Student Affairs (Akkar)', NULL, 'Staff', 9, 122, 1, 1, '2026-03-15 19:55:15', '2026-03-15 17:40:07', '2026-03-15 19:55:15'),
(150, NULL, 'talalshmeis@students.liu.edu.com', '$2y$10$t.6uYHpCdzLx.wpFkltzGOJsk0ZuXXuCZFa5sDeX4hW8I2dLYdT72', 'email', 0, 'talal', NULL, 'Staff', 1, 4, 1, 0, '2026-04-17 19:40:37', '2026-04-17 17:44:09', '2026-04-17 19:40:37'),
(151, NULL, 'johnny@students.liu.edu.lb', '$2y$10$80Smhj7YGYc7adc4PqHH.eF0zAEGo11pK3vFVij45MaU9wdBCAPyC', 'email', 0, 'johnny', NULL, 'Staff', 8, 30, 1, 0, '2026-04-18 08:01:00', '2026-04-18 08:00:24', '2026-04-18 08:01:00');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `assets`
--
ALTER TABLE `assets`
  ADD CONSTRAINT `assets_ibfk_1` FOREIGN KEY (`campus_id`) REFERENCES `campuses` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `assets_ibfk_2` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_assets_po_item` FOREIGN KEY (`po_item_id`) REFERENCES `purchase_order_items` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `asset_assignments`
--
ALTER TABLE `asset_assignments`
  ADD CONSTRAINT `asset_assignments_ibfk_1` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `asset_assignments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `asset_assignments_ibfk_3` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT;

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `audit_logs_ibfk_2` FOREIGN KEY (`campus_id`) REFERENCES `campuses` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `departments`
--
ALTER TABLE `departments`
  ADD CONSTRAINT `departments_ibfk_1` FOREIGN KEY (`campus_id`) REFERENCES `campuses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD CONSTRAINT `purchase_orders_ibfk_1` FOREIGN KEY (`campus_id`) REFERENCES `campuses` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `purchase_orders_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `purchase_orders_ibfk_3` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `purchase_orders_ibfk_4` FOREIGN KEY (`quotation_id`) REFERENCES `quotations` (`id`);

--
-- Constraints for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  ADD CONSTRAINT `fk_poi_stock` FOREIGN KEY (`stock_id`) REFERENCES `stock` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `purchase_order_items_ibfk_1` FOREIGN KEY (`po_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stock`
--
ALTER TABLE `stock`
  ADD CONSTRAINT `stock_ibfk_1` FOREIGN KEY (`campus_id`) REFERENCES `campuses` (`id`) ON DELETE RESTRICT;

--
-- Constraints for table `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `tickets_ibfk_1` FOREIGN KEY (`campus_id`) REFERENCES `campuses` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `tickets_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `tickets_ibfk_3` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `ticket_attachments`
--
ALTER TABLE `ticket_attachments`
  ADD CONSTRAINT `ticket_attachments_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ticket_attachments_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT;

--
-- Constraints for table `ticket_comments`
--
ALTER TABLE `ticket_comments`
  ADD CONSTRAINT `ticket_comments_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ticket_comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`campus_id`) REFERENCES `campuses` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `users_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
