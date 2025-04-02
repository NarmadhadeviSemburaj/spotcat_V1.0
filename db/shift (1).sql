-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 02, 2025 at 05:15 AM
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
-- Table structure for table `shift`
--

CREATE TABLE `shift` (
  `shift_id` varchar(20) NOT NULL,
  `shift_name` enum('Morning','Evening') NOT NULL,
  `shift_start_time` time NOT NULL,
  `shift_end_time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shift`
--

INSERT INTO `shift` (`shift_id`, `shift_name`, `shift_start_time`, `shift_end_time`) VALUES
('SID_01', 'Morning', '06:00:00', '14:00:00');

--
-- Triggers `shift`
--
DELIMITER $$
CREATE TRIGGER `before_shift_insert` BEFORE INSERT ON `shift` FOR EACH ROW BEGIN
    DECLARE next_id INT;
    
    IF NEW.shift_id IS NULL OR NEW.shift_id = '' THEN
        -- Get the maximum numeric part of existing shift_ids
        SELECT IFNULL(MAX(CAST(SUBSTRING(shift_id, 5) AS UNSIGNED)), 0) + 1 INTO next_id
        FROM `shift`
        WHERE shift_id LIKE 'SID_%';
        
        -- Set the new shift_id in format SID_XX with leading zeros
        SET NEW.shift_id = CONCAT('SID_', LPAD(next_id, 2, '0'));
    END IF;
END
$$
DELIMITER ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `shift`
--
ALTER TABLE `shift`
  ADD PRIMARY KEY (`shift_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
