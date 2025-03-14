-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 14, 2025 at 06:12 PM
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
(5, 2, 'low_stock', 'Product wire has reached low stock level (Qty: 10).', 10, 0, '2025-03-14 17:04:19'),
(6, 3, 'low_stock', 'Product screwdriver has reached low stock level (Qty: 10).', 10, 0, '2025-03-14 17:04:27'),
(7, 4, 'low_stock', 'Product tanso has reached low stock level (Qty: 10).', 10, 0, '2025-03-14 17:04:30'),
(8, 5, 'low_stock', 'Product qawqw has reached low stock level (Qty: 10).', 10, 0, '2025-03-14 17:04:34');

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
(1, 'Protek', NULL, 25000.00, 5, 20, '2025-03-13 07:32:43'),
(2, 'wire', NULL, 900.00, 10, 20, '2025-03-13 08:07:28'),
(3, 'screwdriver', NULL, 190.00, 10, 20, '2025-03-13 08:08:36'),
(4, 'tanso', NULL, 90000.00, 10, 20, '2025-03-13 08:08:58'),
(5, 'qawqw', NULL, 123.00, 10, 20, '2025-03-13 08:09:25');

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `sale_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `sale_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `total_amount` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`sale_id`, `user_id`, `sale_date`, `total_amount`) VALUES
(1, 1, '2025-03-13 07:33:30', 125000.00),
(2, 1, '2025-03-13 07:38:53', 25000.00),
(3, 1, '2025-03-13 09:07:14', 1213.00),
(4, 1, '2025-03-13 09:07:17', 1213.00),
(5, 1, '2025-03-13 09:07:20', 116023.00),
(6, 1, '2025-03-13 09:07:22', 91213.00),
(7, 1, '2025-03-13 09:07:25', 116023.00),
(8, 1, '2025-03-13 09:07:28', 1213.00),
(9, NULL, '2025-02-14 16:00:00', 1000.00),
(10, NULL, '2025-02-19 16:00:00', 1500.00),
(11, NULL, '2025-02-28 16:00:00', 2000.00),
(12, NULL, '2025-03-04 16:00:00', 2500.00),
(13, 1, '2025-03-14 16:10:14', 475000.00),
(14, 1, '2025-03-14 16:14:51', 125000.00),
(15, 1, '2025-03-14 16:21:28', 450000.00),
(16, 1, '2025-03-14 16:21:54', 300000.00),
(17, 1, '2025-03-14 16:40:44', 250000.00),
(18, 1, '2025-03-14 16:48:16', 250000.00),
(19, 1, '2025-03-14 16:56:40', 400000.00),
(20, 1, '2025-03-14 16:58:42', 100000.00);

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
(31, 20, 1, 4, 25000.00);

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
  ADD KEY `user_id` (`user_id`);

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
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `sale_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `sales_items`
--
ALTER TABLE `sales_items`
  MODIFY `sale_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

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
