-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 10, 2025 at 09:15 AM
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
-- Database: `db_usg_main`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `event_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `name`, `date`, `time`, `event_id`) VALUES
(32, 'Jeviii Bantiad', '2025-05-09', '12:46:00', 28),
(33, 'Jeviiiere Bantiad', '2025-05-09', '11:51:00', 28),
(34, 'Jel', '2025-05-10', '04:30:00', 28),
(35, 'JD', '2025-05-10', '00:00:00', 28),
(36, 'JD', '2025-05-10', '00:00:00', 28),
(37, 'JD', '2025-05-10', '00:00:00', 28);

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `eventname` varchar(255) NOT NULL,
  `startdate` date NOT NULL,
  `enddate` date NOT NULL,
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `eventname`, `startdate`, `enddate`, `description`) VALUES
(28, 'Sleep', '2025-05-09', '2025-05-17', 'ohahahahha');

-- --------------------------------------------------------

--
-- Table structure for table `feedbk`
--

CREATE TABLE `feedbk` (
  `feed_id` int(11) NOT NULL,
  `feed_type` varchar(50) DEFAULT NULL,
  `feed_sub` varchar(50) DEFAULT NULL,
  `feed_comm` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lst_fnd`
--

CREATE TABLE `lst_fnd` (
  `lst_id` int(11) NOT NULL,
  `lst_name` varchar(50) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `date_found` date DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Unclaimed',
  `lst_descrip` text DEFAULT NULL,
  `lst_img` blob DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pay`
--

CREATE TABLE `pay` (
  `pay_id` int(11) NOT NULL,
  `payname` varchar(50) DEFAULT NULL,
  `pay_startdate` date DEFAULT NULL,
  `pay_enddate` date DEFAULT NULL,
  `pay_description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pay`
--

INSERT INTO `pay` (`pay_id`, `payname`, `pay_startdate`, `pay_enddate`, `pay_description`) VALUES
(6, 'Contributions', '2025-05-08', '2025-05-09', 'sdfsdf'),
(7, 'Contributions', '2025-05-17', '2025-05-24', 'Pay 100 para ligo');

-- --------------------------------------------------------

--
-- Table structure for table `stud_attendance`
--

CREATE TABLE `stud_attendance` (
  `stud_atten_id` int(11) NOT NULL,
  `atten_id` int(11) DEFAULT NULL,
  `event_id` int(11) DEFAULT NULL,
  `stud_name` varchar(50) DEFAULT NULL,
  `stud_date` date DEFAULT NULL,
  `stud_time` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_acc`
--

CREATE TABLE `user_acc` (
  `acc_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `acc_pass` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_prof`
--

CREATE TABLE `user_prof` (
  `user_id` int(11) NOT NULL,
  `user_fullname` varchar(255) DEFAULT NULL,
  `user_mail` varchar(50) DEFAULT NULL,
  `department` varchar(50) DEFAULT NULL,
  `user_img` blob DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_prof`
--

INSERT INTO `user_prof` (`user_id`, `user_fullname`, `user_mail`, `department`, `user_img`) VALUES
(2023304637, 'REDJAN VISITACION', 'redjan@gmail.com', 'BSIT', NULL),
(2023305026, ' JOHN ROLDAN', 'john@gmail.com', 'BSIT', NULL),
(2023305122, 'USG ADMIN', 'admin@gmail.com', 'BSIT', ''),
(2023305178, 'JEVI BANTIAD', 'jevi@gmail.com', 'BSIT', ''),
(2023306358, 'JAY PALANIA', 'jay@gmail.com', 'BSIT', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `feedbk`
--
ALTER TABLE `feedbk`
  ADD PRIMARY KEY (`feed_id`);

--
-- Indexes for table `lst_fnd`
--
ALTER TABLE `lst_fnd`
  ADD PRIMARY KEY (`lst_id`);

--
-- Indexes for table `pay`
--
ALTER TABLE `pay`
  ADD PRIMARY KEY (`pay_id`);

--
-- Indexes for table `stud_attendance`
--
ALTER TABLE `stud_attendance`
  ADD PRIMARY KEY (`stud_atten_id`),
  ADD KEY `atten_id` (`atten_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `user_acc`
--
ALTER TABLE `user_acc`
  ADD PRIMARY KEY (`acc_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_prof`
--
ALTER TABLE `user_prof`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `feedbk`
--
ALTER TABLE `feedbk`
  MODIFY `feed_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lst_fnd`
--
ALTER TABLE `lst_fnd`
  MODIFY `lst_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pay`
--
ALTER TABLE `pay`
  MODIFY `pay_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `stud_attendance`
--
ALTER TABLE `stud_attendance`
  MODIFY `stud_atten_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_acc`
--
ALTER TABLE `user_acc`
  MODIFY `acc_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stud_attendance`
--
ALTER TABLE `stud_attendance`
  ADD CONSTRAINT `stud_attendance_ibfk_1` FOREIGN KEY (`atten_id`) REFERENCES `attendance` (`id`),
  ADD CONSTRAINT `stud_attendance_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`);

--
-- Constraints for table `user_acc`
--
ALTER TABLE `user_acc`
  ADD CONSTRAINT `user_acc_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user_prof` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
