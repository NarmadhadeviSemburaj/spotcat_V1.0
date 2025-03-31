-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 31, 2025 at 09:57 AM
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
(7, NULL, 'API_USER', 'GET /zone_api', 'Zones retrieved', '/spotcat/api/zone_api.php', 'GET', NULL, 0, '0', '::1', 'PostmanRuntime/7.43.2', '2025-03-29 06:56:36'),
(8, NULL, 'system', 'create', 'DCM created', '/spotcat/api/dcm_api.php', 'POST', '{\"dcm_name\":\"Updated DCM Chennai\",\"dcm_location\":\"Thiruchengode\",\"dcm_pincode\":\"600002\",\"zone_id\":\"ZID_00003\"}', 0, '0', '::1', 'PostmanRuntime/7.43.2', '2025-03-29 09:33:19'),
(9, NULL, 'system', 'read', 'DCMs fetched', '/spotcat/api/dcm_api.php', 'GET', NULL, 0, '0', '::1', 'PostmanRuntime/7.43.2', '2025-03-29 09:33:53'),
(10, NULL, 'system', 'validation_error', 'Missing DCM ID', '/spotcat/api/dcm_api.php', 'PUT', '{\"{_\\\"dcm_id\\\"_:_\\\"DCM_00005\\\",\\r\\n__\\\"dcm_name\\\":_\\\"Updated_DCM_Chennai\\\",\\r\\n__\\\"dcm_location\\\":_\\\"Sankari\\\"\\r\\n__\\r\\n}\":\"\"}', 0, '0', '::1', 'PostmanRuntime/7.43.2', '2025-03-29 09:35:06'),
(11, NULL, 'system', 'create', 'DCM created', '/spotcat/api/dcm_api.php', 'POST', '{\"dcm_name\":\"Updated DCM Chennai\",\"dcm_location\":\"Kerala\",\"dcm_pincode\":\"600002\",\"zone_id\":\"ZID_00003\"}', 0, '0', '::1', 'PostmanRuntime/7.43.2', '2025-03-29 09:39:35'),
(12, NULL, 'system', 'read', 'DCMs fetched', '/spotcat/api/dcm_api.php', 'GET', NULL, 0, '0', '::1', 'PostmanRuntime/7.43.2', '2025-03-29 09:40:03'),
(13, NULL, 'system', 'validation_error', 'Missing required fields', '/spotcat/api/dcm_api.php', 'POST', '{\"dcm_name\":\"Perundurai\",\"dcm_location\":\"Kerala\",\"dcm_pincode\":\"600002\",\"zone_id\":\"ZID_00003\"}', 0, '0', '::1', 'PostmanRuntime/7.43.2', '2025-03-29 09:44:45'),
(14, NULL, 'system', 'create', 'DCM created', '/spotcat/api/dcm_api.php', 'POST', '{\"dcm_name\":\"Test DCM\",\"zone_id\":\"ZID_00003\",\"dcm_location\":\"Test Location\",\"dcm_pincode\":\"123456\"}', 0, '0', '::1', 'PostmanRuntime/7.43.2', '2025-03-29 09:49:23'),
(15, NULL, 'system', 'validation_error', 'Missing DCM ID', '/spotcat/api/dcm_api.php', 'PUT', '{\"{\\r\\n____\\\"dcm_name\\\":_\\\"Testingg_DCM\\\",\\r\\n____\\\"dcm_id\\\":_\\\"DCM_00002\\\"\\r\\n___\\r\\n}\\r\\n__\\r\\n\":\"\"}', 0, '0', '::1', 'PostmanRuntime/7.43.2', '2025-03-29 09:53:33'),
(16, NULL, 'system', 'validation_error', 'Missing DCM ID', '/spotcat/api/dcm_api.php', 'PUT', '{\"{\\r\\n____\\\"dcm_id\\\":_\\\"DCM_00002\\\"\\r\\n____\\\"dcm_name\\\":_\\\"Testingg_DCM\\\"\\r\\n____\\r\\n___\\r\\n}\\r\\n__\\r\\n\":\"\"}', 0, '0', '::1', 'PostmanRuntime/7.43.2', '2025-03-29 09:55:04'),
(17, NULL, 'system', 'validation_error', 'Missing DCM ID', '/spotcat/api/dcm_api.php', 'PUT', '{\"{\\r\\n____\\\"dcm_id\\\":_\\\"DCM_00002\\\",\\r\\n____\\\"dcm_name\\\":_\\\"Testingg_DCM\\\"\\r\\n____\\r\\n___\\r\\n}\\r\\n__\\r\\n\":\"\"}', 0, '0', '::1', 'PostmanRuntime/7.43.2', '2025-03-29 09:55:47'),
(18, NULL, 'system', 'validation_error', 'Missing DCM ID', '/spotcat/api/dcm_api.php', 'DELETE', '{\"{\\r\\n____\\\"dcm_id\\\":_\\\"DCM_00002\\\",\\r\\n_____\\r\\n}\\r\\n__\\r\\n\":\"\"}', 0, '0', '::1', 'PostmanRuntime/7.43.2', '2025-03-29 09:56:22'),
(19, NULL, 'system', 'CREATE_DCM', 'DCM added successfully', '/spotcat/api/dcm_api.php', 'POST', '{\"dcm_name\":\"Test DCM\",\"zone_id\":\"ZID_00002\",\"dcm_location\":\"Test Location\",\"dcm_pincode\":\"123456\"}', 0, '0', '::1', 'PostmanRuntime/7.43.2', '2025-03-29 09:58:11'),
(20, NULL, 'system', 'UPDATE_DCM', 'DCM ID missing', '/spotcat/api/dcm_api.php', 'PUT', '{\"{\\r\\n__\\\"dcm_id\\\":_\\\"DCM_00002\\\",\\r\\n__\\\"dcm_name\\\":_\\\"Updated_DCM\\\",\\r\\n__\\\"dcm_location\\\":_\\\"Coimbatore\\\",\\r\\n__\\\"dcm_pincode\\\":_\\\"641001\\\",\\r\\n__\\\"zone_id\\\":_\\\"Z002\\\"\\r\\n}\\r\\n\":\"\"}', 0, '0', '::1', 'PostmanRuntime/7.43.2', '2025-03-29 10:00:09'),
(21, NULL, 'system', 'UPDATE_DCM', 'DCM ID missing', '/spotcat/api/dcm_api.php', 'PUT', '{\"{\\r\\n__\\\"dcm_id\\\":_\\\"DCM_00002\\\",\\r\\n__\\\"dcm_name\\\":_\\\"Updated_DCM\\\",\\r\\n__\\\"dcm_location\\\":_\\\"Coimbatore\\\",\\r\\n__\\\"dcm_pincode\\\":_\\\"641001\\\",\\r\\n__\\\"zone_id\\\":_\\\"ZID_00002\\\"\\r\\n}\\r\\n\":\"\"}', 0, '0', '::1', 'PostmanRuntime/7.43.2', '2025-03-29 10:01:01'),
(22, NULL, 'system', 'validation_error', 'Missing DCM ID', '/spotcat/api/dcm_api.php', 'PUT', '{\"{\\r\\n__\\\"dcm_id\\\":_\\\"DCM_00002\\\",\\r\\n__\\\"dcm_name\\\":_\\\"Updated_DCM\\\",\\r\\n__\\\"dcm_location\\\":_\\\"Coimbatore\\\",\\r\\n__\\\"dcm_pincode\\\":_\\\"641001\\\",\\r\\n__\\\"zone_id\\\":_\\\"ZID_00002\\\"\\r\\n}\\r\\n\":\"\"}', 0, '0', '::1', 'PostmanRuntime/7.43.2', '2025-03-29 10:01:50'),
(23, NULL, 'system', 'update', 'DCM updated', '/spotcat/api/dcm_api.php', 'PUT', '{\"dcm_id\":\"DCM_00002\",\"dcm_name\":\"Updated DCM\",\"dcm_location\":\"Coimbatore\",\"dcm_pincode\":\"641001\",\"zone_id\":\"ZID_00002\"}', 0, '0', '::1', 'PostmanRuntime/7.43.2', '2025-03-29 10:08:59'),
(24, NULL, 'system', 'delete', 'DCM deleted', '/spotcat/api/dcm_api.php', 'DELETE', '{\"dcm_id\":\"DCM_00002\"}', 0, '0', '::1', 'PostmanRuntime/7.43.2', '2025-03-29 10:09:40'),
(25, NULL, 'system', 'create', 'DCM created', '/spotcat/api/dcm_api.php', 'POST', '{\"dcm_name\":\"Updated DCM\",\"dcm_location\":\"Coimbatore\",\"dcm_pincode\":\"641001\",\"zone_id\":\"ZID_00003\"}', 0, '0', '::1', 'PostmanRuntime/7.43.2', '2025-03-29 10:10:03'),
(26, NULL, 'API_USER', 'DELETE /zone_api', 'Zone deleted successfully', '/spotcat/api/zone_api.php', 'DELETE', '{\"zone_id\":\"ZID_00003\"}', 0, '0', '::1', 'PostmanRuntime/7.43.2', '2025-03-29 10:28:02'),
(27, NULL, 'system', 'read', 'DCMs fetched', '/spotcat/api/dcm_api.php', 'GET', NULL, 0, '0', '::1', 'PostmanRuntime/7.43.2', '2025-03-29 10:28:15'),
(28, NULL, 'API_USER', 'GET /zone_api', 'Zones retrieved', '/spotcat/api/zone_api.php', 'GET', '{\"zone_id\":\"ZID_00003\",\"_name\":\"Narmadha DCM\"}', 0, '0', '::1', 'PostmanRuntime/7.43.2', '2025-03-29 10:33:17'),
(29, NULL, 'API_USER', 'PUT /zone_api', 'All fields (zone_id, zone_name, zone_location, zone_pincode) are required', '/spotcat/api/zone_api.php', 'PUT', '{\"zone_id\":\"ZID_00002\",\"zone_name\":\"Narmadha Zone\"}', 0, '0', '::1', 'PostmanRuntime/7.43.2', '2025-03-29 10:33:54'),
(30, NULL, 'API_USER', 'DELETE /zone_api', 'Zone deleted successfully', '/spotcat/api/zone_api.php', 'DELETE', '{\"zone_id\":\"ZID_00002\"}', 0, '0', '::1', 'PostmanRuntime/7.43.2', '2025-03-29 10:34:46'),
(31, NULL, 'API_USER', 'POST /zone_api', 'Zone created successfully', '/spotcat/api/zone_api.php', 'POST', '{\"zone_name\":\"BASKAR\",\"zone_location\":\"Eswaran kovil\",\"zone_pincode\":987654}', 0, '0', '::1', 'PostmanRuntime/7.43.2', '2025-03-29 10:36:48'),
(32, NULL, 'API_USER', 'PUT /zone_api', 'All fields (zone_id, zone_name, zone_location, zone_pincode) are required', '/spotcat/api/zone_api.php', 'PUT', NULL, 0, '0', '::1', 'PostmanRuntime/7.43.2', '2025-03-29 10:37:48'),
(33, NULL, 'API_USER', 'PUT /zone_api', 'Zone updated successfully', '/spotcat/api/zone_api.php', 'PUT', '{\"zone_id\":\"ZID_00001\",\"zone_name\":\"BASKAR\",\"zone_location\":\"Eswaran kovil\",\"zone_pincode\":637301}', 0, '0', '::1', 'PostmanRuntime/7.43.2', '2025-03-29 10:38:03'),
(34, NULL, 'API_USER', 'GET /zone_api', 'Zones retrieved', '/spotcat/api/zone_api.php', 'GET', NULL, 0, '0', '::1', 'PostmanRuntime/7.43.2', '2025-03-29 10:38:34'),
(35, NULL, 'API_USER', 'PUT /zone_api', 'All fields (zone_id, zone_name, zone_location, zone_pincode) are required', '/spotcat/api/zone_api.php', 'PUT', NULL, 0, '0', '::1', 'PostmanRuntime/7.43.2', '2025-03-29 10:38:40'),
(36, NULL, 'system', 'create', 'Cluster created', '/spotcat/api/cluster_api.php', 'POST', '{\"cluster_name\":\"West Region Cluster\"}', 0, '0', '::1', 'PostmanRuntime/7.43.2', '2025-03-29 10:40:00'),
(37, NULL, 'system', 'create', 'Cluster created', '/spotcat/api/cluster_api.php', 'POST', '{\"cluster_id\":\"CID_001\",\"cluster_name\":\"Electronics Purchase\",\"status\":\"active\",\"created_at\":\"2025-03-29 16:23:40\",\"updated_at\":\"2025-03-29 16:23:40\",\"type\":\"purchase\"}', 0, '0', '::1', 'PostmanRuntime/7.43.2', '2025-03-29 10:53:40'),
(38, NULL, 'system', 'read', 'Listed clusters', '/spotcat/api/cluster_api.php', 'GET', '{\"count\":1}', 0, '0', '::1', 'PostmanRuntime/7.43.2', '2025-03-29 10:53:56'),
(39, NULL, 'system', 'delete', 'Cluster deleted', '/spotcat/api/cluster_api.php', 'DELETE', '{\"cluster_id\":\"CID_001\"}', 0, '0', '::1', 'PostmanRuntime/7.43.2', '2025-03-29 10:56:57'),
(40, NULL, 'system', 'error', 'Update failed: Missing ID', '/spotcat/api/cluster_api.php', 'PUT', NULL, 0, NULL, '::1', 'PostmanRuntime/7.43.2', '2025-03-29 10:59:08'),
(41, NULL, 'system', 'update', 'Cluster updated', '/spotcat/api/cluster_api.php', 'PUT', '{\"cluster_id\":\"CID_001\",\"cluster_name\":\"Regional Delivery Hub - West\"}', 0, '0', '::1', 'PostmanRuntime/7.43.2', '2025-03-29 10:59:22'),
(42, NULL, 'system', 'create', 'Cluster created', '/spotcat/api/cluster_api.php', 'POST', '{\"cluster_id\":\"CID_001\",\"cluster_name\":\"Packing Zone 1\",\"status\":\"active\",\"created_at\":\"2025-03-31 08:53:28\",\"updated_at\":\"2025-03-31 08:53:28\",\"type\":\"packing\"}', 0, '0', '::1', 'PostmanRuntime/7.43.3', '2025-03-31 03:23:28'),
(43, NULL, 'system', 'create', 'Cluster created', '/spotcat/api/cluster_api.php', 'POST', '{\"cluster_id\":\"CID_002\",\"cluster_name\":\"Purchase\",\"status\":\"active\",\"created_at\":\"2025-03-31 08:58:27\",\"updated_at\":\"2025-03-31 08:58:27\"}', 0, '0', '::1', 'PostmanRuntime/7.43.3', '2025-03-31 03:28:27'),
(44, NULL, 'system', 'create', 'Cluster created', '/spotcat/api/cluster_api.php', 'POST', '{\"cluster_id\":\"CID_003\",\"cluster_name\":\"Packing\",\"status\":\"active\",\"created_at\":\"2025-03-31 09:06:42\",\"updated_at\":\"2025-03-31 09:06:42\"}', 0, '0', '::1', 'PostmanRuntime/7.43.3', '2025-03-31 03:36:42'),
(45, NULL, 'system', 'delete', 'Cluster deleted', '/spotcat/api/cluster_api.php', 'DELETE', '{\"cluster_id\":\"CID_003\"}', 0, '0', '::1', 'PostmanRuntime/7.43.3', '2025-03-31 03:37:30'),
(46, NULL, 'system', 'error', 'Create failed: Missing name', '/spotcat/api/cluster_api.php', 'POST', '{\"cluster_id\":\"Packing\"}', 0, '0', '::1', 'PostmanRuntime/7.43.3', '2025-03-31 03:37:48'),
(47, NULL, 'system', 'create', 'Cluster created', '/spotcat/api/cluster_api.php', 'POST', '{\"cluster_id\":\"CID_001\",\"cluster_name\":\"Packing\",\"status\":\"active\",\"created_at\":\"2025-03-31 09:07:59\",\"updated_at\":\"2025-03-31 09:07:59\"}', 0, '0', '::1', 'PostmanRuntime/7.43.3', '2025-03-31 03:37:59'),
(48, NULL, 'system', 'read', 'Listed clusters', '/spotcat/api/cluster_api.php', 'GET', '{\"count\":1}', 0, '0', '::1', 'PostmanRuntime/7.43.3', '2025-03-31 03:38:08'),
(49, NULL, 'system', 'update', 'Cluster updated', '/spotcat/api/cluster_api.php', 'PUT', '{\"cluster_id\":\"CID_001\",\"cluster_name\":\"Packingggggg\"}', 0, '0', '::1', 'PostmanRuntime/7.43.3', '2025-03-31 03:39:00'),
(50, NULL, 'system', 'read', 'Listed clusters', '/spotcat/api/cluster_api.php', 'GET', '{\"count\":1}', 0, '0', '::1', 'PostmanRuntime/7.43.3', '2025-03-31 03:39:12'),
(51, NULL, 'system', 'read', 'Listed lines', '/spotcat/api/line_api.php', 'GET', '{\"count\":0}', 0, '0', '::1', 'PostmanRuntime/7.43.3', '2025-03-31 03:48:09'),
(52, NULL, 'system', 'error', 'Create failed: Missing name', '/spotcat/api/cluster_api.php', 'POST', '{\"line_name\":\"Packing Line A\",\"line_location\":\"Warehouse 1\",\"line_pincode\":\"638001\",\"cluster_id\":\"CLUST_12345\"}', 0, '0', '::1', 'PostmanRuntime/7.43.3', '2025-03-31 03:49:16'),
(53, NULL, 'system', 'read', 'Listed clusters', '/spotcat/api/cluster_api.php', 'GET', '{\"count\":1}', 0, '0', '::1', 'PostmanRuntime/7.43.3', '2025-03-31 03:49:35'),
(54, NULL, 'system', 'create', 'Line created', '/spotcat/api/line_api.php', 'POST', '{\"line_id\":\"LID_443817\"}', 0, '0', '::1', 'PostmanRuntime/7.43.3', '2025-03-31 03:50:00'),
(55, NULL, 'system', 'read', 'Listed lines', '/spotcat/api/line_api.php', 'GET', '{\"count\":1}', 0, '0', '::1', 'PostmanRuntime/7.43.3', '2025-03-31 03:54:56'),
(56, NULL, 'system', 'create', 'Line created', '/spotcat/api/line_api.php', 'POST', '{\"line_id\":\"LID_478542\"}', 0, '0', '::1', 'PostmanRuntime/7.43.3', '2025-03-31 03:56:03'),
(57, NULL, 'system', 'create', 'Cluster created', '/spotcat/api/cluster_api.php', 'POST', '{\"cluster_id\":\"CID_001\",\"cluster_name\":\"Packing\",\"status\":\"active\",\"created_at\":\"2025-03-31 09:32:00\",\"updated_at\":\"2025-03-31 09:32:00\"}', 0, '0', '::1', 'PostmanRuntime/7.43.3', '2025-03-31 04:02:00'),
(58, NULL, 'system', 'error', 'Create failed: Missing fields', '/spotcat/api/line_api.php', 'POST', NULL, 0, NULL, '::1', 'PostmanRuntime/7.43.3', '2025-03-31 04:03:04'),
(59, NULL, 'system', 'error', 'Create failed: Missing fields', '/spotcat/api/line_api.php', 'POST', NULL, 0, NULL, '::1', 'PostmanRuntime/7.43.3', '2025-03-31 04:03:17'),
(60, NULL, 'system', 'create', 'Line created', '/spotcat/api/line_api.php', 'POST', '{\"line_id\":\"LID_558616\"}', 0, '0', '::1', 'PostmanRuntime/7.43.3', '2025-03-31 04:03:49'),
(61, NULL, 'system', 'create', 'Line created', '/spotcat/api/line_api.php', 'POST', '{\"line_id\":\"PLID_0001\"}', 0, '0', '::1', 'PostmanRuntime/7.43.3', '2025-03-31 04:10:18'),
(62, NULL, 'system', 'read', 'Listed lines', '/spotcat/api/line_api.php', 'GET', '{\"count\":2}', 0, '0', '::1', 'PostmanRuntime/7.43.3', '2025-03-31 04:11:27'),
(63, NULL, 'system', 'update', 'Line updated', '/spotcat/api/line_api.php', 'PUT', '{\"line_id\":\"PLID_0001\",\"line_name\":\"Packing Line A\",\"line_location\":\"Warehouse 2\"}', 0, '0', '::1', 'PostmanRuntime/7.43.3', '2025-03-31 04:12:28'),
(64, NULL, 'system', 'read', 'Listed lines', '/spotcat/api/line_api.php', 'GET', '{\"count\":2}', 0, '0', '::1', 'PostmanRuntime/7.43.3', '2025-03-31 04:12:36'),
(65, NULL, 'system', 'read', 'Listed lines', '/spotcat/api/line_api.php', 'GET', '{\"count\":2}', 0, '0', '::1', 'PostmanRuntime/7.43.3', '2025-03-31 04:12:47'),
(66, NULL, 'system', 'update', 'Line updated', '/spotcat/api/line_api.php', 'PUT', '{\"line_id\":\"PLID_0001\",\"line_name\":\"Packing Line B\"}', 0, '0', '::1', 'PostmanRuntime/7.43.3', '2025-03-31 04:13:05'),
(67, NULL, 'system', 'delete', 'Line deleted', '/spotcat/api/line_api.php', 'DELETE', '{\"line_id\":\"PLID_0001\"}', 0, '0', '::1', 'PostmanRuntime/7.43.3', '2025-03-31 04:13:21'),
(68, NULL, 'system', 'read', 'No DCMs found', '/spotcat/api/dcm_api.php', 'GET', NULL, 0, '0', '::1', 'PostmanRuntime/7.43.3', '2025-03-31 04:55:23'),
(69, NULL, 'system', 'create', 'DCM created', '/spotcat/api/dcm_api.php', 'POST', '{\"dcm_name\":\"Main Distribution Center\",\"zone_id\":\"ZID_00002\",\"dcm_location\":\"Industrial Area\",\"dcm_pincode\":\"110045\"}', 0, '0', '::1', 'PostmanRuntime/7.43.3', '2025-03-31 04:57:23'),
(70, NULL, 'system', 'read', 'Listed clusters', '/spotcat/api/cluster_api.php', 'GET', '{\"count\":1}', 0, '0', '::1', 'PostmanRuntime/7.43.3', '2025-03-31 05:02:37'),
(71, NULL, 'system', 'read', 'Listed clusters', '/spotcat/api/cluster_api.php', 'GET', '{\"count\":1}', 0, '0', '::1', 'PostmanRuntime/7.43.3', '2025-03-31 05:02:58'),
(72, NULL, 'system', 'create', 'Cluster created', '/spotcat/api/cluster_api.php', 'POST', '{\"cluster_id\":\"CID_002\",\"cluster_name\":\"Incharge\",\"status\":\"active\",\"created_at\":\"2025-03-31 10:33:03\",\"updated_at\":\"2025-03-31 10:33:03\"}', 0, '0', '::1', 'PostmanRuntime/7.43.3', '2025-03-31 05:03:03'),
(73, NULL, 'system', 'update', 'Cluster updated', '/spotcat/api/cluster_api.php', 'PUT', '{\"cluster_id\":\"CID_002\",\"cluster_name\":\"Incharges\"}', 0, '0', '::1', 'PostmanRuntime/7.43.3', '2025-03-31 05:03:29'),
(74, NULL, 'system', 'read', 'Listed clusters', '/spotcat/api/cluster_api.php', 'GET', '{\"count\":2}', 0, '0', '::1', 'PostmanRuntime/7.43.3', '2025-03-31 05:03:37'),
(75, NULL, 'system', 'read', 'Listed clusters', '/spotcat/api/cluster_api.php', 'GET', '{\"count\":2}', 0, '0', '::1', 'PostmanRuntime/7.43.3', '2025-03-31 06:49:23'),
(76, NULL, 'API_USER', 'GET /zone_api', 'Zones retrieved', '/spotcat/api/zone_api.php', 'GET', '{\"clusters_id\":\"CLST_00001\"}', 0, '0', '::1', 'PostmanRuntime/7.43.3', '2025-03-31 06:54:28'),
(77, NULL, 'API_USER', 'DELETE /zone_api', 'Zone deleted successfully', '/spotcat/api/zone_api.php', 'DELETE', '{\"zone_id\":\"ZID_00003\"}', 0, '0', '::1', 'PostmanRuntime/7.43.3', '2025-03-31 06:55:00'),
(78, NULL, 'API_USER', 'ZONE_API_DELETE', 'Zone not found', '/spotcat/api/zone_api.php', 'DELETE', '{\"zone_id\":\"ZID_00003\"}', 0, '0', '::1', 'PostmanRuntime/7.43.3', '2025-03-31 06:59:40'),
(79, NULL, 'API_USER', 'DCM_API_GET', 'DCMs fetched successfully', '/spotcat/api/dcm_api.php', 'GET', '{\"zone_id\":\"ZID_00003\"}', 0, '0', '::1', 'PostmanRuntime/7.43.3', '2025-03-31 07:06:08'),
(80, NULL, 'API_USER', 'LINE_API_GET', 'Lines retrieved', '/spotcat/api/line_api.php', 'GET', '{\"zone_id\":\"ZID_00003\"}', 0, '0', '::1', 'PostmanRuntime/7.43.3', '2025-03-31 07:45:01'),
(81, NULL, 'system', 'read', 'Listed clusters', '/spotcat/api/cluster_api.php', 'GET', '{\"count\":2}', 0, '0', '::1', 'PostmanRuntime/7.43.3', '2025-03-31 07:50:44'),
(82, NULL, 'API_USER', 'ZONE_API_GET', 'Zones retrieved', '/spotcat/api/zone_api.php', 'GET', '{\"zone_id\":\"ZID_00003\"}', 0, '0', '::1', 'PostmanRuntime/7.43.3', '2025-03-31 07:51:22'),
(83, NULL, 'API_USER', 'DCM_API_GET', 'DCMs fetched successfully', '/spotcat/api/dcm_api.php', 'GET', '{\"zone_id\":\"ZID_00003\"}', 0, '0', '::1', 'PostmanRuntime/7.43.3', '2025-03-31 07:51:45'),
(84, NULL, 'system', 'read', 'Listed clusters', '/spotcat/api/cluster_api.php', 'GET', '{\"count\":2}', 0, '0', '::1', 'PostmanRuntime/7.43.3', '2025-03-31 07:52:09'),
(85, NULL, 'API_USER', 'LINE_API_GET', 'Lines retrieved', '/spotcat/api/line_api.php', 'GET', '{\"zone_id\":\"ZID_00003\"}', 0, '0', '::1', 'PostmanRuntime/7.43.3', '2025-03-31 07:52:28'),
(86, NULL, 'System', 'List Clusters', 'Retrieved clusters list', '/spotcat/api/clusters_api.php', 'GET', NULL, 0, '0', '::1', 'PostmanRuntime/7.43.3', '2025-03-31 07:55:22');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=87;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
