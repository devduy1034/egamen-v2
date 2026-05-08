-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 10, 2024 at 11:38 AM
-- Server version: 10.5.23-MariaDB
-- PHP Version: 8.2.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `demonina_ver1`
--

-- --------------------------------------------------------

--
-- Table structure for table `table_extensions`
--

CREATE TABLE `table_extensions` (
  `id` int(10) UNSIGNED NOT NULL,
  `contentvi` text DEFAULT NULL,
  `contenten` text DEFAULT NULL,
  `namevi` varchar(255) DEFAULT NULL,
  `nameen` varchar(255) DEFAULT NULL,
  `hotlinevi` varchar(255) DEFAULT NULL,
  `hotlineen` varchar(255) DEFAULT NULL,
  `linkvi` varchar(255) DEFAULT NULL,
  `linken` varchar(255) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `options` mediumtext DEFAULT NULL,
  `type` varchar(30) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `table_extensions`
--

INSERT INTO `table_extensions` (`id`, `contentvi`, `contenten`, `namevi`, `nameen`, `hotlinevi`, `hotlineen`, `linkvi`, `linken`, `photo`, `options`, `type`, `status`, `updated_at`, `created_at`) VALUES
(1, 'Nội dung khuyến mãi tháng 5', 'Khuyến mãi tháng 5', 'Khuyến mãi tháng 5', 'Khuyến mãi tháng 5', NULL, NULL, 'abc', '', '171507064644-2266.jpg', '{\"type\":\"2\",\"popup\":\"popup1\",\"email\":\"email\",\"trang-chu\":\"trang-chu\",\"san-pham\":\"san-pham\",\"thuc-don\":\"thuc-don\",\"background\":\"9ACD32\",\"color-title\":\"FFFFFF\",\"color-send\":\"FFFFFF\",\"background-send\":\"FF0000\"}', 'popup', 'repeat', '2024-05-07 08:32:39', NULL),
(2, NULL, NULL, 'Hotline', 'Hotline', '0399413032 - 0399413031', '0399413032', NULL, NULL, '1714707163hl.png', '{\"hotline\":\"hotline3\",\"background\":\"FF0000\",\"background-text\":\"FF0000\",\"color\":\"FFFFFF\",\"destop\":{\"device\":\"on\",\"left\":\"20\",\"right\":\"0\",\"bottom\":\"100\"},\"mobile\":{\"device\":\"on\",\"left\":\"20\",\"right\":\"0\",\"bottom\":\"100\"}}', 'hotline', 'hienthi', '2024-05-06 08:57:19', '2024-05-03 01:50:01'),
(4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{\"social1\":{\"status\":\"on\",\"background\":\"FFDD26\",\"color\":\"FF4480\",\"destop\":{\"device\":\"on\",\"left\":\"0\",\"right\":\"20\",\"bottom\":\"100\"},\"mobile\":{\"left\":\"0\",\"right\":\"20\",\"bottom\":\"100\"}},\"social2\":{\"status\":\"on\",\"background\":\"3DFF99\",\"color\":\"FFE3F2\",\"destop\":{\"left\":\"0\",\"right\":\"0\",\"bottom\":\"10\"},\"mobile\":{\"device\":\"on\",\"left\":\"0\",\"right\":\"0\",\"bottom\":\"10\"}}}', 'social', 'hienthi', '2024-05-07 08:45:15', '2024-05-06 08:25:52');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `table_extensions`
--
ALTER TABLE `table_extensions`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `table_extensions`
--
ALTER TABLE `table_extensions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
