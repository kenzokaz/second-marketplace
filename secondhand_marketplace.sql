-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 05, 2026 at 02:42 AM
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
-- Database: `secondhand_marketplace`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE DATABASE IF NOT EXISTS secondhand_marketplace.sql
DEFAULT CHARACTER SET utf8mb4
COLLATE utf8mb4_general_ci;
USE secondhand_marketplace.sql;

-- -----------------------------------------------------

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `total_price` decimal(10,2) DEFAULT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total_price`, `order_date`, `status`) VALUES
(1, 1, 1500.00, '2026-03-23 16:50:25', 'Placed'),
(2, 1, 500.00, '2026-03-23 17:04:45', 'Placed'),
(3, 1, 250.00, '2026-03-23 17:05:16', 'Placed'),
(4, 1, 250.00, '2026-03-23 17:06:50', 'Placed'),
(5, 1, 450.00, '2026-03-27 16:49:26', 'Placed'),
(6, 1, 250.00, '2026-03-30 16:46:23', 'Placed'),
(7, 2, 450.00, '2026-03-30 22:19:22', 'Placed'),
(8, 1, 1499.99, '2026-04-02 18:00:26', 'Placed'),
(9, 1, 49.98, '2026-04-03 22:09:48', 'Placed'),
(10, 1, 34.99, '2026-04-03 22:20:12', 'Placed'),
(11, 9, 1849.99, '2026-04-05 00:25:07', 'Placed');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price_at_purchase` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price_at_purchase`) VALUES
(1, 9, 14, 2, 24.99),
(2, 10, 10, 1, 34.99),
(3, 11, 11, 1, 1849.99);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `condition_of_product` varchar(50) DEFAULT NULL,
  `stock` int(11) DEFAULT NULL,
  `seller_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `image_url`, `category`, `condition_of_product`, `stock`, `seller_id`) VALUES
(1, 'Iphone 11', 'Used but good condition', 250.00, 'images/uploads/Used-Iphone11-1.jpg', 'Electronics', 'Used', 1, 1),
(2, 'iPhone 13 Pro Max', 'Used but good condition', 450.00, 'images/uploads/Used-Iphone13-1.jpg', 'Electronics', 'Used', 2, 1),
(3, 'Ipad Pro 13', 'Used but good condition', 459.99, 'images/uploads/Used-IPad-Pro-13-1.jpg', 'Electronics', 'Used', 2, 1),
(4, 'Macbook Pro 13 Inch', 'Used but still good', 699.99, 'images/uploads/Used-2020-MacbBook-Pro-13-Inch-1.jpg', 'Electronics', 'Used', 3, 1),
(5, 'Dining Table Set', 'Used but good condition', 69.99, 'images/uploads/Dining-table-set-1.jpg', 'Furniture', 'Used', 1, 1),
(6, 'Grey Couch set', 'New and in great condition', 1199.99, 'images/uploads/New-couch-set-1.jpg', 'Furniture', 'New', 1, 1),
(7, 'Armoire Set', 'New and in great condition', 799.99, 'images/uploads/New-armoire-set-1.jpg', 'Furniture', 'New', 1, 1),
(8, 'Mirror Set', 'Bought but never used', 47.99, 'images/uploads/Like-New-Mirror-set-1.jpg', 'Furniture', 'Like New', 2, 1),
(9, 'Lamp Set', 'Bought but never used', 29.99, 'images/uploads/Like-New-Lamp-set-1.jpg', 'Furniture', 'Like New', 2, 1),
(10, 'Vase set', 'Bought but never used', 34.99, 'images/uploads/Like-New-Vase-set-1.jpg', 'Furniture', 'Like New', 3, 1),
(11, 'Black  Storage Cabinet Set (8 pc)', 'Used but in good condition', 1849.99, 'images/uploads/Used-Strorage-Cabinet-set-1.jpg', 'Furniture', 'Used', 0, 1),
(12, 'Desk set', 'Used but in great condition', 599.99, 'images/uploads/Good-Desk-set-1.jpg', 'Furniture', 'Good', 1, 1),
(13, 'Bedroom set (8 pc)', 'Used but in great condition', 2499.99, 'images/uploads/Good-8-piece-bedroom-set-1.jpg', 'Furniture', 'Good', 1, 1),
(14, 'Petit Prince', 'Used but good condition', 24.99, 'images/uploads/book_1.jpg', 'Books', 'Used', 0, 1),
(15, 'Moncler Jacket', 'Used but good condition', 1299.00, 'images/uploads/img_69d19efe10af03.95781534.jpg', 'Clothing', 'Like New', 1, 7);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `is_admin` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `is_admin`) VALUES
(1, 'test', 'test@email.com', '$2y$10$31tOTI6Vvis/V55ib7p5WuqAcWBZtER3tBg9jyBK5yRu2GdO/EKZa', 0),
(2, 'Kazura Kenzo', 'kazurakenzo@gmail.com', '$2y$10$wHTqpo.VQHdxonE.XaFdQ.FBpr4K6468bj2plWPLehIEzFJd5Qngu', 0),
(6, 'Josh Hart', 'joshhart@gmail.com', '$2y$10$TYMYXAYTbebBP3LBALRZL.W2f1EAT8yVBsnrWj4gELOGboGH8Hm.C', 0),
(7, 'Kaze Kami', 'kazekami@gmail.com', '$2y$10$JoueVRifX1s6w/y5lACl9u3JWHKiMfI0HkhkwlBhqZbnuATo.N4yW', 0),
(8, 'Isaac Prince', 'isaacprince@gmail.com', '$2y$10$aBSHQKOPNZxduHedk.9EqehnsH88rELiu5QKUJrykrnHq8eobGhHK', 0),
(9, 'john smith', 'johnsmith@gmail.com', '$2y$10$j/SmS5oybr6IYCGstqEhBuIBA2Lrodn.hQ6WcLgo0ws8POwvJmOEy', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
