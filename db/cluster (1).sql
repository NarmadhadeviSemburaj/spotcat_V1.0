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
-- Table structure for table `cluster`
--

CREATE TABLE `cluster` (
  `cluster_id` varchar(20) NOT NULL,
  `cluster_name` varchar(100) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cluster`
--

INSERT INTO `cluster` (`cluster_id`, `cluster_name`, `status`, `created_at`, `updated_at`) VALUES
('CID_001', 'Packing', 'active', '2025-03-31 04:02:00', '2025-03-31 04:02:00'),
('CID_002', 'Incharges', 'active', '2025-03-31 05:03:03', '2025-03-31 05:03:29');

--
-- Triggers `cluster`
--
DELIMITER $$
CREATE TRIGGER `before_cluster_insert` BEFORE INSERT ON `cluster` FOR EACH ROW BEGIN
    DECLARE next_id INT;
    
    -- Get the highest existing CID_XXX number
    SELECT IFNULL(MAX(CAST(SUBSTRING(cluster_id, 5) AS UNSIGNED)), 0) INTO next_id
    FROM cluster
    WHERE cluster_id LIKE 'CID_%';
    
    SET next_id = next_id + 1;
    
    -- Set the new ID (e.g., CID_001, CID_002)
    SET NEW.cluster_id = CONCAT('CID_', LPAD(next_id, 3, '0'));
END
$$
DELIMITER ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cluster`
--
ALTER TABLE `cluster`
  ADD PRIMARY KEY (`cluster_id`),
  ADD UNIQUE KEY `cluster_name` (`cluster_name`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
