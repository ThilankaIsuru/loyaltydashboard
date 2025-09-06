-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 06, 2025 at 06:41 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `loyalty_rewards`
--

-- --------------------------------------------------------

--
-- Table structure for table `merchants`
--

CREATE TABLE `merchants` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `merchants`
--

INSERT INTO `merchants` (`id`, `name`, `description`) VALUES
(1, 'Cargills', 'Supermarket with loyalty rewards'),
(2, 'Keellssuper', 'The most rewarding and largest supermarket in Sri Lanka.'),
(3, 'Spar', 'Activate your New Spar Rewards card and start getting more!'),
(4, 'Arpico', 'Unlock Exclusive Rewards and Benefits.'),
(5, 'Lanka Super', 'Your one-stop shop for daily essentials.');

-- --------------------------------------------------------

--
-- Table structure for table `rewards`
--

CREATE TABLE `rewards` (
  `id` int NOT NULL,
  `merchant_id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text,
  `points_required` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `rewards`
--

INSERT INTO `rewards` (`id`, `merchant_id`, `name`, `description`, `points_required`) VALUES
(1, 1, 'Free Bread', 'Get a free loaf of bread', 50),
(2, 1, 'Free Bread', 'Get a free loaf of bread', 50),
(3, 2, '50% Off Voucher', 'Use on any item', 100),
(4, 2, 'Free Coffee', 'Enjoy a free coffee', 30),
(5, 3, 'Buy 1 Get 1 Free', 'On selected items', 80),
(6, 4, '10% Off Voucher', 'For all purchases', 60),
(7, 5, 'Exclusive Gift Card', 'Redeem for cash', 200);

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `merchant_id` int DEFAULT NULL,
  `action_type` enum('purchase','redeem','earned') NOT NULL,
  `points_earned` int DEFAULT '0',
  `points_used` int DEFAULT '0',
  `description` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `merchant_id`, `action_type`, `points_earned`, `points_used`, `description`, `created_at`) VALUES
(1, 1, 1, 'purchase', 100, 0, 'Purchased groceries', '2025-08-24 21:21:26'),
(2, 1, 1, 'purchase', 100, 0, 'Bought groceries at Cargills', '2025-09-05 12:10:58'),
(3, 1, 2, 'purchase', 80, 0, 'Bought items at Keells', '2025-09-05 12:10:58'),
(4, 1, 3, 'purchase', 60, 0, 'Bought snacks at Spar', '2025-09-05 12:10:58'),
(5, 1, 4, 'purchase', 120, 0, 'Bought household items at Arpico', '2025-09-05 12:10:58'),
(6, 2, 5, 'purchase', 50, 0, 'Bought daily essentials at Lanka Super', '2025-09-05 12:10:58'),
(7, 2, 4, 'purchase', 60, 0, 'Bought coffee at Cargills', '2025-09-05 12:10:58'),
(8, 2, 2, 'purchase', 40, 0, 'Bought bread at Keells', '2025-09-05 12:10:58'),
(9, 2, 1, 'purchase', 50, 0, 'Bought electronics at Cargills', '2025-09-05 12:10:58'),
(10, 4, 2, 'purchase', 100, 0, 'Bought books at Keells', '2025-09-05 12:10:58'),
(11, 4, 3, 'purchase', 20, 0, 'Bought drinks at Spar', '2025-09-05 12:10:58'),
(12, 5, 3, 'purchase', 125, 0, 'Bought clothes at Arpico', '2025-09-05 12:10:58'),
(13, 6, 4, 'purchase', 75, 0, 'Bought toiletries at Lanka Super', '2025-09-05 12:10:58'),
(14, 7, 1, 'purchase', 50, 0, 'Bought milk at Cargills', '2025-09-05 12:10:58'),
(15, 7, 2, 'purchase', 60, 0, 'Bought eggs at Keells', '2025-09-05 12:10:58'),
(16, 7, 3, 'purchase', 40, 0, 'Bought juice at Spar', '2025-09-05 12:10:58'),
(17, 7, 4, 'purchase', 25, 0, 'Bought soap at Arpico', '2025-09-05 12:10:58');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `phone` varchar(10) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `phone`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'daham', 'ucsc', '0701234567', 'daham@gmail.com', '$2y$10$BaIyE8BOPvhYiR8xv5u/BumZZMvxc7BESlqqgA5ThBU/yMVfOdtv.', 'user', '2025-09-04 23:25:06'),
(2, 'Thilanka', 'ucsc', '0702408563', 'thilanka@gmail.com', '$2y$10$Qkyty7.OxIpERPqsT4LaQuV42q38X7ykOpjyACjd.wqHJYPVoFKnS', 'user', '2025-09-05 14:04:19'),
(3, 'admin', 'user', '0123456789', 'admin@gmail.com', '$2y$10$GizWKoSNN5mEU.NgHW7EPeQbub5r78NTRDr3W8noDGuIH0VuRLWb6', 'admin', '2025-09-05 03:11:33'),
(4, 'yasith', 'ucsc', '0714586953', 'yasith@gmail.com', '$2y$10$N4U9bRzBYO/EWU9/qC7ubeJo/yPtuSFtywC5GwjJIpQvfdTfMh94K', 'user', '2025-09-05 14:30:54'),
(5, 'oshani', 'ucsc', '0778596358', 'oshani@gmail.com', '$2y$10$tF64DPfDOn4kdl8MHM.OO.pOm7T/eME14HvCMUiQwvHjUXahMl1iu', 'user', '2025-09-05 14:31:32'),
(6, 'chanuki', 'ucsc', '0745863257', 'chanuki@gmail.com', '$2y$10$/HtRUxPmcUdMYX9l/21oZu4jJx5TcakzCh683sMptq1Qjmwb8n6U2', 'user', '2025-09-05 14:32:07'),
(7, 'UOC', 'User', '0723456789', 'uoc@loyalty.com', '$2y$10$fvDEagTeCbxq7S2xynRPmOXs74IF8raLPVO9rOwUBbpD/uGawvJJa', 'user', '2025-09-06 10:39:30');

-- --------------------------------------------------------

--
-- Table structure for table `user_merchants`
--

CREATE TABLE `user_merchants` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `merchant_id` int NOT NULL,
  `joined_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user_merchants`
--

INSERT INTO `user_merchants` (`id`, `user_id`, `merchant_id`, `joined_at`) VALUES
(2, 1, 1, '2025-09-02 13:26:30'),
(3, 1, 2, '2025-09-02 13:26:30'),
(4, 1, 3, '2025-09-02 13:26:30'),
(5, 1, 4, '2025-09-02 13:26:30'),
(6, 1, 5, '2025-09-02 13:26:30'),
(7, 2, 1, '2025-09-02 13:36:58'),
(8, 2, 2, '2025-09-02 13:36:58'),
(9, 2, 4, '2025-09-03 15:55:54'),
(10, 2, 5, '2025-09-03 15:55:54'),
(11, 4, 2, '2025-09-03 15:55:54'),
(12, 4, 3, '2025-09-03 15:55:54'),
(13, 5, 1, '2025-09-05 03:11:33'),
(14, 5, 3, '2025-09-05 13:47:10'),
(15, 5, 5, '2025-09-05 13:47:10'),
(16, 6, 2, '2025-09-05 13:47:10'),
(17, 6, 3, '2025-09-05 13:47:10'),
(18, 6, 4, '2025-09-05 13:47:10'),
(19, 7, 1, '2025-09-06 11:19:24'),
(20, 7, 2, '2025-09-06 11:19:24'),
(21, 7, 3, '2025-09-06 11:19:24'),
(22, 7, 4, '2025-09-06 11:19:24'),
(23, 7, 5, '2025-09-06 11:19:24');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `merchants`
--
ALTER TABLE `merchants`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rewards`
--
ALTER TABLE `rewards`
  ADD PRIMARY KEY (`id`),
  ADD KEY `merchant_id` (`merchant_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `merchant_id` (`merchant_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `phone` (`phone`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_merchants`
--
ALTER TABLE `user_merchants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`merchant_id`),
  ADD KEY `merchant_id` (`merchant_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `merchants`
--
ALTER TABLE `merchants`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `rewards`
--
ALTER TABLE `rewards`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `user_merchants`
--
ALTER TABLE `user_merchants`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `rewards`
--
ALTER TABLE `rewards`
  ADD CONSTRAINT `rewards_ibfk_1` FOREIGN KEY (`merchant_id`) REFERENCES `merchants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`merchant_id`) REFERENCES `merchants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_merchants`
--
ALTER TABLE `user_merchants`
  ADD CONSTRAINT `user_merchants_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_merchants_ibfk_2` FOREIGN KEY (`merchant_id`) REFERENCES `merchants` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
