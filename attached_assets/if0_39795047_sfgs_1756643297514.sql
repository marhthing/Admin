-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql100.byetcluster.com
-- Generation Time: Aug 28, 2025 at 08:23 AM
-- Server version: 10.6.22-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `if0_39795047_sfgs`
--

-- --------------------------------------------------------

--
-- Table structure for table `access`
--

CREATE TABLE `access` (
  `id` int(10) NOT NULL,
  `control_access` varchar(50) DEFAULT NULL,
  `date_created` varchar(50) DEFAULT NULL,
  `time_created` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `administratives`
--

CREATE TABLE `administratives` (
  `id` int(10) NOT NULL,
  `school_name` varchar(255) DEFAULT NULL,
  `school_motto` varchar(255) DEFAULT NULL,
  `school_logo` varchar(255) DEFAULT NULL,
  `school_stamp` varchar(255) DEFAULT NULL,
  `upload_date` varchar(50) DEFAULT NULL,
  `upload_time` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

 

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `current_season`
--

CREATE TABLE `current_season` (
  `id` int(10) NOT NULL,
  `current_session` varchar(20) DEFAULT NULL,
  `current_term` varchar(50) DEFAULT NULL,
  `date_declared` varchar(50) DEFAULT NULL,
  `time_declared` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dates`
--

CREATE TABLE `dates` (
  `id` int(10) NOT NULL,
  `end_of_term` varchar(50) DEFAULT NULL,
  `next_term_begins` varchar(50) DEFAULT NULL,
  `date_declared` varchar(50) DEFAULT NULL,
  `time_declared` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jss`
--

CREATE TABLE `jss` (
  `id` int(10) NOT NULL,
  `jss_subjects` varchar(100) DEFAULT NULL,
  `upload_date` varchar(50) DEFAULT NULL,
  `upload_time` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `local_governments`
--

CREATE TABLE `local_governments` (
  `id` int(11) NOT NULL,
  `state_id` int(10) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_general_ci COMMENT='Local governments in Nigeria.';

-- --------------------------------------------------------

--
-- Table structure for table `login_logs`
--

CREATE TABLE `login_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role` enum('admin','teacher','student') NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `device_info` text NOT NULL,
  `login_time` datetime NOT NULL,
  `status` enum('success','failed') DEFAULT 'success'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `positions`
--

CREATE TABLE `positions` (
  `id` int(10) NOT NULL,
  `reg_number` varchar(100) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `class` varchar(50) DEFAULT NULL,
  `term` varchar(50) DEFAULT NULL,
  `session` varchar(50) DEFAULT NULL,
  `students_sub_total` int(50) DEFAULT NULL,
  `students_sub_average` float(11,3) DEFAULT NULL,
  `class_position` int(10) DEFAULT NULL,
  `upload_date` varchar(20) DEFAULT NULL,
  `upload_time` timestamp NULL DEFAULT current_timestamp(),
  `published` tinyint(1) NOT NULL DEFAULT 1,
  `visible_to_student` tinyint(1) NOT NULL DEFAULT 0,
  `total_possible` int(11) DEFAULT 0,
  `subject_count` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `results1`
--

CREATE TABLE `results1` (
  `id` int(10) NOT NULL,
  `class` varchar(10) DEFAULT NULL,
  `term` varchar(50) DEFAULT NULL,
  `session` varchar(50) DEFAULT NULL,
  `reg_number` varchar(50) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `subjects` varchar(100) DEFAULT NULL,
  `ca` int(10) DEFAULT NULL,
  `ca2` int(10) DEFAULT NULL,
  `exam` int(10) DEFAULT NULL,
  `subject_total` int(10) DEFAULT NULL,
  `subject_rank` int(10) DEFAULT NULL,
  `date_of_upload` varchar(20) DEFAULT NULL,
  `time_of_upload` varchar(20) DEFAULT NULL,
  `published` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` int(10) NOT NULL,
  `sessions` varchar(20) DEFAULT NULL,
  `date_added` varchar(50) DEFAULT NULL,
  `time_added` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sss`
--

CREATE TABLE `sss` (
  `id` int(10) NOT NULL,
  `sss_subjects` varchar(100) DEFAULT NULL,
  `upload_date` varchar(50) DEFAULT NULL,
  `upload_time` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `states`
--

CREATE TABLE `states` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_general_ci COMMENT='States in Nigeria.';

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(10) NOT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `firstname` varchar(50) DEFAULT NULL,
  `lastname` varchar(50) DEFAULT NULL,
  `othername` varchar(50) DEFAULT NULL,
  `dob` varchar(50) DEFAULT NULL,
  `mob` varchar(50) DEFAULT NULL,
  `yob` varchar(50) DEFAULT NULL,
  `contact_phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `nationality` varchar(50) DEFAULT NULL,
  `sponsor_name` varchar(50) DEFAULT NULL,
  `sponsor_phone` varchar(20) DEFAULT NULL,
  `relationship` varchar(20) DEFAULT NULL,
  `class` varchar(20) DEFAULT NULL,
  `reg_number` varchar(100) DEFAULT NULL,
  `gen_password` varchar(20) DEFAULT NULL,
  `passport` varchar(255) DEFAULT NULL,
  `date_of_reg` varchar(20) DEFAULT NULL,
  `time_of_reg` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` int(10) NOT NULL,
  `reg_number` varchar(100) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `class` varchar(20) DEFAULT NULL,
  `subjects` varchar(100) DEFAULT NULL,
  `term` varchar(50) DEFAULT NULL,
  `session` varchar(50) DEFAULT NULL,
  `registration_date` varchar(20) DEFAULT NULL,
  `registration_time` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
  `id` int(10) NOT NULL,
  `title` varchar(50) DEFAULT NULL,
  `fullname` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `lga` text DEFAULT NULL,
  `nationality` varchar(50) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `password` varchar(50) DEFAULT NULL,
  `date_of_reg` varchar(50) DEFAULT NULL,
  `time_of_reg` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) NOT NULL,
  `title` varchar(50) DEFAULT NULL,
  `fullname` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `lga` text DEFAULT NULL,
  `nationality` varchar(50) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `password` varchar(50) DEFAULT NULL,
  `date_of_reg` varchar(50) DEFAULT NULL,
  `time_of_reg` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `access`
--
ALTER TABLE `access`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `administratives`
--
ALTER TABLE `administratives`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `current_season`
--
ALTER TABLE `current_season`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `dates`
--
ALTER TABLE `dates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `jss`
--
ALTER TABLE `jss`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `local_governments`
--
ALTER TABLE `local_governments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `state_id` (`state_id`);

--
-- Indexes for table `login_logs`
--
ALTER TABLE `login_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `positions`
--
ALTER TABLE `positions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `results1`
--
ALTER TABLE `results1`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sss`
--
ALTER TABLE `sss`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `states`
--
ALTER TABLE `states`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `access`
--
ALTER TABLE `access`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `administratives`
--
ALTER TABLE `administratives`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `current_season`
--
ALTER TABLE `current_season`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dates`
--
ALTER TABLE `dates`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jss`
--
ALTER TABLE `jss`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `local_governments`
--
ALTER TABLE `local_governments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `login_logs`
--
ALTER TABLE `login_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `positions`
--
ALTER TABLE `positions`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `results1`
--
ALTER TABLE `results1`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sessions`
--
ALTER TABLE `sessions`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sss`
--
ALTER TABLE `sss`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `local_governments`
--
ALTER TABLE `local_governments`
  ADD CONSTRAINT `FK` FOREIGN KEY (`state_id`) REFERENCES `states` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
