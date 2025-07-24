-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jul 24, 2025 at 07:49 PM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `art_gallery`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

DROP TABLE IF EXISTS `admin`;
CREATE TABLE IF NOT EXISTS `admin` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`) VALUES
(1, 'admin', '$2y$10$AbCdEfGhIjKlMnOpQrStUvWxYz012345678901234567890123456'),
(2, 'admin', '$2y$10$AbCdEfGhIjKlMnOpQrStUvWxYz012345678901234567890123456'),
(3, 'Vishal', '$2y$10$yTBLBxyv638oS9ncX.muqO5tSNYB9rtfP02IzLAIp448owZy.iitO');

-- --------------------------------------------------------

--
-- Table structure for table `artists`
--

DROP TABLE IF EXISTS `artists`;
CREATE TABLE IF NOT EXISTS `artists` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `artists`
--

INSERT INTO `artists` (`id`, `name`, `email`, `password`) VALUES
(7, 'madhu', 'madhupatel@gmail.com', '$2y$10$2zBVJAGqHfhCVyye9ULeAul4vlQi/f2TdKJGf52/kXMnRTlTWGGRy'),
(2, 'raj', 'raj@mail.com', '$2y$10$MTL4zN8xDP5sY7zSVB/ttO/R4DY49ia052ozZVapu3XiZau9nlrlS'),
(4, 'Ajay', 'ajay@mail.com', '$2y$10$qj80STdq9dH31cy8CfPMSeH1cn2bbqXzJb21x3/w2Wyyb1DKFlAHO'),
(5, 'HP', 'hp@mail.com', '$2y$10$3BNRNT3am9ImRXx5nnOzbuYACyMql5jOYkHnLLLVbdkARmiN2WlVO');

-- --------------------------------------------------------

--
-- Table structure for table `artworks`
--

DROP TABLE IF EXISTS `artworks`;
CREATE TABLE IF NOT EXISTS `artworks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `artist_id` int DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `description` text,
  `price` decimal(10,2) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `available` tinyint(1) DEFAULT '1',
  `type` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `artist_id` (`artist_id`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `artworks`
--

INSERT INTO `artworks` (`id`, `artist_id`, `title`, `description`, `price`, `image`, `available`, `type`) VALUES
(10, 5, 'Home', 'a man on the moon looking at Earth', 4500.00, '683dbe790623c_amanonthemoonl.jpeg', 1, 'Painting'),
(11, 5, 'Corrupted Superman', 'Destroyer', 4550.00, '683dd2fd5adf4_Corrupted Superman.png', 1, 'Painting'),
(13, 7, 'Mona Lisa', 'The Mona Lisa[a] is a half-length portrait painting by the Italian artist Leonardo da Vinci. Considered an archetypal masterpiece of the Italian Renaissance, it has been described as \"the best known, the most visited.', 8500.00, '684337aa8ce30_Mona_Lisa.jpg', 1, 'Painting'),
(8, 4, 'Deadpool', 'Deadpool vs Taskmaster', 1560.00, 'art_68397136b5349.png', 1, 'Drawing'),
(12, 5, 'Superman', 'Superman has been portrayed as being influenced or controlled by a dark force or being, often with the intent of turning him into a weapon or a threat.', 6500.00, '683dd8a86da3a_May25202510_55_52P.jpeg', 1, 'Painting');

-- --------------------------------------------------------

--
-- Table structure for table `artwork_likes`
--

DROP TABLE IF EXISTS `artwork_likes`;
CREATE TABLE IF NOT EXISTS `artwork_likes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `artwork_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_artwork_unique` (`user_id`,`artwork_id`),
  KEY `artwork_id` (`artwork_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `artwork_likes`
--

INSERT INTO `artwork_likes` (`id`, `user_id`, `artwork_id`) VALUES
(2, 2, 7);

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

DROP TABLE IF EXISTS `cart`;
CREATE TABLE IF NOT EXISTS `cart` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `artwork_id` int NOT NULL,
  `quantity` int DEFAULT '1',
  `added_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `artwork_id` (`artwork_id`)
) ENGINE=MyISAM AUTO_INCREMENT=56 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `artwork_id`, `quantity`, `added_at`) VALUES
(1, 1, 2, 1, '2025-05-27 18:20:40'),
(32, 5, 10, 1, '2025-06-02 15:10:31'),
(11, 4, 7, 1, '2025-05-30 19:45:13'),
(12, 4, 8, 1, '2025-05-30 19:45:18'),
(43, 6, 11, 1, '2025-06-02 21:25:40'),
(28, 2, 7, 1, '2025-06-02 11:57:42'),
(27, 2, 8, 1, '2025-06-02 11:57:41'),
(26, 2, 9, 1, '2025-06-02 11:57:35'),
(38, 9, 11, 1, '2025-06-02 19:29:33'),
(50, 8, 12, 1, '2025-06-02 22:43:33'),
(42, 6, 12, 1, '2025-06-02 21:25:32'),
(53, 10, 11, 1, '2025-06-06 18:41:08'),
(48, 2, 11, 1, '2025-06-02 22:04:43'),
(52, 8, 10, 1, '2025-06-02 22:46:57'),
(54, 10, 12, 1, '2025-06-06 18:41:10'),
(55, 10, 10, 1, '2025-06-06 18:41:13');

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;
CREATE TABLE IF NOT EXISTS `comments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `artwork_id` int DEFAULT NULL,
  `comment` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `artwork_id` int DEFAULT NULL,
  `shipping_address` text,
  `order_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `payment_method` varchar(50) DEFAULT 'Cash on Delivery',
  `status` varchar(50) DEFAULT 'Pending',
  `contact_number` varchar(20) DEFAULT NULL,
  `address` text,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `artwork_id` (`artwork_id`)
) ENGINE=MyISAM AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `artwork_id`, `shipping_address`, `order_date`, `payment_method`, `status`, `contact_number`, `address`) VALUES
(1, 1, 1, NULL, '2025-05-25 07:50:08', 'Cash on Delivery', 'Pending', NULL, NULL),
(2, 1, 3, NULL, '2025-05-25 13:13:20', 'Cash on Delivery', 'Pending', NULL, NULL),
(3, 2, 4, NULL, '2025-05-27 17:34:11', 'Cash on Delivery', 'Placed', NULL, NULL),
(4, 2, 5, NULL, '2025-05-27 17:34:11', 'Cash on Delivery', 'Placed', NULL, NULL),
(5, 2, 4, NULL, '2025-05-27 17:43:24', 'Cash on Delivery', 'Placed', NULL, NULL),
(6, 2, 4, NULL, '2025-05-27 20:19:04', 'COD', 'cancelled', NULL, 'Khandwa'),
(7, 2, 4, NULL, '2025-05-27 20:19:43', 'COD', 'Pending', NULL, 'Khandwa'),
(8, 3, 4, 'khaknar', '2025-05-29 11:30:25', 'Cash on Delivery', 'Pending', '8845796582', NULL),
(9, 2, 7, 'Indore', '2025-05-31 23:13:58', 'Cash on Delivery', 'cancelled', '8815882825', NULL),
(10, 2, 8, 'Indore', '2025-05-31 23:13:58', 'Cash on Delivery', 'cancelled', '8815882825', NULL),
(11, 2, 7, 'Khandwa', '2025-06-02 06:47:32', 'Cash on Delivery', 'Pending', '8815882825', NULL),
(12, 2, 9, 'Khandwa', '2025-06-02 06:47:32', 'Cash on Delivery', 'cancelled', '8815882825', NULL),
(13, 2, 8, 'Khandwa', '2025-06-02 06:47:32', 'Cash on Delivery', 'cancelled', '8815882825', NULL),
(14, 6, 9, NULL, '2025-06-02 10:36:58', 'Cash on Delivery', 'Placed', NULL, NULL),
(15, 6, 9, NULL, '2025-06-02 10:40:01', 'Cash on Delivery', 'Placed', NULL, NULL),
(16, 6, 7, 'Gudi', '2025-06-02 12:43:45', 'Cash on Delivery', 'Pending', '9876543210', NULL),
(17, 6, 8, 'Gudi', '2025-06-02 12:43:45', 'Cash on Delivery', 'Pending', '9876543210', NULL),
(18, 8, 8, 'Piplod', '2025-06-02 14:17:16', 'Cash on Delivery', 'Cancelled', '9874561230', NULL),
(19, 8, 10, 'Khalwa', '2025-06-02 18:42:19', 'Cash on Delivery', 'Cancelled', '8815882825', NULL),
(20, 6, 8, 'Indore', '2025-06-02 21:17:15', 'Cash on Delivery', 'Pending', '8815882825', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `artwork_id` int NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `artwork_id` (`artwork_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'buyer',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`) VALUES
(1, 'Vishal', 'vsakle963@gmail.com', '$2y$10$zTRHQn/afwj73QtSftD9JOO/N0wXVW2/SMvHpXNPHoRqDzZic26b6', 'buyer'),
(2, 'Vikram', 'vikram@mail.com', '$2y$10$jltGfVKxWi3se8PTRCcrHOiku2PvrfWUnO0mj9Rzpq5kwBAzW7rf.', 'buyer'),
(3, 'Fayaj', 'fajay@1mail.com', '$2y$10$MwreJf.ARSCND/QyIVBuJO8prNEuy7pqeEmNFegKBWc0MjvrYoPFC', 'buyer'),
(4, 'Rome', 'rome@mail.com', '$2y$10$IuEiRu4qBFTwJBv7ZH3tS.4/KccTrU5Vhlc4SqMJ/w0fqWMZaRki6', 'buyer'),
(5, 'Vishal', 'vishalsakle382@gmail.com', '$2y$10$XA0jLMwZMOg6Sv/Wgpc6cuXrmSHu6GSVvUgvS9jbQX3h9tcLWx1SW', 'buyer'),
(6, 'Sam', 'sam@mail.com', '$2y$10$kJRgqjpwHe3ofhftomVP5OMp7HNgbEMRV.KUNicRwC179M11nAGxm', 'buyer'),
(7, 'Rudra', 'rudra@mail.com', '$2y$10$lJjPytncrUwuX6ut.uvLUuyG2Rg6KXHvJ7.bneguLg92b081XobEW', 'buyer'),
(8, 'Virat', 'virat@mail.com', '$2y$10$q71zWpTx.mGoSa6p7jPbBOOU.JJpzeKyferKIPV2LokHbTabomwIG', 'buyer'),
(9, 'Ella', 'Ella@mail.com', '$2y$10$nEYqYMBu87d4rQxUtOEQE.IiMJpPtga05UFzgvQcwGRdV2ReM0KtC', 'buyer'),
(10, 'Aarti', 'aarti@mail.com', '$2y$10$JhkONfwiMDf6Q0zVf3fb/.KgL1yh4HWAq139nNr280rlIich5FY7S', 'buyer');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
