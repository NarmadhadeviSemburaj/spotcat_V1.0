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
-- Table structure for table `zone`
--

CREATE TABLE `zone` (
  `zone_id` varchar(20) NOT NULL,
  `zone_name` varchar(100) NOT NULL,
  `zone_location` varchar(255) DEFAULT NULL,
  `zone_pincode` char(6) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `zone`
--

INSERT INTO `zone` (`zone_id`, `zone_name`, `zone_location`, `zone_pincode`) VALUES
('ZID_00002', 'North Zone', 'Delhi', '110001'),
('ZID_00003', 'Salem', 'Sankari', '637301');

--
-- Triggers `zone`
--
DELIMITER $$
CREATE TRIGGER `before_insert_zone` BEFORE INSERT ON `zone` FOR EACH ROW BEGIN
    DECLARE new_zone_id VARCHAR(20);
    
    SELECT CONCAT('ZID_', LPAD(COALESCE(MAX(CAST(SUBSTRING(zone_id, 5) AS UNSIGNED)), 0) + 1, 5, '0'))
    INTO new_zone_id FROM zone;

    SET NEW.zone_id = new_zone_id;
END
$$
DELIMITER ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `zone`
--
ALTER TABLE `zone`
  ADD PRIMARY KEY (`zone_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
