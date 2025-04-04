-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 04, 2025 at 07:06 AM
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
-- Table structure for table `dcm_staffing`
--

CREATE TABLE `dcm_staffing` (
  `staffing_id` varchar(20) NOT NULL,
  `dcm_id` varchar(20) NOT NULL,
  `zone_id` varchar(20) DEFAULT NULL,
  `zone_name` varchar(255) DEFAULT NULL,
  `dcm_name` varchar(255) NOT NULL,
  `regular_staff_count` int(11) DEFAULT 4,
  `backup_count` int(11) DEFAULT 1,
  `incharge_count` int(11) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `dcm_staffing`
--
DELIMITER $$
CREATE TRIGGER `before_dcm_staffing_insert` BEFORE INSERT ON `dcm_staffing` FOR EACH ROW BEGIN
    -- Generate STID_0001 format ID
    SET NEW.staffing_id = CONCAT('STID_', 
        LPAD(
            (SELECT IFNULL(MAX(CAST(SUBSTRING(staffing_id, 6) AS UNSIGNED)), 0) + 1 
            FROM `dcm_staffing` 
            WHERE staffing_id LIKE 'STID_%'
        ), 4, '0')
    );
    
    -- Automatically set dcm_id if only dcm_name was provided
    IF (NEW.dcm_id IS NULL OR NEW.dcm_id = '') AND NEW.dcm_name IS NOT NULL THEN
        SET NEW.dcm_id = (SELECT dcm_id FROM dcm WHERE dcm_name = NEW.dcm_name LIMIT 1);
    END IF;
    
    -- Automatically set zone_id if only zone_name was provided
    IF (NEW.zone_id IS NULL OR NEW.zone_id = '') AND NEW.zone_name IS NOT NULL THEN
        SET NEW.zone_id = (SELECT zone_id FROM zone WHERE zone_name = NEW.zone_name LIMIT 1);
    END IF;
END
$$
DELIMITER ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `dcm_staffing`
--
ALTER TABLE `dcm_staffing`
  ADD PRIMARY KEY (`staffing_id`),
  ADD UNIQUE KEY `unique_dcm` (`dcm_id`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `dcm_staffing`
--
ALTER TABLE `dcm_staffing`
  ADD CONSTRAINT `dcm_staffing_ibfk_1` FOREIGN KEY (`dcm_id`) REFERENCES `dcm` (`dcm_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
