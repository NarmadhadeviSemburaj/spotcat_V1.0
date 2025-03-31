-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 31, 2025 at 08:25 AM
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
-- Table structure for table `line`
--

CREATE TABLE `line` (
  `line_id` varchar(20) NOT NULL,
  `line_name` varchar(100) NOT NULL,
  `line_location` varchar(255) DEFAULT NULL,
  `line_pincode` char(6) DEFAULT NULL,
  `cluster_id` varchar(20) NOT NULL,
  `zone_id` varchar(20) DEFAULT NULL,
  `zone_name` varchar(100) DEFAULT NULL,
  `dcm_id` varchar(20) DEFAULT NULL,
  `dcm_name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `line`
--

INSERT INTO `line` (`line_id`, `line_name`, `line_location`, `line_pincode`, `cluster_id`, `zone_id`, `zone_name`, `dcm_id`, `dcm_name`) VALUES
('PLID_0003', 'Purchase Line A', 'Warehouse Section B', '400001', 'CID_001', 'ZID_00002', 'North Zone', 'DCM_00002', 'Main Distribution Center'),
('PLID_0005', 'Main Purchase Line', 'Warehouse A', '400001', 'CID_001', 'ZID_00002', 'North Zone', 'DCM_00002', 'Main Distribution Center'),
('PLID_0006', 'Main Purchase Line', 'Warehouse A', '400001', 'CID_001', 'ZID_00002', 'North Zone', 'DCM_00002', 'Main Distribution Center'),
('PLID_0007', 'Main Purchase Line', 'Warehouse A', '400001', 'CID_001', 'ZID_00002', 'North Zone', 'DCM_00002', 'Main Distribution Center'),
('PLID_0008', 'Main Purchase Line', 'Warehouse A', '400081', 'CID_001', 'ZID_00002', 'North Zone', 'DCM_00002', 'Main Distribution Center');

--
-- Triggers `line`
--
DELIMITER $$
CREATE TRIGGER `before_insert_line` BEFORE INSERT ON `line` FOR EACH ROW BEGIN
    DECLARE max_id INT;
    DECLARE new_id VARCHAR(20);

    -- Get the numeric part of the highest existing line_id
    SELECT IFNULL(MAX(CAST(SUBSTRING(line_id, 6, 4) AS UNSIGNED)), 0) + 1 INTO max_id FROM `line`;

    -- Format the new line_id as PLID_XXXX
    SET new_id = CONCAT('PLID_', LPAD(max_id, 4, '0'));

    -- Assign the generated line_id to the new row
    SET NEW.line_id = new_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `before_line_insert` BEFORE INSERT ON `line` FOR EACH ROW BEGIN
    -- Get DCM name
    SELECT dcm_name INTO @dcm_name FROM `dcm` WHERE dcm_id = NEW.dcm_id LIMIT 1;
    -- Get Zone name
    SELECT zone_name INTO @zone_name FROM `zone` WHERE zone_id = NEW.zone_id LIMIT 1;
    
    SET NEW.dcm_name = @dcm_name;
    SET NEW.zone_name = @zone_name;
END
$$
DELIMITER ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `line`
--
ALTER TABLE `line`
  ADD PRIMARY KEY (`line_id`),
  ADD KEY `cluster_id` (`cluster_id`),
  ADD KEY `fk_line_zone` (`zone_id`),
  ADD KEY `fk_line_dcm` (`dcm_id`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `line`
--
ALTER TABLE `line`
  ADD CONSTRAINT `fk_line_dcm` FOREIGN KEY (`dcm_id`) REFERENCES `dcm` (`dcm_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_line_zone` FOREIGN KEY (`zone_id`) REFERENCES `zone` (`zone_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `line_ibfk_1` FOREIGN KEY (`cluster_id`) REFERENCES `cluster` (`cluster_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
