-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 13, 2026 at 12:37 PM
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
-- Database: `ceiling_decor_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `design_id` int(11) DEFAULT NULL,
  `dimensions_sqft` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `scheduled_datetime` datetime NOT NULL,
  `status` enum('pending','approved','dispatched','completed','cancelled') DEFAULT 'pending',
  `payment_status` enum('pending','completed') DEFAULT 'pending',
  `payment_method` enum('online','cod') DEFAULT 'cod',
  `color_preference` varchar(50) DEFAULT 'Default',
  `material_finish` varchar(50) DEFAULT 'Default',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `design_id`, `dimensions_sqft`, `total_price`, `scheduled_datetime`, `status`, `payment_status`, `payment_method`, `color_preference`, `material_finish`, `created_at`) VALUES
(3, 5, 12, 150.00, 23250.00, '2026-04-16 15:22:00', 'completed', 'completed', 'online', 'Default', 'Default', '2026-04-13 10:23:04'),
(4, 5, 10, 115.00, 23000.00, '2026-05-06 15:23:00', 'pending', 'pending', 'cod', 'Default', 'Matte', '2026-04-13 10:23:56'),
(5, 5, 7, 300.00, 45000.00, '2026-05-14 15:24:00', 'approved', 'completed', 'online', 'Pure White', 'Default', '2026-04-13 10:24:39');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
(1, 'False Ceiling'),
(2, 'Wallpaper'),
(3, 'PVC Panels');

-- --------------------------------------------------------

--
-- Table structure for table `designs`
--

CREATE TABLE `designs` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price_per_sqft` decimal(10,2) NOT NULL,
  `theme` varchar(50) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `designs`
--

INSERT INTO `designs` (`id`, `category_id`, `title`, `description`, `price_per_sqft`, `theme`, `image_url`, `created_at`) VALUES
(3, 2, 'Soft Botanical Oasis', '', 145.00, 'Neutral Earthy Boho', 'assets/uploads/designs/1776076568_download.jpg', '2026-04-11 11:05:47'),
(5, 2, 'Modern Wall Texture', '', 90.00, 'Green', 'assets/uploads/designs/1775907162_modern green wallpaper.jpg', '2026-04-11 11:32:42'),
(6, 1, 'Modern Canopy Bedroom', '', 190.00, 'Contemporary Warm Minimalism', 'assets/uploads/designs/1776074097_download (1).jpg', '2026-04-11 11:55:15'),
(7, 1, 'Floating Geometric Ceiling', '', 150.00, 'Modern Abstract Design', 'assets/uploads/designs/1776076484_download (2).jpg', '2026-04-11 12:02:20'),
(8, 3, 'Illuminated Mountain Range Lounge', '', 130.00, 'Nature-Inspired Modernism', 'assets/uploads/designs/1775909137_download.jpg', '2026-04-11 12:05:37'),
(9, 1, 'Geometric Wood Canopy Lounge', '', 150.00, 'Contemporary Linear Elegance', 'assets/uploads/designs/1775909238_download (1).jpg', '2026-04-11 12:07:18'),
(10, 3, 'Textured Arches Living Room', '', 200.00, 'Organic Modern Boho', 'assets/uploads/designs/1775909486_pvc.jpg', '2026-04-11 12:11:26'),
(11, 2, 'Abstract Japandi Bedroom', NULL, 105.00, 'Earthy Minimalist Serenity', 'assets/uploads/designs/1776074234_Авторские обои Creativille 259129 _ Темные обои в стиле джапанди с абстрактными горами.jpg', '2026-04-13 09:57:14'),
(12, 3, 'Slatted Feature Wall', NULL, 155.00, 'Contemporary Linear Modernism', 'assets/uploads/designs/1776074316_RIPADOS_PT - Comercialização de ripados pvc.jpg', '2026-04-13 09:58:36');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `is_read`, `created_at`) VALUES
(6, 5, 'Order Booked Successfully', 'You have booked an order for Slatted Feature Wall. Dimensions: 150 sq.ft, Total: Rs 23,250.00. Scheduled on: April 16, 2026, 3:22 pm.', 1, '2026-04-13 10:23:04'),
(7, 5, 'Order Booked Successfully', 'You have booked an order for Textured Arches Living Room. Dimensions: 115 sq.ft, Total: Rs 23,000.00. Scheduled on: May 6, 2026, 3:23 pm.', 1, '2026-04-13 10:23:56'),
(8, 5, 'Order Booked Successfully', 'You have booked an order for Tray Ceiling Bedroom. Dimensions: 300 sq.ft, Total: Rs 45,000.00. Scheduled on: May 14, 2026, 3:24 pm.', 1, '2026-04-13 10:24:39'),
(9, 5, 'Order Updated', 'Your booking for Tray Ceiling Bedroom has been updated to Approved.', 0, '2026-04-13 10:25:36'),
(10, 5, 'Order Updated', 'Your booking for Slatted Feature Wall has been updated to Completed.', 0, '2026-04-13 10:25:46');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `reset_code` varchar(10) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('customer','admin') DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `address`, `password_hash`, `role`, `created_at`) VALUES
(2, 'Admin', 'admin@gmail.com', '03000000000', 'Lahore', '$2y$10$bBi0UnyzxXqhx03vTfNi/uUzAWQxPmSyqgNNg36kELU1o24tsTxFC', 'admin', '2026-04-11 11:09:05'),
(5, 'Hamza', 'mrhamza261@gmail.com', '03247947555', 'Lahore', '$2y$10$jIV724GFjIlsKfp7cYU1lOpsazntOfe9tqJd3OL7mkpqd1aL4gvKK', 'customer', '2026-04-13 08:22:29');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `design_id` (`design_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `designs`
--
ALTER TABLE `designs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `designs`
--
ALTER TABLE `designs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`design_id`) REFERENCES `designs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `designs`
--
ALTER TABLE `designs`
  ADD CONSTRAINT `designs_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
