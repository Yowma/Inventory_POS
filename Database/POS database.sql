-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 13, 2025 at 10:22 AM
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
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `name`, `description`, `price`, `quantity`, `created_at`) VALUES
(1, 'Protek', NULL, 25000.00, 7, '2025-03-13 07:32:43'),
(2, 'wire', NULL, 900.00, 114, '2025-03-13 08:07:28'),
(3, 'screwdriver', NULL, 190.00, 76, '2025-03-13 08:08:36'),
(4, 'tanso', NULL, 90000.00, 187, '2025-03-13 08:08:58'),
(5, 'qawqw', NULL, 123.00, 12306, '2025-03-13 08:09:25');

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
(12, NULL, '2025-03-04 16:00:00', 2500.00);

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
(23, 8, 5, 1, 123.00);

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
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `sale_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `sales_items`
--
ALTER TABLE `sales_items`
  MODIFY `sale_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

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
