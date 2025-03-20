-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 20, 2025 at 03:08 AM
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
-- Database: `sitin_application`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `announce_id` int(11) NOT NULL,
  `admin_name` varchar(50) NOT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp(),
  `message` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`announce_id`, `admin_name`, `date`, `message`) VALUES
(1, 'asdasdad', '2025-03-06 14:58:37', 'asdasdas'),
(3, 'admin', '2025-03-06 15:17:05', 'aadasdas'),
(6, 'admin', '2025-03-06 15:22:37', 'asdasdas'),
(7, 'admin', '2025-03-06 15:23:43', 'asdasdasd'),
(8, 'admin', '2025-03-06 15:24:30', 'asdasdasd'),
(9, 'admin', '2025-03-07 18:29:21', 'pakyo'),
(10, 'admin', '2025-03-07 18:30:04', 'rovic brader'),
(12, 'admin', '2025-03-13 11:16:50', 'asdsadas');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `feedback_id` int(11) NOT NULL,
  `idno` int(11) NOT NULL,
  `lab` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `message` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`feedback_id`, `idno`, `lab`, `date`, `message`) VALUES
(1, 21435607, '321', '2025-03-20', 'adasdas');

-- --------------------------------------------------------

--
-- Table structure for table `laboratory_messages`
--

CREATE TABLE `laboratory_messages` (
  `id` int(11) NOT NULL,
  `idno` int(11) NOT NULL,
  `laboratory` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `message_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sit_in`
--

CREATE TABLE `sit_in` (
  `sit_in_id` int(11) NOT NULL,
  `idno` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `lab` varchar(100) NOT NULL,
  `reason` varchar(255) NOT NULL,
  `in_time` timestamp NULL DEFAULT NULL,
  `out_time` timestamp NULL DEFAULT NULL,
  `sit_date` date DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sit_in`
--

INSERT INTO `sit_in` (`sit_in_id`, `idno`, `full_name`, `lab`, `reason`, `in_time`, `out_time`, `sit_date`, `status`) VALUES
(19, 21435607, 'ASDSAD SDASD', '213', 'asdasdas', '2025-03-20 00:02:55', '2025-03-20 00:03:03', '2025-03-20', '1'),
(20, 21435607, 'ASDSAD SDASD', '213', 'asdasdas', '2025-03-20 00:04:43', '2025-03-20 00:04:49', '2025-03-20', '0'),
(22, 21435607, 'ASDSAD SDASD', '213', 'adsdasdas', '2025-03-20 00:54:38', '2025-03-20 00:54:44', '2025-03-20', '0'),
(23, 21435607, 'ASDSAD SDASD', '530', 'adasdasd', '2025-03-20 00:55:11', '2025-03-20 01:57:50', NULL, '0');

-- --------------------------------------------------------

--
-- Table structure for table `student_session`
--

CREATE TABLE `student_session` (
  `idno` int(11) NOT NULL,
  `session` int(11) NOT NULL DEFAULT 30
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_session`
--

INSERT INTO `student_session` (`idno`, `session`) VALUES
(21435607, 12);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `idno` int(11) DEFAULT NULL,
  `lastname` varchar(50) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `middlename` varchar(50) DEFAULT NULL,
  `course` varchar(100) DEFAULT NULL,
  `year_level` tinyint(2) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `role` enum('Admin','Student') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `idno`, `lastname`, `firstname`, `middlename`, `course`, `year_level`, `username`, `password`, `image_path`, `role`) VALUES
(1, NULL, 'Administrator', 'System', NULL, NULL, NULL, 'admin', '$2y$10$jSuwvVWMDUY0Jnlzj7.g/uFnhG2RgXjzUAEY6PsewLNSHr1RELNIu', NULL, 'Admin'),
(3, 5341223, 'bards', 'sda', 'asds', 'BSIS', 2, 'bards12', '$2y$10$eTJ9eOzKc3nf50ZUVwQSuu/3Hok//BIE3vD7pAhoKhyTO40baNEna', NULL, 'Student'),
(5, 21435607, 'SDASD', 'ASDSAD', '', 'SAD', 0, '1as', '$2y$10$pMHzjKGwb3YQKMWJqHfPuuZFQ0TA1yWJ/XW42j/.lFIKbzdEZz7.q', '../uploads/TINGA_Justin_ Lab05.pdf', 'Student');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`announce_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`feedback_id`),
  ADD KEY `idno` (`idno`);

--
-- Indexes for table `laboratory_messages`
--
ALTER TABLE `laboratory_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idno` (`idno`);

--
-- Indexes for table `sit_in`
--
ALTER TABLE `sit_in`
  ADD PRIMARY KEY (`sit_in_id`),
  ADD KEY `fk_sit_in_idno` (`idno`);

--
-- Indexes for table `student_session`
--
ALTER TABLE `student_session`
  ADD PRIMARY KEY (`idno`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `idno` (`idno`),
  ADD UNIQUE KEY `idno_2` (`idno`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `announce_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `feedback_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `laboratory_messages`
--
ALTER TABLE `laboratory_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sit_in`
--
ALTER TABLE `sit_in`
  MODIFY `sit_in_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`idno`) REFERENCES `users` (`idno`) ON DELETE CASCADE;

--
-- Constraints for table `laboratory_messages`
--
ALTER TABLE `laboratory_messages`
  ADD CONSTRAINT `laboratory_messages_ibfk_1` FOREIGN KEY (`idno`) REFERENCES `users` (`idno`) ON DELETE CASCADE;

--
-- Constraints for table `sit_in`
--
ALTER TABLE `sit_in`
  ADD CONSTRAINT `fk_sit_in_idno` FOREIGN KEY (`idno`) REFERENCES `student_session` (`idno`) ON DELETE CASCADE;

--
-- Constraints for table `student_session`
--
ALTER TABLE `student_session`
  ADD CONSTRAINT `student_session_ibfk_1` FOREIGN KEY (`idno`) REFERENCES `users` (`idno`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
