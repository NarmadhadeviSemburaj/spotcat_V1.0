-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 29, 2025 at 08:01 AM
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
-- Table structure for table `log`
--

CREATE TABLE `log` (
  `id` int(11) NOT NULL,
  `user_id` varchar(10) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `action_type` varchar(100) NOT NULL,
  `action_description` text DEFAULT NULL,
  `endpoint` varchar(255) DEFAULT NULL,
  `http_method` varchar(10) DEFAULT NULL,
  `request_payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`request_payload`)),
  `response_status` int(11) DEFAULT NULL,
  `response_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`response_data`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `log`
--

INSERT INTO `log` (`id`, `user_id`, `username`, `action_type`, `action_description`, `endpoint`, `http_method`, `request_payload`, `response_status`, `response_data`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, NULL, 'API_USER', 'PUT /zone_api', 'All fields (zone_id, zone_name, zone_location, zone_pincode) are required', '/spotcat/api/zone_api.php', 'PUT', '{\"zone_name\":\"Salem\",\"zone_location\":\"Sankari\",\"zone_pincode\":\"637301\"}', 0, '0', '::1', 'PostmanRuntime/7.43.2', '2025-03-29 06:43:00'),
(2, NULL, 'API_USER', 'PUT /zone_api', 'All fields (zone_id, zone_name, zone_location, zone_pincode) are required', '/spotcat/api/zone_api.php', 'PUT', '{\"zone_name\":\"Salem\",\"zone_location\":\"Sankari\",\"zone_pincode\":\"637301\"}', 0, '0', '::1', 'PostmanRuntime/7.43.2', '2025-03-29 06:45:57'),
(3, NULL, 'API_USER', 'POST /zone_api', 'Zone created successfully', '/spotcat/api/zone_api.php', 'POST', '{\"zone_name\":\"Salem\",\"zone_location\":\"Sankari\",\"zone_pincode\":\"637301\"}', 0, '0', '::1', 'PostmanRuntime/7.43.2', '2025-03-29 06:46:13'),
(4, NULL, 'API_USER', 'GET /zone_api', 'Zones retrieved', '/spotcat/api/zone_api.php', 'GET', NULL, 0, '0', '::1', 'PostmanRuntime/7.43.2', '2025-03-29 06:54:38'),
(6, NULL, 'API_USER', 'GET /zone_api', 'Zones retrieved', '/spotcat/api/zone_api.php', 'GET', NULL, 0, '0', '::1', 'PostmanRuntime/7.43.2', '2025-03-29 06:56:34'),
(7, NULL, 'API_USER', 'GET /zone_api', 'Zones retrieved', '/spotcat/api/zone_api.php', 'GET', NULL, 0, '0', '::1', 'PostmanRuntime/7.43.2', '2025-03-29 06:56:36');

--
-- Triggers `log`
--
DELIMITER $$
CREATE TRIGGER `before_insert_log` BEFORE INSERT ON `log` FOR EACH ROW BEGIN
    DECLARE new_id VARCHAR(15);

    -- Generate the next ID in LG_000000001 format
    SELECT CONCAT('LG_', LPAD(COALESCE(SUBSTRING(MAX(id), 4) + 1, 1), 9, '0'))
    INTO new_id
    FROM log;

    -- Assign the new ID
    SET NEW.id = new_id;
END
$$
DELIMITER ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `log`
--
ALTER TABLE `log`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `log`
--
ALTER TABLE `log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
