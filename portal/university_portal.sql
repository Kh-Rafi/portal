-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 12, 2026 at 05:56 PM
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
-- Database: `university_portal`
--

-- --------------------------------------------------------

--
-- Table structure for table `hostel_admin`
--

CREATE TABLE `hostel_admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hostel_admin`
--

INSERT INTO `hostel_admin` (`id`, `username`, `password`) VALUES
(1, 'hosteladmin', 'd531556735eec14dbfc30b2638c05cd8');

-- --------------------------------------------------------

--
-- Table structure for table `hostel_bills`
--

CREATE TABLE `hostel_bills` (
  `id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `university_id` varchar(50) DEFAULT NULL,
  `bill_type` varchar(50) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `month_year` varchar(20) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `paid_date` date DEFAULT NULL,
  `status` varchar(20) DEFAULT 'unpaid',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `late_fine` decimal(10,2) DEFAULT 0.00,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `days_present` int(11) DEFAULT 30,
  `days_stayed` int(11) DEFAULT 30,
  `original_food_cost` decimal(10,2) DEFAULT 3500.00,
  `total_days_in_month` int(11) DEFAULT 30,
  `leave_days` int(11) DEFAULT 0,
  `manual_late_fine` tinyint(4) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hostel_bills`
--

INSERT INTO `hostel_bills` (`id`, `student_id`, `university_id`, `bill_type`, `amount`, `month_year`, `due_date`, `paid_date`, `status`, `created_at`, `late_fine`, `discount_amount`, `days_present`, `days_stayed`, `original_food_cost`, `total_days_in_month`, `leave_days`, `manual_late_fine`) VALUES
(70, 56, '241002033', 'security', 5000.00, 'Apr 2026', '2026-05-28', NULL, 'unpaid', '2026-04-28 16:04:55', 0.00, 0.00, 30, 30, 3500.00, 30, 0, 0),
(71, 56, '241002033', 'monthly', 8000.00, '2026-04', '2026-04-20', NULL, 'unpaid', '2026-04-28 16:04:55', 500.00, 0.00, 30, 30, 3500.00, 30, 0, 0),
(72, 57, '241002060', 'security', 5000.00, 'Apr 2026', '2026-05-28', NULL, 'unpaid', '2026-04-28 16:07:35', 0.00, 0.00, 30, 30, 3500.00, 30, 0, 0),
(73, 57, '241002060', 'monthly', 8000.00, '2026-04', '2026-04-20', NULL, 'unpaid', '2026-04-28 16:07:35', 500.00, 0.00, 30, 30, 3500.00, 30, 0, 0),
(74, 58, '241002061', 'security', 5000.00, 'Apr 2026', '2026-05-28', NULL, 'unpaid', '2026-04-28 16:09:26', 0.00, 0.00, 30, 30, 3500.00, 30, 0, 0),
(75, 58, '241002061', 'monthly', 8000.00, '2026-04', '2026-04-20', NULL, 'unpaid', '2026-04-28 16:09:26', 500.00, 0.00, 30, 30, 3500.00, 30, 0, 0),
(76, 59, '241002062', 'security', 5000.00, 'Apr 2026', '2026-05-28', NULL, 'unpaid', '2026-04-28 16:11:15', 0.00, 0.00, 30, 30, 3500.00, 30, 0, 0),
(77, 59, '241002062', 'monthly', 8000.00, '2026-04', '2026-04-20', NULL, 'unpaid', '2026-04-28 16:11:15', 500.00, 0.00, 30, 30, 3500.00, 30, 0, 0),
(78, 60, '241002063', 'security', 5000.00, 'Apr 2026', '2026-05-28', '2026-04-28', 'paid', '2026-04-28 16:13:08', 0.00, 0.00, 30, 30, 3500.00, 30, 0, 0),
(79, 60, '241002063', 'monthly', 7500.00, '2026-04', '2026-04-20', '2026-04-28', 'paid', '2026-04-28 16:13:08', 0.00, 0.00, 30, 30, 3500.00, 30, 0, 1),
(80, 61, '241002064', 'security', 5000.00, 'Apr 2026', '2026-05-28', '2026-04-28', 'paid', '2026-04-28 16:14:53', 0.00, 0.00, 30, 30, 3500.00, 30, 0, 0),
(81, 61, '241002064', 'monthly', 4666.67, '2026-04', '2026-04-20', '2026-04-28', 'paid', '2026-04-28 16:14:53', 0.00, 2333.33, 30, 10, 3500.00, 30, 0, 1),
(84, 63, '241002066', 'security', 5000.00, 'Apr 2026', '2026-05-28', '2026-04-28', 'paid', '2026-04-28 16:38:30', 0.00, 0.00, 30, 30, 3500.00, 30, 0, 0),
(85, 63, '241002066', 'monthly', 7500.00, '2026-04', '2026-04-20', '2026-04-28', 'paid', '2026-04-28 16:38:30', 0.00, 0.00, 30, 30, 3500.00, 30, 0, 1),
(88, 65, '241002055', 'security', 5000.00, 'Apr 2026', '2026-05-29', '2026-04-29', 'paid', '2026-04-29 03:16:42', 0.00, 0.00, 30, 30, 3500.00, 30, 0, 0),
(89, 65, '241002055', 'monthly', 4583.33, '2026-04', '2026-04-20', '2026-04-29', 'paid', '2026-04-29 03:16:42', 0.00, 2916.67, 30, 5, 3500.00, 30, 0, 1),
(90, 55, '2410020666', 'monthly', 7500.00, '2026-05', '2026-05-20', '2026-05-01', 'paid', '2026-05-01 13:38:33', 0.00, 0.00, 30, 31, 3500.00, 31, 0, 0),
(91, 56, '241002033', 'monthly', 7500.00, '2026-05', '2026-05-20', NULL, 'unpaid', '2026-05-01 13:38:33', 0.00, 0.00, 30, 31, 3500.00, 31, 0, 0),
(92, 57, '241002060', 'monthly', 7500.00, '2026-05', '2026-05-20', NULL, 'unpaid', '2026-05-01 13:38:33', 0.00, 0.00, 30, 31, 3500.00, 31, 0, 0),
(93, 58, '241002061', 'monthly', 7500.00, '2026-05', '2026-05-20', NULL, 'unpaid', '2026-05-01 13:38:33', 0.00, 0.00, 30, 31, 3500.00, 31, 0, 0),
(94, 59, '241002062', 'monthly', 7500.00, '2026-05', '2026-05-20', NULL, 'unpaid', '2026-05-01 13:38:33', 0.00, 0.00, 30, 31, 3500.00, 31, 0, 0),
(95, 60, '241002063', 'monthly', 7500.00, '2026-05', '2026-05-20', NULL, 'unpaid', '2026-05-01 13:38:33', 0.00, 0.00, 30, 31, 3500.00, 31, 0, 0),
(96, 61, '241002064', 'monthly', 7500.00, '2026-05', '2026-05-20', NULL, 'unpaid', '2026-05-01 13:38:33', 0.00, 0.00, 30, 31, 3500.00, 31, 0, 0),
(97, 63, '241002066', 'monthly', 7500.00, '2026-05', '2026-05-20', NULL, 'unpaid', '2026-05-01 13:38:33', 0.00, 0.00, 30, 31, 3500.00, 31, 0, 0),
(98, 65, '241002055', 'monthly', 7500.00, '2026-05', '2026-05-20', NULL, 'unpaid', '2026-05-01 13:38:33', 0.00, 0.00, 30, 31, 3500.00, 31, 0, 0),
(99, 66, '242002083', 'security', 5000.00, 'Jun 2026', '2026-07-12', '2026-06-12', 'paid', '2026-06-12 15:49:59', 0.00, 0.00, 30, 30, 3500.00, 30, 0, 0),
(100, 66, '242002083', 'monthly', 7500.00, '2026-06', '2026-06-20', NULL, 'unpaid', '2026-06-12 15:49:59', 0.00, 0.00, 30, 30, 3500.00, 30, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `hostel_bills_backup`
--

CREATE TABLE `hostel_bills_backup` (
  `id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `university_id` varchar(50) DEFAULT NULL,
  `bill_type` varchar(50) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `month_year` varchar(20) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `paid_date` date DEFAULT NULL,
  `status` varchar(20) DEFAULT 'unpaid',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `late_fine` decimal(10,2) DEFAULT 0.00,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `days_present` int(11) DEFAULT 30,
  `days_stayed` int(11) DEFAULT 30,
  `original_food_cost` decimal(10,2) DEFAULT 3500.00,
  `total_days_in_month` int(11) DEFAULT 30,
  `leave_days` int(11) DEFAULT 0,
  `manual_late_fine` tinyint(4) DEFAULT 0,
  `deleted_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hostel_bills_backup`
--

INSERT INTO `hostel_bills_backup` (`id`, `student_id`, `university_id`, `bill_type`, `amount`, `month_year`, `due_date`, `paid_date`, `status`, `created_at`, `late_fine`, `discount_amount`, `days_present`, `days_stayed`, `original_food_cost`, `total_days_in_month`, `leave_days`, `manual_late_fine`, `deleted_date`) VALUES
(59, 62, '241002066', 'security', 5000.00, 'Apr 2026', '2026-05-28', '2026-04-28', 'paid', '2026-04-28 16:38:30', 0.00, 0.00, 30, 30, 3500.00, 30, 0, 0, '2026-04-28 16:41:24'),
(60, 62, '241002066', 'monthly', 7500.00, '2026-04', '2026-04-20', '0000-00-00', 'unpaid', '2026-04-28 16:38:30', 0.00, 0.00, 30, 30, 3500.00, 30, 0, 1, '2026-04-28 16:41:24'),
(61, 64, '241002055', 'security', 5000.00, 'Apr 2026', '2026-05-29', '2026-04-29', 'paid', '2026-04-29 03:16:42', 0.00, 0.00, 30, 30, 3500.00, 30, 0, 0, '2026-04-29 03:24:34'),
(62, 64, '241002055', 'monthly', 4583.33, '2026-04', '2026-04-20', '0000-00-00', 'unpaid', '2026-04-29 03:16:42', 0.00, 2916.67, 30, 5, 3500.00, 30, 0, 1, '2026-04-29 03:24:34');

-- --------------------------------------------------------

--
-- Table structure for table `hostel_students`
--

CREATE TABLE `hostel_students` (
  `id` int(11) NOT NULL,
  `university_id` varchar(50) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `father_name` varchar(100) DEFAULT NULL,
  `mother_name` varchar(100) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `blood_group` varchar(10) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `present_address` text DEFAULT NULL,
  `permanent_address` text DEFAULT NULL,
  `guardian_name` varchar(100) DEFAULT NULL,
  `guardian_phone` varchar(20) DEFAULT NULL,
  `hostel_name` varchar(20) DEFAULT 'Hostel_A',
  `floor_no` int(11) DEFAULT 1,
  `room_no` varchar(20) DEFAULT NULL,
  `seat_no` varchar(20) DEFAULT NULL,
  `student_photo` varchar(255) DEFAULT NULL,
  `id_card_photo` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `total_due` decimal(10,2) DEFAULT 0.00,
  `join_date` date DEFAULT NULL,
  `meal_status` varchar(20) DEFAULT 'active',
  `meal_count` int(11) DEFAULT 0,
  `advance_balance` decimal(10,2) DEFAULT 0.00,
  `due_amount` decimal(10,2) DEFAULT 0.00,
  `manual_due_amount` decimal(10,2) DEFAULT 0.00,
  `manual_due_adjustment` decimal(10,2) DEFAULT 0.00,
  `security_deposit` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hostel_students`
--

INSERT INTO `hostel_students` (`id`, `university_id`, `name`, `department`, `father_name`, `mother_name`, `dob`, `blood_group`, `email`, `phone`, `present_address`, `permanent_address`, `guardian_name`, `guardian_phone`, `hostel_name`, `floor_no`, `room_no`, `seat_no`, `student_photo`, `id_card_photo`, `password`, `total_due`, `join_date`, `meal_status`, `meal_count`, `advance_balance`, `due_amount`, `manual_due_amount`, `manual_due_adjustment`, `security_deposit`) VALUES
(55, '2410020666', 'Redoy Khan Redu', 'CSE', 'ismail', 'kajoly', '2002-11-11', 'B+', 'redu@gmail.com', '017*******', 'kst', 'kst', 'ismail', '017******', 'Hostel_A', 1, '2', '3', 'uploads/placeholder.jpg', 'uploads/placeholder.jpg', 'e10adc3949ba59abbe56e057f20f883e', 0.00, '2026-04-27', 'active', 0, 0.00, 0.00, 0.00, 0.00, 0.00),
(56, '241002033', 'Mamun', 'CSE', 'Father', 'Mother', '2003-11-02', 'O+', 'mamun@gmail.com', '017********', 'Dhaka', 'Dhaka', 'Father', '017********', 'Hostel_A', 1, '1', '1', 'uploads/1777392295_cat.jpg', 'uploads/1777392295_cat.jpg', '81dc9bdb52d04dc20036dbd8313ed055', 20500.00, '2026-04-28', 'active', 0, 0.00, 0.00, 0.00, 0.00, 0.00),
(57, '241002060', 'Siyam', 'TEX', 'Father', 'Mother', '1999-02-22', 'B+', 'siyam@gmail.com', '017********', 'Dhaka', 'Dhaka', 'Father', '017********', 'Hostel_A', 1, '1', '2', 'uploads/1777392455_deer.jpg', 'uploads/1777392455_deer.jpg', '81dc9bdb52d04dc20036dbd8313ed055', 20500.00, '2026-04-28', 'active', 0, 0.00, 0.00, 0.00, 0.00, 0.00),
(58, '241002061', 'Rafi', 'CSE', 'Father', 'Mother', '2002-01-01', 'O+', 'rafi@gmail.com', '017********', 'Dhaka', 'Dhaka', 'Father', '017********', 'Hostel_A', 2, '1', '1', 'uploads/1777392566_dog.jpg', 'uploads/1777392566_dog.jpg', '81dc9bdb52d04dc20036dbd8313ed055', 20500.00, '2026-04-28', 'active', 0, 0.00, 0.00, 0.00, 0.00, 0.00),
(59, '241002062', 'Maruf', 'EEE', 'Father', 'Mother', '1998-03-11', 'A+', 'maruf@gmail.com', '017********', 'Kanchan', 'Kanchan', 'Father', '017********', 'Hostel_A', 2, '1', '2', 'uploads/1777392675_eagle.jpg', 'uploads/1777392675_eagle.jpg', '81dc9bdb52d04dc20036dbd8313ed055', 20500.00, '2026-04-28', 'active', 0, 0.00, 0.00, 0.00, 0.00, 0.00),
(60, '241002063', 'Joy', 'EEE', 'Father', 'Mother', '2000-12-16', 'B+', 'joy@gmail.com', '017********', 'Kanchan', 'Kanchan', 'Father', '017********', 'Hostel_B', 1, '1', '1', 'uploads/1777392788_elephant.jpg', 'uploads/1777392788_elephant.jpg', '81dc9bdb52d04dc20036dbd8313ed055', 7500.00, '2026-04-28', 'active', 0, 0.00, 0.00, 0.00, 0.00, 0.00),
(61, '241002064', 'Nahiyan', 'ADS', 'Father', 'Mother', '2000-02-22', 'A+', 'nahiyan@gmail.com', '017********', 'Rupgang', 'Rupgang', 'Father', '017********', 'Hostel_C', 1, '1', '1', 'uploads/1777392893_horse.jpg', 'uploads/1777392893_horse.jpg', '9af0f26a2791f9bc0b0c072b35264d39', 7500.00, '2026-04-28', 'active', 0, 0.00, 0.00, 0.00, 0.00, 0.00),
(63, '241002066', 'Md.Sakruzzman Redoy', 'CSE', 'Md.Ismail Hossain', 'Mst.Kamrunnahar Kajoly', '2002-09-11', 'B+', 'reduredoykhan@gmail.com', '01726043291', 'Kushtia', 'Kushtia', 'Md.Ismail Hossain', '017********', 'Hostel_D', 1, '1', '1', 'uploads/1777394310_lion.jpg', 'uploads/1777394310_lion.jpg', '81dc9bdb52d04dc20036dbd8313ed055', 7500.00, '2026-04-28', 'active', 0, 0.00, 0.00, 0.00, 0.00, 0.00),
(65, '241002055', 'atik', 'LAW', 'Father', 'Mother', '2000-11-23', 'O+', 'atik@gmail.com', '017********', 'Dhaka', 'DHaka', 'Father', '017********', 'Hostel_C', 3, '2', '2', 'uploads/1777432602_tiger.jpg', 'uploads/1777432602_rabbit.jpg', '81dc9bdb52d04dc20036dbd8313ed055', 7500.00, '2026-04-29', 'active', 0, 0.00, 0.00, 0.00, 0.00, 0.00),
(66, '242002083', 'shanto', 'CSE', 'f', 'h', '0002-12-03', 'B+', 'reduredoykhan@gmail.com', '01726043291', 'Kushtia', 'fd', 'fdf', '00', 'Hostel_A', 2, '2', '1', 'uploads/1781279399_WhatsApp Image 2026-05-01 at 6.56.33 PM (1).jpeg', 'uploads/1781279399_CEA.png', 'e10adc3949ba59abbe56e057f20f883e', 7500.00, '2026-06-12', 'active', 0, 0.00, 0.00, 0.00, 0.00, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `hostel_students_backup`
--

CREATE TABLE `hostel_students_backup` (
  `id` int(11) NOT NULL,
  `university_id` varchar(50) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `father_name` varchar(100) DEFAULT NULL,
  `mother_name` varchar(100) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `blood_group` varchar(10) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `present_address` text DEFAULT NULL,
  `permanent_address` text DEFAULT NULL,
  `guardian_name` varchar(100) DEFAULT NULL,
  `guardian_phone` varchar(20) DEFAULT NULL,
  `hostel_name` varchar(20) DEFAULT 'Hostel_A',
  `floor_no` int(11) DEFAULT 1,
  `room_no` varchar(20) DEFAULT NULL,
  `seat_no` varchar(20) DEFAULT NULL,
  `student_photo` varchar(255) DEFAULT NULL,
  `id_card_photo` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `total_due` decimal(10,2) DEFAULT 0.00,
  `join_date` date DEFAULT NULL,
  `deleted_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hostel_students_backup`
--

INSERT INTO `hostel_students_backup` (`id`, `university_id`, `name`, `department`, `father_name`, `mother_name`, `dob`, `blood_group`, `email`, `phone`, `present_address`, `permanent_address`, `guardian_name`, `guardian_phone`, `hostel_name`, `floor_no`, `room_no`, `seat_no`, `student_photo`, `id_card_photo`, `password`, `total_due`, `join_date`, `deleted_date`) VALUES
(62, '241002066', 'Md.Sakruzzman Redoy', 'CSE', 'Md.Ismail Hossain', 'Mst.Kamrunnahar Kajoly', '2002-09-11', 'B+', 'reduredoykhan@gmail.com', '01726043291', 'Kushtia', 'Kushtia', 'Md.Ismail Hossain', '017********', 'Hostel_D', 1, '1', '1', 'uploads/1777394310_lion.jpg', 'uploads/1777394310_lion.jpg', '81dc9bdb52d04dc20036dbd8313ed055', 7500.00, '2026-04-28', '2026-04-28 16:41:24'),
(64, '241002055', 'atik', 'LAW', 'Father', 'Mother', '2000-11-23', 'O+', 'atik@gmail.com', '017********', 'Dhaka', 'DHaka', 'Father', '017********', 'Hostel_C', 3, '2', '2', 'uploads/1777432602_tiger.jpg', 'uploads/1777432602_rabbit.jpg', '81dc9bdb52d04dc20036dbd8313ed055', 4583.33, '2026-04-29', '2026-04-29 03:24:34');

-- --------------------------------------------------------

--
-- Table structure for table `meal_requests`
--

CREATE TABLE `meal_requests` (
  `id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `university_id` varchar(50) DEFAULT NULL,
  `request_date` date DEFAULT NULL,
  `meal_date` date DEFAULT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `approved_by` varchar(50) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `meal_tracking`
--

CREATE TABLE `meal_tracking` (
  `id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `meal_date` date DEFAULT NULL,
  `status` enum('taken','not_taken') DEFAULT 'not_taken',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` varchar(50) DEFAULT 'student'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `meal_tracking`
--

INSERT INTO `meal_tracking` (`id`, `student_id`, `meal_date`, `status`, `updated_at`, `updated_by`) VALUES
(13, 65, '2026-04-30', 'taken', '2026-04-29 03:31:14', 'student'),
(14, 63, '2026-05-02', 'taken', '2026-05-01 13:40:38', 'student');

-- --------------------------------------------------------

--
-- Table structure for table `payment_history`
--

CREATE TABLE `payment_history` (
  `id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `paid_due` decimal(10,2) DEFAULT NULL,
  `new_advance` decimal(10,2) DEFAULT NULL,
  `remaining_due` decimal(10,2) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_history`
--

INSERT INTO `payment_history` (`id`, `student_id`, `amount`, `paid_due`, `new_advance`, `remaining_due`, `note`, `payment_date`) VALUES
(1, 35, 500.00, 500.00, 0.00, -500.00, '', '2026-04-27 18:36:56');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `hostel_admin`
--
ALTER TABLE `hostel_admin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hostel_bills`
--
ALTER TABLE `hostel_bills`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hostel_bills_backup`
--
ALTER TABLE `hostel_bills_backup`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hostel_students`
--
ALTER TABLE `hostel_students`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hostel_students_backup`
--
ALTER TABLE `hostel_students_backup`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `meal_requests`
--
ALTER TABLE `meal_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `meal_tracking`
--
ALTER TABLE `meal_tracking`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_meal` (`student_id`,`meal_date`);

--
-- Indexes for table `payment_history`
--
ALTER TABLE `payment_history`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `hostel_admin`
--
ALTER TABLE `hostel_admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `hostel_bills`
--
ALTER TABLE `hostel_bills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- AUTO_INCREMENT for table `hostel_bills_backup`
--
ALTER TABLE `hostel_bills_backup`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `hostel_students`
--
ALTER TABLE `hostel_students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT for table `hostel_students_backup`
--
ALTER TABLE `hostel_students_backup`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `meal_requests`
--
ALTER TABLE `meal_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `meal_tracking`
--
ALTER TABLE `meal_tracking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `payment_history`
--
ALTER TABLE `payment_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `meal_requests`
--
ALTER TABLE `meal_requests`
  ADD CONSTRAINT `meal_requests_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `hostel_students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `meal_tracking`
--
ALTER TABLE `meal_tracking`
  ADD CONSTRAINT `meal_tracking_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `hostel_students` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
