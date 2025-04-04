-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 04, 2025 at 12:56 PM
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
-- Table structure for table `leave_request`
--

CREATE TABLE `leave_request` (
  `leave_id` varchar(20) NOT NULL,
  `employee_id` varchar(20) NOT NULL,
  `leave_date` date NOT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `approved_by` varchar(20) DEFAULT NULL,
  `is_emergency` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `rejection_reason` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leave_request`
--

INSERT INTO `leave_request` (`leave_id`, `employee_id`, `leave_date`, `reason`, `status`, `approved_by`, `is_emergency`, `created_at`, `updated_at`, `rejection_reason`) VALUES
('LEAVE_1743757174_974', 'EID_00001', '2025-04-06', 'Planned vacation', 'Approved', NULL, 0, '2025-04-04 08:59:34', '2025-04-04 08:59:34', NULL),
('LEAVE_1743757363_515', 'EID_00002', '2025-04-05', 'Planned vacation', 'Pending', NULL, 0, '2025-04-04 09:02:43', '2025-04-04 09:02:43', NULL),
('LEAVE_1743761857_514', 'EID_00004', '2025-04-15', 'Christmas holiday', 'Approved', NULL, 0, '2025-04-04 10:17:37', '2025-04-04 10:17:37', NULL),
('LEAVE_1743761885_826', 'EID_00005', '2025-04-05', 'Christmas holiday', 'Approved', 'EID_00001', 1, '2025-04-04 10:18:05', '2025-04-04 10:20:11', NULL);

--
-- Triggers `leave_request`
--
DELIMITER $$
CREATE TRIGGER `before_leave_request_insert` BEFORE INSERT ON `leave_request` FOR EACH ROW BEGIN
    DECLARE max_id INT;
    DECLARE next_id INT;
    
    -- Find the highest existing numeric ID
    SELECT IFNULL(MAX(CAST(SUBSTRING(leave_id, 4) AS UNSIGNED)), 0) 
    INTO max_id 
    FROM leave_request 
    WHERE leave_id REGEXP '^LV_[0-9]+$';
    
    -- Calculate next ID
    SET next_id = max_id + 1;
    
    -- Format the ID with leading zeros
    SET NEW.leave_id = CONCAT('LV_', LPAD(next_id, 9, '0'));
END
$$
DELIMITER ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `leave_request`
--
ALTER TABLE `leave_request`
  ADD PRIMARY KEY (`leave_id`),
  ADD UNIQUE KEY `unique_employee_date` (`employee_id`,`leave_date`),
  ADD KEY `approved_by` (`approved_by`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `leave_request`
--
ALTER TABLE `leave_request`
  ADD CONSTRAINT `leave_request_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employee` (`employee_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `leave_request_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `employee` (`employee_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
