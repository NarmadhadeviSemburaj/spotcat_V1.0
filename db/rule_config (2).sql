-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 02, 2025 at 07:10 AM
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
-- Table structure for table `rule_config`
--

CREATE TABLE `rule_config` (
  `rule_id` varchar(10) NOT NULL,
  `rule_name` varchar(255) NOT NULL,
  `rule_value` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rule_config`
--

INSERT INTO `rule_config` (`rule_id`, `rule_name`, `rule_value`, `description`, `last_updated`, `created_at`) VALUES
('RID_000001', 'Minimum Leave ', '1', 'Employees can take leave for minimum 1 day', '2025-04-02 04:56:01', '2025-04-02 05:03:36'),
('RID_000002', 'advccv ', '8', 'Employees can take leave for minimum 1 day', '2025-04-02 05:01:48', '2025-04-02 05:03:36'),
('RID_000003', 'narmadhadcv ', '8', 'Employees can take leave ndkjkncxv1 day', '2025-04-02 05:08:39', '2025-04-02 05:07:59');

--
-- Triggers `rule_config`
--
DELIMITER $$
CREATE TRIGGER `before_rule_config_insert` BEFORE INSERT ON `rule_config` FOR EACH ROW BEGIN
    DECLARE next_id INT;
    
    -- Get the maximum numeric part of existing rule_ids
    SELECT IFNULL(MAX(CAST(SUBSTRING(rule_id, 5) AS UNSIGNED)), 0) INTO next_id 
    FROM rule_config;
    
    -- Increment and format as RID_000001
    SET NEW.rule_id = CONCAT('RID_', LPAD(next_id + 1, 6, '0'));
END
$$
DELIMITER ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `rule_config`
--
ALTER TABLE `rule_config`
  ADD PRIMARY KEY (`rule_id`),
  ADD UNIQUE KEY `rule_name` (`rule_name`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
