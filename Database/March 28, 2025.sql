-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 28, 2025 at 09:10 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `inventory_pos`
--
CREATE DATABASE IF NOT EXISTS `inventory_pos` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `inventory_pos`;

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

CREATE TABLE `companies` (
  `company_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `tin_no` varchar(50) NOT NULL,
  `contact_person` varchar(255) NOT NULL,
  `contact_number` varchar(50) NOT NULL,
  `business_style` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `companies`
--

INSERT INTO `companies` (`company_id`, `name`, `address`, `tin_no`, `contact_person`, `contact_number`, `business_style`) VALUES
(1, 'Corner Steel Systems Corporation', '536 Calbayog St., Mandaluyong City', '000-315-460-000', 'Ms. Michelle Nozaleda', '867-8301', 'Sales'),
(2, 'Powerguide ', 'Busilak St', '123-123-123', 'Yuma Wakayama', '867-8301', 'Electric kineme '),
(4, 'Unitec Resources ', 'Busilak St.', '143-143-143', 'Claymar', '0921092121', 'Electricity'),
(10, 'POWERGUIDE SOLUTIONS INC.', 'AYALA HOUSING, 351 SAMPAGUITA, BARANGKA DRIVE 1550, CITY OF MANDALUYONG NCR, SECOND DISTRICT PHILIPPINES', '008-931-956-00000', '', '', ''),
(11, 'UnivMakati', 'JP rizal', '123-123-123', 'Bundang', '093946735262', 'School'),
(13, 'clay', 'werty123', '879-908-908', 'yum', '09878908789', 'wert'),
(14, 'Jeanetic Diva Inc.', 'sa puso mo ', '123-456-897', 'Jinrey Bundang', '098909878', 'none '),
(15, 'claymar poukelya', 'poukelya st', '123-765-890', 'tihe', '098998789', 'electric');

-- --------------------------------------------------------

--
-- Table structure for table `company_product_prices`
--

CREATE TABLE `company_product_prices` (
  `company_product_price_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `company_product_prices`
--

INSERT INTO `company_product_prices` (`company_product_price_id`, `company_id`, `product_id`, `price`) VALUES
(7, 1, 1, 180000.00),
(8, 2, 1, 12000.00),
(9, 10, 1, 14000.00),
(10, 4, 1, 16000.00),
(12, 1, 2, 1000.00),
(14, 2, 2, 1500.00),
(16, 10, 2, 2000.00),
(18, 4, 2, 2500.00),
(20, 15, 1, 50.00),
(21, 15, 10, 65.00),
(23, 15, 2, 100.00);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `notification_type` varchar(50) NOT NULL DEFAULT 'low_stock',
  `message` text NOT NULL,
  `current_quantity` int(11) NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `product_id`, `notification_type`, `message`, `current_quantity`, `is_read`, `created_at`) VALUES
(1, 1, 'low_stock', 'Product Protek has reached low stock level (Qty: 10).', 10, 1, '2025-03-14 16:55:36'),
(2, 1, 'low_stock', 'Product Protek has reached low stock level (Qty: 20).', 20, 1, '2025-03-14 16:56:30'),
(3, 1, 'low_stock', 'Product Protek has reached low stock level (Qty: 10).', 10, 1, '2025-03-14 17:03:37'),
(4, 1, 'low_stock', 'Product Protek has reached low stock level (Qty: 5).', 5, 1, '2025-03-14 17:03:59'),
(5, 2, 'low_stock', 'Product wire has reached low stock level (Qty: 10).', 10, 1, '2025-03-14 17:04:19'),
(6, 3, 'low_stock', 'Product screwdriver has reached low stock level (Qty: 10).', 10, 1, '2025-03-14 17:04:27'),
(7, 4, 'low_stock', 'Product tanso has reached low stock level (Qty: 10).', 10, 1, '2025-03-14 17:04:30'),
(8, 5, 'low_stock', 'Product qawqw has reached low stock level (Qty: 10).', 10, 1, '2025-03-14 17:04:34'),
(9, 1, 'low_stock', 'Product Protek has reached low stock level (Qty: 5).', 5, 1, '2025-03-14 17:18:36'),
(10, 1, 'low_stock', 'Product Protek has reached low stock level (Qty: 5).', 5, 1, '2025-03-15 11:38:27'),
(11, 1, 'low_stock', 'Product Protek has reached low stock level (Qty: 1).', 1, 1, '2025-03-15 12:30:44'),
(13, 1, 'low_stock', 'Product Protek has reached low stock level (Qty: 10).', 10, 1, '2025-03-16 11:47:38'),
(14, 1, 'low_stock', 'Product Protek has reached low stock level (Qty: 10).', 10, 1, '2025-03-16 11:50:09'),
(15, 1, 'low_stock', 'Product Protek has reached low stock level (Qty: 10).', 10, 1, '2025-03-16 11:53:32'),
(16, 1, 'low_stock', 'Product Protek has reached low stock level (Qty: 5).', 5, 1, '2025-03-16 11:54:39'),
(17, 3, 'low_stock', 'Product screwdriver has reached low stock level (Qty: 9).', 9, 1, '2025-03-16 12:07:11'),
(18, 4, 'low_stock', 'Product ID 4 stock is low.', 9, 1, '2025-03-16 12:13:41'),
(19, 3, 'low_stock', 'Product screwdriver has reached low stock level (Qty: 10).', 10, 1, '2025-03-16 12:14:02'),
(20, 3, 'low_stock', 'Product ID 3 stock is low.', 9, 1, '2025-03-16 12:14:20'),
(21, 2, 'low_stock', 'Product ID 2 stock is low.', 4, 1, '2025-03-16 12:17:15'),
(22, 1, 'low_stock', 'Product ID Protek stock is low.', 5, 1, '2025-03-16 12:18:56'),
(23, 1, 'low_stock', 'Product Protek has reached low stock level (Qty: 4).', 4, 1, '2025-03-16 12:21:19'),
(24, 2, 'low_stock', 'Product wire stock is low.', 9, 1, '2025-03-16 12:26:46'),
(25, 1, 'low_stock', 'Product Protek has reached low stock level (Qty: 3).', 3, 1, '2025-03-16 12:38:43'),
(26, 1, 'low_stock', 'Product Protek has reached low stock level (Qty: 2).', 2, 1, '2025-03-20 14:18:38'),
(27, 1, 'low_stock', 'Product Protek has reached low stock level (Qty: 1).', 1, 1, '2025-03-25 09:31:40'),
(28, 1, 'low_stock', 'Product Protek has reached low stock level (Qty: 0).', 0, 1, '2025-03-25 09:39:21'),
(29, 3, 'low_stock', 'Product screwdriver has reached low stock level (Qty: 8).', 8, 1, '2025-03-25 10:02:24'),
(30, 7, 'low_stock', 'Product Yuma has reached low stock level (Qty: 3).', 3, 1, '2025-03-25 11:09:01'),
(31, 7, 'low_stock', 'Product Yuma has reached low stock level (Qty: 8).', 8, 1, '2025-03-25 11:15:12'),
(32, 3, 'low_stock', 'Product screwdriver has reached low stock level (Qty: 9).', 9, 1, '2025-03-25 11:26:00'),
(33, 1, 'low_stock', 'Product Protek has reached low stock level (Qty: 10).', 10, 1, '2025-03-25 11:53:53'),
(34, 1, 'low_stock', 'Product Protek has reached low stock level (Qty: 0).', 0, 1, '2025-03-25 11:54:09'),
(35, 3, 'low_stock', 'Product \'screwdriver\' is running low on stock.', 8, 1, '2025-03-25 11:55:27'),
(36, 4, 'low_stock', 'Product \'tanso\' is running low on stock.', 9, 1, '2025-03-25 11:55:59'),
(37, 1, 'low_stock', 'Product \'Protek\' is running low on stock.', 9, 1, '2025-03-26 01:56:41'),
(38, 1, 'low_stock', 'Product \'Protek\' is running low on stock.', 8, 1, '2025-03-26 03:37:33'),
(39, 8, 'low_stock', 'Product \'ckay\' is running low on stock.', 9, 1, '2025-03-26 04:35:01'),
(40, 1, 'low_stock', 'Product \'Protek\' is running low on stock.', 5, 1, '2025-03-28 01:25:02'),
(42, 1, 'low_stock', 'Product \'Protek\' is running low on stock.', 4, 1, '2025-03-28 02:48:33'),
(43, 1, 'low_stock', 'Product \'Protek\' is running low on stock.', 3, 1, '2025-03-28 02:51:24'),
(44, 1, 'low_stock', 'Product \'Protek\' is running low on stock.', 2, 1, '2025-03-28 06:48:30'),
(45, 1, 'low_stock', 'Product \'Protek\' is running low on stock.', 1, 1, '2025-03-28 07:49:33'),
(46, 1, 'low_stock', 'Product \'Protek\' is running low on stock.', 0, 1, '2025-03-28 07:53:14');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `threshold` int(11) DEFAULT 10,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `name`, `description`, `price`, `quantity`, `threshold`, `created_at`) VALUES
(1, 'Protek', NULL, 25000.00, 0, 20, '2025-03-13 07:32:43'),
(2, 'wire', NULL, 900.00, 9, 20, '2025-03-13 08:07:28'),
(3, 'screwdriver', NULL, 190.00, 8, 20, '2025-03-13 08:08:36'),
(4, 'tanso', NULL, 90000.00, 9, 20, '2025-03-13 08:08:58'),
(5, 'qawqw', NULL, 123.00, 20, 20, '2025-03-13 08:09:25'),
(7, 'Yuma', NULL, 12.00, 20, 10, '2025-03-25 09:38:44'),
(8, 'ckay', NULL, 90.00, 9, 10, '2025-03-26 03:42:48'),
(9, 'omg', NULL, 900.00, 29, 10, '2025-03-28 07:26:49'),
(10, 'tite', NULL, 90.00, 8, 10, '2025-03-28 07:52:42');

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `sale_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `sale_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `total_amount` decimal(10,2) DEFAULT NULL,
  `company_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`sale_id`, `user_id`, `sale_date`, `total_amount`, `company_id`) VALUES
(1, 1, '2025-03-13 07:33:30', 125000.00, NULL),
(2, 1, '2025-03-13 07:38:53', 25000.00, NULL),
(3, 1, '2025-03-13 09:07:14', 1213.00, NULL),
(4, 1, '2025-03-13 09:07:17', 1213.00, NULL),
(5, 1, '2025-03-13 09:07:20', 116023.00, NULL),
(6, 1, '2025-03-13 09:07:22', 91213.00, NULL),
(7, 1, '2025-03-13 09:07:25', 116023.00, NULL),
(8, 1, '2025-03-13 09:07:28', 1213.00, NULL),
(9, NULL, '2025-02-14 16:00:00', 1000.00, NULL),
(10, NULL, '2025-02-19 16:00:00', 1500.00, NULL),
(11, NULL, '2025-02-28 16:00:00', 2000.00, NULL),
(12, NULL, '2025-03-04 16:00:00', 2500.00, NULL),
(13, 1, '2025-03-14 16:10:14', 475000.00, NULL),
(14, 1, '2025-03-14 16:14:51', 125000.00, NULL),
(15, 1, '2025-03-14 16:21:28', 450000.00, NULL),
(16, 1, '2025-03-14 16:21:54', 300000.00, NULL),
(17, 1, '2025-03-14 16:40:44', 250000.00, NULL),
(18, 1, '2025-03-14 16:48:16', 250000.00, NULL),
(19, 1, '2025-03-14 16:56:40', 400000.00, NULL),
(20, 1, '2025-03-14 16:58:42', 100000.00, NULL),
(21, 1, '2025-03-15 11:53:51', 900.00, NULL),
(22, 1, '2025-03-15 12:32:19', 25900.00, NULL),
(23, 1, '2025-03-16 11:55:38', 25900.00, NULL),
(24, 1, '2025-03-16 12:05:24', 25000.00, NULL),
(25, 1, '2025-03-16 12:05:47', 100000.00, NULL),
(26, 1, '2025-03-16 12:11:37', 25000.00, NULL),
(27, 1, '2025-03-16 12:11:55', 300000.00, NULL),
(28, 1, '2025-03-16 12:13:41', 180000.00, NULL),
(29, 1, '2025-03-16 12:14:20', 380.00, NULL),
(30, 1, '2025-03-16 12:17:15', 2700.00, NULL),
(31, 1, '2025-03-16 12:18:56', 25000.00, NULL),
(32, 1, '2025-03-16 12:26:46', 1800.00, NULL),
(33, 1, '2025-03-16 12:38:43', 25000.00, NULL),
(34, 1, '2025-03-25 09:31:40', 25000.00, NULL),
(35, 1, '2025-03-25 09:39:21', 25000.00, NULL),
(36, 1, '2025-03-25 10:02:24', 190.00, NULL),
(37, 1, '2025-03-25 10:11:42', 202.00, 2),
(38, 1, '2025-03-25 10:31:14', 24.00, 1),
(39, 1, '2025-03-25 10:47:37', 135.00, 1),
(40, 1, '2025-03-25 10:53:52', 1800.00, 1),
(41, 1, '2025-03-25 10:58:24', 24.00, 1),
(42, 1, '2025-03-25 10:59:06', 60.00, 1),
(43, 1, '2025-03-25 11:02:28', 570.00, 4),
(44, 1, '2025-03-25 11:08:04', 60.00, 4),
(45, 1, '2025-03-25 11:14:47', 60.00, 1),
(46, 1, '2025-03-25 11:24:57', 570.00, 4),
(47, 1, '2025-03-25 11:26:59', 275000.00, 2),
(48, 1, '2025-03-25 11:37:02', 760.00, 2),
(49, 1, '2025-03-25 11:37:26', 9900.00, 2),
(50, 1, '2025-03-25 11:55:27', 1520.00, 1),
(51, 1, '2025-03-25 11:55:59', 990000.00, 4),
(52, 1, '2025-03-26 01:56:41', 275000.00, 1),
(53, 1, '2025-03-26 03:37:33', 25000.00, 1),
(54, 1, '2025-03-26 04:35:01', 990.00, 4),
(55, 1, '2025-03-28 01:25:02', 75000.00, 1),
(57, 1, '2025-03-28 02:48:33', 25000.00, 4),
(58, 1, '2025-03-28 02:51:24', 26000.00, 2),
(59, 1, '2025-03-28 06:48:30', 16000.00, 4),
(60, 1, '2025-03-28 07:49:33', 25000.00, 1),
(61, 1, '2025-03-28 07:53:14', 50.00, 15);

-- --------------------------------------------------------

--
-- Table structure for table `sales_items`
--

CREATE TABLE `sales_items` (
  `sale_item_id` int(11) NOT NULL,
  `sale_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales_items`
--

INSERT INTO `sales_items` (`sale_item_id`, `sale_id`, `product_id`, `quantity`, `price`) VALUES
(1, 1, 1, 5, 25000.00),
(2, 2, 1, 1, 25000.00),
(3, 3, 3, 1, 190.00),
(4, 3, 2, 1, 900.00),
(5, 3, 5, 1, 123.00),
(6, 4, 2, 1, 900.00),
(7, 4, 3, 1, 190.00),
(8, 4, 5, 1, 123.00),
(9, 5, 2, 1, 900.00),
(10, 5, 5, 1, 123.00),
(11, 5, 4, 1, 90000.00),
(12, 5, 1, 1, 25000.00),
(13, 6, 2, 1, 900.00),
(14, 6, 3, 1, 190.00),
(15, 6, 5, 1, 123.00),
(16, 6, 4, 1, 90000.00),
(17, 7, 2, 1, 900.00),
(18, 7, 5, 1, 123.00),
(19, 7, 4, 1, 90000.00),
(20, 7, 1, 1, 25000.00),
(21, 8, 3, 1, 190.00),
(22, 8, 2, 1, 900.00),
(23, 8, 5, 1, 123.00),
(24, 13, 1, 19, 25000.00),
(25, 14, 1, 5, 25000.00),
(26, 15, 1, 18, 25000.00),
(27, 16, 1, 12, 25000.00),
(28, 17, 1, 10, 25000.00),
(29, 18, 1, 10, 25000.00),
(30, 19, 1, 16, 25000.00),
(31, 20, 1, 4, 25000.00),
(32, 21, 2, 1, 900.00),
(33, 22, 1, 1, 25000.00),
(34, 22, 2, 1, 900.00),
(35, 23, 1, 1, 25000.00),
(36, 23, 2, 1, 900.00),
(37, 24, 1, 1, 25000.00),
(38, 25, 1, 4, 25000.00),
(39, 26, 1, 1, 25000.00),
(40, 27, 1, 12, 25000.00),
(41, 28, 4, 2, 90000.00),
(42, 29, 3, 2, 190.00),
(43, 30, 2, 3, 900.00),
(44, 31, 1, 1, 25000.00),
(45, 32, 2, 2, 900.00),
(46, 33, 1, 1, 25000.00),
(47, 34, 1, 1, 25000.00),
(48, 35, 1, 1, 25000.00),
(49, 36, 3, 1, 190.00),
(50, 37, 7, 1, 12.00),
(51, 37, 3, 1, 190.00),
(52, 38, 7, 2, 12.00),
(53, 39, 7, 1, 12.00),
(54, 39, 5, 1, 123.00),
(55, 40, 2, 2, 900.00),
(56, 41, 7, 2, 12.00),
(57, 42, 7, 5, 12.00),
(58, 43, 3, 3, 190.00),
(59, 44, 7, 5, 12.00),
(60, 45, 7, 5, 12.00),
(61, 46, 3, 3, 190.00),
(62, 47, 1, 11, 25000.00),
(63, 48, 3, 4, 190.00),
(64, 49, 2, 11, 900.00),
(65, 50, 3, 8, 190.00),
(66, 51, 4, 11, 90000.00),
(67, 52, 1, 11, 25000.00),
(68, 53, 1, 1, 25000.00),
(69, 54, 8, 11, 90.00),
(70, 55, 1, 3, 25000.00),
(72, 57, 1, 1, 25000.00),
(73, 58, 1, 1, 26000.00),
(74, 59, 1, 1, 16000.00),
(75, 60, 1, 1, 25000.00),
(76, 61, 1, 1, 50.00);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('admin','cashier') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `role`, `created_at`) VALUES
(1, 'admin', '$2y$10$EO9Am/WLeaLDuEmtFbjro.Q90dP/pfyzHc56mzNsWauStsQotfgFe', 'admin', '2025-03-13 07:23:46');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`company_id`);

--
-- Indexes for table `company_product_prices`
--
ALTER TABLE `company_product_prices`
  ADD PRIMARY KEY (`company_product_price_id`),
  ADD UNIQUE KEY `company_product_unique` (`company_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`sale_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `sales_ibfk_2` (`company_id`);

--
-- Indexes for table `sales_items`
--
ALTER TABLE `sales_items`
  ADD PRIMARY KEY (`sale_item_id`),
  ADD KEY `sale_id` (`sale_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `company_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `company_product_prices`
--
ALTER TABLE `company_product_prices`
  MODIFY `company_product_price_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `sale_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `sales_items`
--
ALTER TABLE `sales_items`
  MODIFY `sale_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `company_product_prices`
--
ALTER TABLE `company_product_prices`
  ADD CONSTRAINT `company_product_prices_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`company_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `company_product_prices_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `sales_ibfk_2` FOREIGN KEY (`company_id`) REFERENCES `companies` (`company_id`) ON DELETE SET NULL;

--
-- Constraints for table `sales_items`
--
ALTER TABLE `sales_items`
  ADD CONSTRAINT `sales_items_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`sale_id`),
  ADD CONSTRAINT `sales_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
