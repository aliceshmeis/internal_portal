-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Apr 18, 2026 at 07:50 AM
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
) ENGINE=InnoDB AUTO_INCREMENT=151 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(40, NULL, 'admin.rayak@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Admin Rayak', NULL, 'Admin', 8, NULL, 1, 0, '2026-02-23 07:23:36', '2026-02-23 06:55:30', '2026-03-15 19:54:43'),
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
(134, NULL, 'head.pharmacy.c8@liu.edu.lb', '$2y$10$y5RJpIXMM7XxauloYLCmKuQfH7ds2H8o2C6LxvnuEeSyhJhsldvKy', 'email', 1, 'Head of Pharmacy (Rayak)', NULL, 'Staff', 8, 30, 1, 1, NULL, '2026-03-15 17:40:07', '2026-03-15 19:54:43'),
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
(150, NULL, 'talalshmeis@students.liu.edu.com', '$2y$10$t.6uYHpCdzLx.wpFkltzGOJsk0ZuXXuCZFa5sDeX4hW8I2dLYdT72', 'email', 0, 'talal', NULL, 'Staff', 1, 4, 1, 0, '2026-04-17 19:40:37', '2026-04-17 17:44:09', '2026-04-17 19:40:37');

--
-- Constraints for dumped tables
--

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
