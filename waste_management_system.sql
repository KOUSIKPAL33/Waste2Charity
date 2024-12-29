-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 28, 2024 at 02:00 PM
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
-- Database: `waste_management_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(10) NOT NULL,
  `name` varchar(50) NOT NULL,
  `userid` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `name`, `userid`, `password`) VALUES
(1, 'kousikadmin', 'kadmin', 'kadmin');

-- --------------------------------------------------------

--
-- Table structure for table `cloth`
--

CREATE TABLE `cloth` (
  `id` int(11) NOT NULL,
  `location` varchar(255) NOT NULL,
  `type_of_cloth` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `submitted_at` datetime NOT NULL DEFAULT current_timestamp(),
  `approval_datetime` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `cloth`
--

INSERT INTO `cloth` (`id`, `location`, `type_of_cloth`, `quantity`, `name`, `mobile`, `user_id`, `status`, `submitted_at`, `approval_datetime`) VALUES
(5, '1.8k hostel', 'shirt', 2, 'Krishna Murari', '54564544', 4, 'pending', '2024-11-26 15:29:39', NULL),
(6, '1.8k hostel', 'pant', 10, 'Kousik pal', '7602783633', 8, 'granted', '2024-11-28 18:21:07', '2024-11-29 22:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `cloth_requests`
--

CREATE TABLE `cloth_requests` (
  `id` int(11) NOT NULL,
  `type_of_cloth` varchar(255) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `approval_datetime` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `cloth_requests`
--

INSERT INTO `cloth_requests` (`id`, `type_of_cloth`, `quantity`, `user_id`, `requested_at`, `status`, `approval_datetime`) VALUES
(1, 'shirt', 2, 1, '2024-11-27 20:43:53', 'pending', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `food`
--

CREATE TABLE `food` (
  `id` int(11) NOT NULL,
  `location` varchar(255) NOT NULL,
  `type_of_food` enum('veg','non-veg') NOT NULL,
  `food_category` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `submitted_at` datetime NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `user_id` int(11) NOT NULL,
  `approval_datetime` datetime DEFAULT NULL,
  `completed` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `food`
--

INSERT INTO `food` (`id`, `location`, `type_of_food`, `food_category`, `quantity`, `name`, `mobile`, `submitted_at`, `status`, `user_id`, `approval_datetime`, `completed`) VALUES
(13, 'IFC_B', 'non-veg', 'cooked food', 10, 'ifc_b manager', '541654854', '2024-11-27 01:40:03', 'granted', 4, '2024-11-29 07:30:00', 0),
(14, 'Jaipur', 'non-veg', 'packed food', 100, 'Rathore Rajdeep', '760278363', '2024-11-27 22:09:03', 'granted', 5, '2024-11-28 22:14:00', 0),
(15, 'ifc-b mess', 'veg', 'cooked food', 30, 'Kousik Pal', '7602783633', '2024-11-28 18:20:36', 'rejected', 8, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `food_requests`
--

CREATE TABLE `food_requests` (
  `id` int(11) NOT NULL,
  `food_type` varchar(255) DEFAULT NULL,
  `food_category` varchar(255) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `approval_datetime` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `food_requests`
--

INSERT INTO `food_requests` (`id`, `food_type`, `food_category`, `quantity`, `user_id`, `requested_at`, `status`, `approval_datetime`) VALUES
(7, 'veg', 'cooked food', 20, 1, '2024-11-26 09:57:38', 'granted', '2024-11-28 21:15:00'),
(9, 'non-veg', 'packed food', 500, 1, '2024-11-27 16:41:32', 'rejected', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `ngo`
--

CREATE TABLE `ngo` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `userid` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `mobileno` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `ngo`
--

INSERT INTO `ngo` (`id`, `name`, `userid`, `password`, `mobileno`) VALUES
(1, 'koushik', 'kngo', 'kngo', ''),
(3, 'soumik', 'sngo123@gmail.com', 'sngo', '7412589630');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) NOT NULL,
  `name` varchar(50) NOT NULL,
  `userid` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `mobileno` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `userid`, `password`, `mobileno`) VALUES
(3, 'soumik pal', 'spal123@gmail.com', 'spal', '9932382643'),
(4, 'Krishna Murari', 'kmxxx@gmail.com', 'kmak', '41545646545'),
(5, 'Rathore', 'rr123@gmail.com', 'rrrr', '123789456'),
(8, 'Kousik Pal', 'kousikpal652@gmail.com', 'Kousik@123', '7602783633');

-- --------------------------------------------------------

--
-- Table structure for table `vehicles`
--

CREATE TABLE `vehicles` (
  `id` int(5) NOT NULL,
  `drivername` varchar(100) NOT NULL,
  `mobileno` varchar(20) NOT NULL,
  `carno` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `vehicles`
--

INSERT INTO `vehicles` (`id`, `drivername`, `mobileno`, `carno`) VALUES
(1, 'Sunil nandi', '1234567890', 'WB 23A 1234');

-- --------------------------------------------------------

--
-- Table structure for table `waste`
--

CREATE TABLE `waste` (
  `id` int(11) NOT NULL,
  `location` varchar(255) NOT NULL,
  `type_of_waste` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `user_id` int(11) NOT NULL,
  `submitted_at` datetime NOT NULL DEFAULT current_timestamp(),
  `approval_datetime` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `waste`
--

INSERT INTO `waste` (`id`, `location`, `type_of_waste`, `quantity`, `name`, `mobile`, `status`, `user_id`, `submitted_at`, `approval_datetime`) VALUES
(3, '1.8k hostel', 'inorganic,household', 10, 'Soumilk Pal', '7602783633', 'pending', 4, '2024-11-27 01:31:52', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `workers`
--

CREATE TABLE `workers` (
  `id` int(5) NOT NULL,
  `name` varchar(100) NOT NULL,
  `mobileno` varchar(20) NOT NULL,
  `Address` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `workers`
--

INSERT INTO `workers` (`id`, `name`, `mobileno`, `Address`) VALUES
(1, 'Bishnu Kundu', '1234567890', 'jofhaer;oyhvjnvou;gvhgjmcvhojsdf'),
(2, 'subham pal', '1234567890', 'sryjtymktjtiuei6ij6fghk9;;\'/gf');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cloth`
--
ALTER TABLE `cloth`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cloth_requests`
--
ALTER TABLE `cloth_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `food`
--
ALTER TABLE `food`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `food_requests`
--
ALTER TABLE `food_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ngo`
--
ALTER TABLE `ngo`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `waste`
--
ALTER TABLE `waste`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `workers`
--
ALTER TABLE `workers`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `cloth`
--
ALTER TABLE `cloth`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `cloth_requests`
--
ALTER TABLE `cloth_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `food`
--
ALTER TABLE `food`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `food_requests`
--
ALTER TABLE `food_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `ngo`
--
ALTER TABLE `ngo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `id` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `waste`
--
ALTER TABLE `waste`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `workers`
--
ALTER TABLE `workers`
  MODIFY `id` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
