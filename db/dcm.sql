-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 29, 2025 at 08:00 AM
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
-- Table structure for table `dcm`
--

CREATE TABLE `dcm` (
  `dcm_id` varchar(20) NOT NULL,
  `dcm_name` varchar(100) NOT NULL,
  `dcm_location` varchar(255) DEFAULT NULL,
  `dcm_pincode` char(6) DEFAULT NULL,
  `zone_id` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `dcm`
--
DELIMITER $$
CREATE TRIGGER `before_insert_dcm` BEFORE INSERT ON `dcm` FOR EACH ROW BEGIN
    DECLARE new_dcm_id VARCHAR(20);
    
    SELECT CONCAT('DCM_', LPAD(COALESCE(MAX(CAST(SUBSTRING(dcm_id, 5) AS UNSIGNED)), 0) + 1, 5, '0'))
    INTO new_dcm_id FROM dcm;

    SET NEW.dcm_id = new_dcm_id;
END
$$
DELIMITER ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `dcm`
--
ALTER TABLE `dcm`
  ADD PRIMARY KEY (`dcm_id`),
  ADD KEY `zone_id` (`zone_id`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `dcm`
--
ALTER TABLE `dcm`
  ADD CONSTRAINT `dcm_ibfk_1` FOREIGN KEY (`zone_id`) REFERENCES `zone` (`zone_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
