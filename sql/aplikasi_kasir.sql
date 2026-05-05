-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 04 Bulan Mei 2026 pada 01.40
-- Versi server: 10.4.6-MariaDB
-- Versi PHP: 7.3.10

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data untuk tabel `log_aktivitas`
--

INSERT INTO `log_aktivitas` (`id`, `user_id`, `aktivitas`, `detail`, `created_at`) VALUES
(1, 1, 'Logout', 'Pak Ahmad logout', '2026-05-03 04:18:29'),
(2, 2, 'Login', 'Budi Santoso berhasil login', '2026-05-03 04:18:40'),
(3, 2, 'Logout', 'Budi Santoso logout', '2026-05-03 04:50:10'),
(4, 1, 'Login', 'Administrator berhasil login', '2026-05-03 04:50:28'),
(5, 1, 'Transaksi', 'Transaksi INV-20260503-792: Total Rp 4000, Bayar Rp 5000', '2026-05-03 07:31:56'),
(6, 1, 'Login', 'Administrator berhasil login', '2026-05-03 09:09:29'),
(7, 1, 'Login', 'Administrator berhasil login', '2026-05-03 09:10:01'),
(8, 1, 'Login', 'Administrator berhasil login', '2026-05-03 21:25:43'),
(9, 1, 'Login', 'Administrator berhasil login', '2026-05-03 22:14:34'),
(10, 1, 'Login', 'Administrator berhasil login', '2026-05-03 22:18:29'),
(11, 1, 'Login', 'Administrator berhasil login', '2026-05-04 00:51:50'),
(12, 1, 'Tambah Satuan', 'Satuan Dus untuk Aqua Botol 600ml', '2026-05-04 00:54:23'),
(13, 1, 'Transaksi', 'Transaksi INV-20260504-473: Total Rp 120000, Bayar Rp 150000', '2026-05-04 01:02:57'),
(14, 1, 'Transaksi', 'Transaksi INV-20260504-691: Total Rp 120000, Bayar Rp 200000', '2026-05-04 01:11:06'),
(15, 1, 'Void Transaksi', 'Void transaksi INV-20260504-691: uji coba', '2026-05-04 01:13:42'),
(16, 1, 'Void Transaksi', 'Void transaksi INV-20260504-473: uji coba 2', '2026-05-04 01:13:57'),
(17, 1, 'Transaksi', 'Transaksi INV-20260504-283: Total Rp 120000, Bayar Rp 200000', '2026-05-04 01:20:03'),
(18, 1, 'Transaksi', 'Transaksi INV-20260504-275: Total Rp 2500, Bayar Rp 5000', '2026-05-04 01:20:26'),
(19, 1, 'Transaksi', 'Transaksi INV-20260504-576: Total Rp 55000, Bayar Rp 60000', '2026-05-04 01:20:51'),
(20, 1, 'Void Transaksi', 'Void transaksi INV-20260504-275: tes lagi', '2026-05-04 01:35:48'),
(21, 1, 'Void Transaksi', 'Void transaksi INV-20260504-576: tes tes lagi', '2026-05-04 01:36:00');

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data untuk tabel `mutasi_stok`
--

INSERT INTO `mutasi_stok` (`id`, `produk_id`, `transaksi_id`, `pembelian_id`, `qty_masuk`, `qty_keluar`, `keterangan`, `created_at`) VALUES
(1, 30, 1, NULL, 0, 1, 'Penjualan INV-20260503-792', '2026-05-03 07:31:56'),
(2, 45, 1, NULL, 0, 1, 'Penjualan INV-20260503-792', '2026-05-03 07:31:56'),
(3, 44, 1, NULL, 0, 1, 'Penjualan INV-20260503-792', '2026-05-03 07:31:56'),
(4, 30, 2, NULL, 0, 2, 'Penjualan INV-20260504-473', '2026-05-04 01:02:57'),
(5, 30, 3, NULL, 0, 2, 'Penjualan INV-20260504-691', '2026-05-04 01:11:06'),
(6, 30, 3, NULL, 2, 0, 'Void transaksi: INV-20260504-691', '2026-05-04 01:13:42'),
(7, 30, 2, NULL, 2, 0, 'Void transaksi: INV-20260504-473', '2026-05-04 01:13:57'),
(8, 30, 4, NULL, 0, 48, 'Penjualan INV-20260504-283', '2026-05-04 01:20:03'),
(9, 30, 5, NULL, 0, 1, 'Penjualan INV-20260504-275', '2026-05-04 01:20:26'),
(10, 30, 6, NULL, 0, 24, 'Penjualan INV-20260504-576', '2026-05-04 01:20:51'),
(11, 30, 5, NULL, 1, 0, 'Void transaksi: INV-20260504-275', '2026-05-04 01:35:48'),
(12, 30, 6, NULL, 1, 0, 'Void transaksi: INV-20260504-576', '2026-05-04 01:36:00');

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data untuk tabel `pelanggan`
--

INSERT INTO `pelanggan` (`id`, `nama`, `no_hp`, `alamat`, `saldo_piutang`, `created_at`) VALUES
(1, 'Umum', '-', '-', 0.00, '2026-05-03 04:11:18');

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data untuk tabel `produk`
--

INSERT INTO `produk` (`id`, `kode`, `nama`, `gambar`, `satuan_dasar`, `harga_beli`, `harga_jual`, `stok_dasar`, `created_at`, `updated_at`) VALUES
(30, '888608101053', 'Aqua Botol 600ml', NULL, 'botol', 2000.00, 3000.00, 28, '2026-05-03 04:49:13', '2026-05-04 01:36:00'),
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
(43, 'BRG-RINSO', 'Rinso Cair 20ml / 1 Renteng', NULL, 'renteng', 4625.00, 5500.00, 25, '2026-05-03 04:49:13', '2026-05-03 05:58:52'),
(44, 'BRG-SPENTEN', 'Sasu Pentene', NULL, 'sachet', 400.00, 500.00, 99, '2026-05-03 04:49:13', '2026-05-03 07:31:56'),
(45, '4902430566896', 'Head and Shoulders Sachet', NULL, 'sachet', 400.00, 500.00, 79, '2026-05-03 04:49:13', '2026-05-03 07:31:56'),
(46, 'BRG-EMERON', 'Sampo Emeron / 1 Renteng', NULL, 'renteng', 4450.00, 5000.00, 20, '2026-05-03 04:49:13', '2026-05-03 05:58:52'),
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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data untuk tabel `transaksi`
--

INSERT INTO `transaksi` (`id`, `no_invoice`, `user_id`, `pelanggan_id`, `total`, `bayar`, `kembalian`, `kurang`, `status`, `created_at`) VALUES
(1, 'INV-20260503-792', 1, NULL, 4000.00, 5000.00, 1000.00, 0.00, 'lunas', '2026-05-03 07:31:56'),
(2, 'INV-20260504-473', 1, NULL, 120000.00, 150000.00, 30000.00, 0.00, 'void', '2026-05-04 01:02:57'),
(3, 'INV-20260504-691', 1, NULL, 120000.00, 200000.00, 80000.00, 0.00, 'void', '2026-05-04 01:11:06'),
(4, 'INV-20260504-283', 1, NULL, 120000.00, 200000.00, 80000.00, 0.00, 'lunas', '2026-05-04 01:20:03'),
(5, 'INV-20260504-275', 1, NULL, 2500.00, 5000.00, 2500.00, 0.00, 'void', '2026-05-04 01:20:26'),
(6, 'INV-20260504-576', 1, NULL, 55000.00, 60000.00, 5000.00, 0.00, 'void', '2026-05-04 01:20:51');

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data untuk tabel `transaksi_detail`
--

INSERT INTO `transaksi_detail` (`id`, `transaksi_id`, `produk_id`, `qty`, `qty_dasar`, `satuan`, `tipe_harga`, `harga_satuan`, `subtotal`) VALUES
(1, 1, 30, 1, 0, 'botol', 'Ecer', 3000.00, 3000.00),
(2, 1, 45, 1, 0, 'sachet', 'Ecer', 500.00, 500.00),
(3, 1, 44, 1, 0, 'sachet', 'Ecer', 500.00, 500.00),
(4, 2, 30, 2, 0, 'Dus', 'Paket', 60000.00, 120000.00),
(5, 3, 30, 2, 0, 'Dus', 'Paket', 60000.00, 120000.00),
(6, 4, 30, 2, 0, 'Dus', 'Paket', 60000.00, 120000.00),
(7, 5, 30, 1, 0, 'botol', 'override', 2500.00, 2500.00),
(8, 6, 30, 1, 0, 'Dus', 'Paket', 55000.00, 55000.00);

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT untuk tabel `mutasi_stok`
--
ALTER TABLE `mutasi_stok`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `pelanggan`
--
ALTER TABLE `pelanggan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `transaksi_detail`
--
ALTER TABLE `transaksi_detail`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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
