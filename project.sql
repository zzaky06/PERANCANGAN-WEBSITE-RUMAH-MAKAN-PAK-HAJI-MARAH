-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 24, 2026 at 06:06 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `project`
--

-- --------------------------------------------------------

--
-- Table structure for table `menu_images`
--

CREATE TABLE `menu_images` (
  `id` int(11) NOT NULL,
  `menu_item_id` int(11) NOT NULL,
  `image` varchar(500) NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_images`
--

INSERT INTO `menu_images` (`id`, `menu_item_id`, `image`, `is_primary`, `created_at`) VALUES
(1, 32, 'uploads/menu_1780820640_d95bbed7.jpg', 1, '2026-06-07 15:24:00'),
(2, 33, 'uploads/menu_1780820668_8a321a5e.jpg', 1, '2026-06-07 15:24:28'),
(3, 34, 'uploads/menu_1780820682_b659bec7.jpg', 1, '2026-06-07 15:24:42'),
(4, 35, 'uploads/menu_1780820696_d775e014.jpg', 1, '2026-06-07 15:24:56'),
(5, 36, 'uploads/menu_1780820708_a3e24d1a.jpg', 1, '2026-06-07 15:25:08'),
(6, 37, 'uploads/menu_1780820718_60523d3a.jpg', 1, '2026-06-07 15:25:18'),
(7, 38, 'uploads/menu_1780821139_bed607d0.jpg', 1, '2026-06-07 15:32:19'),
(8, 39, 'uploads/menu_1780821152_5636bd35.jpg', 1, '2026-06-07 15:32:32'),
(9, 40, 'uploads/menu_1780821165_0c942dde.jpg', 1, '2026-06-07 15:32:45'),
(10, 41, 'uploads/menu_1780821178_f28ea844.jpg', 1, '2026-06-07 15:32:58'),
(11, 42, 'uploads/menu_1780821190_86fcfa60.jpg', 1, '2026-06-07 15:33:10'),
(12, 43, 'uploads/menu_1780821201_e578112d.jpg', 1, '2026-06-07 15:33:21'),
(13, 44, 'uploads/menu_1780821215_57b02660.jpg', 1, '2026-06-07 15:33:35'),
(14, 45, 'uploads/menu_1780821226_8f2d85ed.jpg', 1, '2026-06-07 15:33:46'),
(15, 46, 'uploads/menu_1780821241_5eab7b8a.jpg', 1, '2026-06-07 15:34:01'),
(16, 47, 'uploads/menu_1780821452_2ad86f72.jpg', 1, '2026-06-07 15:37:32'),
(17, 48, 'uploads/menu_1780821465_3388449e.jpg', 1, '2026-06-07 15:37:45'),
(18, 49, 'uploads/menu_1780821486_875c6f3d.jpg', 1, '2026-06-07 15:38:06'),
(19, 50, 'uploads/menu_1780821500_3b10fd82.jpg', 1, '2026-06-07 15:38:20'),
(20, 51, 'uploads/menu_1780821513_295cf5e4.jpg', 1, '2026-06-07 15:38:33'),
(21, 52, 'uploads/menu_1780821530_fc709756.jpg', 1, '2026-06-07 15:38:50'),
(22, 53, 'uploads/menu_1780821548_f9bf5a18.jpg', 1, '2026-06-07 15:39:09'),
(23, 54, 'uploads/menu_1780821570_994eb399.jpg', 1, '2026-06-07 15:39:30'),
(24, 55, 'uploads/menu_1780821584_86e4d933.jpg', 1, '2026-06-07 15:39:44'),
(25, 56, 'uploads/menu_1780821596_1a1fc5c0.jpg', 1, '2026-06-07 15:39:56'),
(26, 57, 'uploads/menu_1780821610_e5ab84b4.jpg', 1, '2026-06-07 15:40:10'),
(27, 58, 'uploads/menu_1780821628_3adc7b40.jpg', 1, '2026-06-07 15:40:28'),
(28, 59, 'uploads/menu_1781429884_e4d9044d.jpg', 1, '2026-06-14 16:38:04');

-- --------------------------------------------------------

--
-- Table structure for table `menu_items`
--

CREATE TABLE `menu_items` (
  `id` int(11) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `harga` decimal(12,2) NOT NULL,
  `kategori` varchar(50) NOT NULL DEFAULT 'makanan',
  `gambar` varchar(500) NOT NULL DEFAULT '',
  `stock_status` varchar(20) NOT NULL DEFAULT 'tersedia',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_items`
--

INSERT INTO `menu_items` (`id`, `nama`, `harga`, `kategori`, `gambar`, `stock_status`, `created_at`, `updated_at`) VALUES
(32, 'Kerupuk Kuah', 3000.00, 'lainnya', 'uploads/menu_1780820640_d95bbed7.jpg', 'tersedia', '2026-06-07 15:24:00', '2026-06-07 15:24:00'),
(33, 'Kerupuk Kulit', 3000.00, 'lainnya', 'uploads/menu_1780820668_8a321a5e.jpg', 'tersedia', '2026-06-07 15:24:28', '2026-06-07 15:24:28'),
(34, 'Nasi Tambah', 3000.00, 'lainnya', 'uploads/menu_1780820682_b659bec7.jpg', 'tersedia', '2026-06-07 15:24:42', '2026-06-07 15:24:42'),
(35, 'Emping', 4000.00, 'lainnya', 'uploads/menu_1780820696_d775e014.jpg', 'tersedia', '2026-06-07 15:24:56', '2026-06-07 15:24:56'),
(36, 'Rengginang', 5000.00, 'lainnya', 'uploads/menu_1780820708_a3e24d1a.jpg', 'tersedia', '2026-06-07 15:25:08', '2026-06-07 15:25:08'),
(37, 'Petai', 10000.00, 'lainnya', 'uploads/menu_1780820718_60523d3a.jpg', 'tersedia', '2026-06-07 15:25:18', '2026-06-07 15:25:18'),
(38, 'Rendang', 24000.00, 'makanan', 'uploads/menu_1780821139_bed607d0.jpg', 'tersedia', '2026-06-07 15:32:19', '2026-06-07 21:36:54'),
(39, 'Kepala Kambing', 85000.00, 'makanan', 'uploads/menu_1780821152_5636bd35.jpg', 'tersedia', '2026-06-07 15:32:32', '2026-06-07 15:32:32'),
(41, 'Dendeng Lambok', 24000.00, 'makanan', 'uploads/menu_1780821178_f28ea844.jpg', 'tersedia', '2026-06-07 15:32:58', '2026-06-07 15:32:58'),
(42, 'Dendeng Kering', 24000.00, 'makanan', 'uploads/menu_1780821190_86fcfa60.jpg', 'tersedia', '2026-06-07 15:33:10', '2026-06-07 15:33:10'),
(43, 'Tambusu', 20000.00, 'makanan', 'uploads/menu_1780821201_e578112d.jpg', 'tersedia', '2026-06-07 15:33:21', '2026-06-07 15:33:21'),
(44, 'Babat', 20000.00, 'makanan', 'uploads/menu_1780821215_57b02660.jpg', 'tersedia', '2026-06-07 15:33:35', '2026-06-07 15:33:35'),
(45, 'Sayur Pepaya', 12000.00, 'makanan', 'uploads/menu_1780821226_8f2d85ed.jpg', 'tersedia', '2026-06-07 15:33:46', '2026-06-07 15:33:46'),
(46, 'Sayur Nangka', 12000.00, 'makanan', 'uploads/menu_1780821241_5eab7b8a.jpg', 'tersedia', '2026-06-07 15:34:01', '2026-06-07 15:34:01'),
(47, 'Teh Manis', 3000.00, 'minuman', 'uploads/menu_1780821452_2ad86f72.jpg', 'tersedia', '2026-06-07 15:37:32', '2026-06-07 15:37:32'),
(48, 'Kopi', 5000.00, 'minuman', 'uploads/menu_1780821465_3388449e.jpg', 'tersedia', '2026-06-07 15:37:45', '2026-06-07 15:37:45'),
(49, 'Kopi Susu', 6000.00, 'minuman', 'uploads/menu_1780821486_875c6f3d.jpg', 'tersedia', '2026-06-07 15:38:06', '2026-06-07 15:38:06'),
(50, 'Teh Talua', 10000.00, 'minuman', 'uploads/menu_1780821500_3b10fd82.jpg', 'tersedia', '2026-06-07 15:38:20', '2026-06-07 15:38:20'),
(51, 'Tomat Top', 10000.00, 'minuman', 'uploads/menu_1780821513_295cf5e4.jpg', 'tersedia', '2026-06-07 15:38:33', '2026-06-07 15:38:33'),
(52, 'Jus Alpukat', 12000.00, 'minuman', 'uploads/menu_1780821530_fc709756.jpg', 'tersedia', '2026-06-07 15:38:50', '2026-06-07 15:38:50'),
(53, 'Jus Buah Naga', 12000.00, 'minuman', 'uploads/menu_1780821548_f9bf5a18.jpg', 'tersedia', '2026-06-07 15:39:09', '2026-06-07 15:39:09'),
(54, 'Jus Jambu Biji', 12000.00, 'minuman', 'uploads/menu_1780821570_994eb399.jpg', 'tersedia', '2026-06-07 15:39:30', '2026-06-07 15:39:30'),
(55, 'Jus Jeruk', 12000.00, 'minuman', 'uploads/menu_1780821584_86e4d933.jpg', 'tersedia', '2026-06-07 15:39:44', '2026-06-07 15:39:44'),
(56, 'Jus Mangga', 12000.00, 'minuman', 'uploads/menu_1780821596_1a1fc5c0.jpg', 'tersedia', '2026-06-07 15:39:56', '2026-06-07 15:39:56'),
(57, 'Jus Sirsak', 12000.00, 'minuman', 'uploads/menu_1780821610_e5ab84b4.jpg', 'tersedia', '2026-06-07 15:40:10', '2026-06-07 15:40:10'),
(58, 'Jus Tomat', 12000.00, 'minuman', 'uploads/menu_1780821628_3adc7b40.jpg', 'tersedia', '2026-06-07 15:40:28', '2026-06-07 15:40:28'),
(59, 'Cancang Kambing', 27000.00, 'makanan', 'uploads/menu_1781429884_e4d9044d.jpg', 'tersedia', '2026-06-14 16:38:04', '2026-06-14 16:38:04');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` bigint(20) NOT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `nama` varchar(255) NOT NULL,
  `no_hp` varchar(30) NOT NULL,
  `jenis_pesanan` varchar(50) NOT NULL,
  `metode_pembayaran` varchar(50) NOT NULL,
  `items` text NOT NULL,
  `total_harga` decimal(12,2) NOT NULL,
  `status` varchar(50) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `nama`, `no_hp`, `jenis_pesanan`, `metode_pembayaran`, `items`, `total_harga`, `status`, `created_at`) VALUES
(1781448117937, 21, 'hayu', '0893459359', 'take_away', 'qris', 'Kepala Kambing x1 (Nasi)', 95000.00, 'menunggu', '2026-06-14 16:41:57'),
(1781448366616, 22, 'oyoy', '0808008880', 'take_away', 'qris', 'Kepala Kambing x1 (Nasi)', 95000.00, 'menunggu', '2026-06-14 16:46:06'),
(1781448452494, 23, 'ssa', '083924943298', 'take_away', 'qris', 'Dendeng Kering x1 (Nasi Double)', 37000.00, 'menunggu', '2026-06-14 16:47:32'),
(1781584854649, 25, 'Ilzi fahrezi', '0899839223', 'take_away', 'langsung', 'Rendang x1 (Nasi)', 34000.00, 'menunggu', '2026-06-16 06:40:54'),
(1781585215828, 25, 'Ilzi fahrezi', '0899839223', 'dine_in', 'langsung', 'Kepala Kambing x1 (Nasi)', 95000.00, 'menunggu', '2026-06-16 06:46:55'),
(1781585351744, 26, 'Ilzi fahrezi', '08997363', 'take_away', 'langsung', 'Kepala Kambing x1 (Nasi)', 95000.00, 'menunggu', '2026-06-16 06:49:11');

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `id` bigint(20) NOT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `table_id` int(11) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `no_hp` varchar(30) NOT NULL,
  `reservasi_code` varchar(8) NOT NULL,
  `tanggal` date NOT NULL,
  `waktu` time NOT NULL,
  `jumlah_orang` int(11) NOT NULL,
  `catatan` text DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservations`
--

INSERT INTO `reservations` (`id`, `user_id`, `table_id`, `nama`, `no_hp`, `reservasi_code`, `tanggal`, `waktu`, `jumlah_orang`, `catatan`, `status`, `created_at`, `updated_at`) VALUES
(1779425443328, 8, 9, 'Budi Santoso', '081234567890', 'KHE2SGWR', '2026-05-22', '08:50:00', 2, 'Contoh reservasi', 'confirmed', '2026-05-22 06:50:43', '2026-05-22 06:50:43'),
(1781449497070, 24, 10, 'ilzi', '08997736', 'BIRZH0AG', '2026-06-14', '02:09:00', 3, 'dekat sungai', 'menunggu', '2026-06-14 17:04:57', '2026-06-14 17:04:57');

-- --------------------------------------------------------

--
-- Table structure for table `restaurant_status`
--

CREATE TABLE `restaurant_status` (
  `id` tinyint(4) NOT NULL,
  `status` varchar(20) NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `restaurant_status`
--

INSERT INTO `restaurant_status` (`id`, `status`, `updated_at`) VALUES
(1, 'closed', '2026-06-16 12:00:17');

-- --------------------------------------------------------

--
-- Table structure for table `tables`
--

CREATE TABLE `tables` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `kapasitas` int(11) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'tersedia',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tables`
--

INSERT INTO `tables` (`id`, `nama`, `kapasitas`, `status`, `created_at`, `updated_at`) VALUES
(9, 'Meja 1', 4, 'tersedia', '2026-05-22 06:50:43', '2026-05-25 11:48:18'),
(10, 'Meja 2', 4, 'tersedia', '2026-05-22 06:50:43', '2026-05-24 14:10:03'),
(11, 'Meja 3', 4, 'tersedia', '2026-05-22 06:50:43', '2026-05-22 06:50:43'),
(12, 'Meja 4', 4, 'tersedia', '2026-05-22 06:50:43', '2026-05-22 06:50:43'),
(13, 'Meja 5', 4, 'tersedia', '2026-05-22 06:50:43', '2026-05-22 06:50:43'),
(14, 'Meja 6', 4, 'tersedia', '2026-05-22 06:50:43', '2026-05-22 06:50:43'),
(15, 'Meja 7', 4, 'tersedia', '2026-05-22 06:50:43', '2026-05-22 06:50:43'),
(16, 'Meja 8', 4, 'tersedia', '2026-05-22 06:50:43', '2026-05-22 14:11:39');

-- --------------------------------------------------------

--
-- Table structure for table `table_images`
--

CREATE TABLE `table_images` (
  `id` int(11) NOT NULL,
  `table_id` int(11) NOT NULL,
  `image` varchar(500) NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `table_images`
--

INSERT INTO `table_images` (`id`, `table_id`, `image`, `is_primary`, `created_at`) VALUES
(1, 9, 'uploads/menu_1780820141_7bde3424.jpg', 1, '2026-06-07 15:15:41'),
(2, 10, 'uploads/menu_1780820148_93795a8c.jpg', 1, '2026-06-07 15:15:48'),
(3, 11, 'uploads/menu_1780820155_e08be88d.jpg', 1, '2026-06-07 15:15:55'),
(4, 12, 'uploads/menu_1780820162_af38da1a.jpg', 1, '2026-06-07 15:16:02'),
(5, 13, 'uploads/menu_1780820169_ea7d61d3.jpg', 1, '2026-06-07 15:16:09'),
(6, 14, 'uploads/menu_1780820176_f69ba399.jpg', 1, '2026-06-07 15:16:16'),
(7, 15, 'uploads/menu_1780820185_e8cb77c0.jpg', 1, '2026-06-07 15:16:25');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `no_hp` varchar(30) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama`, `no_hp`, `created_at`, `updated_at`) VALUES
(8, 'Budi Santoso', '081234567890', '2026-05-22 06:50:43', '2026-05-22 06:50:43'),
(21, 'hayu', '0893459359', '2026-06-14 16:41:57', '2026-06-14 16:41:57'),
(22, 'oyoy', '0808008880', '2026-06-14 16:46:06', '2026-06-14 16:46:06'),
(23, 'ssa', '083924943298', '2026-06-14 16:47:32', '2026-06-14 16:47:32'),
(24, 'ilzi', '08997736', '2026-06-14 17:04:57', '2026-06-14 17:04:57'),
(25, 'Ilzi fahrezi', '0899839223', '2026-06-16 06:40:54', '2026-06-16 06:40:54'),
(26, 'Ilzi fahrezi', '08997363', '2026-06-16 06:49:11', '2026-06-16 06:49:11');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `menu_images`
--
ALTER TABLE `menu_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_menu_item_id` (`menu_item_id`);

--
-- Indexes for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_stock_status` (`stock_status`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_no_hp` (`no_hp`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reservasi_code` (`reservasi_code`),
  ADD KEY `idx_table_id` (`table_id`),
  ADD KEY `idx_no_hp` (`no_hp`),
  ADD KEY `idx_reservasi_code` (`reservasi_code`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `restaurant_status`
--
ALTER TABLE `restaurant_status`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tables`
--
ALTER TABLE `tables`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nama` (`nama`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `table_images`
--
ALTER TABLE `table_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_table_id` (`table_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `no_hp` (`no_hp`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `menu_images`
--
ALTER TABLE `menu_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `tables`
--
ALTER TABLE `tables`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `table_images`
--
ALTER TABLE `table_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
