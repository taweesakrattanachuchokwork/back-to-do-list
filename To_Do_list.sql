-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Feb 26, 2025 at 06:43 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.0.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `To_Do_list`
--

-- --------------------------------------------------------

--
-- Table structure for table `Comments`
--

CREATE TABLE `Comments` (
  `comment_id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Comments`
--

INSERT INTO `Comments` (`comment_id`, `task_id`, `user_id`, `comment`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'รบกวนเพิ่มข้อมูลหน่วยงาน \"ไอที\" เข้าไปในเอกสารด้วยครับ', '2025-02-25 12:55:08', '2025-02-25 12:55:08'),
(2, 2, 2, 'ขอให้ตรวจสอบโค้ดอีกครั้งก่อน deploy', '2025-02-25 12:55:08', '2025-02-25 12:55:08'),
(3, 3, 3, 'ช่วยตรวจสอบการอัปเดตข้อมูลด้วย', '2025-02-25 12:55:08', '2025-02-25 12:55:08'),
(4, 1, 1, 'เอกสารดีมากครับ แต่ขอให้เพิ่มรายละเอียดเพิ่มเติม', '2025-02-25 12:55:08', '2025-02-26 08:04:46'),
(5, 4, 1, 'งานเสร็จเรียบร้อยแล้วครับ', '2025-02-25 12:55:08', '2025-02-25 12:55:08'),
(6, 1, 1, 'เพิ่มเติมแล้วครับ', '2025-02-26 09:05:20', '2025-02-26 09:05:20'),
(7, 1, 1, 'จัดทำเอกสารส่งมอบงาน 1', '2025-02-26 09:05:43', '2025-02-26 09:30:35'),
(9, 1, 1, 'เพิ่มเติมแล้วครับ 444', '2025-02-26 10:56:21', '2025-02-26 10:56:21'),
(11, 1, 1, 'จัดทำเอกสารส่งมอบงาน 1', '2025-02-26 16:42:15', '2025-02-26 16:42:43');

-- --------------------------------------------------------

--
-- Table structure for table `Tasks`
--

CREATE TABLE `Tasks` (
  `task_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `status` enum('pending','in_progress','completed') NOT NULL DEFAULT 'pending',
  `priority` enum('low','medium','high') NOT NULL DEFAULT 'medium',
  `due_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Tasks`
--

INSERT INTO `Tasks` (`task_id`, `user_id`, `title`, `description`, `status`, `priority`, `due_date`, `created_at`, `updated_at`) VALUES
(1, 1, 'จัดทำเอกสารส่งมอบงาน 1', 'สร้างไฟล์เอกสารและบันทึกข้อมูล', 'pending', 'high', '2025-02-26', '2025-02-25 12:49:30', '2025-02-26 05:55:43'),
(2, 2, 'พัฒนาเว็บไซต์', 'เขียนโค้ดและทดสอบระบบ', 'in_progress', 'medium', '2024-02-15', '2025-02-25 12:49:30', '2025-02-25 12:49:30'),
(3, 3, 'อัปเดตฐานข้อมูล', 'เพิ่มข้อมูลใหม่และปรับโครงสร้าง', 'pending', 'low', '2024-03-01', '2025-02-25 12:49:30', '2025-02-25 12:49:30'),
(4, 1, 'วางแผนโปรเจค', 'กำหนดเป้าหมายและงานย่อย', 'completed', 'high', '2024-01-20', '2025-02-25 12:49:30', '2025-02-25 12:49:30'),
(5, 2, 'ประชุมทีม', 'วางแผนงานสัปดาห์หน้า', 'pending', 'medium', '2024-02-10', '2025-02-25 12:49:30', '2025-02-25 12:49:30'),
(6, 4, 'จัดทำเอกสารส่งมอบงาน', 'สร้างไฟล์เอกสารและบันทึกข้อมูล', 'pending', 'medium', '2025-02-26', '2025-02-26 05:40:20', '2025-02-26 05:40:20'),
(7, 4, 'จัดทำเอกสารส่งมอบงาน 7', '', 'in_progress', 'high', '2024-01-05', '2025-02-26 05:45:34', '2025-02-26 05:52:21');

-- --------------------------------------------------------

--
-- Table structure for table `User`
--

CREATE TABLE `User` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `User`
--

INSERT INTO `User` (`user_id`, `username`, `email`, `password`, `created_at`, `updated_at`) VALUES
(1, 'testuser1', 'testuser@example.com', '$2y$10$fCTW9dWSgPy2NJ06rlilWO4p.Cl1t5.8udscnOZDDvzfZbcDfdZJG', '2025-02-25 12:47:51', '2025-02-25 18:56:04'),
(2, 'test2', '2222@example.com', '$2y$10$RPiIv/93d72URtvZmVMGk.TIammDhM6xcaOpulM3adUkgE835off2', '2025-02-25 12:47:51', '2025-02-25 18:57:30'),
(3, 'example3', 'example3@gmail.co.th', 'c5678901234C+', '2025-02-25 12:47:51', '2025-02-25 12:47:51'),
(4, 'phet', '2222@example.com', '$2y$10$mKf/SbJY4iEw44GV09cvDeJpIKsDUBHLV3.BDAVoucLn3Yibp0c8.', '2025-02-25 18:31:17', '2025-02-26 16:22:14'),
(7, 'phet5', 'taweesak.555.work@gmail.com', '$2y$10$NGycLbniL6avShsD06AOouFZliXj4EWjIy5tDJDLw3nAPdGENmyRK', '2025-02-26 11:39:31', '2025-02-26 11:39:31'),
(8, 'phet888', 'taweesak.555.work@gmail.com', '$2y$10$snhklEy5/iHVAoyZ4E9zgu8MXkiIj3GsnjKBjZuOnZw3aqWDLXLZC', '2025-02-26 11:43:55', '2025-02-26 11:43:55'),
(9, 'phet123', 'taweesak.555.work@gmail.com', '$2y$10$wRyRfjglxkXSjesMHxAnUOG0jPviF5uYDS3NfN8jRmGN7hW.qnRQy', '2025-02-26 14:03:11', '2025-02-26 14:03:11');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `Comments`
--
ALTER TABLE `Comments`
  ADD PRIMARY KEY (`comment_id`),
  ADD KEY `task_id` (`task_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `Tasks`
--
ALTER TABLE `Tasks`
  ADD PRIMARY KEY (`task_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `User`
--
ALTER TABLE `User`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `Comments`
--
ALTER TABLE `Comments`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `Tasks`
--
ALTER TABLE `Tasks`
  MODIFY `task_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `User`
--
ALTER TABLE `User`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `Comments`
--
ALTER TABLE `Comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `Tasks` (`task_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `User` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `Tasks`
--
ALTER TABLE `Tasks`
  ADD CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `User` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
