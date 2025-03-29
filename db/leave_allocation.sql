-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 29, 2025 at 08:01 AM
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
-- Table structure for table `leave_allocation`
--

CREATE TABLE `leave_allocation` (
  `leave_id` varchar(20) NOT NULL,
  `employee_id` varchar(20) NOT NULL,
  `leave_type` enum('Casual','Sick','Earned','Maternity','Other') NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `total_days` int(11) GENERATED ALWAYS AS (to_days(`end_date`) - to_days(`start_date`) + 1) STORED,
  `leave_status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `is_emergency` tinyint(1) DEFAULT 0,
  `reason` text DEFAULT NULL,
  `applied_on` timestamp NOT NULL DEFAULT current_timestamp(),
  `approved_by` varchar(20) DEFAULT NULL,
  `approval_date` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `leave_allocation`
--
DELIMITER $$
CREATE TRIGGER `before_insert_leave_allocation` BEFORE INSERT ON `leave_allocation` FOR EACH ROW BEGIN
    DECLARE new_leave_id VARCHAR(20);
    
    SELECT CONCAT('LID_', LPAD(COALESCE(MAX(CAST(SUBSTRING(leave_id, 5) AS UNSIGNED)), 0) + 1, 5, '0'))
    INTO new_leave_id FROM leave_allocation;

    SET NEW.leave_id = new_leave_id;
END
$$
DELIMITER ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `leave_allocation`
--
ALTER TABLE `leave_allocation`
  ADD PRIMARY KEY (`leave_id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `approved_by` (`approved_by`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `leave_allocation`
--
ALTER TABLE `leave_allocation`
  ADD CONSTRAINT `leave_allocation_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employee` (`employee_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `leave_allocation_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `employee` (`employee_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
