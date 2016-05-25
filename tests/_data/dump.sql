-- phpMyAdmin SQL Dump
-- version 4.7.0-dev
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: May 25, 2016 at 05:26 PM
-- Server version: 5.5.47-MariaDB-1ubuntu0.14.04.1
-- PHP Version: 5.6.20-1+deb.sury.org~trusty+1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `phanbook`
--

-- --------------------------------------------------------

--
-- Table structure for table `subscriptions`
--

CREATE TABLE `subscriptions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `stripe_id` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `stripe_plan` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `quantity` int(11) NOT NULL,
  `trial_ends_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `ends_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `uuid` varchar(200) NOT NULL,
  `username` varchar(50) CHARACTER SET utf8 NOT NULL,
  `firstname` varchar(100) CHARACTER SET utf8 NOT NULL,
  `lastname` varchar(100) CHARACTER SET utf8 NOT NULL,
  `email` varchar(100) CHARACTER SET utf8 NOT NULL,
  `password` varchar(200) CHARACTER SET utf8 NOT NULL,
  `register_hash` varchar(200) CHARACTER SET utf8 DEFAULT NULL,
  `passwd_forgot_hash` varchar(200) CHARACTER SET utf8 DEFAULT NULL,
  `status` char(1) CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  `card_last_four` varchar(255) DEFAULT NULL,
  `card_brand` varchar(255) DEFAULT NULL,
  `stripe_id` varchar(255) DEFAULT NULL,
  `trial_ends_at` timestamp NULL DEFAULT NULL,
  `created` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `uuid`, `username`, `firstname`, `lastname`, `email`, `password`, `register_hash`, `passwd_forgot_hash`, `status`, `card_last_four`, `card_brand`, `stripe_id`, `trial_ends_at`, `created`) VALUES
(1, '11--222', 'phanbook', 'Thien', 'Tran', 'hello@phanbook.com', '$2a$12$wCpQq8iqKlKhFdwh7SgKVeEmjkuriZYje20RKq5/lN3HuNKAvb.i2', NULL, NULL, '1', '4242', 'Visa', NULL, NULL, 0),
(2, '4cea558b-6455-421a-98a3-5705182157e4', 'fcduythien@gmail.com', 'ddj', 'fc', 'fcduythien@gmail.com', '$2a$12$taYvfYIFExYHpigMztO9Uup4/JBNnA5iUr1Pq4mcZMMcyd2vbi6Mq', '0e31511b12f342fcac58547e723a8282', NULL, '3', NULL, NULL, NULL, NULL, 0),
(6, 'eb4ebed7-a57c-4bf6-9fbe-ef7da89fd2c0', 'admin', 'Thien', 'Tran', 'avc@gmail.com', '$2a$12$hRrz3gNegnQ6g4qHss5IiekdBBDAgaCagQtax3dmqW7k11.L.BHpa', '1d024e0fd52710257477a600cf73dc26', NULL, '1', NULL, NULL, NULL, NULL, 0),
(7, '2bc205a2-b1b6-4483-9351-c7944532a820', 'google', 'Thien', 'Tran', 'fcduythien@gmail.com', '$2y$12$Fcz35XriokNf88iswhVd0.hbhv4nwVrYENnTNsJGzzVxOdZ1h3ILm', '21d0857e552b65c8acb68e91d5be5d88', NULL, '3', NULL, NULL, NULL, NULL, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
