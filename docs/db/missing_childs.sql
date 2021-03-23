-- phpMyAdmin SQL Dump
-- version 5.1.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Mar 22, 2021 at 09:50 PM
-- Server version: 5.7.33-0ubuntu0.18.04.1
-- PHP Version: 7.4.15

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `missing_childs`
--

-- --------------------------------------------------------

--
-- Table structure for table `api_keys`
--

CREATE TABLE `api_keys` (
  `uuid` varchar(36) NOT NULL,
  `value` varchar(60) NOT NULL COMMENT 'hashed',
  `user_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `api_keys`
--

INSERT INTO `api_keys` (`uuid`, `value`, `user_id`, `created_at`) VALUES
('00000000000ffffffffffffff1', '0ZfTPJB3VeCAAkeB', 324, NULL),
('00000000000fffffffffffffff', '8ZfTPJB3VeCAAkea', 90, NULL),
('gtgg4g566556546', '0ZfTPJB3VeCAAkeC', 400, '2020-11-04 12:51:32');

-- --------------------------------------------------------

--
-- Table structure for table `files`
--

CREATE TABLE `files` (
  `uuid` varchar(36) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `file_ext` varchar(30) NOT NULL,
  `filename_as_stored` varchar(60) NOT NULL,
  `belongs_to` int(11) DEFAULT NULL,
  `guest_access` tinyint(4) DEFAULT NULL,
  `locked` tinyint(4) NOT NULL DEFAULT '0',
  `broken` tinyint(4) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `files`
--

INSERT INTO `files` (`uuid`, `filename`, `file_ext`, `filename_as_stored`, `belongs_to`, `guest_access`, `locked`, `broken`, `created_at`, `deleted_at`) VALUES
('01a37256-3f00-400b-ad0d-01997829326f', 'LyA logo cuadrado.png', 'png', '6-6059341440f554.06055368.png', NULL, 0, 0, NULL, '2021-03-22 21:19:32', NULL),
('d2aa0043-cb01-4806-b2ec-d212920920f1', 'TODO.jpg', 'jpg', '6-605938728ad933.37447374.jpg', NULL, 0, 0, NULL, '2021-03-22 21:38:10', NULL),
('eaa4f092-6693-4ac8-828f-c7ed659b666e', 'cronos.png', 'png', '6-605934144122e8.15380455.png', NULL, 0, 0, NULL, '2021-03-22 21:19:32', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `folder_permissions`
--

CREATE TABLE `folder_permissions` (
  `id` int(11) NOT NULL,
  `folder_id` int(11) NOT NULL,
  `belongs_to` int(11) NOT NULL,
  `access_to` int(11) NOT NULL,
  `r` tinyint(4) NOT NULL,
  `w` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`) VALUES
(500, 'admin'),
(10, 'dev'),
(-1, 'guest'),
(1, 'registered'),
(1000, 'superadmin'),
(100, 'supervisor');

-- --------------------------------------------------------

--
-- Table structure for table `sp_permissions`
--

CREATE TABLE `sp_permissions` (
  `id` int(11) NOT NULL,
  `name` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sp_permissions`
--

INSERT INTO `sp_permissions` (`id`, `name`) VALUES
(10, 'fill_all'),
(11, 'grant'),
(9, 'impersonate'),
(7, 'lock'),
(1, 'read_all'),
(3, 'read_all_folders'),
(5, 'read_all_trashcan'),
(8, 'transfer'),
(2, 'write_all'),
(12, 'write_all_collections'),
(4, 'write_all_folders'),
(6, 'write_all_trashcan');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(15) NOT NULL,
  `active` tinyint(4) DEFAULT NULL,
  `locked` tinyint(4) NOT NULL DEFAULT '0',
  `email` varchar(60) NOT NULL,
  `confirmed_email` tinyint(4) DEFAULT '0',
  `firstname` varchar(50) DEFAULT NULL,
  `lastname` varchar(80) DEFAULT NULL,
  `password` varchar(60) DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `belongs_to` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `active`, `locked`, `email`, `confirmed_email`, `firstname`, `lastname`, `password`, `deleted_at`, `belongs_to`) VALUES
(4, 'boctulus', 1, 0, 'boctulus@gmail.com', 0, NULL, NULL, NULL, NULL, 4),
(5, 'dios', 1, 0, 'boctulus+dios@gmail.com', 0, NULL, NULL, NULL, NULL, NULL),
(6, 'master', 1, 0, 'boctulus+master@gmail.com', 0, NULL, NULL, '$2y$10$KmpIm8o8H596oK2oUP8w7egweJ6YnwrbZ04lUwEOPek1kaXhGmkiC', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_roles`
--

CREATE TABLE `user_roles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `user_roles`
--

INSERT INTO `user_roles` (`id`, `user_id`, `role_id`, `created_at`, `updated_at`) VALUES
(1, 4, 1000, '2021-03-22 12:03:53', NULL),
(2, 5, 1000, '2021-03-22 20:18:07', NULL),
(3, 6, 1000, '2021-03-22 20:21:07', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_sp_permissions`
--

CREATE TABLE `user_sp_permissions` (
  `id` int(11) NOT NULL,
  `sp_permission_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `user_tb_permissions`
--

CREATE TABLE `user_tb_permissions` (
  `id` int(11) NOT NULL,
  `tb` varchar(80) COLLATE utf16_spanish_ci NOT NULL,
  `can_list_all` tinyint(4) DEFAULT NULL,
  `can_show_all` tinyint(4) DEFAULT NULL,
  `can_list` tinyint(4) DEFAULT NULL,
  `can_show` tinyint(4) DEFAULT NULL,
  `can_create` tinyint(4) DEFAULT NULL,
  `can_update` tinyint(4) DEFAULT NULL,
  `can_delete` tinyint(4) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_spanish_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `api_keys`
--
ALTER TABLE `api_keys`
  ADD PRIMARY KEY (`uuid`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `files`
--
ALTER TABLE `files`
  ADD PRIMARY KEY (`uuid`),
  ADD KEY `belongs_to` (`belongs_to`);

--
-- Indexes for table `folder_permissions`
--
ALTER TABLE `folder_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `folder_id` (`folder_id`,`access_to`),
  ADD KEY `member` (`access_to`),
  ADD KEY `belongs_to` (`belongs_to`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `sp_permissions`
--
ALTER TABLE `sp_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email_2` (`email`),
  ADD KEY `email` (`email`);

--
-- Indexes for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id_2` (`user_id`,`role_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `role_id` (`role_id`);

--
-- Indexes for table `user_sp_permissions`
--
ALTER TABLE `user_sp_permissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `permission` (`sp_permission_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `user_tb_permissions`
--
ALTER TABLE `user_tb_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_table` (`tb`,`user_id`) USING BTREE,
  ADD KEY `user_id` (`user_id`),
  ADD KEY `created_by` (`created_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `folder_permissions`
--
ALTER TABLE `folder_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1001;

--
-- AUTO_INCREMENT for table `sp_permissions`
--
ALTER TABLE `sp_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `user_roles`
--
ALTER TABLE `user_roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user_sp_permissions`
--
ALTER TABLE `user_sp_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_tb_permissions`
--
ALTER TABLE `user_tb_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
