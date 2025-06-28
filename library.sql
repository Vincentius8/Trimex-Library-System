-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 26, 2025 at 07:49 AM
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
-- Database: `library`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL,
  `school_id` varchar(50) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `school_id`, `firstname`, `lastname`, `email`, `password`, `profile_image`, `created_at`, `updated_at`) VALUES
(2, '21-0813', 'John Vincent', 'Pangilinan', 'johnvincentpangilinan@trimex.edu.ph', '$2y$10$mmM3Lok7N7OkSaOKIxORL.X4EbeBPvArAPHIY3WOhvLACmix6.Bny', 'profile_67b55e696c5296.67257928.jpg', '2025-02-14 04:12:51', '2025-02-19 04:30:33'),
(4, '12-3456', 'LeBron', 'James', 'lebronjames23@gmail.com', '$2y$10$blWUGqlwQzCMDj7Fljn8I.mTmTvklVm/smWGr3qKB43n69AkSG/Qy', 'profile_67bc2125cccf70.25864273.jpg', '2025-02-24 06:03:17', '2025-02-24 07:35:01'),
(5, '22-0813', 'Admin1', 'Admin1', 'admin@trimexcolleges.edu.ph', '$2y$10$zNlz7/zGSCnOOM9lppXxhucc28sbzH37rn2JVsDLcQvgLieMMNcti', 'admin_685cb06f61de94.58403041.jpg', '2025-06-26 02:28:50', '2025-06-26 02:29:03');

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `attendance_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rfid` varchar(50) NOT NULL DEFAULT '',
  `time_in` datetime NOT NULL,
  `time_out` datetime DEFAULT NULL,
  `record_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `book_id` int(11) NOT NULL,
  `accession_no` varchar(50) NOT NULL,
  `call_no` varchar(50) NOT NULL,
  `author` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `publisher` varchar(100) DEFAULT NULL,
  `copies` varchar(50) DEFAULT NULL,
  `copyright` varchar(10) DEFAULT NULL,
  `course` varchar(50) DEFAULT NULL,
  `book_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('available','reserved','borrowed','missing','discarded') NOT NULL DEFAULT 'available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`book_id`, `accession_no`, `call_no`, `author`, `title`, `publisher`, `copies`, `copyright`, `course`, `book_image`, `created_at`, `updated_at`, `status`) VALUES
(1, 'B001', 'CALL001', 'Author 1', 'Book 1', 'Publisher 1', '1', '2022', 'Computer Science', 'uploads/book1.jpg', '2025-02-28 02:17:07', '2025-02-28 04:58:18', 'available'),
(2, 'B002', 'CALL002', 'Author 2', 'Book 2', 'Publisher 2', '1', '2022', 'Mathematics', 'uploads/book2.jpg', '2025-02-28 02:17:07', '2025-02-28 05:36:36', 'available'),
(3, 'B003', 'CALL003', 'Author 3', 'Book 3', 'Publisher 3', '1', '2022', 'Engineering', 'uploads/book3.jpg', '2025-02-28 02:17:07', '2025-02-28 04:57:53', 'discarded'),
(4, 'B004', 'CALL004', 'Author 4', 'Book 4', 'Publisher 4', '1', '2022', 'Biology', 'uploads/book4.jpg', '2025-02-28 02:17:07', '2025-02-28 02:17:07', 'available'),
(5, 'B005', 'CALL005', 'Author 5', 'Book 5', 'Publisher 5', '1', '2022', 'Chemistry', 'uploads/book5.jpg', '2025-02-28 02:17:07', '2025-02-28 02:17:07', 'available'),
(6, 'B006', 'CALL006', 'Author 6', 'Book 6', 'Publisher 6', '1', '2022', 'Physics', 'uploads/book6.jpg', '2025-02-28 02:17:07', '2025-02-28 02:17:07', 'available'),
(7, 'B007', 'CALL007', 'Author 7', 'Book 7', 'Publisher 7', '1', '2022', 'History', 'uploads/book7.jpg', '2025-02-28 02:17:07', '2025-02-28 02:17:07', 'available'),
(8, 'B008', 'CALL008', 'Author 8', 'Book 8', 'Publisher 8', '1', '2022', 'Literature', 'uploads/book8.jpg', '2025-02-28 02:17:07', '2025-02-28 02:17:07', 'available'),
(9, 'B009', 'CALL009', 'Author 9', 'Book 9', 'Publisher 9', '1', '2022', 'Philosophy', 'uploads/book9.jpg', '2025-02-28 02:17:07', '2025-02-28 02:17:07', 'available'),
(10, 'B010', 'CALL010', 'Author 10', 'Book 10', 'Publisher 10', '1', '2022', 'Economics', 'uploads/book10.jpg', '2025-02-28 02:17:07', '2025-02-28 02:17:07', 'available'),
(11, 'B011', 'CALL011', 'Author 11', 'Book 11', 'Publisher 11', '1', '2022', 'Computer Science', 'uploads/book11.jpg', '2025-02-28 02:17:07', '2025-02-28 02:17:07', 'available'),
(12, 'B012', 'CALL012', 'Author 12', 'Book 12', 'Publisher 12', '1', '2022', 'Mathematics', 'uploads/book12.jpg', '2025-02-28 02:17:07', '2025-02-28 02:17:07', 'available'),
(13, 'B013', 'CALL013', 'Author 13', 'Book 13', 'Publisher 13', '1', '2022', 'Engineering', 'uploads/book13.jpg', '2025-02-28 02:17:07', '2025-02-28 02:17:07', 'available'),
(14, 'B014', 'CALL014', 'Author 14', 'Book 14', 'Publisher 14', '1', '2022', 'Biology', 'uploads/book14.jpg', '2025-02-28 02:17:07', '2025-06-26 03:38:14', 'available'),
(15, 'B015', 'CALL015', 'Author 15', 'Book 15', 'Publisher 15', '1', '2022', 'Chemistry', 'uploads/book15.jpg', '2025-02-28 02:17:07', '2025-02-28 02:17:07', 'available'),
(16, 'B016', 'CALL016', 'Author 16', 'Book 16', 'Publisher 16', '1', '2022', 'Physics', 'uploads/book16.jpg', '2025-02-28 02:17:07', '2025-02-28 02:17:07', 'available'),
(17, 'B017', 'CALL017', 'Author 17', 'Book 17', 'Publisher 17', '1', '2022', 'History', 'uploads/book17.jpg', '2025-02-28 02:17:07', '2025-02-28 02:17:07', 'available'),
(18, 'B018', 'CALL018', 'Author 18', 'Book 18', 'Publisher 18', '1', '2022', 'Literature', 'uploads/book18.jpg', '2025-02-28 02:17:07', '2025-02-28 02:17:07', 'available'),
(19, 'B019', 'CALL019', 'Author 19', 'Book 19', 'Publisher 19', '1', '2022', 'Philosophy', 'uploads/book19.jpg', '2025-02-28 02:17:07', '2025-02-28 02:17:07', 'available'),
(20, 'B020', 'CALL020', 'Author 20', 'Book 20', 'Publisher 20', '1', '2022', 'Economics', 'uploads/book20.jpg', '2025-02-28 02:17:07', '2025-02-28 02:17:07', 'available'),
(21, 'B021', 'CALL021', 'Author 21', 'Book 21', 'Publisher 21', '1', '2022', 'Computer Science', 'uploads/book21.jpg', '2025-02-28 02:17:07', '2025-02-28 02:17:07', 'available'),
(22, 'B022', 'CALL022', 'Author 22', 'Book 22', 'Publisher 22', '1', '2022', 'Mathematics', 'uploads/book22.jpg', '2025-02-28 02:17:07', '2025-02-28 02:17:07', 'available'),
(23, 'B023', 'CALL023', 'Author 23', 'Book 23', 'Publisher 23', '1', '2022', 'Engineering', 'uploads/book23.jpg', '2025-02-28 02:17:07', '2025-02-28 02:17:07', 'available'),
(24, 'B024', 'CALL024', 'Author 24', 'Book 24', 'Publisher 24', '1', '2022', 'Biology', 'uploads/book24.jpg', '2025-02-28 02:17:07', '2025-02-28 02:17:07', 'available'),
(25, 'B025', 'CALL025', 'Author 25', 'Book 25', 'Publisher 25', '1', '2022', 'Chemistry', 'uploads/book25.jpg', '2025-02-28 02:17:07', '2025-02-28 02:17:07', 'available'),
(26, 'B026', 'CALL026', 'Author 26', 'Book 26', 'Publisher 26', '1', '2022', 'Physics', 'uploads/book26.jpg', '2025-02-28 02:17:07', '2025-02-28 02:17:07', 'available'),
(27, 'B027', 'CALL027', 'Author 27', 'Book 27', 'Publisher 27', '1', '2022', 'History', 'uploads/book27.jpg', '2025-02-28 02:17:07', '2025-02-28 02:17:07', 'available'),
(28, 'B028', 'CALL028', 'Author 28', 'Book 28', 'Publisher 28', '1', '2022', 'Literature', 'uploads/book28.jpg', '2025-02-28 02:17:07', '2025-02-28 02:17:07', 'available'),
(29, 'B029', 'CALL029', 'Author 29', 'Book 29', 'Publisher 29', '1', '2022', 'Philosophy', 'uploads/book29.jpg', '2025-02-28 02:17:07', '2025-02-28 02:17:07', 'available'),
(30, 'B030', 'CALL030', 'Author 30', 'Book 30', 'Publisher 30', '1', '2022', 'Economics', 'uploads/book30.jpg', '2025-02-28 02:17:07', '2025-02-28 02:17:07', 'available'),
(31, 'B031', 'CALL031', 'Author 31', 'Book 31', 'Publisher 31', '1', '2022', 'Computer Science', 'uploads/book31.jpg', '2025-02-28 02:17:07', '2025-02-28 02:17:07', 'available'),
(32, 'B032', 'CALL032', 'Author 32', 'Book 32', 'Publisher 32', '1', '2022', 'Mathematics', 'uploads/book32.jpg', '2025-02-28 02:17:07', '2025-02-28 02:17:07', 'available'),
(33, 'B033', 'CALL033', 'Author 33', 'Book 33', 'Publisher 33', '1', '2022', 'Engineering', 'uploads/book33.jpg', '2025-02-28 02:17:07', '2025-02-28 02:17:07', 'available'),
(34, 'B034', 'CALL034', 'Author 34', 'Book 34', 'Publisher 34', '1', '2022', 'Biology', 'uploads/book34.jpg', '2025-02-28 02:17:07', '2025-02-28 02:17:07', 'available'),
(35, 'B035', 'CALL035', 'Author 35', 'Book 35', 'Publisher 35', '1', '2022', 'Chemistry', 'uploads/book35.jpg', '2025-02-28 02:17:07', '2025-02-28 02:17:07', 'available'),
(36, 'B036', 'CALL036', 'Author 36', 'Book 36', 'Publisher 36', '1', '2022', 'Physics', 'uploads/book36.jpg', '2025-02-28 02:17:07', '2025-02-28 02:17:07', 'available'),
(37, 'B037', 'CALL037', 'Author 37', 'Book 37', 'Publisher 37', '1', '2022', 'History', 'uploads/book37.jpg', '2025-02-28 02:17:07', '2025-02-28 02:17:07', 'available'),
(38, 'B038', 'CALL038', 'Author 38', 'Book 38', 'Publisher 38', '1', '2022', 'Literature', 'uploads/book38.jpg', '2025-02-28 02:17:07', '2025-02-28 02:17:07', 'available'),
(39, 'B039', 'CALL039', 'Author 39', 'Book 39', 'Publisher 39', '1', '2022', 'Philosophy', 'uploads/book39.jpg', '2025-02-28 02:17:07', '2025-02-28 02:17:07', 'available'),
(40, 'B040', 'CALL040', 'Author 40', 'Book 40', 'Publisher 40', '1', '2022', 'Economics', 'uploads/book40.jpg', '2025-02-28 02:17:07', '2025-02-28 02:17:07', 'available'),
(41, 'B041', 'CALL041', 'Author 41', 'Book 41', 'Publisher 41', '1', '2022', 'Computer Science', 'uploads/book41.jpg', '2025-02-28 02:17:07', '2025-02-28 02:17:07', 'available'),
(42, 'B042', 'CALL042', 'Author 42', 'Book 42', 'Publisher 42', '1', '2022', 'Mathematics', 'uploads/book42.jpg', '2025-02-28 02:17:07', '2025-02-28 02:17:07', 'available'),
(43, 'B043', 'CALL043', 'Author 43', 'Book 43', 'Publisher 43', '1', '2022', 'Engineering', 'uploads/book43.jpg', '2025-02-28 02:17:07', '2025-02-28 02:17:07', 'available'),
(44, 'B044', 'CALL044', 'Author 44', 'Book 44', 'Publisher 44', '1', '2022', 'Biology', 'uploads/book44.jpg', '2025-02-28 02:17:07', '2025-02-28 02:17:07', 'available'),
(45, 'B045', 'CALL045', 'Author 45', 'Book 45', 'Publisher 45', '1', '2022', 'Chemistry', 'uploads/book45.jpg', '2025-02-28 02:17:07', '2025-02-28 02:17:07', 'available'),
(46, 'B046', 'CALL046', 'Author 46', 'Book 46', 'Publisher 46', '1', '2022', 'Physics', 'uploads/book46.jpg', '2025-02-28 02:17:07', '2025-02-28 02:17:07', 'available'),
(47, 'B047', 'CALL047', 'Author 47', 'Book 47', 'Publisher 47', '1', '2022', 'History', 'uploads/book47.jpg', '2025-02-28 02:17:07', '2025-02-28 02:17:07', 'available'),
(48, 'B048', 'CALL048', 'Author 48', 'Book 48', 'Publisher 48', '1', '2022', 'Literature', 'uploads/book48.jpg', '2025-02-28 02:17:07', '2025-02-28 02:17:07', 'available'),
(49, 'B049', 'CALL049', 'Author 49', 'Book 49', 'Publisher 49', '1', '2022', 'Philosophy', 'uploads/book49.jpg', '2025-02-28 02:17:07', '2025-02-28 02:17:07', 'available'),
(50, 'B050', 'CALL050', 'Author 50', 'Book 50', 'Publisher 50', '1', '2022', 'Economics', 'uploads/book50.jpg', '2025-02-28 02:17:07', '2025-02-28 02:17:07', 'available'),
(232, '51', '1234', 'Andres Bonifacio', 'Katipunan', 'ph first fepublic', '1', '1891', 'Political Science', '', '2025-02-28 03:20:58', '2025-02-28 03:20:58', 'available'),
(233, '52', '14515', 'njnjj', 'test1', 'publisher1', '1', '2013', 'Computer Science', 'uploads/1750900960_download.jpg', '2025-06-26 01:22:40', '2025-06-26 01:22:40', 'available');

-- --------------------------------------------------------

--
-- Table structure for table `book_transactions`
--

CREATE TABLE `book_transactions` (
  `transaction_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `borrow_date` datetime NOT NULL,
  `due_date` datetime NOT NULL,
  `return_date` datetime DEFAULT NULL,
  `fine_amount` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `book_transactions`
--

INSERT INTO `book_transactions` (`transaction_id`, `user_id`, `book_id`, `borrow_date`, `due_date`, `return_date`, `fine_amount`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '2025-02-18 00:00:00', '2025-02-21 00:00:00', '2025-02-18 00:00:00', 0.00, '2025-02-28 02:17:07', '2025-02-28 02:17:07'),
(2, 1, 1, '2025-02-18 00:00:00', '2025-02-21 00:00:00', '2025-02-18 00:00:00', 0.00, '2025-02-28 02:17:07', '2025-02-28 02:17:07'),
(3, 2, 6, '2025-02-24 00:00:00', '2025-02-27 00:00:00', '2025-02-24 00:00:00', 0.00, '2025-02-28 02:17:07', '2025-02-28 02:17:07');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_requests`
--

CREATE TABLE `password_requests` (
  `request_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `request_date` datetime NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','approved','declined') NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `reservation_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `reservation_date` date NOT NULL,
  `due_date` date NOT NULL,
  `return_date` date DEFAULT NULL,
  `fine_amount` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('pending','approved','cancelled','return_requested','returned') NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservations`
--

INSERT INTO `reservations` (`reservation_id`, `user_id`, `book_id`, `reservation_date`, `due_date`, `return_date`, `fine_amount`, `created_at`, `updated_at`, `status`) VALUES
(15, 2, 1, '2025-02-17', '2025-02-24', '2025-02-17', 0.00, '2025-02-28 02:17:07', '2025-02-28 05:36:39', 'cancelled'),
(16, 1, 1, '2025-02-17', '2025-02-20', '2025-02-17', 0.00, '2025-02-28 02:17:07', '2025-02-28 05:36:38', 'cancelled'),
(17, 2, 2, '2025-02-21', '2025-02-24', NULL, 0.00, '2025-02-28 02:17:07', '2025-02-28 02:17:07', 'cancelled'),
(18, 2, 2, '2025-02-21', '2025-02-24', '2025-02-28', 20.00, '2025-02-28 02:17:07', '2025-02-28 05:36:36', 'cancelled'),
(19, 1, 1, '2025-02-21', '2025-03-02', NULL, 0.00, '2025-02-28 02:17:07', '2025-02-28 02:17:07', 'approved'),
(20, 1, 6, '2025-02-21', '2025-02-24', NULL, 0.00, '2025-02-28 02:17:07', '2025-02-28 02:17:07', 'cancelled'),
(21, 1, 7, '2025-02-21', '2025-02-24', NULL, 0.00, '2025-02-28 02:17:07', '2025-02-28 02:17:07', 'cancelled'),
(22, 2, 1, '2025-02-27', '2025-03-02', NULL, 0.00, '2025-02-28 02:17:07', '2025-02-28 02:17:07', 'cancelled'),
(23, 2, 1, '2025-02-27', '2025-03-02', NULL, 0.00, '2025-02-28 02:17:07', '2025-02-28 02:17:07', 'cancelled'),
(24, 2, 6, '2025-02-27', '2025-03-02', NULL, 0.00, '2025-02-28 02:17:07', '2025-02-28 02:17:07', 'cancelled'),
(25, 2, 1, '2025-02-28', '2025-03-03', NULL, 0.00, '2025-02-28 05:15:55', '2025-02-28 05:16:05', 'cancelled'),
(26, 2, 1, '2025-02-28', '2025-03-03', NULL, 0.00, '2025-02-28 05:16:12', '2025-02-28 05:16:19', 'cancelled'),
(27, 2, 1, '2025-02-28', '2025-03-03', NULL, 0.00, '2025-02-28 05:34:00', '2025-02-28 05:36:27', 'cancelled'),
(28, 4, 14, '2025-06-26', '2025-06-29', '2025-06-26', 0.00, '2025-06-26 03:27:53', '2025-06-26 03:38:14', 'returned');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `library_id` varchar(50) NOT NULL,
  `role` enum('teacher','faculty','student') NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rfid` varchar(50) NOT NULL DEFAULT '',
  `contact` varchar(20) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `library_id`, `role`, `firstname`, `lastname`, `email`, `password`, `rfid`, `contact`, `profile_image`, `created_at`, `updated_at`) VALUES
(1, '20-0035', 'student', 'Ranzel', 'Lodor', 'Ranzellodor@trimexcolleges.edu.ph', '$2y$10$aPh9lOdLJqBFYrP7gPgFm.jnPLh65G24ixjD1VNwW3SVpkcYn6fsK', 'RFID12345', '0913654872', NULL, '2025-02-14 02:16:44', '2025-02-14 02:16:44'),
(2, '21-3542', 'student', 'Janela', 'Mitra', 'janelamitra@trimexcolleges.edu.ph', '$2y$10$O0gUiEf2qbLYbhDB4aFPEeXIGHEwUP/XIeRRjGQlaFS5uucWLiSRa', 'RFID67890', '09185575592', 'uploads/profile_67b7e132c8af6.jpg', '2025-02-14 04:29:52', '2025-02-21 02:13:06'),
(3, '18-2736', 'teacher', 'Ericka', 'Pangilinan', 'Erickapangilinan@trimexcolleges.edu.ph', '$2y$10$PULbPOs8Boc8yw5LPn1ymeCwsqxjUUDWSn6Yv3g9TvN6Zanp4UUnO', 'RFID112234', '09635224326', NULL, '2025-02-20 00:23:42', '2025-06-26 02:02:26'),
(4, '23-0813', 'student', 'Student', '1', 'student1@trimexcolleges.edu.ph', '$2y$10$GuI82HW28wypKUyH9GAg/.t.DOCGouOtIMIJ8NsZU663AXpjhnmgC', 'RFID685cba2234dee', 'N/A', 'uploads/profile_685cbcd35644c0.50221078.jpg', '2025-06-26 03:10:26', '2025-06-26 03:25:59');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`attendance_id`),
  ADD KEY `fk_attendance_user` (`user_id`);

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`book_id`),
  ADD UNIQUE KEY `accession_no` (`accession_no`);

--
-- Indexes for table `book_transactions`
--
ALTER TABLE `book_transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `fk_booktransactions_user` (`user_id`),
  ADD KEY `fk_booktransactions_book` (`book_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`);

--
-- Indexes for table `password_requests`
--
ALTER TABLE `password_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `fk_request_user` (`user_id`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`reservation_id`),
  ADD KEY `fk_reservation_user` (`user_id`),
  ADD KEY `fk_reservation_book` (`book_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `library_id` (`library_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `rfid` (`rfid`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `attendance_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `book_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=235;

--
-- AUTO_INCREMENT for table `book_transactions`
--
ALTER TABLE `book_transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `password_requests`
--
ALTER TABLE `password_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `reservation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `fk_attendance_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `book_transactions`
--
ALTER TABLE `book_transactions`
  ADD CONSTRAINT `fk_booktransactions_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_booktransactions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `password_requests`
--
ALTER TABLE `password_requests`
  ADD CONSTRAINT `fk_request_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `fk_reservation_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_reservation_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
