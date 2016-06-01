-- phpMyAdmin SQL Dump
-- version 4.1.14
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Jun 01, 2016 at 07:08 PM
-- Server version: 5.6.17
-- PHP Version: 5.5.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `decisions`
--

-- --------------------------------------------------------

--
-- Table structure for table `agreements`
--

CREATE TABLE IF NOT EXISTS `agreements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `negotiations_users_id` int(11) NOT NULL,
  `points` float NOT NULL,
  `option_id` int(11) NOT NULL,
  `negotiation_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `agreements`
--

INSERT INTO `agreements` (`id`, `negotiations_users_id`, `points`, `option_id`, `negotiation_id`) VALUES
(1, 6, -0.19, 4, 1),
(2, 4, 0.43, 4, 1),
(3, 11, 0.58, 25, 4),
(4, 10, 0.4, 25, 4);

-- --------------------------------------------------------

--
-- Table structure for table `experiments`
--

CREATE TABLE IF NOT EXISTS `experiments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `starting_time` datetime NOT NULL,
  `ending_time` datetime NOT NULL,
  `template_negotiations` int(11) NOT NULL,
  `number_of_instances` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `experiments`
--

INSERT INTO `experiments` (`id`, `title`, `starting_time`, `ending_time`, `template_negotiations`, `number_of_instances`) VALUES
(1, 'Drinks', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 3),
(2, 'Drinks', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 2),
(3, 'Football', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 2);

-- --------------------------------------------------------

--
-- Table structure for table `negotiations`
--

CREATE TABLE IF NOT EXISTS `negotiations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `starting_time` datetime DEFAULT NULL,
  `ending_time` datetime DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `title` varchar(255) NOT NULL,
  `settings` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=8 ;

--
-- Dumping data for table `negotiations`
--

INSERT INTO `negotiations` (`id`, `starting_time`, `ending_time`, `active`, `title`, `settings`) VALUES
(1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 'Drinks (0)', 0),
(2, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'Drinks (1)', 0),
(3, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'Drinks (2)', 0),
(4, '1899-11-30 00:00:00', '1899-11-30 00:00:00', 0, 'Drinks (4)', 0),
(5, '1899-11-30 00:00:00', '1899-11-30 00:00:00', 1, 'Drinks (3)', 0),
(6, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'Football (0)', 0),
(7, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'Football (1)', 0);

-- --------------------------------------------------------

--
-- Table structure for table `negotiations_users`
--

CREATE TABLE IF NOT EXISTS `negotiations_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `negotiation_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=14 ;

--
-- Dumping data for table `negotiations_users`
--

INSERT INTO `negotiations_users` (`id`, `negotiation_id`, `user_id`) VALUES
(4, 1, 1),
(5, 2, 1),
(6, 1, 5),
(7, 2, 6),
(8, 3, 5),
(9, 3, 6),
(10, 4, 1),
(11, 4, 5),
(12, 6, 5),
(13, 6, 6);

-- --------------------------------------------------------

--
-- Table structure for table `offers`
--

CREATE TABLE IF NOT EXISTS `offers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `negotiation_id` int(11) DEFAULT NULL,
  `negotiations_users_id` bigint(20) DEFAULT NULL,
  `option_id` bigint(20) DEFAULT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=78 ;

--
-- Dumping data for table `offers`
--

INSERT INTO `offers` (`id`, `negotiation_id`, `negotiations_users_id`, `option_id`, `time`) VALUES
(1, 1, 4, 2, '2016-02-11 20:49:04'),
(2, 1, 4, 5, '2016-02-11 20:49:05'),
(3, 1, 4, 1, '2016-02-11 20:49:05'),
(4, 1, 4, 2, '2016-02-11 20:49:06'),
(5, 1, 4, 3, '2016-02-11 20:49:07'),
(6, 1, 4, 4, '2016-02-11 20:49:08'),
(7, 1, 4, 3, '2016-02-11 20:49:08'),
(8, 1, 4, 2, '2016-02-11 20:49:08'),
(9, 1, 4, 5, '2016-02-11 20:49:09'),
(10, 1, 4, 1, '2016-02-11 20:49:09'),
(11, 1, 4, 5, '2016-02-11 20:49:10'),
(12, 1, 4, 2, '2016-02-11 20:49:10'),
(13, 1, 4, 3, '2016-02-11 20:49:10'),
(14, 1, 4, 3, '2016-02-11 20:49:11'),
(15, 1, 4, 4, '2016-02-11 20:49:12'),
(16, 1, 4, 3, '2016-02-11 20:49:13'),
(17, 1, 4, 2, '2016-02-11 20:49:13'),
(18, 1, 4, 5, '2016-02-11 20:49:14'),
(19, 1, 4, 1, '2016-02-11 20:49:14'),
(20, 1, 4, 2, '2016-02-11 20:49:15'),
(21, 1, 4, 5, '2016-02-11 20:49:16'),
(22, 1, 4, 3, '2016-02-11 20:49:16'),
(23, 1, 4, 4, '2016-02-11 20:49:17'),
(24, 1, 4, 2, '2016-02-11 20:49:17'),
(25, 1, 4, 5, '2016-02-11 20:49:18'),
(26, 1, 4, 1, '2016-02-11 20:49:18'),
(27, 1, 4, 2, '2016-02-11 20:57:33'),
(28, 1, 4, 2, '2016-02-11 20:57:40'),
(29, 1, 4, 5, '2016-02-11 20:57:47'),
(30, 1, 6, 4, '2016-02-11 20:58:05'),
(31, 1, 6, 3, '2016-02-11 20:58:26'),
(32, 1, 6, 4, '2016-02-11 21:02:20'),
(33, 1, 6, 2, '2016-02-11 21:02:54'),
(34, 1, 6, 3, '2016-02-11 21:02:56'),
(35, 1, 6, 3, '2016-02-11 21:03:04'),
(36, 1, 6, 2, '2016-02-11 21:03:06'),
(37, 1, 4, 1, '2016-02-11 21:06:59'),
(38, 1, 6, 3, '2016-02-11 21:10:39'),
(39, 1, 6, 4, '2016-02-11 21:11:57'),
(40, 1, 6, 2, '2016-02-11 21:12:03'),
(41, 1, 6, 4, '2016-02-11 21:54:41'),
(42, 1, 6, 3, '2016-02-11 21:54:44'),
(43, 1, 6, 5, '2016-02-11 21:54:47'),
(44, 1, 6, 2, '2016-02-11 21:54:48'),
(45, 1, 6, 4, '2016-02-11 21:54:49'),
(46, 1, 4, 2, '2016-02-11 21:54:50'),
(47, 1, 4, 5, '2016-02-11 21:54:51'),
(48, 1, 4, 1, '2016-02-11 21:54:52'),
(49, 1, 4, 3, '2016-02-11 21:54:53'),
(50, 1, 4, 5, '2016-02-11 21:54:57'),
(51, 1, 4, 2, '2016-02-11 21:54:58'),
(52, 1, 4, 5, '2016-02-19 18:24:13'),
(53, 1, 4, 1, '2016-02-19 18:24:14'),
(54, 1, 4, 2, '2016-02-19 18:24:16'),
(55, 1, 4, 5, '2016-02-20 17:52:32'),
(56, 1, 4, 1, '2016-02-20 17:52:37'),
(57, 1, 4, 5, '2016-02-20 17:52:37'),
(58, 1, 4, 2, '2016-02-20 17:52:38'),
(59, 1, 4, 4, '2016-02-20 17:52:40'),
(60, 4, 10, 26, '2016-02-20 22:42:03'),
(61, 4, 11, 24, '2016-02-20 22:42:10'),
(62, 4, 11, 23, '2016-02-20 22:42:11'),
(63, 4, 11, 22, '2016-02-20 22:42:12'),
(64, 4, 11, 23, '2016-02-20 22:42:20'),
(65, 4, 10, 25, '2016-02-20 22:42:22'),
(66, 4, 11, 25, '2016-02-20 22:42:45'),
(67, 2, 5, 11, '2016-04-06 05:44:55'),
(68, 2, 7, 9, '2016-04-06 05:44:59'),
(69, 2, 7, 12, '2016-04-06 05:45:02'),
(70, 2, 7, 10, '2016-04-06 05:45:03'),
(71, 2, 5, 8, '2016-04-06 05:45:27'),
(72, 2, 7, 12, '2016-04-06 05:46:20'),
(73, 6, 13, 36, '2016-05-25 22:26:36'),
(74, 6, 13, 35, '2016-05-25 22:26:38'),
(75, 6, 13, 34, '2016-05-25 22:26:39'),
(76, 6, 13, 37, '2016-05-25 22:26:40'),
(77, 6, 13, 38, '2016-05-25 22:26:41');

-- --------------------------------------------------------

--
-- Table structure for table `options`
--

CREATE TABLE IF NOT EXISTS `options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `agreement_action` text NOT NULL,
  `negotiation_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=46 ;

--
-- Dumping data for table `options`
--

INSERT INTO `options` (`id`, `title`, `agreement_action`, `negotiation_id`) VALUES
(1, 'Coffee', '', 1),
(2, 'Tea', '', 1),
(3, 'Soda', '', 1),
(4, 'Milk', '', 1),
(5, 'Juice', '', 1),
(8, 'Coffee', '', 2),
(9, 'Tea', '', 2),
(10, 'Soda', '', 2),
(11, 'Milk', '', 2),
(12, 'Juice', '', 2),
(15, 'Coffee', '', 3),
(16, 'Tea', '', 3),
(17, 'Soda', '', 3),
(18, 'Milk', '', 3),
(19, 'Juice', '', 3),
(22, 'Coffee', '', 4),
(23, 'Tea', '', 4),
(24, 'Soda', '', 4),
(25, 'Milk', '', 4),
(26, 'Juice', '', 4),
(29, 'Coffee', '', 5),
(30, 'Tea', '', 5),
(31, 'Soda', '', 5),
(32, 'Milk', '', 5),
(33, 'Juice', '', 5),
(34, 'Coffee', '', 6),
(35, 'Tea', '', 6),
(36, 'Soda', '', 6),
(37, 'Milk', '', 6),
(38, 'Juice', '', 6),
(41, 'Coffee', '', 7),
(42, 'Tea', '', 7),
(43, 'Soda', '', 7),
(44, 'Milk', '', 7),
(45, 'Juice', '', 7);

-- --------------------------------------------------------

--
-- Table structure for table `preferences`
--

CREATE TABLE IF NOT EXISTS `preferences` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `negotiations_users_id` int(11) DEFAULT NULL,
  `option_id` int(11) DEFAULT NULL,
  `value` float(10,5) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=41 ;

--
-- Dumping data for table `preferences`
--

INSERT INTO `preferences` (`id`, `negotiations_users_id`, `option_id`, `value`) VALUES
(1, 4, 1, 0.75000),
(2, 4, 2, 0.62000),
(3, 4, 3, 0.27000),
(4, 4, 4, 0.08000),
(5, 4, 5, 0.73000),
(6, 6, 1, 0.20000),
(7, 6, 2, 0.30000),
(8, 6, 3, 0.76000),
(9, 6, 4, 0.63000),
(10, 6, 5, 0.25000),
(11, 11, 22, 1.00000),
(12, 11, 23, 0.88000),
(13, 11, 24, 0.73000),
(14, 11, 25, 0.51000),
(15, 11, 26, 0.11000),
(16, 10, 22, 0.09000),
(17, 10, 23, 0.28000),
(18, 10, 24, 0.50000),
(19, 10, 25, 0.67000),
(20, 10, 26, 0.83000),
(21, 5, 8, 0.80000),
(22, 5, 9, 0.67000),
(23, 5, 10, 0.84000),
(24, 5, 11, 0.91000),
(25, 5, 12, 0.19000),
(26, 7, 8, 0.97000),
(27, 7, 9, 0.10000),
(28, 7, 10, 0.27000),
(29, 7, 11, 0.83000),
(30, 7, 12, 0.06000),
(31, 12, 34, 0.70000),
(32, 12, 35, 0.50000),
(33, 12, 36, 0.60000),
(34, 12, 37, 0.63000),
(35, 12, 38, 1.00000),
(36, 13, 34, 0.61000),
(37, 13, 35, 0.71000),
(38, 13, 36, 0.87000),
(39, 13, 37, 0.60000),
(40, 13, 38, 0.50000);

-- --------------------------------------------------------

--
-- Table structure for table `template_negotiations`
--

CREATE TABLE IF NOT EXISTS `template_negotiations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `starting_time` datetime DEFAULT NULL,
  `ending_time` datetime DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `title` varchar(255) NOT NULL,
  `settings` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `template_negotiations`
--

INSERT INTO `template_negotiations` (`id`, `starting_time`, `ending_time`, `active`, `title`, `settings`) VALUES
(1, NULL, NULL, 1, 'Drinks', 0);

-- --------------------------------------------------------

--
-- Table structure for table `template_options`
--

CREATE TABLE IF NOT EXISTS `template_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `agreement_action` text NOT NULL,
  `negotiation_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

--
-- Dumping data for table `template_options`
--

INSERT INTO `template_options` (`id`, `title`, `agreement_action`, `negotiation_id`) VALUES
(1, 'Coffee', '', 1),
(2, 'Tea', '', 1),
(3, 'Soda', '', 1),
(4, 'Milk', '', 1),
(5, 'Juice', '', 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `login`, `password`, `first_name`, `last_name`) VALUES
(1, 'user1', '827ccb0eea8a706c4c34a16891f84e7b', 'Dmitry', 'Gimon'),
(2, 'user2', '827ccb0eea8a706c4c34a16891f84e7b', 'John', 'James'),
(4, 'user3', '827ccb0eea8a706c4c34a16891f84e7b', 'Jessica', 'Johnes'),
(5, 'user4', '827ccb0eea8a706c4c34a16891f84e7b', 'Billy', 'Jones'),
(6, 'michelle', '827ccb0eea8a706c4c34a16891f84e7b', 'Michelle', 'Lin');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
