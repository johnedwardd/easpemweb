-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.0.30 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for stepx_db
CREATE DATABASE IF NOT EXISTS `stepx_db` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `stepx_db`;

-- Dumping structure for table stepx_db.alamat_pembeli
CREATE TABLE IF NOT EXISTS `alamat_pembeli` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `label` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Rumah',
  `nama_penerima` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `no_hp` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `provinsi` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kota` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kecamatan` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kode_pos` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alamat_lengkap` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_utama` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_alamat_user` (`user_id`),
  CONSTRAINT `fk_alamat_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table stepx_db.alamat_pembeli: ~7 rows (approximately)
DELETE FROM `alamat_pembeli`;
INSERT INTO `alamat_pembeli` (`id`, `user_id`, `label`, `nama_penerima`, `no_hp`, `provinsi`, `kota`, `kecamatan`, `kode_pos`, `alamat_lengkap`, `is_utama`, `created_at`) VALUES
	(1, 3, 'Rumah', 'Rizky Pratama', '081211111111', 'Jawa Timur', 'Surabaya', NULL, '60111', 'Jl. Pahlawan No. 12, RT 03/04', 1, '2026-06-02 21:26:05'),
	(2, 4, 'Rumah', 'Sari Dewi', '081222222222', 'Jawa Timur', 'Malang', NULL, '65112', 'Jl. Semeru No. 45, RT 01/02', 1, '2026-06-02 21:26:05'),
	(3, 5, 'Rumah', 'Budi Santoso', '081233333333', 'Jawa Tengah', 'Semarang', NULL, '50131', 'Jl. Pandanaran No. 8, Blok B', 1, '2026-06-02 21:26:05'),
	(4, 6, 'Kantor', 'Eka Putri', '081244444444', 'DKI Jakarta', 'Jakarta Selatan', NULL, '12950', 'Jl. Sudirman Kav 22, Lt 5', 1, '2026-06-02 21:26:05'),
	(5, 7, 'Rumah', 'Ahmad Fauzi', '081255555555', 'Jawa Barat', 'Bandung', NULL, '40111', 'Jl. Dago No. 99, RT 07/08', 1, '2026-06-02 21:26:05'),
	(6, 8, 'Rumah', 'Nining Wahyu', '081266666666', 'D.I. Yogyakarta', 'Yogyakarta', NULL, '55111', 'Jl. Malioboro No. 14', 1, '2026-06-02 21:26:05'),
	(7, 9, 'Rumah', 'Dani Kurnia', '081277777777', 'Jawa Timur', 'Sidoarjo', NULL, '61212', 'Perum Graha Natura Blok C-5', 1, '2026-06-02 21:26:05');

-- Dumping structure for table stepx_db.biaya_operasional
CREATE TABLE IF NOT EXISTS `biaya_operasional` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `penjual_id` int unsigned NOT NULL,
  `nama_biaya` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nominal` decimal(12,2) NOT NULL,
  `bulan` tinyint NOT NULL,
  `tahun` year NOT NULL,
  `keterangan` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_biaya_bulan_thn` (`penjual_id`,`tahun`,`bulan`),
  CONSTRAINT `fk_biaya_penjual` FOREIGN KEY (`penjual_id`) REFERENCES `users` (`id`),
  CONSTRAINT `biaya_operasional_chk_1` CHECK ((`bulan` between 1 and 12))
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table stepx_db.biaya_operasional: ~24 rows (approximately)
DELETE FROM `biaya_operasional`;
INSERT INTO `biaya_operasional` (`id`, `penjual_id`, `nama_biaya`, `nominal`, `bulan`, `tahun`, `keterangan`, `created_at`) VALUES
	(1, 2, 'Sewa Toko', 1500000.00, 1, '2025', NULL, '2026-06-02 21:26:05'),
	(2, 2, 'Gaji Karyawan', 800000.00, 1, '2025', NULL, '2026-06-02 21:26:05'),
	(3, 2, 'Listrik & Air', 300000.00, 1, '2025', NULL, '2026-06-02 21:26:05'),
	(4, 2, 'Marketing', 200000.00, 1, '2025', NULL, '2026-06-02 21:26:05'),
	(5, 2, 'Sewa Toko', 1500000.00, 2, '2025', NULL, '2026-06-02 21:26:05'),
	(6, 2, 'Gaji Karyawan', 800000.00, 2, '2025', NULL, '2026-06-02 21:26:05'),
	(7, 2, 'Listrik & Air', 300000.00, 2, '2025', NULL, '2026-06-02 21:26:05'),
	(8, 2, 'Marketing', 300000.00, 2, '2025', NULL, '2026-06-02 21:26:05'),
	(9, 2, 'Sewa Toko', 1500000.00, 3, '2025', NULL, '2026-06-02 21:26:05'),
	(10, 2, 'Gaji Karyawan', 800000.00, 3, '2025', NULL, '2026-06-02 21:26:05'),
	(11, 2, 'Listrik & Air', 250000.00, 3, '2025', NULL, '2026-06-02 21:26:05'),
	(12, 2, 'Marketing', 200000.00, 3, '2025', NULL, '2026-06-02 21:26:05'),
	(13, 2, 'Sewa Toko', 1500000.00, 4, '2025', NULL, '2026-06-02 21:26:05'),
	(14, 2, 'Gaji Karyawan', 900000.00, 4, '2025', NULL, '2026-06-02 21:26:05'),
	(15, 2, 'Listrik & Air', 350000.00, 4, '2025', NULL, '2026-06-02 21:26:05'),
	(16, 2, 'Marketing', 350000.00, 4, '2025', NULL, '2026-06-02 21:26:05'),
	(17, 2, 'Sewa Toko', 1500000.00, 5, '2025', NULL, '2026-06-02 21:26:05'),
	(18, 2, 'Gaji Karyawan', 900000.00, 5, '2025', NULL, '2026-06-02 21:26:05'),
	(19, 2, 'Listrik & Air', 400000.00, 5, '2025', NULL, '2026-06-02 21:26:05'),
	(20, 2, 'Marketing', 600000.00, 5, '2025', NULL, '2026-06-02 21:26:05'),
	(21, 2, 'Sewa Toko', 1500000.00, 6, '2025', NULL, '2026-06-02 21:26:05'),
	(22, 2, 'Gaji Karyawan', 900000.00, 6, '2025', NULL, '2026-06-02 21:26:05'),
	(23, 2, 'Listrik & Air', 350000.00, 6, '2025', NULL, '2026-06-02 21:26:05'),
	(24, 2, 'Marketing', 450000.00, 6, '2025', NULL, '2026-06-02 21:26:05');

-- Dumping structure for table stepx_db.detail_pesanan
CREATE TABLE IF NOT EXISTS `detail_pesanan` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `pesanan_id` int unsigned NOT NULL,
  `produk_id` int unsigned NOT NULL,
  `penjual_id` int unsigned NOT NULL,
  `nama_produk` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Snapshot nama saat order',
  `harga_satuan` decimal(12,2) NOT NULL COMMENT 'Snapshot harga saat order',
  `hpp_satuan` decimal(12,2) NOT NULL COMMENT 'Snapshot HPP saat order',
  `ukuran` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qty` int NOT NULL DEFAULT '1',
  `subtotal` decimal(12,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_detail_produk` (`produk_id`),
  KEY `idx_detail_penjual` (`penjual_id`),
  KEY `idx_detail_pesanan` (`pesanan_id`),
  CONSTRAINT `fk_detail_penjual` FOREIGN KEY (`penjual_id`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_detail_pesanan` FOREIGN KEY (`pesanan_id`) REFERENCES `pesanan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_detail_produk` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table stepx_db.detail_pesanan: ~7 rows (approximately)
DELETE FROM `detail_pesanan`;
INSERT INTO `detail_pesanan` (`id`, `pesanan_id`, `produk_id`, `penjual_id`, `nama_produk`, `harga_satuan`, `hpp_satuan`, `ukuran`, `qty`, `subtotal`) VALUES
	(1, 1, 1, 2, 'Air Max 270', 1350000.00, 750000.00, '41', 1, 1350000.00),
	(2, 2, 2, 2, 'Ultra Boost 22', 1750000.00, 950000.00, '42', 2, 3500000.00),
	(3, 3, 3, 2, 'Old Skool Classic', 650000.00, 320000.00, '40', 1, 650000.00),
	(4, 4, 5, 2, 'Oxford Brogue', 980000.00, 520000.00, '43', 1, 980000.00),
	(5, 5, 6, 2, 'Gel-Kayano 29', 1580000.00, 880000.00, '40', 1, 1580000.00),
	(6, 6, 4, 2, 'Chuck Taylor All Star', 550000.00, 270000.00, '39', 2, 1100000.00),
	(7, 7, 7, 2, 'Pegasus 39', 1250000.00, 680000.00, '41', 1, 1250000.00),
	(8, 8, 9, 2, '0', 720000.00, 380000.00, NULL, 1, 720000.00),
	(9, 8, 10, 2, '0', 420000.00, 200000.00, NULL, 1, 420000.00);

-- Dumping structure for table stepx_db.kategori
CREATE TABLE IF NOT EXISTS `kategori` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nama` (`nama`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table stepx_db.kategori: ~4 rows (approximately)
DELETE FROM `kategori`;
INSERT INTO `kategori` (`id`, `nama`, `slug`) VALUES
	(1, 'Running', 'running'),
	(2, 'Casual', 'casual'),
	(3, 'Formal', 'formal'),
	(4, 'Sport', 'sport');

-- Dumping structure for table stepx_db.keranjang
CREATE TABLE IF NOT EXISTS `keranjang` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `produk_id` int unsigned NOT NULL,
  `ukuran` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qty` int NOT NULL DEFAULT '1',
  `added_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_keranjang` (`user_id`,`produk_id`,`ukuran`),
  KEY `fk_keranjang_produk` (`produk_id`),
  CONSTRAINT `fk_keranjang_produk` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_keranjang_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table stepx_db.keranjang: ~0 rows (approximately)
DELETE FROM `keranjang`;

-- Dumping structure for table stepx_db.pesanan
CREATE TABLE IF NOT EXISTS `pesanan` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `kode_pesanan` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pembeli_id` int unsigned NOT NULL,
  `alamat_id` int unsigned NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `ongkos_kirim` decimal(10,2) NOT NULL DEFAULT '0.00',
  `diskon` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_bayar` decimal(12,2) NOT NULL,
  `metode_bayar` enum('transfer_bank','cod','ewallet','kartu_kredit') COLLATE utf8mb4_unicode_ci NOT NULL,
  `status_bayar` enum('menunggu','lunas','gagal','refund') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'menunggu',
  `status_pesanan` enum('proses','dikemas','dikirim','selesai','dibatalkan') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'proses',
  `catatan` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_pesanan` (`kode_pesanan`),
  KEY `fk_pesanan_alamat` (`alamat_id`),
  KEY `idx_pesanan_pembeli` (`pembeli_id`),
  KEY `idx_pesanan_status` (`status_pesanan`),
  KEY `idx_pesanan_tanggal` (`created_at`),
  CONSTRAINT `fk_pesanan_alamat` FOREIGN KEY (`alamat_id`) REFERENCES `alamat_pembeli` (`id`),
  CONSTRAINT `fk_pesanan_pembeli` FOREIGN KEY (`pembeli_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table stepx_db.pesanan: ~7 rows (approximately)
DELETE FROM `pesanan`;
INSERT INTO `pesanan` (`id`, `kode_pesanan`, `pembeli_id`, `alamat_id`, `subtotal`, `ongkos_kirim`, `diskon`, `total_bayar`, `metode_bayar`, `status_bayar`, `status_pesanan`, `catatan`, `created_at`, `updated_at`) VALUES
	(1, '#ORD-001', 3, 1, 1350000.00, 15000.00, 0.00, 1365000.00, 'transfer_bank', 'lunas', 'selesai', NULL, '2026-06-02 21:26:05', '2026-06-02 21:26:05'),
	(2, '#ORD-002', 4, 2, 3500000.00, 20000.00, 175000.00, 3345000.00, 'ewallet', 'lunas', 'selesai', NULL, '2026-06-02 21:26:05', '2026-06-03 15:54:04'),
	(3, '#ORD-003', 5, 3, 650000.00, 15000.00, 0.00, 665000.00, 'cod', 'lunas', 'selesai', NULL, '2026-06-02 21:26:05', '2026-06-02 21:26:05'),
	(4, '#ORD-004', 6, 4, 980000.00, 15000.00, 0.00, 995000.00, 'transfer_bank', 'menunggu', 'dikirim', NULL, '2026-06-02 21:26:05', '2026-06-03 16:02:20'),
	(5, '#ORD-005', 7, 5, 1580000.00, 20000.00, 0.00, 1600000.00, 'transfer_bank', 'lunas', 'selesai', NULL, '2026-06-02 21:26:05', '2026-06-02 21:26:05'),
	(6, '#ORD-006', 8, 6, 1100000.00, 15000.00, 0.00, 1115000.00, 'ewallet', 'lunas', 'selesai', NULL, '2026-06-02 21:26:05', '2026-06-03 15:53:53'),
	(7, '#ORD-007', 9, 7, 1250000.00, 15000.00, 0.00, 1265000.00, 'transfer_bank', 'menunggu', 'selesai', NULL, '2026-06-02 21:26:05', '2026-06-03 16:02:36'),
	(8, '#ORD-797C46', 3, 1, 1140000.00, 15000.00, 0.00, 1155000.00, 'transfer_bank', 'menunggu', 'proses', NULL, '2026-06-03 16:06:47', '2026-06-03 16:06:47');

-- Dumping structure for table stepx_db.produk
CREATE TABLE IF NOT EXISTS `produk` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `penjual_id` int unsigned NOT NULL,
  `kategori_id` int unsigned NOT NULL,
  `nama` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `brand` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `harga_jual` decimal(12,2) NOT NULL,
  `hpp` decimal(12,2) NOT NULL COMMENT 'Harga Pokok Penjualan / Modal',
  `stok` int NOT NULL DEFAULT '0',
  `berat_gram` int NOT NULL DEFAULT '500',
  `emoji` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT '?',
  `is_new` tinyint(1) NOT NULL DEFAULT '0',
  `status` enum('aktif','nonaktif','habis') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aktif',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_produk_kategori` (`kategori_id`),
  KEY `idx_produk_penjual` (`penjual_id`),
  KEY `idx_produk_status` (`status`),
  CONSTRAINT `fk_produk_kategori` FOREIGN KEY (`kategori_id`) REFERENCES `kategori` (`id`),
  CONSTRAINT `fk_produk_penjual` FOREIGN KEY (`penjual_id`) REFERENCES `users` (`id`),
  CONSTRAINT `chk_harga` CHECK ((`harga_jual` > 0)),
  CONSTRAINT `chk_hpp` CHECK ((`hpp` > 0)),
  CONSTRAINT `chk_stok` CHECK ((`stok` >= 0))
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table stepx_db.produk: ~12 rows (approximately)
DELETE FROM `produk`;
INSERT INTO `produk` (`id`, `penjual_id`, `kategori_id`, `nama`, `brand`, `deskripsi`, `harga_jual`, `hpp`, `stok`, `berat_gram`, `emoji`, `is_new`, `status`, `created_at`, `updated_at`) VALUES
	(1, 2, 1, 'Air Max 270', 'Nike', NULL, 1350000.00, 750000.00, 24, 500, '👟', 1, 'aktif', '2026-06-02 21:26:05', '2026-06-02 21:26:05'),
	(2, 2, 1, 'Ultra Boost 22', 'Adidas', NULL, 1750000.00, 950000.00, 15, 500, '🏃', 0, 'aktif', '2026-06-02 21:26:05', '2026-06-02 21:26:05'),
	(3, 2, 2, 'Old Skool Classic', 'Vans', NULL, 650000.00, 320000.00, 38, 500, '🥿', 0, 'aktif', '2026-06-02 21:26:05', '2026-06-02 21:26:05'),
	(4, 2, 2, 'Chuck Taylor All Star', 'Converse', NULL, 550000.00, 270000.00, 45, 500, '👞', 0, 'aktif', '2026-06-02 21:26:05', '2026-06-02 21:26:05'),
	(5, 2, 3, 'Oxford Brogue', 'Clarks', NULL, 980000.00, 520000.00, 12, 500, '🥾', 0, 'aktif', '2026-06-02 21:26:05', '2026-06-02 21:26:05'),
	(6, 2, 1, 'Gel-Kayano 29', 'Asics', NULL, 1580000.00, 880000.00, 8, 500, '👟', 1, 'aktif', '2026-06-02 21:26:05', '2026-06-02 21:26:05'),
	(7, 2, 1, 'Pegasus 39', 'Nike', NULL, 1250000.00, 680000.00, 30, 500, '👟', 0, 'aktif', '2026-06-02 21:26:05', '2026-06-02 21:26:05'),
	(8, 2, 2, 'Handball Spezial', 'Adidas', NULL, 1100000.00, 580000.00, 5, 500, '👟', 1, 'aktif', '2026-06-02 21:26:05', '2026-06-02 21:26:05'),
	(9, 2, 2, 'Suede Classic', 'Puma', NULL, 720000.00, 380000.00, 19, 500, '🥿', 0, 'aktif', '2026-06-02 21:26:05', '2026-06-03 16:06:47'),
	(10, 2, 4, 'Pro Court', 'Puma', NULL, 420000.00, 200000.00, 49, 500, '🏓', 0, 'aktif', '2026-06-02 21:26:05', '2026-06-03 16:06:47'),
	(11, 2, 2, 'Slip-On Pro', 'Vans', NULL, 580000.00, 290000.00, 5, 500, '🥿', 0, 'aktif', '2026-06-02 21:26:05', '2026-06-03 15:33:59'),
	(12, 2, 3, 'Loafer Derby', 'Ecco', NULL, 1450000.00, 800000.00, 9, 500, '👞', 1, 'aktif', '2026-06-02 21:26:05', '2026-06-02 21:26:05');

-- Dumping structure for table stepx_db.produk_gambar
CREATE TABLE IF NOT EXISTS `produk_gambar` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `produk_id` int unsigned NOT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `urutan` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `fk_gambar_produk` (`produk_id`),
  CONSTRAINT `fk_gambar_produk` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table stepx_db.produk_gambar: ~0 rows (approximately)
DELETE FROM `produk_gambar`;

-- Dumping structure for table stepx_db.produk_ukuran
CREATE TABLE IF NOT EXISTS `produk_ukuran` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `produk_id` int unsigned NOT NULL,
  `ukuran` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stok` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_produk_ukuran` (`produk_id`,`ukuran`),
  CONSTRAINT `fk_ukuran_produk` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table stepx_db.produk_ukuran: ~17 rows (approximately)
DELETE FROM `produk_ukuran`;
INSERT INTO `produk_ukuran` (`id`, `produk_id`, `ukuran`, `stok`) VALUES
	(1, 1, '38', 3),
	(2, 1, '39', 5),
	(3, 1, '40', 6),
	(4, 1, '41', 5),
	(5, 1, '42', 4),
	(6, 1, '43', 1),
	(7, 2, '39', 3),
	(8, 2, '40', 4),
	(9, 2, '41', 5),
	(10, 2, '42', 2),
	(11, 2, '43', 1),
	(12, 3, '37', 5),
	(13, 3, '38', 8),
	(14, 3, '39', 9),
	(15, 3, '40', 8),
	(16, 3, '41', 5),
	(17, 3, '42', 3);

-- Dumping structure for table stepx_db.profil_penjual
CREATE TABLE IF NOT EXISTS `profil_penjual` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `nama_toko` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `logo_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provinsi` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kota` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alamat_toko` text COLLATE utf8mb4_unicode_ci,
  `no_rekening` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_bank` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `atas_nama` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  CONSTRAINT `fk_profil_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table stepx_db.profil_penjual: ~1 rows (approximately)
DELETE FROM `profil_penjual`;
INSERT INTO `profil_penjual` (`id`, `user_id`, `nama_toko`, `deskripsi`, `logo_url`, `provinsi`, `kota`, `alamat_toko`, `no_rekening`, `nama_bank`, `atas_nama`, `created_at`, `updated_at`) VALUES
	(1, 2, 'StepX Official', 'Toko sepatu premium terpercaya sejak 2020', NULL, 'Jawa Timur', 'Surabaya', NULL, '1234567890', 'BCA', 'Toko StepX', '2026-06-02 21:26:05', '2026-06-02 21:26:05');

-- Dumping structure for table stepx_db.riwayat_status_pesanan
CREATE TABLE IF NOT EXISTS `riwayat_status_pesanan` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `pesanan_id` int unsigned NOT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `keterangan` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_riwayat_pesanan` (`pesanan_id`),
  CONSTRAINT `fk_riwayat_pesanan` FOREIGN KEY (`pesanan_id`) REFERENCES `pesanan` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table stepx_db.riwayat_status_pesanan: ~10 rows (approximately)
DELETE FROM `riwayat_status_pesanan`;
INSERT INTO `riwayat_status_pesanan` (`id`, `pesanan_id`, `status`, `keterangan`, `created_at`) VALUES
	(1, 1, 'proses', 'Pesanan diterima, sedang dikemas', '2026-06-02 21:26:05'),
	(2, 1, 'dikirim', 'Paket dikirim via JNE, no resi: JNE123456', '2026-06-02 21:26:05'),
	(3, 1, 'selesai', 'Pesanan diterima pembeli', '2026-06-02 21:26:05'),
	(4, 2, 'proses', 'Pesanan diterima, sedang dikemas', '2026-06-02 21:26:05'),
	(5, 2, 'dikirim', 'Paket dikirim via SiCepat, no resi: SCP789012', '2026-06-02 21:26:05'),
	(6, 3, 'proses', 'Pesanan diterima, COD dijadwalkan', '2026-06-02 21:26:05'),
	(7, 3, 'selesai', 'Pembayaran COD diterima', '2026-06-02 21:26:05'),
	(8, 5, 'proses', 'Pesanan diterima, sedang dikemas', '2026-06-02 21:26:05'),
	(9, 5, 'dikirim', 'Paket dikirim via AnterAja, no resi: AA345678', '2026-06-02 21:26:05'),
	(10, 5, 'selesai', 'Pesanan diterima pembeli', '2026-06-02 21:26:05');

-- Dumping structure for table stepx_db.ulasan
CREATE TABLE IF NOT EXISTS `ulasan` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `produk_id` int unsigned NOT NULL,
  `user_id` int unsigned NOT NULL,
  `pesanan_id` int unsigned NOT NULL,
  `rating` tinyint NOT NULL,
  `komentar` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ulasan` (`user_id`,`produk_id`,`pesanan_id`),
  KEY `fk_ulasan_produk` (`produk_id`),
  KEY `fk_ulasan_pesanan` (`pesanan_id`),
  CONSTRAINT `fk_ulasan_pesanan` FOREIGN KEY (`pesanan_id`) REFERENCES `pesanan` (`id`),
  CONSTRAINT `fk_ulasan_produk` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`),
  CONSTRAINT `fk_ulasan_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `ulasan_chk_1` CHECK ((`rating` between 1 and 5))
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table stepx_db.ulasan: ~3 rows (approximately)
DELETE FROM `ulasan`;
INSERT INTO `ulasan` (`id`, `produk_id`, `user_id`, `pesanan_id`, `rating`, `komentar`, `created_at`) VALUES
	(1, 1, 3, 1, 5, 'Sepatu sangat nyaman dipakai, ukuran pas. Pengiriman cepat!', '2026-06-02 21:26:05'),
	(2, 3, 5, 3, 4, 'Kualitas bagus, sesuai gambar. Tapi pengiriman agak lama.', '2026-06-02 21:26:05'),
	(3, 6, 7, 5, 5, 'Mantap banget, cocok buat lari pagi. Recommended!', '2026-06-02 21:26:05');

-- Dumping structure for table stepx_db.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `no_hp` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('pembeli','penjual','admin') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pembeli',
  `status` enum('aktif','nonaktif','banned') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aktif',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table stepx_db.users: ~9 rows (approximately)
DELETE FROM `users`;
INSERT INTO `users` (`id`, `nama`, `email`, `password`, `no_hp`, `role`, `status`, `created_at`, `updated_at`) VALUES
	(1, 'Admin StepX', 'admin@stepx.id', 'password123', '081200000001', 'admin', 'aktif', '2026-06-02 21:26:05', '2026-06-03 14:25:20'),
	(2, 'Toko StepX', 'penjual@stepx.id', 'password123', '081200000002', 'penjual', 'aktif', '2026-06-02 21:26:05', '2026-06-03 14:25:20'),
	(3, 'Rizky Pratama', 'rizky@gmail.com', 'password123', '081211111111', 'pembeli', 'aktif', '2026-06-02 21:26:05', '2026-06-03 14:25:20'),
	(4, 'Sari Dewi', 'sari@gmail.com', 'password123', '081222222222', 'pembeli', 'aktif', '2026-06-02 21:26:05', '2026-06-03 14:25:20'),
	(5, 'Budi Santoso', 'budi@gmail.com', 'password123', '081233333333', 'pembeli', 'aktif', '2026-06-02 21:26:05', '2026-06-03 14:25:20'),
	(6, 'Eka Putri', 'eka@gmail.com', 'password123', '081244444444', 'pembeli', 'aktif', '2026-06-02 21:26:05', '2026-06-03 14:25:20'),
	(7, 'Ahmad Fauzi', 'ahmad@gmail.com', 'password123', '081255555555', 'pembeli', 'aktif', '2026-06-02 21:26:05', '2026-06-03 14:25:20'),
	(8, 'Nining Wahyu', 'nining@gmail.com', 'password123', '081266666666', 'pembeli', 'aktif', '2026-06-02 21:26:05', '2026-06-03 14:25:20'),
	(9, 'Dani Kurnia', 'dani@gmail.com', 'password123', '081277777777', 'pembeli', 'aktif', '2026-06-02 21:26:05', '2026-06-03 14:25:20');

-- Dumping structure for view stepx_db.v_database_pelanggan
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `v_database_pelanggan` (
	`id` INT(10) UNSIGNED NOT NULL,
	`nama` VARCHAR(100) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`email` VARCHAR(150) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`no_hp` VARCHAR(20) NULL COLLATE 'utf8mb4_unicode_ci',
	`total_transaksi` BIGINT(19) NOT NULL,
	`total_belanja` DECIMAL(34,2) NULL,
	`terakhir_belanja` DATETIME NULL,
	`segmen_pelanggan` VARCHAR(7) NOT NULL COLLATE 'utf8mb4_0900_ai_ci'
) ENGINE=MyISAM;

-- Dumping structure for view stepx_db.v_laporan_laba_bulanan
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `v_laporan_laba_bulanan` (
	`penjual_id` INT(10) UNSIGNED NOT NULL,
	`nama_penjual` VARCHAR(100) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`tahun` INT(10) NULL,
	`bulan` INT(10) NULL,
	`nama_bulan` VARCHAR(9) NULL COLLATE 'utf8mb4_0900_ai_ci',
	`jumlah_transaksi` BIGINT(19) NOT NULL,
	`unit_terjual` DECIMAL(32,0) NULL,
	`pendapatan` DECIMAL(34,2) NULL,
	`total_hpp` DECIMAL(44,2) NULL,
	`laba_bruto` DECIMAL(45,2) NULL,
	`biaya_operasional` DECIMAL(34,2) NOT NULL,
	`laba_neto` DECIMAL(46,2) NULL,
	`margin_bruto_pct` DECIMAL(51,2) NULL
) ENGINE=MyISAM;

-- Dumping structure for view stepx_db.v_produk_terlaris
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `v_produk_terlaris` (
	`produk_id` INT(10) UNSIGNED NOT NULL,
	`nama_produk` VARCHAR(200) NOT NULL COMMENT 'Snapshot nama saat order' COLLATE 'utf8mb4_unicode_ci',
	`penjual_id` INT(10) UNSIGNED NOT NULL,
	`brand` VARCHAR(100) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`kategori` VARCHAR(100) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`total_terjual` DECIMAL(32,0) NULL,
	`total_pendapatan` DECIMAL(34,2) NULL,
	`total_hpp` DECIMAL(44,2) NULL,
	`total_laba_bruto` DECIMAL(45,2) NULL,
	`margin_pct` DECIMAL(51,2) NULL
) ENGINE=MyISAM;

-- Dumping structure for view stepx_db.v_stok_margin
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `v_stok_margin` (
	`id` INT(10) UNSIGNED NOT NULL,
	`nama` VARCHAR(200) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`brand` VARCHAR(100) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`kategori` VARCHAR(100) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`harga_jual` DECIMAL(12,2) NOT NULL,
	`hpp` DECIMAL(12,2) NOT NULL COMMENT 'Harga Pokok Penjualan / Modal',
	`stok` INT(10) NOT NULL,
	`laba_per_unit` DECIMAL(13,2) NOT NULL,
	`margin_pct` DECIMAL(19,2) NULL,
	`status_stok` VARCHAR(6) NOT NULL COLLATE 'utf8mb4_0900_ai_ci'
) ENGINE=MyISAM;

-- Dumping structure for view stepx_db.v_database_pelanggan
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `v_database_pelanggan`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_database_pelanggan` AS select `u`.`id` AS `id`,`u`.`nama` AS `nama`,`u`.`email` AS `email`,`u`.`no_hp` AS `no_hp`,count(`p`.`id`) AS `total_transaksi`,sum(`p`.`total_bayar`) AS `total_belanja`,max(`p`.`created_at`) AS `terakhir_belanja`,(case when (sum(`p`.`total_bayar`) >= 5000000) then 'VIP' when (count(`p`.`id`) >= 3) then 'Regular' else 'Baru' end) AS `segmen_pelanggan` from (`users` `u` left join `pesanan` `p` on(((`p`.`pembeli_id` = `u`.`id`) and (`p`.`status_pesanan` <> 'dibatalkan')))) where (`u`.`role` = 'pembeli') group by `u`.`id`,`u`.`nama`,`u`.`email`,`u`.`no_hp`;

-- Dumping structure for view stepx_db.v_laporan_laba_bulanan
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `v_laporan_laba_bulanan`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_laporan_laba_bulanan` AS select `dp`.`penjual_id` AS `penjual_id`,`u`.`nama` AS `nama_penjual`,year(`p`.`created_at`) AS `tahun`,month(`p`.`created_at`) AS `bulan`,monthname(`p`.`created_at`) AS `nama_bulan`,count(distinct `p`.`id`) AS `jumlah_transaksi`,sum(`dp`.`qty`) AS `unit_terjual`,sum(`dp`.`subtotal`) AS `pendapatan`,sum((`dp`.`hpp_satuan` * `dp`.`qty`)) AS `total_hpp`,(sum(`dp`.`subtotal`) - sum((`dp`.`hpp_satuan` * `dp`.`qty`))) AS `laba_bruto`,coalesce((select sum(`bo`.`nominal`) from `biaya_operasional` `bo` where ((`bo`.`penjual_id` = `dp`.`penjual_id`) and (`bo`.`tahun` = year(`p`.`created_at`)) and (`bo`.`bulan` = month(`p`.`created_at`)))),0) AS `biaya_operasional`,((sum(`dp`.`subtotal`) - sum((`dp`.`hpp_satuan` * `dp`.`qty`))) - coalesce((select sum(`bo`.`nominal`) from `biaya_operasional` `bo` where ((`bo`.`penjual_id` = `dp`.`penjual_id`) and (`bo`.`tahun` = year(`p`.`created_at`)) and (`bo`.`bulan` = month(`p`.`created_at`)))),0)) AS `laba_neto`,round((((sum(`dp`.`subtotal`) - sum((`dp`.`hpp_satuan` * `dp`.`qty`))) / nullif(sum(`dp`.`subtotal`),0)) * 100),2) AS `margin_bruto_pct` from ((`detail_pesanan` `dp` join `pesanan` `p` on((`p`.`id` = `dp`.`pesanan_id`))) join `users` `u` on((`u`.`id` = `dp`.`penjual_id`))) where (`p`.`status_pesanan` in ('selesai','dikirim')) group by `dp`.`penjual_id`,`u`.`nama`,year(`p`.`created_at`),month(`p`.`created_at`),monthname(`p`.`created_at`) order by `tahun` desc,`bulan` desc;

-- Dumping structure for view stepx_db.v_produk_terlaris
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `v_produk_terlaris`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_produk_terlaris` AS select `dp`.`produk_id` AS `produk_id`,`dp`.`nama_produk` AS `nama_produk`,`dp`.`penjual_id` AS `penjual_id`,`pr`.`brand` AS `brand`,`k`.`nama` AS `kategori`,sum(`dp`.`qty`) AS `total_terjual`,sum(`dp`.`subtotal`) AS `total_pendapatan`,sum((`dp`.`hpp_satuan` * `dp`.`qty`)) AS `total_hpp`,(sum(`dp`.`subtotal`) - sum((`dp`.`hpp_satuan` * `dp`.`qty`))) AS `total_laba_bruto`,round((((sum(`dp`.`subtotal`) - sum((`dp`.`hpp_satuan` * `dp`.`qty`))) / nullif(sum(`dp`.`subtotal`),0)) * 100),2) AS `margin_pct` from (((`detail_pesanan` `dp` join `pesanan` `p` on(((`p`.`id` = `dp`.`pesanan_id`) and (`p`.`status_pesanan` in ('selesai','dikirim'))))) join `produk` `pr` on((`pr`.`id` = `dp`.`produk_id`))) join `kategori` `k` on((`k`.`id` = `pr`.`kategori_id`))) group by `dp`.`produk_id`,`dp`.`nama_produk`,`dp`.`penjual_id`,`pr`.`brand`,`k`.`nama` order by `total_terjual` desc;

-- Dumping structure for view stepx_db.v_stok_margin
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `v_stok_margin`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_stok_margin` AS select `pr`.`id` AS `id`,`pr`.`nama` AS `nama`,`pr`.`brand` AS `brand`,`k`.`nama` AS `kategori`,`pr`.`harga_jual` AS `harga_jual`,`pr`.`hpp` AS `hpp`,`pr`.`stok` AS `stok`,(`pr`.`harga_jual` - `pr`.`hpp`) AS `laba_per_unit`,round((((`pr`.`harga_jual` - `pr`.`hpp`) / `pr`.`harga_jual`) * 100),2) AS `margin_pct`,(case when (`pr`.`stok` = 0) then 'Habis' when (`pr`.`stok` <= 5) then 'Kritis' when (`pr`.`stok` <= 15) then 'Rendah' else 'Aman' end) AS `status_stok` from (`produk` `pr` join `kategori` `k` on((`k`.`id` = `pr`.`kategori_id`))) where (`pr`.`status` <> 'nonaktif') order by `pr`.`stok`;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
