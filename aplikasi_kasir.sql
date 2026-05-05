-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 04 Bulan Mei 2026 pada 06.48
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `aplikasi_kasir`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `grosir`
--

CREATE TABLE `grosir` (
  `id` int(11) NOT NULL,
  `produk_id` int(11) NOT NULL,
  `min_qty` int(11) NOT NULL,
  `tipe_diskon` enum('persen','nominal') NOT NULL DEFAULT 'persen',
  `nilai_diskon` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `log_aktivitas`
--

CREATE TABLE `log_aktivitas` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `aktivitas` varchar(100) NOT NULL,
  `detail` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `log_aktivitas`
--

INSERT INTO `log_aktivitas` (`id`, `user_id`, `aktivitas`, `detail`, `created_at`) VALUES
(23, 1, 'Transaksi', 'Transaksi INV-20260504-029: Total Rp 60000, Bayar Rp 100000', '2026-05-04 02:45:05'),
(24, 1, 'Stock Opname', 'Opname: Aqua Botol 600ml | Sistem: 4 ? Nyata: 100 | Selisih: 96 | Alasan: Lainnya', '2026-05-04 03:08:25'),
(25, 1, 'Void Transaksi', 'Void transaksi INV-20260504-029: tes 1', '2026-05-04 03:13:34'),
(26, 1, 'Transaksi', 'Transaksi INV-20260504-248: Total Rp 55000, Bayar Rp 60000', '2026-05-04 03:14:54'),
(27, 1, 'Void Transaksi', 'Void transaksi INV-20260504-248: tes 2', '2026-05-04 03:44:30'),
(28, 1, 'Transaksi', 'Transaksi INV-20260504-039: Total Rp 5000, Bayar Rp 0, Pelanggan: pelanggan 1, Piutang: Rp 5000', '2026-05-04 04:20:26'),
(29, 1, 'Transaksi', 'Transaksi INV-20260504-322: Total Rp 3000, Bayar Rp 0, Pelanggan: pelanggan 1, Piutang: Rp 3000', '2026-05-04 04:28:26'),
(30, 1, 'Pinjaman Manual', 'Pinjaman untuk pelanggan 1: Rp 5000, pulsa', '2026-05-04 04:31:57'),
(31, 1, 'Bayar Piutang', 'Pembayaran Rp 5000 dari pelanggan ID 2', '2026-05-04 04:32:13'),
(32, 1, 'Transaksi', 'Transaksi INV-20260504-185: Total Rp 8500, Bayar Rp 0, Pelanggan: pelanggan 1, Piutang: Rp 8500', '2026-05-04 04:42:33');

-- --------------------------------------------------------

--
-- Struktur dari tabel `mutasi_stok`
--

CREATE TABLE `mutasi_stok` (
  `id` int(11) NOT NULL,
  `produk_id` int(11) NOT NULL,
  `transaksi_id` int(11) DEFAULT NULL,
  `pembelian_id` int(11) DEFAULT NULL,
  `qty_masuk` int(11) NOT NULL DEFAULT 0,
  `qty_keluar` int(11) NOT NULL DEFAULT 0,
  `keterangan` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `mutasi_stok`
--

INSERT INTO `mutasi_stok` (`id`, `produk_id`, `transaksi_id`, `pembelian_id`, `qty_masuk`, `qty_keluar`, `keterangan`, `created_at`) VALUES
(13, 30, 7, NULL, 0, 24, 'Penjualan INV-20260504-029', '2026-05-04 02:45:05'),
(14, 30, NULL, NULL, 96, 0, 'Opname: Lainnya', '2026-05-04 03:08:25'),
(15, 30, 7, NULL, 24, 0, 'Void transaksi: INV-20260504-029', '2026-05-04 03:13:34'),
(16, 30, 10, NULL, 0, 24, 'Penjualan INV-20260504-248', '2026-05-04 03:14:54'),
(17, 30, 10, NULL, 24, 0, 'Void transaksi: INV-20260504-248', '2026-05-04 03:44:30'),
(18, 46, 11, NULL, 0, 1, 'Penjualan INV-20260504-039', '2026-05-04 04:20:26'),
(19, 30, 12, NULL, 0, 1, 'Penjualan INV-20260504-322', '2026-05-04 04:28:26'),
(20, 30, 13, NULL, 0, 1, 'Penjualan INV-20260504-185', '2026-05-04 04:42:33'),
(21, 43, 13, NULL, 0, 1, 'Penjualan INV-20260504-185', '2026-05-04 04:42:33');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pelanggan`
--

CREATE TABLE `pelanggan` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `saldo_piutang` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `pelanggan`
--

INSERT INTO `pelanggan` (`id`, `nama`, `no_hp`, `alamat`, `saldo_piutang`, `created_at`) VALUES
(1, 'Umum', '-', '-', 0.00, '2026-05-03 04:11:18'),
(2, 'pelanggan 1', '085204614659', 'lmg', 16500.00, '2026-05-04 03:16:38');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pembelian`
--

CREATE TABLE `pembelian` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `produk_id` int(11) NOT NULL,
  `supplier` varchar(100) DEFAULT NULL,
  `qty` int(11) NOT NULL,
  `total_harga` decimal(10,2) NOT NULL,
  `harga_satuan` decimal(10,2) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengeluaran`
--

CREATE TABLE `pengeluaran` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `kategori` varchar(50) NOT NULL,
  `jumlah` decimal(10,2) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `piutang`
--

CREATE TABLE `piutang` (
  `id` int(11) NOT NULL,
  `pelanggan_id` int(11) NOT NULL,
  `transaksi_id` int(11) DEFAULT NULL,
  `no_referensi` varchar(50) DEFAULT NULL,
  `tipe` enum('transaksi','pinjaman','pembayaran') NOT NULL,
  `jumlah` decimal(10,2) NOT NULL,
  `saldo_sebelum` decimal(10,2) NOT NULL DEFAULT 0.00,
  `saldo_sesudah` decimal(10,2) NOT NULL DEFAULT 0.00,
  `keterangan` text DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `piutang`
--

INSERT INTO `piutang` (`id`, `pelanggan_id`, `transaksi_id`, `no_referensi`, `tipe`, `jumlah`, `saldo_sebelum`, `saldo_sesudah`, `keterangan`, `user_id`, `created_at`) VALUES
(1, 2, 11, 'INV-20260504-039', 'transaksi', 5000.00, 0.00, 5000.00, 'Kekurangan bayar transaksi', 1, '2026-05-04 04:20:26'),
(2, 2, 12, 'INV-20260504-322', 'transaksi', 3000.00, 5000.00, 8000.00, 'Kekurangan bayar transaksi', 1, '2026-05-04 04:28:26'),
(3, 2, NULL, 'PINJ-20260504-960', 'pinjaman', 5000.00, 8000.00, 13000.00, 'pulsa', 1, '2026-05-04 04:31:57'),
(4, 2, NULL, 'BYR-202605041132', 'pembayaran', 5000.00, 13000.00, 8000.00, 'Pembayaran piutang', 1, '2026-05-04 04:32:13'),
(5, 2, 13, 'INV-20260504-185', 'transaksi', 8500.00, 8000.00, 16500.00, 'Kekurangan bayar transaksi', 1, '2026-05-04 04:42:33');

-- --------------------------------------------------------

--
-- Struktur dari tabel `produk`
--

CREATE TABLE `produk` (
  `id` int(11) NOT NULL,
  `kode` varchar(50) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `satuan_dasar` varchar(20) NOT NULL DEFAULT 'pcs',
  `harga_beli` decimal(10,2) NOT NULL DEFAULT 0.00,
  `harga_jual` decimal(10,2) NOT NULL DEFAULT 0.00,
  `stok_dasar` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `produk`
--

INSERT INTO `produk` (`id`, `kode`, `nama`, `gambar`, `satuan_dasar`, `harga_beli`, `harga_jual`, `stok_dasar`, `created_at`, `updated_at`) VALUES
(30, '888608101053', 'Aqua Botol 600ml', NULL, 'botol', 2000.00, 3000.00, 122, '2026-05-03 04:49:13', '2026-05-04 04:42:33'),
(31, '888608101138', 'Aqua Galon Asli', NULL, 'galon', 19000.00, 21000.00, 10, '2026-05-03 04:49:13', '2026-05-03 05:58:52'),
(32, 'BRG-AQUA-DUS', 'Aqua Botol 600ml / 1 Dus', NULL, 'dus', 46000.00, 52000.00, 20, '2026-05-03 04:49:13', '2026-05-03 05:58:52'),
(33, 'BRG-CLEO-DUS', 'Cleo Gelas / 1 Dus', NULL, 'dus', 21500.00, 24000.00, 15, '2026-05-03 04:49:13', '2026-05-03 05:58:52'),
(34, '8992696523081', 'Milo Kotak 115ml', NULL, 'kotak', 2850.00, 3500.00, 40, '2026-05-03 04:49:13', '2026-05-03 05:58:52'),
(35, '89906010600849', 'Nips Madu Botol', NULL, 'botol', 3250.00, 4000.00, 30, '2026-05-03 04:49:13', '2026-05-03 05:58:52'),
(36, 'BRG-POCARI', 'Pocari Sweat 500ml', NULL, 'botol', 6350.00, 7500.00, 35, '2026-05-03 04:49:13', '2026-05-03 05:58:52'),
(37, 'BRG-TEH-PUCUK', 'Teh Pucuk 350ml', NULL, 'botol', 2500.00, 3500.00, 50, '2026-05-03 04:49:13', '2026-05-03 05:58:52'),
(38, '8999908000101', 'NEO RHEUMACYLO 10 pcs', NULL, 'pcs', 4500.00, 5000.00, 30, '2026-05-03 04:49:13', '2026-05-03 05:58:52'),
(39, '89992003782354', 'Antangin Cair', NULL, 'sachet', 3125.00, 4000.00, 60, '2026-05-03 04:49:13', '2026-05-03 05:58:52'),
(40, 'BRG-GULA-SJP', 'Gula SJP', NULL, 'kg', 15800.00, 17000.00, 25, '2026-05-03 04:49:13', '2026-05-03 05:58:52'),
(41, 'BRG-FERMINA', 'Fermina Botol 700ml', NULL, 'botol', 16250.00, 17500.00, 20, '2026-05-03 04:49:13', '2026-05-03 05:58:52'),
(42, 'BRG-MINYAK-KITA', 'Minyak Kita 1 Liter', NULL, 'botol', 15800.00, 17000.00, 30, '2026-05-03 04:49:13', '2026-05-03 05:58:52'),
(43, 'BRG-RINSO', 'Rinso Cair 20ml / 1 Renteng', NULL, 'renteng', 4625.00, 5500.00, 24, '2026-05-03 04:49:13', '2026-05-04 04:42:33'),
(44, 'BRG-SPENTEN', 'Sasu Pentene', NULL, 'sachet', 400.00, 500.00, 99, '2026-05-03 04:49:13', '2026-05-03 07:31:56'),
(45, '4902430566896', 'Head and Shoulders Sachet', NULL, 'sachet', 400.00, 500.00, 79, '2026-05-03 04:49:13', '2026-05-03 07:31:56'),
(46, 'BRG-EMERON', 'Sampo Emeron / 1 Renteng', NULL, 'renteng', 4450.00, 5000.00, 19, '2026-05-03 04:49:13', '2026-05-04 04:20:26'),
(47, 'BRG-SOKLIN-10RB', 'Soklin 10rb 425gr', NULL, 'pcs', 8600.00, 10000.00, 25, '2026-05-03 04:49:13', '2026-05-03 05:58:52'),
(48, 'BRG-SOKLIN-PK-BIJI', 'Soklin PK / Biji', NULL, 'pcs', 700.00, 1000.00, 80, '2026-05-03 04:49:13', '2026-05-03 05:58:52'),
(49, 'BRG-SOKLIN-PK-RENTENG', 'Soklin PK / Renteng', NULL, 'renteng', 4750.00, 5500.00, 20, '2026-05-03 04:49:13', '2026-05-03 05:58:52'),
(50, '8999909096004', 'Sampoerna A Mild 16', NULL, 'bungkus', 34900.00, 38000.00, 50, '2026-05-03 04:49:13', '2026-05-03 05:58:52'),
(51, '8999909000773', 'Marlboro Hitam 12', NULL, 'bungkus', 23150.00, 25000.00, 40, '2026-05-03 04:49:13', '2026-05-03 05:58:52'),
(52, '8999106101019', 'Djarum Super 12', NULL, 'bungkus', 22950.00, 26000.00, 45, '2026-05-03 04:49:13', '2026-05-03 05:58:52'),
(53, '8997103020829', 'Gajah Baru Filter 12', NULL, 'bungkus', 16750.00, 18500.00, 30, '2026-05-03 04:49:13', '2026-05-03 05:58:52'),
(54, '8997224070176', 'Galang Baru 12', NULL, 'bungkus', 16100.00, 17500.00, 35, '2026-05-03 04:49:13', '2026-05-03 05:58:52'),
(55, '8997229129891', 'Grandel Filter 12', NULL, 'bungkus', 15100.00, 16500.00, 30, '2026-05-03 04:49:13', '2026-05-03 05:58:52'),
(56, '8992745320050', 'Stella Gantung', NULL, 'pcs', 9000.00, 10000.00, 20, '2026-05-03 04:49:13', '2026-05-03 05:58:52'),
(57, 'BRG-KAPAL-SPESIAL-BIJI', 'Kapal Api Spesial Mix / Biji', NULL, 'sachet', 1600.00, 2000.00, 100, '2026-05-03 04:49:13', '2026-05-03 05:58:52'),
(58, 'BRG-KAPAL-SPESIAL-RENTENG', 'Kapal Api Spesial Mix / Renteng', NULL, 'renteng', 16500.00, 18000.00, 20, '2026-05-03 04:49:13', '2026-05-03 05:58:52');

-- --------------------------------------------------------

--
-- Struktur dari tabel `satuan`
--

CREATE TABLE `satuan` (
  `id` int(11) NOT NULL,
  `produk_id` int(11) NOT NULL,
  `nama_satuan` varchar(50) NOT NULL,
  `isi_satuan` int(11) NOT NULL,
  `harga_jual` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `satuan`
--

INSERT INTO `satuan` (`id`, `produk_id`, `nama_satuan`, `isi_satuan`, `harga_jual`, `created_at`) VALUES
(1, 30, 'Dus', 24, 60000.00, '2026-05-04 00:54:23');

-- --------------------------------------------------------

--
-- Struktur dari tabel `stock_opname`
--

CREATE TABLE `stock_opname` (
  `id` int(11) NOT NULL,
  `produk_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `stok_sistem` int(11) NOT NULL,
  `stok_nyata` int(11) NOT NULL,
  `selisih` int(11) NOT NULL,
  `tipe` enum('kurang','tambah') NOT NULL,
  `alasan` varchar(50) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `kerugian` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `stock_opname`
--

INSERT INTO `stock_opname` (`id`, `produk_id`, `user_id`, `stok_sistem`, `stok_nyata`, `selisih`, `tipe`, `alasan`, `keterangan`, `kerugian`, `created_at`) VALUES
(1, 30, 1, 4, 100, 96, 'tambah', 'Lainnya', 'tambahan', 0.00, '2026-05-04 03:08:25');

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaksi`
--

CREATE TABLE `transaksi` (
  `id` int(11) NOT NULL,
  `no_invoice` varchar(20) NOT NULL,
  `user_id` int(11) NOT NULL,
  `pelanggan_id` int(11) DEFAULT NULL,
  `total` decimal(10,2) NOT NULL,
  `bayar` decimal(10,2) NOT NULL,
  `kembalian` decimal(10,2) NOT NULL DEFAULT 0.00,
  `kurang` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('lunas','piutang','void') NOT NULL DEFAULT 'lunas',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `transaksi`
--

INSERT INTO `transaksi` (`id`, `no_invoice`, `user_id`, `pelanggan_id`, `total`, `bayar`, `kembalian`, `kurang`, `status`, `created_at`) VALUES
(7, 'INV-20260504-029', 1, NULL, 60000.00, 100000.00, 40000.00, 0.00, 'void', '2026-05-04 02:45:05'),
(10, 'INV-20260504-248', 1, NULL, 55000.00, 60000.00, 5000.00, 0.00, 'void', '2026-05-04 03:14:54'),
(11, 'INV-20260504-039', 1, 2, 5000.00, 0.00, 0.00, 5000.00, 'piutang', '2026-05-04 04:20:26'),
(12, 'INV-20260504-322', 1, 2, 3000.00, 0.00, 0.00, 3000.00, 'piutang', '2026-05-04 04:28:26'),
(13, 'INV-20260504-185', 1, 2, 8500.00, 0.00, 0.00, 8500.00, 'piutang', '2026-05-04 04:42:33');

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaksi_detail`
--

CREATE TABLE `transaksi_detail` (
  `id` int(11) NOT NULL,
  `transaksi_id` int(11) NOT NULL,
  `produk_id` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  `qty_dasar` int(11) NOT NULL DEFAULT 0,
  `satuan` varchar(50) DEFAULT NULL,
  `tipe_harga` varchar(50) DEFAULT NULL,
  `harga_satuan` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `transaksi_detail`
--

INSERT INTO `transaksi_detail` (`id`, `transaksi_id`, `produk_id`, `qty`, `qty_dasar`, `satuan`, `tipe_harga`, `harga_satuan`, `subtotal`) VALUES
(1, 7, 30, 1, 24, 'Dus', 'Paket', 60000.00, 60000.00),
(2, 10, 30, 1, 24, 'Dus', 'Paket', 55000.00, 55000.00),
(3, 11, 46, 1, 1, 'renteng', 'Ecer', 5000.00, 5000.00),
(4, 12, 30, 1, 1, 'botol', 'Ecer', 3000.00, 3000.00),
(5, 13, 30, 1, 1, 'botol', 'Ecer', 3000.00, 3000.00),
(6, 13, 43, 1, 1, 'renteng', 'Ecer', 5500.00, 5500.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `role` enum('admin','kasir') NOT NULL DEFAULT 'kasir',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `nama`, `role`, `created_at`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin', '2026-05-03 04:11:18'),
(2, 'kasir1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Budi Santoso', 'kasir', '2026-05-03 04:11:18'),
(3, 'kasir2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Siti Nurhaliza', 'kasir', '2026-05-03 04:11:18');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `grosir`
--
ALTER TABLE `grosir`
  ADD PRIMARY KEY (`id`),
  ADD KEY `produk_id` (`produk_id`);

--
-- Indeks untuk tabel `log_aktivitas`
--
ALTER TABLE `log_aktivitas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `mutasi_stok`
--
ALTER TABLE `mutasi_stok`
  ADD PRIMARY KEY (`id`),
  ADD KEY `produk_id` (`produk_id`),
  ADD KEY `transaksi_id` (`transaksi_id`),
  ADD KEY `pembelian_id` (`pembelian_id`);

--
-- Indeks untuk tabel `pelanggan`
--
ALTER TABLE `pelanggan`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `pembelian`
--
ALTER TABLE `pembelian`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `produk_id` (`produk_id`);

--
-- Indeks untuk tabel `pengeluaran`
--
ALTER TABLE `pengeluaran`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `piutang`
--
ALTER TABLE `piutang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pelanggan_id` (`pelanggan_id`),
  ADD KEY `transaksi_id` (`transaksi_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode` (`kode`);

--
-- Indeks untuk tabel `satuan`
--
ALTER TABLE `satuan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `produk_id` (`produk_id`);

--
-- Indeks untuk tabel `stock_opname`
--
ALTER TABLE `stock_opname`
  ADD PRIMARY KEY (`id`),
  ADD KEY `produk_id` (`produk_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `no_invoice` (`no_invoice`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `pelanggan_id` (`pelanggan_id`);

--
-- Indeks untuk tabel `transaksi_detail`
--
ALTER TABLE `transaksi_detail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transaksi_id` (`transaksi_id`),
  ADD KEY `produk_id` (`produk_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `grosir`
--
ALTER TABLE `grosir`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `log_aktivitas`
--
ALTER TABLE `log_aktivitas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT untuk tabel `mutasi_stok`
--
ALTER TABLE `mutasi_stok`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT untuk tabel `pelanggan`
--
ALTER TABLE `pelanggan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `pembelian`
--
ALTER TABLE `pembelian`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `pengeluaran`
--
ALTER TABLE `pengeluaran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `piutang`
--
ALTER TABLE `piutang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `produk`
--
ALTER TABLE `produk`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT untuk tabel `satuan`
--
ALTER TABLE `satuan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `stock_opname`
--
ALTER TABLE `stock_opname`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `transaksi_detail`
--
ALTER TABLE `transaksi_detail`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `grosir`
--
ALTER TABLE `grosir`
  ADD CONSTRAINT `grosir_ibfk_1` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `log_aktivitas`
--
ALTER TABLE `log_aktivitas`
  ADD CONSTRAINT `log_aktivitas_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `mutasi_stok`
--
ALTER TABLE `mutasi_stok`
  ADD CONSTRAINT `mutasi_stok_ibfk_1` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`),
  ADD CONSTRAINT `mutasi_stok_ibfk_2` FOREIGN KEY (`transaksi_id`) REFERENCES `transaksi` (`id`),
  ADD CONSTRAINT `mutasi_stok_ibfk_3` FOREIGN KEY (`pembelian_id`) REFERENCES `pembelian` (`id`);

--
-- Ketidakleluasaan untuk tabel `pembelian`
--
ALTER TABLE `pembelian`
  ADD CONSTRAINT `pembelian_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `pembelian_ibfk_2` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`);

--
-- Ketidakleluasaan untuk tabel `pengeluaran`
--
ALTER TABLE `pengeluaran`
  ADD CONSTRAINT `pengeluaran_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `piutang`
--
ALTER TABLE `piutang`
  ADD CONSTRAINT `piutang_ibfk_1` FOREIGN KEY (`pelanggan_id`) REFERENCES `pelanggan` (`id`),
  ADD CONSTRAINT `piutang_ibfk_2` FOREIGN KEY (`transaksi_id`) REFERENCES `transaksi` (`id`),
  ADD CONSTRAINT `piutang_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `satuan`
--
ALTER TABLE `satuan`
  ADD CONSTRAINT `satuan_ibfk_1` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `stock_opname`
--
ALTER TABLE `stock_opname`
  ADD CONSTRAINT `stock_opname_ibfk_1` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`),
  ADD CONSTRAINT `stock_opname_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `transaksi_ibfk_2` FOREIGN KEY (`pelanggan_id`) REFERENCES `pelanggan` (`id`);

--
-- Ketidakleluasaan untuk tabel `transaksi_detail`
--
ALTER TABLE `transaksi_detail`
  ADD CONSTRAINT `transaksi_detail_ibfk_1` FOREIGN KEY (`transaksi_id`) REFERENCES `transaksi` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transaksi_detail_ibfk_2` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
