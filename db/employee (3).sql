-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 01, 2025 at 11:27 AM
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
-- Database: `spotcat_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `employee`
--

CREATE TABLE `employee` (
  `employee_id` varchar(20) NOT NULL,
  `emp_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mobile_number` varchar(15) NOT NULL,
  `aadhar_number` char(12) DEFAULT NULL,
  `pan_number` char(10) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `address` varchar(100) NOT NULL,
  `emp_pincode` char(6) NOT NULL,
  `designation` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_admin` tinyint(1) DEFAULT 0,
  `zone_id` varchar(20) NOT NULL,
  `zone_name` varchar(100) NOT NULL,
  `dcm_id` varchar(20) NOT NULL,
  `dcm_name` varchar(100) NOT NULL,
  `cluster_id` varchar(20) NOT NULL,
  `cluster_name` varchar(100) NOT NULL,
  `shift_id` varchar(20) DEFAULT NULL,
  `shift_name` enum('Morning','Evening') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee`
--

INSERT INTO `employee` (`employee_id`, `emp_name`, `email`, `mobile_number`, `aadhar_number`, `pan_number`, `photo`, `address`, `emp_pincode`, `designation`, `password`, `is_admin`, `zone_id`, `zone_name`, `dcm_id`, `dcm_name`, `cluster_id`, `cluster_name`, `shift_id`, `shift_name`, `created_at`, `updated_at`) VALUES
('EID_00009', 'Priya Sharma', 'priya.sharma@example.com', '7418529631', '123856789012', 'ABFDE1234F', 'https://example.com/photos/priya.jpg', '456 MG Road, Bangalore', '560002', 'Operations Head', '$2y$10$W75ATq/vfD/nISavvaibLexLYiEKs7DV0ILAGgV.evJFAqzZQqC2e', 1, 'ZID_00001', 'Salem', 'DCM_00001', 'Narmadha', 'CID_001', 'Packing', 'SID_01', 'Morning', '2025-04-01 08:48:25', '2025-04-01 08:48:25'),
('EID_00010', 'Priya Sharma', 'priya@example.com', '7458529631', '128856789012', 'ABFDE1634F', 'https://example.com/photos/priya.jpg', '456 MG Road, Bangalore', '560002', 'Operations Head', '$2y$10$n9Wo.xeNdZIe9ZkroB/ime3TCFki3gxpfT.k.wYWqVvV7so8zL4XO', 1, 'ZID_00001', 'Salem', 'DCM_00001', 'Narmadha', 'CID_001', 'Packing', 'SID_01', 'Morning', '2025-04-01 08:51:56', '2025-04-01 08:51:56');

--
-- Triggers `employee`
--
DELIMITER $$
CREATE TRIGGER `before_insert_employee` BEFORE INSERT ON `employee` FOR EACH ROW BEGIN
    DECLARE new_id VARCHAR(20);
    
    -- Generate next employee ID in the format EID_00001
    SELECT CONCAT('EID_', LPAD(COALESCE(MAX(CAST(SUBSTRING(employee_id, 5) AS UNSIGNED)), 0) + 1, 5, '0'))
    INTO new_id FROM employee;

    SET NEW.employee_id = new_id;
END
$$
DELIMITER ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `employee`
--
ALTER TABLE `employee`
  ADD PRIMARY KEY (`employee_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `shift_id` (`shift_id`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `employee`
--
ALTER TABLE `employee`
  ADD CONSTRAINT `employee_ibfk_1` FOREIGN KEY (`shift_id`) REFERENCES `shift` (`shift_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
