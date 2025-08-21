-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 21, 2025 at 06:43 AM
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
-- Database: `water_monitoring`
--

-- --------------------------------------------------------

--
-- Table structure for table `refilling_stations`
--

CREATE TABLE `refilling_stations` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `device_sensor_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `refilling_stations`
--

INSERT INTO `refilling_stations` (`id`, `name`, `location`, `device_sensor_id`) VALUES
(9, 'Adrian\'s Water Refilling Station', 'Anao, Cabagan, Isabela', 'WQMS-DEV-00123'),
(10, 'Clarise\'s H2O', 'Casibarag Sur, Cabagan, Isabela', 'FKDS-LPV-028435'),
(11, 'Jericho\'s Water Market', 'Cubag, Cabagan, Isabela', 'AHSD-JWD-14825'),
(13, 'Juan\'s Water Station', 'Catabayungan, Cabagan, Isabela', 'SAKX-SFG-34632');

-- --------------------------------------------------------

--
-- Table structure for table `station_autotest_settings`
--

CREATE TABLE `station_autotest_settings` (
  `station_id` int(11) NOT NULL,
  `mode` varchar(10) NOT NULL,
  `interval_hours` int(11) DEFAULT NULL,
  `interval_days` int(11) DEFAULT NULL,
  `interval_months` int(11) DEFAULT NULL,
  `day_of_month` int(11) DEFAULT NULL,
  `time_of_day` time DEFAULT NULL,
  `enabled` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fullname` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `number` varchar(255) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `password`, `email`, `number`) VALUES
(1, 'admin', '240be518fabd2724ddb6f04eeb1da5967448d7e831c08c8fa822809f74c720a9', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `water_data`
--

CREATE TABLE `water_data` (
  `id` int(11) NOT NULL,
  `station_id` int(11) DEFAULT NULL,
  `ph_level` float DEFAULT NULL,
  `turbidity` float DEFAULT NULL,
  `temperature` float DEFAULT NULL,
  `timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `refilling_stations`
--
ALTER TABLE `refilling_stations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `station_autotest_settings`
--
ALTER TABLE `station_autotest_settings`
  ADD PRIMARY KEY (`station_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `water_data`
--
ALTER TABLE `water_data`
  ADD PRIMARY KEY (`id`),
  ADD KEY `station_id` (`station_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `refilling_stations`
--
ALTER TABLE `refilling_stations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `water_data`
--
ALTER TABLE `water_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `water_data`
--
ALTER TABLE `water_data`
  ADD CONSTRAINT `water_data_ibfk_1` FOREIGN KEY (`station_id`) REFERENCES `refilling_stations` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
