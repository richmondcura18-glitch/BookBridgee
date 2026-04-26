-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql208.infinityfree.com
-- Generation Time: Apr 26, 2026 at 11:17 AM
-- Server version: 11.4.10-MariaDB
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
-- Database: `if0_41354514_bookbride_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
(1, 'eBook'),
(2, 'Reviewer'),
(3, 'Notes'),
(5, 'Video');

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `resource_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `favorites`
--

INSERT INTO `favorites` (`id`, `user_id`, `resource_id`) VALUES
(2, 8, 51),
(3, 7, 55),
(4, 10, 50),
(7, 12, 55),
(9, 12, 53),
(10, 12, 52),
(11, 12, 51);

-- --------------------------------------------------------

--
-- Table structure for table `reading_status`
--

CREATE TABLE `reading_status` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `resource_id` int(11) NOT NULL,
  `status` enum('not_started','reading','completed') DEFAULT 'reading',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `reading_status`
--

INSERT INTO `reading_status` (`id`, `user_id`, `resource_id`, `status`, `updated_at`) VALUES
(1, 7, 56, 'reading', '2026-04-05 14:35:14'),
(2, 7, 25, 'reading', '2026-04-05 14:38:50'),
(3, 10, 50, 'completed', '2026-04-05 15:14:32'),
(4, 11, 50, 'completed', '2026-04-05 21:31:20'),
(5, 11, 49, 'completed', '2026-04-05 21:31:22'),
(6, 11, 55, 'reading', '2026-04-05 21:31:27'),
(7, 11, 56, 'not_started', '2026-04-05 21:39:25'),
(8, 11, 30, 'reading', '2026-04-05 23:14:45'),
(9, 11, 28, 'reading', '2026-04-05 23:14:51'),
(10, 12, 55, 'reading', '2026-04-06 00:48:35'),
(11, 12, 52, 'completed', '2026-04-06 00:48:38'),
(12, 1, 30, 'completed', '2026-04-13 01:53:57'),
(13, 1, 56, 'reading', '2026-04-13 02:00:57');

-- --------------------------------------------------------

--
-- Table structure for table `resources`
--

CREATE TABLE `resources` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `subject` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `author` varchar(100) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `uploaded_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `video_url` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `resources`
--

INSERT INTO `resources` (`id`, `title`, `subject`, `description`, `author`, `category`, `file_path`, `uploaded_by`, `created_at`, `video_url`) VALUES
(25, 'Factoring Perfect Square Trinomials', 'Algebra', 'Lesson in algebra', NULL, 'eBook', '', NULL, '2026-03-21 05:44:15', 'fbrpc://nativethirdparty/f?app_id=256002347743983&app_name=Facebook+Messenger+for+Android&tap_behavior=web_always&target_url=https%3A%2F%2Fyoutu.be%2Ff6yhfmW41wI%3Fsi%3DSJzYl55qge1JbIKB%26fbclid%3DIwZXh0bgNhZW0CMTEAc3J0YwZhcHBfaWQPNDM3NjI2MzE2OTczNzg4AAEe'),
(23, 'Factoring Binomials - Sum & Difference of Perfect Squares', 'Algebra', 'Lesson in algebra', NULL, 'eBook', '', NULL, '2026-03-21 05:42:12', 'https://youtu.be/6QQJoDshUt8?si=SlMHKwVtVF6j-DYV'),
(24, 'Special Products - Square of Binomials (Multiplying Polynomials)', 'Algebra', 'Lesson in algebra', NULL, 'eBook', '', NULL, '2026-03-21 05:43:34', 'https://youtu.be/Ul6x99bTSGA?si=nFIMDUT2O_2JJm6c'),
(21, 'Algebra - How to Solve Equations Quickly', 'Algebra', 'Lesson in algebra', NULL, 'eBook', '', NULL, '2026-03-21 05:40:03', 'https://youtu.be/FxHWoUOq2iQ?si=J-QdE3EU3n1fcYTe'),
(19, 'How to Solve Number Problems? | Civil Servicr Exam - LET - SAT - PUPCET', 'Algebra', 'Lesson in algebra', NULL, 'eBook', '', NULL, '2026-03-21 05:37:43', 'https://youtu.be/KC-ylJeSA5c?si=B9mB4oit3Dl7iGXu'),
(20, 'Solving Systems of Equations by Elimination & Substitution With 2 Variables', 'Algebra', 'Lesson in algebra', NULL, 'eBook', '', NULL, '2026-03-21 05:38:55', 'fbrpc://nativethirdparty/f?app_id=256002347743983&app_name=Facebook+Messenger+for+Android&tap_behavior=web_always&target_url=https%3A%2F%2Fyoutu.be%2F3UXnvVhbCG0%3Fsi%3Dsty_WyUKbca6sPqL%26fbclid%3DIwZXh0bgNhZW0CMTEAc3J0YwZhcHBfaWQPNDM3NjI2MzE2OTczNzg4AAEe'),
(26, 'How to Factor & Solve Quadratic Trinomials', 'Algebra', 'Lesson in algebra', NULL, 'eBook', '', NULL, '2026-03-21 05:44:36', 'https://youtu.be/Ul6x99bTSGA?si=nFIMDUT2O_2JJm6c'),
(27, 'Factoring Binomials & Trinomials - Special Cases', 'Algebra', 'Lesson in algebra', NULL, 'eBook', '', NULL, '2026-03-21 05:46:49', 'fbrpc://nativethirdparty/f?app_id=256002347743983&app_name=Facebook+Messenger+for+Android&tap_behavior=web_always&target_url=https%3A%2F%2Fyoutu.be%2FNOLUnoMyE5k%3Fsi%3DRUYqueWVlps1n2R_%26fbclid%3DIwZXh0bgNhZW0CMTEAc3J0YwZhcHBfaWQPNDM3NjI2MzE2OTczNzg4AAEe'),
(28, 'Synthetic Division of Polynomials', 'Algebra', 'Lesson in algebra', NULL, 'eBook', '', NULL, '2026-03-21 05:48:52', 'fbrpc://nativethirdparty/f?app_id=256002347743983&app_name=Facebook+Messenger+for+Android&tap_behavior=web_always&target_url=https%3A%2F%2Fyoutu.be%2FZ-ZkmpQBIFo%3Fsi%3Dacpl7mDS9bkxM6cm%26fbclid%3DIwZXh0bgNhZW0CMTEAc3J0YwZhcHBfaWQPNDM3NjI2MzE2OTczNzg4AAEe'),
(29, 'How to Evaluate Algebraic Expression: Step by step Guide by Teacher Gon', 'Algebra', 'Lesson in algebra', NULL, 'eBook', '', NULL, '2026-03-21 05:50:04', 'https://youtu.be/oKqtgz2eo-Y?si=WvL6aGtSsTr-qW__'),
(30, 'Removal of Grouping Symbols', 'Algebra', 'Lesson in algebra', NULL, 'eBook', '', NULL, '2026-03-21 05:50:47', 'fbrpc://nativethirdparty/f?app_id=256002347743983&app_name=Facebook+Messenger+for+Android&tap_behavior=web_always&target_url=https%3A%2F%2Fyoutu.be%2F95dGkfHGWT0%3Fsi%3D-4QByKd89fE8NXf3%26fbclid%3DIwZXh0bgNhZW0CMTEAc3J0YwZhcHBfaWQPNDM3NjI2MzE2OTczNzg4AAEe'),
(31, 'JS Lesson 1', 'Web System', 'Lesson in web system', NULL, 'Reviewer', 'uploads/1774073602_inbound8870393623145311073.pdf', NULL, '2026-03-21 06:13:21', ''),
(32, 'CSS Fonts', 'Web System', 'Lesson', NULL, 'Reviewer', 'uploads/1774073641_inbound8679762381990156782.pdf', NULL, '2026-03-21 06:14:01', ''),
(33, 'CSS Texts', 'Web System', 'Lesson', NULL, 'Reviewer', 'uploads/1774073733_inbound1943300867629511368.pdf', NULL, '2026-03-21 06:15:32', ''),
(34, 'CSS Outline Offsets', 'Web System', 'Lesson', NULL, 'Reviewer', 'uploads/1774073807_inbound6025391136628017851.pdf', NULL, '2026-03-21 06:16:46', ''),
(35, 'CSS Box Models', 'Web System', 'Lesson', NULL, 'Reviewer', 'uploads/1774073851_inbound7038362989908329897.pdf', NULL, '2026-03-21 06:17:30', ''),
(36, 'CSS Height and Width', 'Web System', 'Lesson', NULL, 'Reviewer', 'uploads/1774074010_inbound7366487041948211882.pdf', NULL, '2026-03-21 06:20:10', ''),
(37, 'CSS Paddings', 'Web System', 'Lesson', NULL, 'Reviewer', 'uploads/1774074054_inbound1341540818660679562.pdf', NULL, '2026-03-21 06:20:54', ''),
(39, 'CSS Margins', 'Web System', 'Lesson', NULL, 'Reviewer', 'uploads/1774074169_inbound2234493239669188915.pdf', NULL, '2026-03-21 06:22:49', ''),
(40, 'CSS Borders', 'Web System', 'Lesson', NULL, 'Reviewer', 'uploads/1774074212_inbound7909554153323501717.pdf', NULL, '2026-03-21 06:23:31', ''),
(41, 'CSS Backgrounds', 'Web System', 'Lesson', NULL, 'Reviewer', 'uploads/1774074237_inbound397369618163848225.pdf', NULL, '2026-03-21 06:23:57', ''),
(42, 'Adding CSS and CSS Comments', 'Web System', 'Lesson', NULL, 'Reviewer', 'uploads/1774074302_inbound7421286035949146094.pdf', NULL, '2026-03-21 06:25:01', ''),
(43, 'CSS Colors', 'Web System', 'Lesson', NULL, 'Reviewer', 'uploads/1774074331_inbound4765356363211421785.pdf', NULL, '2026-03-21 06:25:30', ''),
(44, 'Introduction to CSS', 'Web System', 'Lesson', NULL, 'Reviewer', 'uploads/1774074358_inbound7927987227680464911.pdf', NULL, '2026-03-21 06:25:57', ''),
(45, 'Strings', 'Comprog', 'Lesson', NULL, 'Reviewer', 'uploads/1774074752_inbound141076130838267483.pdf', NULL, '2026-03-21 06:32:32', ''),
(46, 'Arrays', 'Comprog', 'Lesson', NULL, 'Reviewer', 'uploads/1774074814_inbound4975256080428553355.pdf', NULL, '2026-03-21 06:33:34', ''),
(47, 'For Loops', 'Comprog', 'Lesson', NULL, 'Reviewer', 'uploads/1774074845_inbound4646970857540222049.pdf', NULL, '2026-03-21 06:34:05', ''),
(48, 'While', 'Comprog', 'Lesson', NULL, 'Reviewer', 'uploads/1774074869_inbound4085988360897123099.pdf', NULL, '2026-03-21 06:34:29', ''),
(49, 'Advance-if-else', 'Comprog', 'Lesson', NULL, 'Reviewer', 'uploads/1774074892_inbound3693333785353442291.pdf', NULL, '2026-03-21 06:34:52', ''),
(50, 'Nested-if-else', 'Comprog', 'Lesson', NULL, 'Reviewer', 'uploads/1774074919_inbound2797533868918191576.pdf', NULL, '2026-03-21 06:35:19', ''),
(51, 'Conditional', 'Comprog', 'Lesson', NULL, 'Reviewer', 'uploads/1774074945_inbound7843034997148312882.pdf', NULL, '2026-03-21 06:35:44', ''),
(52, 'Scanner', 'Comprog', 'Lesson', NULL, 'Reviewer', 'uploads/1774074969_inbound1130711835316229712.pdf', NULL, '2026-03-21 06:36:09', ''),
(53, 'Methods', 'Comprog', 'Lesson', NULL, 'Reviewer', 'uploads/1774074997_inbound1323649187060372112.pdf', NULL, '2026-03-21 06:36:37', ''),
(54, 'Expressions', 'Comprog', 'Lesson', NULL, 'Reviewer', 'uploads/1774075030_inbound2318309469846350794.pdf', NULL, '2026-03-21 06:37:10', ''),
(55, 'Introduction to Programming', 'Comprog', 'Lesson', NULL, 'Reviewer', 'uploads/1774075050_inbound7721802875372133381.pdf', NULL, '2026-03-21 06:37:30', '');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) DEFAULT NULL,
  `firstname` varchar(100) NOT NULL,
  `middlename` varchar(100) DEFAULT NULL,
  `lastname` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `birthday` date NOT NULL,
  `secret_question` varchar(255) NOT NULL,
  `secret_answer` varchar(255) NOT NULL,
  `is_admin` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `firstname`, `middlename`, `lastname`, `email`, `password`, `birthday`, `secret_question`, `secret_answer`, `is_admin`, `created_at`) VALUES
(1, NULL, 'Richmond', 'Caberr', 'Curaa', 'jhiandelossantoss@gmail.com', '$2y$10$fB0ickGxBvyO3uoog4mL5eq9Sy.MbfEov0B1bm5YRp/iEGwu76COy', '2004-11-14', 'What is your first pet\'s name?', '$2y$10$Ycg9/JiTGtxdkzadcBZ44upKPUHWQHEOs8UMLRbGN2YW54sqzAe1K', 1, '2026-03-19 07:46:53'),
(2, NULL, 'Aliyah Leen', 'Bevera', 'Maniquis', 'mcflurryyen1@gmail.com', '$2y$10$6.3CMFI1jqK4x1ZUzYKaout.VkY76j0A2ejDA/CGhgMIp4HbunNwu', '2007-05-30', 'In what city were you born?', '$2y$10$pyLnKiMl.JEg7IQWaa5a6eBXn/BmoLvY4OyG0NAmK07W0/jRJvMTi', 0, '2026-03-19 10:19:10'),
(3, NULL, 'Richmond', '', 'Cura', 'Richmondcura14@gmail.com', '$2y$10$NzMj5w8eU92sDaLhFS3gTegPuHjlsU.mBW0kLdMBZGa2o0T6rBjGC', '2004-11-14', 'What is your first pet\'s name?', '$2y$10$TNYHPQTUBfGeQgQL1BPAAeB.CsLdLQtJvZZO30ijisn.YcQPRVGdm', 0, '2026-03-20 00:57:01'),
(4, NULL, 'Aliyah', 'Bevera', 'Maniquis', 'maniquis.aliyahleen.bscs2025@gmail.com', '$2y$10$rZ/bwNKcyjdLXVc6p8IBWOkeg4xDLRQIHhHxnEUfw8cP7DwUaT2Y.', '2007-05-30', 'What is your first pet\'s name?', '$2y$10$SLYzc/viKYq7YfiULWKXTeoMqcld5AaxOWn1JU8n63KA8NMqQTGZ.', 0, '2026-03-20 03:23:41'),
(5, NULL, 'jajabz', 'jsinc', 'janao', 'kfifmao@gmail.com', '$2y$10$tCVujOA1ojIWLxzL/Dj4UOwLA4IjScL2QHGRpIEZF8UxnYh5LsFRO', '2026-03-20', 'What is your first pet\'s name?', '$2y$10$VnlZTdz14kAqSeSTW2xOseWvEFzBJs6lPJqDm5B2x0pkxzb.WNlli', 0, '2026-03-20 12:58:40'),
(6, NULL, 'Flores', 'Arellano', 'Flores', 'cristinaflores012@gmail.com', '$2y$10$EL6xaWJEcilhBWMld2TRV.rhYyanH15UqLQVDz8Emrm2ucqpTGeeO', '2007-03-23', 'What was the name of your first school?', '$2y$10$ewsxTqkfc2gRfAD7HQn61u/PVbg8lqge248IZYfH51dD8x.V18hya', 0, '2026-03-21 02:04:57'),
(7, NULL, 'Richmond', 'Caberrrr', 'Curaaa', 'richmond1010@gmail.com', '$2y$10$Kd6j6K13ctRMkwrvw3hLruCh8bCaLCm3LnbtV9oMcIaMJyFmeOQgy', '2004-11-14', 'What is your first pet\'s name?', '$2y$10$Ls/4VKcDMsygs2Hx2oED2.SwwAxE7/XQlnhJF1TpjRHrVb5kB8/42', 0, '2026-03-23 01:23:00'),
(8, NULL, 'Richmond', 'Caber', 'Cura', 'Richmond132@gmail.com', '$2y$10$03t4OP9YEvSn61gUFGjZsOEGKjDUdSMQPC06sU8aMqwlSg9Zk93Cq', '2004-11-14', 'What is your first pet\'s name?', '$2y$10$lHOOb2vH9NGPAg45pBhA6.0.pCoxgDMDzVNLjhPyb4EhImVCp3sBC', 0, '2026-03-23 01:50:07'),
(9, NULL, 'Cristina', 'Arellano', 'Flores', 'cristinaarellano012@gmail.com', '$2y$10$3lsZG90InD42cqtpP0QYbO0.pqboCjWL/bHfnTXX/m7gzUsHHaTnK', '2007-01-23', 'What is your first pet\'s name?', '$2y$10$wsxzS8LCdiNX76ePxG7.1uqhTn18x8mILM5ikbbe0YovG.SkB1gi.', 0, '2026-03-27 13:00:24'),
(10, NULL, 'Aliyah Leen', 'Bevera', 'Maniquis', 'aliyahleenm@gmail.com', '$2y$10$wWAnM.d8oygHYPUiwhLwN.6h01MM5qsWl6t4n/2viKq6iXFG6rKgu', '2026-04-05', 'In what city were you born?', '$2y$10$Mmxs8hpyjOPnV3TGpBtwjufhbu0.Vt3gKRlrb1gPt8KwOL4d8dYYq', 0, '2026-04-05 15:13:12'),
(11, NULL, 'John Joseph', NULL, 'Benito', 'johnjosephbenito10@gmail.com', '$2y$10$kiItIswDg2bLbw669h.9CuU00fQWkqW9V7jNq53DkDcv6C5nfPP5u', '2006-10-15', 'What is your first pet\'s name?', '$2y$10$9ViV7YZOPUZKw.DWV9Dc/.fKEFoY2Ih8hOxqmLHEjIkqEWXyzJNqe', 0, '2026-04-05 21:28:51'),
(12, NULL, 'Richmond', NULL, 'Cura', 'richmond1234@gmail.com', '$2y$10$APAyA3BEUul9B1fltZyc9.WPmqYfKa5K3mCLCuYF6vO2aabrJJtzO', '2004-11-14', 'What is your first pet\'s name?', '$2y$10$mjBzu3/OC/ZpT.hM6id4zOlnKWglXSsbbXzZOEo3beLGnD1mmGf4K', 0, '2026-04-06 00:45:56'),
(13, NULL, '12321', NULL, '1312321', 'Richmondcura114@gmail.com', '$2y$10$8w14qR2pHPo8Sc.5pTU3X.Df28kseaut.GyLx0Bl5oQW6woHPkP0.', '2004-11-14', 'What is your first pet\'s name?', '$2y$10$uUJXB.QNptWC5d8jKvcS8.GbbuPv6M55seDh.6K0DXVoOBGd9ktK2', 0, '2026-04-06 01:07:12');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`resource_id`);

--
-- Indexes for table `reading_status`
--
ALTER TABLE `reading_status`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_resource` (`user_id`,`resource_id`);

--
-- Indexes for table `resources`
--
ALTER TABLE `resources`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uploaded_by` (`uploaded_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `favorites`
--
ALTER TABLE `favorites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `reading_status`
--
ALTER TABLE `reading_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `resources`
--
ALTER TABLE `resources`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
