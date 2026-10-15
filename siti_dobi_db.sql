-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 11, 2026 at 06:44 AM
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
-- Database: `siti_dobi_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `customer_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `registration_date` date NOT NULL DEFAULT curdate()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`customer_id`, `name`, `phone`, `email`, `password_hash`, `registration_date`) VALUES
(1, 'Ali bin Abu', '0111234567', 'ali@email.com', '$2y$10$n43MQIoHby7DVY41MowcIOpDGRGgq73r9E8yUAiFFTFW7ivGZ7CjK', '2026-05-20'),
(2, 'Siti Rahimah', '0129876543', 'siti@email.com', '$2y$10$n43MQIoHby7DVY41MowcIOpDGRGgq73r9E8yUAiFFTFW7ivGZ7CjK', '2026-05-20'),
(3, 'Muthu Krishnan', '0175550011', NULL, '$2y$10$n43MQIoHby7DVY41MowcIOpDGRGgq73r9E8yUAiFFTFW7ivGZ7CjK', '2026-05-20'),
(4, 'Tan Wei Liang', '0133334444', 'tan@email.com', '$2y$10$n43MQIoHby7DVY41MowcIOpDGRGgq73r9E8yUAiFFTFW7ivGZ7CjK', '2026-05-20'),
(5, 'Farah Adilah', '0165557788', 'farah@email.com', '$2y$10$n43MQIoHby7DVY41MowcIOpDGRGgq73r9E8yUAiFFTFW7ivGZ7CjK', '2026-05-20');

--
-- Triggers `customer`
--
DELIMITER $$
CREATE TRIGGER `trg_create_loyalty_card` AFTER INSERT ON `customer` FOR EACH ROW BEGIN
    INSERT INTO loyalty_card (customer_id) VALUES (NEW.customer_id);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `loyalty_card`
--

CREATE TABLE `loyalty_card` (
  `card_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `stamp_count` tinyint(4) NOT NULL DEFAULT 0 CHECK (`stamp_count` >= 0 and `stamp_count` <= 10),
  `total_free_washes` int(11) NOT NULL DEFAULT 0,
  `last_updated` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `loyalty_card`
--

INSERT INTO `loyalty_card` (`card_id`, `customer_id`, `stamp_count`, `total_free_washes`, `last_updated`) VALUES
(1, 1, 0, 1, '2026-05-20 23:14:21'),
(2, 2, 0, 0, '2026-05-20 23:14:21'),
(3, 3, 0, 0, '2026-05-20 23:14:21'),
(4, 4, 0, 0, '2026-05-20 23:14:21'),
(5, 5, 0, 0, '2026-05-20 23:14:21');

-- --------------------------------------------------------

--
-- Table structure for table `reward_redemption`
--

CREATE TABLE `reward_redemption` (
  `redemption_id` int(11) NOT NULL,
  `card_id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `redeemed_at` datetime NOT NULL DEFAULT current_timestamp(),
  `reward_type` enum('free_wash') NOT NULL DEFAULT 'free_wash',
  `status` enum('pending','redeemed','expired') NOT NULL DEFAULT 'redeemed'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reward_redemption`
--

INSERT INTO `reward_redemption` (`redemption_id`, `card_id`, `transaction_id`, `redeemed_at`, `reward_type`, `status`) VALUES
(1, 1, 10, '2026-05-20 23:14:21', 'free_wash', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `staff_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `role` enum('admin','staff') NOT NULL DEFAULT 'staff',
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`staff_id`, `name`, `role`, `username`, `password_hash`, `created_at`, `is_active`) VALUES
(1, 'Ahmad Rifqi', 'admin', 'rifqi_admin', '$2y$10$g8F6kGRiYsp4WZTlXcHenuvdLd1Ak.WsHfzNqhAGBBNxq76NozpN6', '2026-05-20 23:14:21', 1),
(2, 'Arash Adnin', 'staff', 'arash_staff', '$2y$10$g8F6kGRiYsp4WZTlXcHenuvdLd1Ak.WsHfzNqhAGBBNxq76NozpN6', '2026-05-20 23:14:21', 1),
(3, 'Haiqal Rifqi', 'staff', 'haiqal_staff', '$2y$10$g8F6kGRiYsp4WZTlXcHenuvdLd1Ak.WsHfzNqhAGBBNxq76NozpN6', '2026-05-20 23:14:21', 1);

-- --------------------------------------------------------

--
-- Table structure for table `transaction`
--

CREATE TABLE `transaction` (
  `transaction_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `transaction_date` datetime NOT NULL DEFAULT current_timestamp(),
  `total_amount` decimal(8,2) NOT NULL CHECK (`total_amount` >= 0),
  `payment_method` enum('cash','online_transfer','ewallet','debit_card') NOT NULL DEFAULT 'cash',
  `status` enum('pending','completed','cancelled') NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transaction`
--

INSERT INTO `transaction` (`transaction_id`, `customer_id`, `staff_id`, `transaction_date`, `total_amount`, `payment_method`, `status`, `notes`) VALUES
(1, 1, 2, '2026-05-20 23:14:21', 12.00, 'cash', 'completed', NULL),
(2, 1, 2, '2026-05-20 23:14:21', 15.50, 'cash', 'completed', NULL),
(3, 1, 3, '2026-05-20 23:14:21', 10.00, 'ewallet', 'completed', NULL),
(4, 1, 2, '2026-05-20 23:14:21', 18.00, 'cash', 'completed', NULL),
(5, 1, 3, '2026-05-20 23:14:21', 12.00, 'online_transfer', 'completed', NULL),
(6, 1, 2, '2026-05-20 23:14:21', 14.00, 'cash', 'completed', NULL),
(7, 1, 3, '2026-05-20 23:14:21', 11.00, 'cash', 'completed', NULL),
(8, 1, 2, '2026-05-20 23:14:21', 16.50, 'ewallet', 'completed', NULL),
(9, 1, 3, '2026-05-20 23:14:21', 13.00, 'cash', 'completed', NULL),
(10, 1, 2, '2026-05-20 23:14:21', 14.00, 'cash', 'completed', NULL),
(11, 1, 1, '2026-05-21 00:51:10', 15.00, 'ewallet', 'pending', NULL),
(12, 1, 1, '2026-06-11 12:26:50', 15.50, 'cash', 'pending', NULL),
(13, 2, 1, '2026-06-11 12:30:14', 20.00, 'cash', 'completed', NULL);

--
-- Triggers `transaction`
--
DELIMITER $$
CREATE TRIGGER `trg_add_stamp_on_update` AFTER UPDATE ON `transaction` FOR EACH ROW BEGIN
    DECLARE v_card_id   INT;
    DECLARE v_old_count TINYINT;
    DECLARE v_new_count TINYINT;

    -- Stamps are only awarded when order transitions from anything else to 'completed'
    IF NEW.status = 'completed' AND OLD.status != 'completed' THEN
        
        SELECT card_id, stamp_count 
        INTO   v_card_id, v_old_count
        FROM   loyalty_card
        WHERE  customer_id = NEW.customer_id;

        SET v_new_count = v_old_count + 1;

        IF v_new_count >= 10 THEN
            -- Reset stamp counter and record free wash earned
            UPDATE loyalty_card
            SET    stamp_count       = 0,
                   total_free_washes = total_free_washes + 1
            WHERE  card_id           = v_card_id;

            INSERT INTO reward_redemption (card_id, transaction_id, reward_type, status)
            VALUES (v_card_id, NEW.transaction_id, 'free_wash', 'pending');
        ELSE
            -- Increment stamp count normally
            UPDATE loyalty_card
            SET    stamp_count = v_new_count
            WHERE  card_id     = v_card_id;
        END IF;

    END IF;
END
$$
DELIMITER ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`customer_id`),
  ADD UNIQUE KEY `phone` (`phone`),
  ADD KEY `idx_customer_phone` (`phone`);

--
-- Indexes for table `loyalty_card`
--
ALTER TABLE `loyalty_card`
  ADD PRIMARY KEY (`card_id`),
  ADD UNIQUE KEY `customer_id` (`customer_id`);

--
-- Indexes for table `reward_redemption`
--
ALTER TABLE `reward_redemption`
  ADD PRIMARY KEY (`redemption_id`),
  ADD KEY `fk_reward_card` (`card_id`),
  ADD KEY `fk_reward_txn` (`transaction_id`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`staff_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `transaction`
--
ALTER TABLE `transaction`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `idx_txn_customer` (`customer_id`),
  ADD KEY `idx_txn_date` (`transaction_date`),
  ADD KEY `idx_status_date` (`status`,`transaction_date`),
  ADD KEY `fk_txn_staff` (`staff_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `customer`
--
ALTER TABLE `customer`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `loyalty_card`
--
ALTER TABLE `loyalty_card`
  MODIFY `card_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `reward_redemption`
--
ALTER TABLE `reward_redemption`
  MODIFY `redemption_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `staff_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `transaction`
--
ALTER TABLE `transaction`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `loyalty_card`
--
ALTER TABLE `loyalty_card`
  ADD CONSTRAINT `fk_loyalty_customer` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `reward_redemption`
--
ALTER TABLE `reward_redemption`
  ADD CONSTRAINT `fk_reward_card` FOREIGN KEY (`card_id`) REFERENCES `loyalty_card` (`card_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_reward_txn` FOREIGN KEY (`transaction_id`) REFERENCES `transaction` (`transaction_id`) ON UPDATE CASCADE;

--
-- Constraints for table `transaction`
--
ALTER TABLE `transaction`
  ADD CONSTRAINT `fk_txn_customer` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_txn_staff` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`staff_id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
