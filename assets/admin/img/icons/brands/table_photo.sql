-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 16, 2025 at 10:04 AM
-- Server version: 8.0.30
-- PHP Version: 8.3.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `source_nina`
--

-- --------------------------------------------------------

--
-- Table structure for table `table_photo`
--

CREATE TABLE `table_photo` (
  `id` int UNSIGNED NOT NULL,
  `photo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contenten` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `contentvi` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `descen` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `descvi` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `nameen` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `namevi` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `link` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `link_video` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `options` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `com` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numb` int DEFAULT '0',
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_created` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `date_updated` int DEFAULT '0',
  `link_redirect` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `redirect` int DEFAULT '301',
  `contentja` mediumtext COLLATE utf8mb4_unicode_ci,
  `descja` mediumtext COLLATE utf8mb4_unicode_ci,
  `nameja` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contentko` mediumtext COLLATE utf8mb4_unicode_ci,
  `descko` mediumtext COLLATE utf8mb4_unicode_ci,
  `nameko` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `table_photo`
--

INSERT INTO `table_photo` (`id`, `photo`, `contenten`, `contentvi`, `descen`, `descvi`, `nameen`, `namevi`, `link`, `link_video`, `options`, `type`, `com`, `numb`, `status`, `date_created`, `created_at`, `updated_at`, `date_updated`, `link_redirect`, `redirect`, `contentja`, `descja`, `nameja`, `contentko`, `descko`, `nameko`) VALUES
(177, 'icons8-phone-1730040045.webp', NULL, NULL, NULL, NULL, NULL, 'Phone', 'tel:09xxxxxxxx', NULL, NULL, 'social', 'photo-album', 1, 'hienthi', 0, NULL, NULL, 0, NULL, 301, NULL, NULL, NULL, NULL, NULL, NULL),
(178, 'logos-messenger-1730040045.webp', NULL, NULL, NULL, NULL, NULL, 'Messenger', '', NULL, NULL, 'social', 'photo-album', 2, 'hienthi,hienthi', 0, NULL, NULL, 0, NULL, 301, NULL, NULL, NULL, NULL, NULL, NULL),
(179, 'icon-zalo-1730040045.webp', NULL, NULL, NULL, NULL, NULL, 'Zalo', 'https://zalo.me/09xxxxxxxx', NULL, NULL, 'social', 'photo-album', 3, 'hienthi,hienthi,hienthi', 0, NULL, NULL, 0, NULL, 301, NULL, NULL, NULL, NULL, NULL, NULL),
(180, 'icons8-address-1730040045.webp', NULL, NULL, NULL, NULL, NULL, 'Maps', '', NULL, NULL, 'social', 'photo-album', 4, 'hienthi,hienthi,hienthi,hienthi', 0, NULL, NULL, 0, NULL, 301, NULL, NULL, NULL, NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `table_photo`
--
ALTER TABLE `table_photo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `type` (`type`);
ALTER TABLE `table_photo` ADD FULLTEXT KEY `status` (`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `table_photo`
--
ALTER TABLE `table_photo`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=181;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
