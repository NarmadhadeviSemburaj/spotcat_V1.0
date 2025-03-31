-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 31, 2025 at 08:26 AM
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
-- Table structure for table `clusters`
--

CREATE TABLE `clusters` (
  `clusters_id` varchar(20) NOT NULL,
  `clusters_name` varchar(100) NOT NULL,
  `clusters_location` varchar(255) DEFAULT NULL,
  `clusters_pincode` char(6) DEFAULT NULL,
  `cluster_id` varchar(20) NOT NULL,
  `zone_id` varchar(20) DEFAULT NULL,
  `zone_name` varchar(100) DEFAULT NULL,
  `dcm_id` varchar(20) DEFAULT NULL,
  `dcm_name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `clusters`
--
DELIMITER $$
CREATE TRIGGER `before_clusters_insert` BEFORE INSERT ON `clusters` FOR EACH ROW BEGIN
    DECLARE next_id INT;
    DECLARE prefix VARCHAR(5) DEFAULT 'CLST_';
    
    SELECT IFNULL(MAX(CAST(SUBSTRING(clusters_id, 6) AS UNSIGNED)), 0) INTO next_id
    FROM clusters
    WHERE clusters_id LIKE CONCAT(prefix, '%');
    
    SET next_id = next_id + 1;
    SET NEW.clusters_id = CONCAT(prefix, LPAD(next_id, 5, '0'));
END
$$
DELIMITER ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `clusters`
--
ALTER TABLE `clusters`
  ADD PRIMARY KEY (`clusters_id`),
  ADD KEY `cluster_id` (`cluster_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
