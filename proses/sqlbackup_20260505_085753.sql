-- ============================================
-- BACKUP DATABASE APLIKASI KASIR
-- Tanggal: 05/05/2026 08:57:53
-- Oleh: Administrator
-- ============================================


-- --------------------------------------------
-- Tabel: grosir
-- --------------------------------------------

DROP TABLE IF EXISTS `grosir`;
CREATE TABLE `grosir` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `produk_id` int(11) NOT NULL,
  `min_qty` int(11) NOT NULL,
  `tipe_diskon` enum('persen','nominal') NOT NULL DEFAULT 'persen',
  `nilai_diskon` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `produk_id` (`produk_id`),
  CONSTRAINT `grosir_ibfk_1` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;


-- --------------------------------------------
-- Tabel: log_aktivitas
-- --------------------------------------------

DROP TABLE IF EXISTS `log_aktivitas`;
CREATE TABLE `log_aktivitas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `aktivitas` varchar(100) NOT NULL,
  `detail` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `log_aktivitas_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Data untuk tabel log_aktivitas
INSERT INTO `log_aktivitas` VALUES ('23', '1', 'Transaksi', 'Transaksi INV-20260504-029: Total Rp 60000, Bayar Rp 100000', '2026-05-04 09:45:05');
INSERT INTO `log_aktivitas` VALUES ('24', '1', 'Stock Opname', 'Opname: Aqua Botol 600ml | Sistem: 4 ? Nyata: 100 | Selisih: 96 | Alasan: Lainnya', '2026-05-04 10:08:25');
INSERT INTO `log_aktivitas` VALUES ('25', '1', 'Void Transaksi', 'Void transaksi INV-20260504-029: tes 1', '2026-05-04 10:13:34');
INSERT INTO `log_aktivitas` VALUES ('26', '1', 'Transaksi', 'Transaksi INV-20260504-248: Total Rp 55000, Bayar Rp 60000', '2026-05-04 10:14:54');
INSERT INTO `log_aktivitas` VALUES ('27', '1', 'Void Transaksi', 'Void transaksi INV-20260504-248: tes 2', '2026-05-04 10:44:30');
INSERT INTO `log_aktivitas` VALUES ('28', '1', 'Transaksi', 'Transaksi INV-20260504-039: Total Rp 5000, Bayar Rp 0, Pelanggan: pelanggan 1, Piutang: Rp 5000', '2026-05-04 11:20:26');
INSERT INTO `log_aktivitas` VALUES ('29', '1', 'Transaksi', 'Transaksi INV-20260504-322: Total Rp 3000, Bayar Rp 0, Pelanggan: pelanggan 1, Piutang: Rp 3000', '2026-05-04 11:28:26');
INSERT INTO `log_aktivitas` VALUES ('30', '1', 'Pinjaman Manual', 'Pinjaman untuk pelanggan 1: Rp 5000, pulsa', '2026-05-04 11:31:57');
INSERT INTO `log_aktivitas` VALUES ('31', '1', 'Bayar Piutang', 'Pembayaran Rp 5000 dari pelanggan ID 2', '2026-05-04 11:32:13');
INSERT INTO `log_aktivitas` VALUES ('32', '1', 'Transaksi', 'Transaksi INV-20260504-185: Total Rp 8500, Bayar Rp 0, Pelanggan: pelanggan 1, Piutang: Rp 8500', '2026-05-04 11:42:33');
INSERT INTO `log_aktivitas` VALUES ('33', '1', 'Bayar Piutang', 'Pembayaran Rp 16500 dari pelanggan ID 2', '2026-05-04 13:09:12');
INSERT INTO `log_aktivitas` VALUES ('34', '1', 'Login', 'Administrator berhasil login', '2026-05-05 08:09:31');
INSERT INTO `log_aktivitas` VALUES ('35', '1', 'Pengaturan', 'Update pengaturan toko', '2026-05-05 08:43:21');
INSERT INTO `log_aktivitas` VALUES ('36', '1', 'Pengaturan', 'Update pengaturan toko', '2026-05-05 08:43:37');
INSERT INTO `log_aktivitas` VALUES ('37', '1', 'Pengaturan', 'Update pengaturan toko', '2026-05-05 08:44:35');
INSERT INTO `log_aktivitas` VALUES ('38', '1', 'Backup', 'Backup database: backup_20260505_084445.sql (22.6 KB)', '2026-05-05 08:44:45');
INSERT INTO `log_aktivitas` VALUES ('39', '1', 'Backup', 'Backup database: backup_20260505_084521.sql (22.8 KB)', '2026-05-05 08:45:21');
INSERT INTO `log_aktivitas` VALUES ('40', '1', 'Backup', 'Backup database: backup_20260505_085000.sql (22.9 KB)', '2026-05-05 08:50:00');
INSERT INTO `log_aktivitas` VALUES ('41', '1', 'Backup', 'Backup database: backup_20260505_085218.sql (23.1 KB)', '2026-05-05 08:52:18');
INSERT INTO `log_aktivitas` VALUES ('42', '1', 'Backup', 'Backup database: backup_20260505_085304.sql (23.2 KB)', '2026-05-05 08:53:04');
INSERT INTO `log_aktivitas` VALUES ('43', '1', 'Pengaturan', 'Update pengaturan toko', '2026-05-05 08:56:10');


-- --------------------------------------------
-- Tabel: mutasi_stok
-- --------------------------------------------

DROP TABLE IF EXISTS `mutasi_stok`;
CREATE TABLE `mutasi_stok` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `produk_id` int(11) NOT NULL,
  `transaksi_id` int(11) DEFAULT NULL,
  `pembelian_id` int(11) DEFAULT NULL,
  `qty_masuk` int(11) NOT NULL DEFAULT 0,
  `qty_keluar` int(11) NOT NULL DEFAULT 0,
  `keterangan` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `produk_id` (`produk_id`),
  KEY `transaksi_id` (`transaksi_id`),
  KEY `pembelian_id` (`pembelian_id`),
  CONSTRAINT `mutasi_stok_ibfk_1` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`),
  CONSTRAINT `mutasi_stok_ibfk_2` FOREIGN KEY (`transaksi_id`) REFERENCES `transaksi` (`id`),
  CONSTRAINT `mutasi_stok_ibfk_3` FOREIGN KEY (`pembelian_id`) REFERENCES `pembelian` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Data untuk tabel mutasi_stok
INSERT INTO `mutasi_stok` VALUES ('13', '30', '7', NULL, '0', '24', 'Penjualan INV-20260504-029', '2026-05-04 09:45:05');
INSERT INTO `mutasi_stok` VALUES ('14', '30', NULL, NULL, '96', '0', 'Opname: Lainnya', '2026-05-04 10:08:25');
INSERT INTO `mutasi_stok` VALUES ('15', '30', '7', NULL, '24', '0', 'Void transaksi: INV-20260504-029', '2026-05-04 10:13:34');
INSERT INTO `mutasi_stok` VALUES ('16', '30', '10', NULL, '0', '24', 'Penjualan INV-20260504-248', '2026-05-04 10:14:54');
INSERT INTO `mutasi_stok` VALUES ('17', '30', '10', NULL, '24', '0', 'Void transaksi: INV-20260504-248', '2026-05-04 10:44:30');
INSERT INTO `mutasi_stok` VALUES ('18', '46', '11', NULL, '0', '1', 'Penjualan INV-20260504-039', '2026-05-04 11:20:26');
INSERT INTO `mutasi_stok` VALUES ('19', '30', '12', NULL, '0', '1', 'Penjualan INV-20260504-322', '2026-05-04 11:28:26');
INSERT INTO `mutasi_stok` VALUES ('20', '30', '13', NULL, '0', '1', 'Penjualan INV-20260504-185', '2026-05-04 11:42:33');
INSERT INTO `mutasi_stok` VALUES ('21', '43', '13', NULL, '0', '1', 'Penjualan INV-20260504-185', '2026-05-04 11:42:33');


-- --------------------------------------------
-- Tabel: pelanggan
-- --------------------------------------------

DROP TABLE IF EXISTS `pelanggan`;
CREATE TABLE `pelanggan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `saldo_piutang` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Data untuk tabel pelanggan
INSERT INTO `pelanggan` VALUES ('1', 'Umum', '-', '-', '0.00', '2026-05-03 11:11:18');
INSERT INTO `pelanggan` VALUES ('2', 'pelanggan 1', '085204614659', 'lmg', '0.00', '2026-05-04 10:16:38');


-- --------------------------------------------
-- Tabel: pembelian
-- --------------------------------------------

DROP TABLE IF EXISTS `pembelian`;
CREATE TABLE `pembelian` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `produk_id` int(11) NOT NULL,
  `supplier` varchar(100) DEFAULT NULL,
  `qty` int(11) NOT NULL,
  `total_harga` decimal(10,2) NOT NULL,
  `harga_satuan` decimal(10,2) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `produk_id` (`produk_id`),
  CONSTRAINT `pembelian_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `pembelian_ibfk_2` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;


-- --------------------------------------------
-- Tabel: pengaturan
-- --------------------------------------------

DROP TABLE IF EXISTS `pengaturan`;
CREATE TABLE `pengaturan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kunci` varchar(50) NOT NULL,
  `nilai` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kunci` (`kunci`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data untuk tabel pengaturan
INSERT INTO `pengaturan` VALUES ('1', 'toko_nama', 'Toko Kelontong Jaya Raya');
INSERT INTO `pengaturan` VALUES ('2', 'toko_alamat', 'Jl. Merdeka No. 123, Jakarta');
INSERT INTO `pengaturan` VALUES ('3', 'toko_telp', '0812-3456-7890');
INSERT INTO `pengaturan` VALUES ('4', 'struk_footer', 'Terima kasih telah berbelanja\r\nBarang yang sudah dibeli\r\ntidak dapat ditukar');
INSERT INTO `pengaturan` VALUES ('5', 'printer_ukuran', '58mm');
INSERT INTO `pengaturan` VALUES ('6', 'printer_auto', '0');
INSERT INTO `pengaturan` VALUES ('7', 'backup_path', 'sql');
INSERT INTO `pengaturan` VALUES ('8', 'backup_auto', '0');


-- --------------------------------------------
-- Tabel: pengeluaran
-- --------------------------------------------

DROP TABLE IF EXISTS `pengeluaran`;
CREATE TABLE `pengeluaran` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `kategori` varchar(50) NOT NULL,
  `jumlah` decimal(10,2) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `pengeluaran_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;


-- --------------------------------------------
-- Tabel: piutang
-- --------------------------------------------

DROP TABLE IF EXISTS `piutang`;
CREATE TABLE `piutang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pelanggan_id` int(11) NOT NULL,
  `transaksi_id` int(11) DEFAULT NULL,
  `no_referensi` varchar(50) DEFAULT NULL,
  `tipe` enum('transaksi','pinjaman','pembayaran') NOT NULL,
  `jumlah` decimal(10,2) NOT NULL,
  `saldo_sebelum` decimal(10,2) NOT NULL DEFAULT 0.00,
  `saldo_sesudah` decimal(10,2) NOT NULL DEFAULT 0.00,
  `keterangan` text DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `pelanggan_id` (`pelanggan_id`),
  KEY `transaksi_id` (`transaksi_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `piutang_ibfk_1` FOREIGN KEY (`pelanggan_id`) REFERENCES `pelanggan` (`id`),
  CONSTRAINT `piutang_ibfk_2` FOREIGN KEY (`transaksi_id`) REFERENCES `transaksi` (`id`),
  CONSTRAINT `piutang_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Data untuk tabel piutang
INSERT INTO `piutang` VALUES ('1', '2', '11', 'INV-20260504-039', 'transaksi', '5000.00', '0.00', '5000.00', 'Kekurangan bayar transaksi', '1', '2026-05-04 11:20:26');
INSERT INTO `piutang` VALUES ('2', '2', '12', 'INV-20260504-322', 'transaksi', '3000.00', '5000.00', '8000.00', 'Kekurangan bayar transaksi', '1', '2026-05-04 11:28:26');
INSERT INTO `piutang` VALUES ('3', '2', NULL, 'PINJ-20260504-960', 'pinjaman', '5000.00', '8000.00', '13000.00', 'pulsa', '1', '2026-05-04 11:31:57');
INSERT INTO `piutang` VALUES ('4', '2', NULL, 'BYR-202605041132', 'pembayaran', '5000.00', '13000.00', '8000.00', 'Pembayaran piutang', '1', '2026-05-04 11:32:13');
INSERT INTO `piutang` VALUES ('5', '2', '13', 'INV-20260504-185', 'transaksi', '8500.00', '8000.00', '16500.00', 'Kekurangan bayar transaksi', '1', '2026-05-04 11:42:33');
INSERT INTO `piutang` VALUES ('6', '2', NULL, 'BYR-202605041309', 'pembayaran', '16500.00', '16500.00', '0.00', 'Pembayaran piutang', '1', '2026-05-04 13:09:12');


-- --------------------------------------------
-- Tabel: produk
-- --------------------------------------------

DROP TABLE IF EXISTS `produk`;
CREATE TABLE `produk` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kode` varchar(50) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `satuan_dasar` varchar(20) NOT NULL DEFAULT 'pcs',
  `harga_beli` decimal(10,2) NOT NULL DEFAULT 0.00,
  `harga_jual` decimal(10,2) NOT NULL DEFAULT 0.00,
  `stok_dasar` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode` (`kode`)
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Data untuk tabel produk
INSERT INTO `produk` VALUES ('30', '888608101053', 'Aqua Botol 600ml', NULL, 'botol', '2000.00', '3000.00', '122', '2026-05-03 11:49:13', '2026-05-04 11:42:33');
INSERT INTO `produk` VALUES ('31', '888608101138', 'Aqua Galon Asli', NULL, 'galon', '19000.00', '21000.00', '10', '2026-05-03 11:49:13', '2026-05-03 12:58:52');
INSERT INTO `produk` VALUES ('32', 'BRG-AQUA-DUS', 'Aqua Botol 600ml / 1 Dus', NULL, 'dus', '46000.00', '52000.00', '20', '2026-05-03 11:49:13', '2026-05-03 12:58:52');
INSERT INTO `produk` VALUES ('33', 'BRG-CLEO-DUS', 'Cleo Gelas / 1 Dus', NULL, 'dus', '21500.00', '24000.00', '15', '2026-05-03 11:49:13', '2026-05-03 12:58:52');
INSERT INTO `produk` VALUES ('34', '8992696523081', 'Milo Kotak 115ml', NULL, 'kotak', '2850.00', '3500.00', '40', '2026-05-03 11:49:13', '2026-05-03 12:58:52');
INSERT INTO `produk` VALUES ('35', '89906010600849', 'Nips Madu Botol', NULL, 'botol', '3250.00', '4000.00', '30', '2026-05-03 11:49:13', '2026-05-03 12:58:52');
INSERT INTO `produk` VALUES ('36', 'BRG-POCARI', 'Pocari Sweat 500ml', NULL, 'botol', '6350.00', '7500.00', '35', '2026-05-03 11:49:13', '2026-05-03 12:58:52');
INSERT INTO `produk` VALUES ('37', 'BRG-TEH-PUCUK', 'Teh Pucuk 350ml', NULL, 'botol', '2500.00', '3500.00', '50', '2026-05-03 11:49:13', '2026-05-03 12:58:52');
INSERT INTO `produk` VALUES ('38', '8999908000101', 'NEO RHEUMACYLO 10 pcs', NULL, 'pcs', '4500.00', '5000.00', '30', '2026-05-03 11:49:13', '2026-05-03 12:58:52');
INSERT INTO `produk` VALUES ('39', '89992003782354', 'Antangin Cair', NULL, 'sachet', '3125.00', '4000.00', '60', '2026-05-03 11:49:13', '2026-05-03 12:58:52');
INSERT INTO `produk` VALUES ('40', 'BRG-GULA-SJP', 'Gula SJP', NULL, 'kg', '15800.00', '17000.00', '25', '2026-05-03 11:49:13', '2026-05-03 12:58:52');
INSERT INTO `produk` VALUES ('41', 'BRG-FERMINA', 'Fermina Botol 700ml', NULL, 'botol', '16250.00', '17500.00', '20', '2026-05-03 11:49:13', '2026-05-03 12:58:52');
INSERT INTO `produk` VALUES ('42', 'BRG-MINYAK-KITA', 'Minyak Kita 1 Liter', NULL, 'botol', '15800.00', '17000.00', '30', '2026-05-03 11:49:13', '2026-05-03 12:58:52');
INSERT INTO `produk` VALUES ('43', 'BRG-RINSO', 'Rinso Cair 20ml / 1 Renteng', NULL, 'renteng', '4625.00', '5500.00', '24', '2026-05-03 11:49:13', '2026-05-04 11:42:33');
INSERT INTO `produk` VALUES ('44', 'BRG-SPENTEN', 'Sasu Pentene', NULL, 'sachet', '400.00', '500.00', '99', '2026-05-03 11:49:13', '2026-05-03 14:31:56');
INSERT INTO `produk` VALUES ('45', '4902430566896', 'Head and Shoulders Sachet', NULL, 'sachet', '400.00', '500.00', '79', '2026-05-03 11:49:13', '2026-05-03 14:31:56');
INSERT INTO `produk` VALUES ('46', 'BRG-EMERON', 'Sampo Emeron / 1 Renteng', NULL, 'renteng', '4450.00', '5000.00', '19', '2026-05-03 11:49:13', '2026-05-04 11:20:26');
INSERT INTO `produk` VALUES ('47', 'BRG-SOKLIN-10RB', 'Soklin 10rb 425gr', NULL, 'pcs', '8600.00', '10000.00', '25', '2026-05-03 11:49:13', '2026-05-03 12:58:52');
INSERT INTO `produk` VALUES ('48', 'BRG-SOKLIN-PK-BIJI', 'Soklin PK / Biji', NULL, 'pcs', '700.00', '1000.00', '80', '2026-05-03 11:49:13', '2026-05-03 12:58:52');
INSERT INTO `produk` VALUES ('49', 'BRG-SOKLIN-PK-RENTENG', 'Soklin PK / Renteng', NULL, 'renteng', '4750.00', '5500.00', '20', '2026-05-03 11:49:13', '2026-05-03 12:58:52');
INSERT INTO `produk` VALUES ('50', '8999909096004', 'Sampoerna A Mild 16', NULL, 'bungkus', '34900.00', '38000.00', '50', '2026-05-03 11:49:13', '2026-05-03 12:58:52');
INSERT INTO `produk` VALUES ('51', '8999909000773', 'Marlboro Hitam 12', NULL, 'bungkus', '23150.00', '25000.00', '40', '2026-05-03 11:49:13', '2026-05-03 12:58:52');
INSERT INTO `produk` VALUES ('52', '8999106101019', 'Djarum Super 12', NULL, 'bungkus', '22950.00', '26000.00', '45', '2026-05-03 11:49:13', '2026-05-03 12:58:52');
INSERT INTO `produk` VALUES ('53', '8997103020829', 'Gajah Baru Filter 12', NULL, 'bungkus', '16750.00', '18500.00', '30', '2026-05-03 11:49:13', '2026-05-03 12:58:52');
INSERT INTO `produk` VALUES ('54', '8997224070176', 'Galang Baru 12', NULL, 'bungkus', '16100.00', '17500.00', '35', '2026-05-03 11:49:13', '2026-05-03 12:58:52');
INSERT INTO `produk` VALUES ('55', '8997229129891', 'Grandel Filter 12', NULL, 'bungkus', '15100.00', '16500.00', '30', '2026-05-03 11:49:13', '2026-05-03 12:58:52');
INSERT INTO `produk` VALUES ('56', '8992745320050', 'Stella Gantung', NULL, 'pcs', '9000.00', '10000.00', '20', '2026-05-03 11:49:13', '2026-05-03 12:58:52');
INSERT INTO `produk` VALUES ('57', 'BRG-KAPAL-SPESIAL-BIJI', 'Kapal Api Spesial Mix / Biji', NULL, 'sachet', '1600.00', '2000.00', '100', '2026-05-03 11:49:13', '2026-05-03 12:58:52');
INSERT INTO `produk` VALUES ('58', 'BRG-KAPAL-SPESIAL-RENTENG', 'Kapal Api Spesial Mix / Renteng', NULL, 'renteng', '16500.00', '18000.00', '20', '2026-05-03 11:49:13', '2026-05-03 12:58:52');


-- --------------------------------------------
-- Tabel: satuan
-- --------------------------------------------

DROP TABLE IF EXISTS `satuan`;
CREATE TABLE `satuan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `produk_id` int(11) NOT NULL,
  `nama_satuan` varchar(50) NOT NULL,
  `isi_satuan` int(11) NOT NULL,
  `harga_jual` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `produk_id` (`produk_id`),
  CONSTRAINT `satuan_ibfk_1` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Data untuk tabel satuan
INSERT INTO `satuan` VALUES ('1', '30', 'Dus', '24', '60000.00', '2026-05-04 07:54:23');


-- --------------------------------------------
-- Tabel: stock_opname
-- --------------------------------------------

DROP TABLE IF EXISTS `stock_opname`;
CREATE TABLE `stock_opname` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `produk_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `stok_sistem` int(11) NOT NULL,
  `stok_nyata` int(11) NOT NULL,
  `selisih` int(11) NOT NULL,
  `tipe` enum('kurang','tambah') NOT NULL,
  `alasan` varchar(50) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `kerugian` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `produk_id` (`produk_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `stock_opname_ibfk_1` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`),
  CONSTRAINT `stock_opname_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Data untuk tabel stock_opname
INSERT INTO `stock_opname` VALUES ('1', '30', '1', '4', '100', '96', 'tambah', 'Lainnya', 'tambahan', '0.00', '2026-05-04 10:08:25');


-- --------------------------------------------
-- Tabel: transaksi
-- --------------------------------------------

DROP TABLE IF EXISTS `transaksi`;
CREATE TABLE `transaksi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `no_invoice` varchar(20) NOT NULL,
  `user_id` int(11) NOT NULL,
  `pelanggan_id` int(11) DEFAULT NULL,
  `total` decimal(10,2) NOT NULL,
  `bayar` decimal(10,2) NOT NULL,
  `kembalian` decimal(10,2) NOT NULL DEFAULT 0.00,
  `kurang` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('lunas','piutang','void') NOT NULL DEFAULT 'lunas',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `no_invoice` (`no_invoice`),
  KEY `user_id` (`user_id`),
  KEY `pelanggan_id` (`pelanggan_id`),
  CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `transaksi_ibfk_2` FOREIGN KEY (`pelanggan_id`) REFERENCES `pelanggan` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Data untuk tabel transaksi
INSERT INTO `transaksi` VALUES ('7', 'INV-20260504-029', '1', NULL, '60000.00', '100000.00', '40000.00', '0.00', 'void', '2026-05-04 09:45:05');
INSERT INTO `transaksi` VALUES ('10', 'INV-20260504-248', '1', NULL, '55000.00', '60000.00', '5000.00', '0.00', 'void', '2026-05-04 10:14:54');
INSERT INTO `transaksi` VALUES ('11', 'INV-20260504-039', '1', '2', '5000.00', '0.00', '0.00', '5000.00', 'piutang', '2026-05-04 11:20:26');
INSERT INTO `transaksi` VALUES ('12', 'INV-20260504-322', '1', '2', '3000.00', '0.00', '0.00', '3000.00', 'piutang', '2026-05-04 11:28:26');
INSERT INTO `transaksi` VALUES ('13', 'INV-20260504-185', '1', '2', '8500.00', '0.00', '0.00', '8500.00', 'piutang', '2026-05-04 11:42:33');


-- --------------------------------------------
-- Tabel: transaksi_detail
-- --------------------------------------------

DROP TABLE IF EXISTS `transaksi_detail`;
CREATE TABLE `transaksi_detail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transaksi_id` int(11) NOT NULL,
  `produk_id` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  `qty_dasar` int(11) NOT NULL DEFAULT 0,
  `satuan` varchar(50) DEFAULT NULL,
  `tipe_harga` varchar(50) DEFAULT NULL,
  `harga_satuan` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `transaksi_id` (`transaksi_id`),
  KEY `produk_id` (`produk_id`),
  CONSTRAINT `transaksi_detail_ibfk_1` FOREIGN KEY (`transaksi_id`) REFERENCES `transaksi` (`id`) ON DELETE CASCADE,
  CONSTRAINT `transaksi_detail_ibfk_2` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Data untuk tabel transaksi_detail
INSERT INTO `transaksi_detail` VALUES ('1', '7', '30', '1', '24', 'Dus', 'Paket', '60000.00', '60000.00');
INSERT INTO `transaksi_detail` VALUES ('2', '10', '30', '1', '24', 'Dus', 'Paket', '55000.00', '55000.00');
INSERT INTO `transaksi_detail` VALUES ('3', '11', '46', '1', '1', 'renteng', 'Ecer', '5000.00', '5000.00');
INSERT INTO `transaksi_detail` VALUES ('4', '12', '30', '1', '1', 'botol', 'Ecer', '3000.00', '3000.00');
INSERT INTO `transaksi_detail` VALUES ('5', '13', '30', '1', '1', 'botol', 'Ecer', '3000.00', '3000.00');
INSERT INTO `transaksi_detail` VALUES ('6', '13', '43', '1', '1', 'renteng', 'Ecer', '5500.00', '5500.00');


-- --------------------------------------------
-- Tabel: users
-- --------------------------------------------

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `role` enum('admin','kasir') NOT NULL DEFAULT 'kasir',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Data untuk tabel users
INSERT INTO `users` VALUES ('1', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin', '2026-05-03 11:11:18');
INSERT INTO `users` VALUES ('2', 'kasir1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Budi Santoso', 'kasir', '2026-05-03 11:11:18');
INSERT INTO `users` VALUES ('3', 'kasir2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Siti Nurhaliza', 'kasir', '2026-05-03 11:11:18');

