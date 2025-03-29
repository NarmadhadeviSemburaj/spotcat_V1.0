-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 29, 2025 at 08:03 AM
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
-- Table structure for table `staff_allocation`
--

CREATE TABLE `staff_allocation` (
  `allocation_id` varchar(20) NOT NULL,
  `employee_id` varchar(20) NOT NULL,
  `cluster_id` varchar(20) NOT NULL,
  `line_id` varchar(20) NOT NULL,
  `shift_id` varchar(20) NOT NULL,
  `allocation_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `staff_allocation`
--
DELIMITER $$
CREATE TRIGGER `before_insert_staff_allocation` BEFORE INSERT ON `staff_allocation` FOR EACH ROW BEGIN
    DECLARE new_allocation_id VARCHAR(20);
    
    SELECT CONCAT('SAID_', LPAD(COALESCE(MAX(CAST(SUBSTRING(allocation_id, 6) AS UNSIGNED)), 0) + 1, 5, '0'))
    INTO new_allocation_id FROM staff_allocation;

    SET NEW.allocation_id = new_allocation_id;
END
$$
DELIMITER ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `staff_allocation`
--
ALTER TABLE `staff_allocation`
  ADD PRIMARY KEY (`allocation_id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `cluster_id` (`cluster_id`),
  ADD KEY `line_id` (`line_id`),
  ADD KEY `shift_id` (`shift_id`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `staff_allocation`
--
ALTER TABLE `staff_allocation`
  ADD CONSTRAINT `staff_allocation_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employee` (`employee_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `staff_allocation_ibfk_2` FOREIGN KEY (`cluster_id`) REFERENCES `cluster` (`cluster_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `staff_allocation_ibfk_3` FOREIGN KEY (`line_id`) REFERENCES `line` (`line_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `staff_allocation_ibfk_4` FOREIGN KEY (`shift_id`) REFERENCES `shift` (`shift_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
