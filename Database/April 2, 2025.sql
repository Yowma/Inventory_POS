-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 02, 2025 at 09:04 AM
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
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `name`) VALUES
(1, 'SINGLE PHASE CONNECTION'),
(4, 'THREE PHASE DELTA CONNECTION'),
(5, 'THREE PHASE WYE CONNECTION'),
(6, 'THREE PHASE WYE CONNECTION (3Y6)'),
(7, 'THREE PHASE DELTA CONNECTION (PROHV'),
(8, 'THREE PHASE WYE CONNECTION (PROHV)');

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
(18, 'sample', 'JP rizal', '121-231-212', 'Bundang', '12312312312', 'School');

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
(45, 18, 16, 1000.00);

-- --------------------------------------------------------

--
-- Table structure for table `models`
--

CREATE TABLE `models` (
  `model_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `models`
--

INSERT INTO `models` (`model_id`, `name`, `quantity`) VALUES
(1, '1P24', 130),
(2, 'BERT 1P24', 146),
(3, 'THREE PHASE DELTA (PROT)', 28),
(4, 'THREE PHASE WYE (PROT)', 39);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `notification_type` varchar(50) NOT NULL DEFAULT 'low_stock',
  `message` text NOT NULL,
  `current_quantity` int(11) NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `model_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `notification_type`, `message`, `current_quantity`, `is_read`, `created_at`, `model_id`) VALUES
(56, 'low_stock', 'Model \'1P24\' is running low on stock.', 4, 1, '2025-04-02 06:03:40', 1);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `quantity` int(255) NOT NULL,
  `threshold` int(11) DEFAULT 10,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `category_id` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT 0.00,
  `model_id` int(11) DEFAULT NULL,
  `shared_quantity` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `name`, `description`, `quantity`, `threshold`, `created_at`, `category_id`, `price`, `model_id`, `shared_quantity`) VALUES
(16, 'Prot 30 1P24', '30kA/Phase, 220-240 vac', 0, 10, '2025-04-02 05:40:47', NULL, 21000.00, 1, 0),
(17, 'Prot 60 1P24', '60kA/Phase, 220-240 vac', 0, 10, '2025-04-02 05:50:12', NULL, 21000.00, 1, 0),
(18, 'Prot 100 1P24', '100kA/Phase, 220-240 vac', 0, 10, '2025-04-02 05:50:43', NULL, 22000.00, 1, 0),
(19, 'Prot 120 1P24', '120kA/Phase, 220-240 vac', 0, 10, '2025-04-02 05:51:31', NULL, 22000.00, 1, 0),
(20, 'Prot 160 1P24', '160kA/Phase, 220-240 vac', 0, 10, '2025-04-02 05:51:43', NULL, 22000.00, 1, 0),
(21, 'Prot 30 1P24', '30kA/Phase, 220-240 vac', 0, 10, '2025-04-02 05:58:25', NULL, 21000.00, 2, 0),
(22, 'Prot 60 1P24', '60kA/Phase, 220-240 vac', 0, 10, '2025-04-02 06:05:08', NULL, 21000.00, 2, 0),
(23, 'Prot 100 1P24', '100kA/Phase, 220-240 vac', 0, 10, '2025-04-02 06:05:34', NULL, 22000.00, 2, 0),
(24, 'Prot 120 1P24', '120kA/Phase, 220-240 vac', 0, 10, '2025-04-02 06:06:08', NULL, 22000.00, 2, 0),
(25, 'Prot 160 1P24', '160kA/Phase, 220-240 vac', 0, 10, '2025-04-02 06:06:51', NULL, 22000.00, 2, 0),
(26, 'Prot 30 3N2', '30kA/Phase, 220-240 vac', 0, 10, '2025-04-02 06:15:32', NULL, 28000.00, 3, 0),
(27, 'Prot 60 3N2', '60kA/Phase, 220-240 vac', 0, 10, '2025-04-02 06:15:54', NULL, 30000.00, 3, 0),
(28, 'Prot 100 3N2', '100kA/Phase, 220-240 vac', 0, 10, '2025-04-02 06:16:10', NULL, 35000.00, 3, 0),
(29, 'Prot 120 3N2', '120kA/Phase, 220-240 vac', 0, 10, '2025-04-02 06:16:52', NULL, 35000.00, 3, 0),
(30, 'Prot 160 3N2', '160kA/Phase, 220-240 vac', 0, 10, '2025-04-02 06:17:05', NULL, 45000.00, 3, 0),
(31, 'Prot 30 3N4', '30kA/Phase, 220-240 vac', 0, 10, '2025-04-02 06:25:42', NULL, 28000.00, 3, 0),
(32, 'Prot 60 3N4', '60kA/Phase, 220-240 vac', 0, 10, '2025-04-02 06:25:57', NULL, 30000.00, 3, 0),
(33, 'Prot 100 3N4', '100kA/Phase, 220-240 vac', 0, 10, '2025-04-02 06:26:07', NULL, 35000.00, 3, 0),
(34, 'Prot 120 3N4', '120kA/Phase, 220-240 vac', 0, 10, '2025-04-02 06:26:20', NULL, 35000.00, 3, 0),
(35, 'Prot 160 3N4', '160kA/Phase, 220-240 vac', 0, 10, '2025-04-02 06:26:30', NULL, 45000.00, 3, 0),
(36, 'Prot 30 3Y6', '30kA/Phase, 230-600 vac', 0, 10, '2025-04-02 06:54:27', NULL, 28000.00, 4, 0),
(37, 'Prot 60 3Y6', '60kA/Phase, 230-600', 0, 10, '2025-04-02 06:55:12', NULL, 30000.00, 4, 0),
(38, 'Prot 100 3Y6', '100kA/Phase, 230-600', 0, 10, '2025-04-02 06:55:37', NULL, 35000.00, 4, 0),
(39, 'Prot 120 3Y6', '120kA/Phase, 230-600', 0, 10, '2025-04-02 06:55:58', NULL, 35000.00, 4, 0),
(40, 'Prot 160 3Y6', '160kA/Phase, 230-600', 0, 10, '2025-04-02 06:56:41', NULL, 45000.00, 4, 0);

-- --------------------------------------------------------

--
-- Table structure for table `receipts`
--

CREATE TABLE `receipts` (
  `receipt_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `upload_date` datetime DEFAULT current_timestamp(),
  `dr_file_name` varchar(255) DEFAULT NULL,
  `po_file_name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `receipts`
--

INSERT INTO `receipts` (`receipt_id`, `company_id`, `file_name`, `upload_date`, `dr_file_name`, `po_file_name`) VALUES
(1, 18, '1743561777_sample.pdf', '2025-04-02 10:42:57', NULL, NULL),
(2, 18, '1743563050_receipt_Wakayama_HomeTask2(HT2) - History.pdf', '2025-04-02 11:04:10', '1743563050_dr_Template - Thesis 1 - Kick off meeting.docx.pdf', '1743563050_po_Wakayama_Cuerdo_LabAct4_IPO,FLOWCHART, VARIABLE TABLE.pdf');

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `sale_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `sale_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `total_amount` decimal(10,2) DEFAULT NULL,
  `company_id` int(11) DEFAULT NULL,
  `po_number` int(11) NOT NULL DEFAULT 3000,
  `sales_number` int(11) NOT NULL DEFAULT 3000
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`sale_id`, `user_id`, `sale_date`, `total_amount`, `company_id`, `po_number`, `sales_number`) VALUES
(74, 1, '2025-04-02 05:48:59', 5000.00, 18, 1290, 3000),
(75, 1, '2025-04-02 06:03:40', 124000.00, 18, 1212, 3001),
(76, 1, '2025-04-02 07:02:27', 43000.00, 18, 1212, 3002);

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
(92, 74, 16, 5, 1000.00),
(93, 75, 16, 124, 1000.00),
(94, 76, 17, 1, 21000.00),
(95, 76, 18, 1, 22000.00);

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
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

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
-- Indexes for table `models`
--
ALTER TABLE `models`
  ADD PRIMARY KEY (`model_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `fk_notifications_model_id` (`model_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `model_id` (`model_id`);

--
-- Indexes for table `receipts`
--
ALTER TABLE `receipts`
  ADD PRIMARY KEY (`receipt_id`),
  ADD KEY `company_id` (`company_id`);

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
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `company_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `company_product_prices`
--
ALTER TABLE `company_product_prices`
  MODIFY `company_product_price_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `models`
--
ALTER TABLE `models`
  MODIFY `model_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `receipts`
--
ALTER TABLE `receipts`
  MODIFY `receipt_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `sale_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT for table `sales_items`
--
ALTER TABLE `sales_items`
  MODIFY `sale_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;

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
  ADD CONSTRAINT `fk_notifications_model_id` FOREIGN KEY (`model_id`) REFERENCES `models` (`model_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`model_id`) REFERENCES `models` (`model_id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`),
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`model_id`) REFERENCES `models` (`model_id`);

--
-- Constraints for table `receipts`
--
ALTER TABLE `receipts`
  ADD CONSTRAINT `receipts_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`company_id`);

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
