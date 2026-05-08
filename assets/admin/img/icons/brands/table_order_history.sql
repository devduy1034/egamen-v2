-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 10, 2024 at 02:05 PM
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
-- Table structure for table `table_order_history`
--

CREATE TABLE `table_order_history` (
  `id` int(11) NOT NULL,
  `id_order` int(11) DEFAULT 0,
  `notes` text DEFAULT NULL,
  `order_status` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `table_order_history`
--

INSERT INTO `table_order_history` (`id`, `id_order`, `notes`, `order_status`, `created_at`, `updated_at`) VALUES
(1, 4, NULL, 3, '2024-05-10 06:06:26', '2024-05-10 06:06:26'),
(2, 4, 'Ghi chú 2', 3, '2024-05-10 06:25:40', '2024-05-10 06:25:40'),
(3, 4, 'Ghi chú 3', 3, '2024-05-10 06:36:57', '2024-05-10 06:36:57'),
(4, 4, 'Ghi chú 4', 2, '2024-05-10 06:43:10', '2024-05-10 06:43:10');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `table_order_history`
--
ALTER TABLE `table_order_history`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `table_order_history`
--
ALTER TABLE `table_order_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
