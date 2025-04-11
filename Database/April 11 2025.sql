-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 11, 2025 at 11:10 AM
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
  `tin_no` varchar(255) DEFAULT NULL,
  `contact_person` varchar(255) NOT NULL,
  `contact_number` varchar(50) NOT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `business_style` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `companies`
--

INSERT INTO `companies` (`company_id`, `name`, `address`, `tin_no`, `contact_person`, `contact_number`, `telephone`, `business_style`) VALUES
(18, 'sample', 'JP rizal', '121-231-212', 'Bundang', '12312312312', NULL, 'School'),
(19, 'BRADSMART PHILS. CO', '973 Maligaya St. Sta. Cruz Sta Maria, Bulacan', '010-275-926', 'Antonette', '', NULL, 'Sales'),
(21, 'MC ENGINEERING, INC.', '98 sgt. J. Catolos St., Cubao, Quezon City', '000-388-110', 'Mr. Jonjett Miranda', '', '867-8301', 'Sales'),
(22, 'G. UYMATIAO JR. CONSTRUCTION', '49 Sta. Catalina St. Dumaguete City', '102-713-377', 'Gerard Uymatiao', '09173143841', '', 'Sales'),
(23, 'SPEC-MASTER, INCORPORATED', '4P Bernal St. Brgy. Rosario, Pasig City', '003-509-728', 'Mary Jane Lopez', '', '86435567', 'SPEC-MASTER, INCORPORATED'),
(24, 'TC FLORENTIN ELECTROSYSTEM BUILDERS CORP.', 'TCF Bldg., Blk 4 Lot 1 Janice St., MCB II Subd. Bagumbong, Caloocan City', '', '', '', NULL, 'TC FLORENTIN ELECTROSYSTEM BUILDERS CORP.'),
(25, 'KYKISH ENTERPRISE', '346 Villa Verde Avenue Villa Verde Subd. Brgy. Sta Monica Novaliches Quezon City', '', '', '', NULL, 'KYKISH ENTERPRISE'),
(26, 'ENERGYNET, INC.', '112 San Miguel St., Brgy. Plainview, Mandaluyong City', '236-600-473', 'Ms. Donita Rose', '', '', 'ENERGYNET, INC.'),
(27, 'UNITEC RESOURCES INC.', '1199 Mendiola Extension, Paco Manila, 1107', '', 'Ms. Theresa', '09063268867', '', 'UNITEC RESOURCES INC'),
(28, 'DIGITAL NETWORK & MOBILE SOLUTIONS INC.', '2nd Floor GCN Building, 240 Narra St., Marikina Heights Marikina City', '', '', '', '', 'DIGITAL NETWORK & MOBILE SOLUTIONS INC.');

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
(1, '1P24', 114),
(2, 'BERT 1P24', 145),
(3, 'THREE PHASE DELTA (PROT)', 28),
(4, 'THREE PHASE WYE (PROT)', 39),
(5, 'THREE PHASE DELTA (ProHv)', 132);

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
(56, 'low_stock', 'Model \'1P24\' is running low on stock.', 4, 1, '2025-04-02 06:03:40', 1),
(57, 'low_stock', 'Model \'1P24\' is running low on stock.', 5, 1, '2025-04-04 04:25:20', 1);

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
(40, 'Prot 160 3Y6', '160kA/Phase, 230-600', 0, 10, '2025-04-02 06:56:41', NULL, 45000.00, 4, 0),
(41, 'ProHv 200 3N2', '200kA/Phase, 220-240 vac', 0, 10, '2025-04-04 03:29:32', NULL, 65000.00, 5, 0),
(42, 'ProHv 240 3N2', '240kA/Phase, 220-240 vac', 0, 10, '2025-04-04 03:30:04', NULL, 75000.00, 5, 0),
(43, 'ProHv 300 3N2', '300kA/Phase, 220-240 vac', 0, 10, '2025-04-04 03:30:29', NULL, 90000.00, 5, 0),
(44, 'ProHv 400 3N2', '400kA/Phase, 220-240 vac', 0, 10, '2025-04-04 03:30:52', NULL, 110000.00, 5, 0),
(45, 'ProHv 500 3N2', '500kA/Phase, 220-240 vac', 0, 10, '2025-04-04 03:31:10', NULL, 165000.00, 5, 0),
(46, 'ProHv 200 3N4', '200kA/Phase, 440-480 vac', 0, 10, '2025-04-04 03:32:30', NULL, 65000.00, 5, 0),
(47, 'ProHv 240 3N4', '240kA/Phase, 440-480 vac', 0, 10, '2025-04-04 03:32:48', NULL, 75000.00, 5, 0),
(48, 'ProHv 300 3N4', '300kA/Phase, 440-480 vac', 0, 10, '2025-04-04 03:33:08', NULL, 90000.00, 5, 0),
(49, 'ProHv 400 3N4', '400kA/Phase, 440-480 vac', 0, 10, '2025-04-04 03:33:20', NULL, 110000.00, 5, 0),
(50, 'ProHv 500 3N4', '500kA/Phase, 440-480 vac', 0, 10, '2025-04-04 03:34:13', NULL, 165000.00, 5, 0);

-- --------------------------------------------------------

--
-- Table structure for table `receipts`
--

CREATE TABLE `receipts` (
  `receipt_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `upload_date` datetime DEFAULT current_timestamp(),
  `dr_file_name` varchar(255) DEFAULT NULL,
  `po_file_name` varchar(255) DEFAULT NULL,
  `po_number` varchar(255) DEFAULT NULL,
  `tax_type` enum('inclusive','exclusive') DEFAULT 'inclusive'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `receipts`
--

INSERT INTO `receipts` (`receipt_id`, `company_id`, `file_name`, `upload_date`, `dr_file_name`, `po_file_name`, `po_number`, `tax_type`) VALUES
(1, 18, '1743561777_sample.pdf', '2025-04-02 10:42:57', NULL, NULL, NULL, 'inclusive'),
(2, 18, '1743563050_receipt_Wakayama_HomeTask2(HT2) - History.pdf', '2025-04-02 11:04:10', '1743563050_dr_Template - Thesis 1 - Kick off meeting.docx.pdf', '1743563050_po_Wakayama_Cuerdo_LabAct4_IPO,FLOWCHART, VARIABLE TABLE.pdf', NULL, 'inclusive'),
(3, 18, '1743582480_receipt_Problem 1 Flowchart and IPO.pdf', '2025-04-02 16:28:00', '1743582480_dr_Problem 2 Flowchart and IPO.pdf', '1743582480_po_sample.pdf', NULL, 'inclusive'),
(4, 18, '1743740957_receipt_sample.pdf', '2025-04-04 12:29:17', '1743740957_dr_sample.pdf', '1743740957_po_sample.pdf', NULL, 'inclusive'),
(5, 18, '1743750634_receipt_sample.pdf', '2025-04-04 15:10:34', '1743750634_dr_sample.pdf', '1743750634_po_sample.pdf', NULL, 'exclusive'),
(6, 18, 'receipt_3009_9090.pdf', '2025-04-11 05:28:45', NULL, '1744347371_po_Problem 1 Flowchart and IPO.pdf', '9090', 'inclusive'),
(7, 18, 'receipt_3010_9010.pdf', '2025-04-11 06:16:21', 'dr_3010_9010.pdf', NULL, '9010', 'inclusive'),
(8, 18, 'receipt_3011_9030.pdf', '2025-04-11 06:21:59', 'dr_3011_9030.pdf', '1744349596_po_Problem 1 Flowchart and IPO.pdf', '9030', 'inclusive'),
(9, 18, 'receipt_3012_9080.pdf', '2025-04-11 06:27:25', 'dr_3012_9080.pdf', '1744347446_po_Problem 2 Flowchart and IPO.pdf', '9080', 'inclusive'),
(10, 18, NULL, '2025-04-11 07:29:12', 'dr_3013_9050.pdf', NULL, '9050', 'exclusive');

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
(76, 1, '2025-04-02 07:02:27', 43000.00, 18, 1212, 3002),
(77, 1, '2025-04-04 02:29:04', 1000.00, 18, 9090, 3003),
(78, 1, '2025-04-04 02:29:25', 1000.00, 18, 9090, 3004),
(79, 1, '2025-04-04 04:25:20', 125000.00, 18, 90, 3005),
(80, 1, '2025-04-04 05:14:35', 10000.00, 18, 9090, 3006),
(81, 1, '2025-04-04 05:23:57', 1000.00, 18, 9090, 3007),
(82, 1, '2025-04-04 06:00:43', 1000.00, 18, 9090, 3008),
(83, 1, '2025-04-11 03:28:44', 1000.00, 18, 9090, 3009),
(84, 1, '2025-04-11 04:16:21', 1000.00, 18, 9010, 3010),
(85, 1, '2025-04-11 04:21:59', 21000.00, 18, 9030, 3011),
(86, 1, '2025-04-11 04:27:25', 1000.00, 18, 9080, 3012),
(91, 1, '2025-04-11 05:29:12', 1000.00, 18, 9050, 3013);

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
(95, 76, 18, 1, 22000.00),
(96, 77, 16, 1, 1000.00),
(97, 78, 16, 1, 1000.00),
(98, 79, 16, 125, 1000.00),
(99, 80, 16, 10, 1000.00),
(100, 81, 16, 1, 1000.00),
(101, 82, 16, 1, 1000.00),
(102, 83, 16, 1, 1000.00),
(103, 84, 16, 1, 1000.00),
(104, 85, 21, 1, 21000.00),
(105, 86, 16, 1, 1000.00),
(110, 91, 16, 1, 1000.00);

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
  MODIFY `company_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `company_product_prices`
--
ALTER TABLE `company_product_prices`
  MODIFY `company_product_price_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `models`
--
ALTER TABLE `models`
  MODIFY `model_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `receipts`
--
ALTER TABLE `receipts`
  MODIFY `receipt_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `sale_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=92;

--
-- AUTO_INCREMENT for table `sales_items`
--
ALTER TABLE `sales_items`
  MODIFY `sale_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=111;

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
