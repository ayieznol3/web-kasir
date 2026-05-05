<?php
session_start();
require_once '../config/database.php';
require_once '../functions/helper.php';
require_once '../functions/upload.php';

if (!isset($_SESSION['user_id']) || !isAdmin()) {
    redirect('../index.php?page=login');
}

$id = $_GET['id'] ?? 0;

// Ambil nama & gambar
$p = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM produk WHERE id = $id"));

if (!$p) {
    $_SESSION['error'] = 'Produk tidak ditemukan!';
    redirect('../index.php?page=produk');
}

// Cek apakah ada transaksi terkait
$cek = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM transaksi_detail WHERE produk_id = $id"));

if ($cek['total'] > 0) {
    $_SESSION['error'] = 'Produk tidak bisa dihapus karena sudah ada transaksi! Nonaktifkan saja stoknya.';
    $_SESSION['error_type'] = 'danger';
    redirect('../index.php?page=produk');
}

// Hapus gambar
hapusGambar($p['gambar']);

// Hapus produk
mysqli_query($conn, "DELETE FROM produk WHERE id = $id");
mysqli_query($conn, "INSERT INTO log_aktivitas (user_id, aktivitas, detail) VALUES ({$_SESSION['user_id']}, 'Hapus Produk', 'Hapus produk: {$p['nama']}')");

$_SESSION['sukses'] = "Produk <strong>{$p['nama']}</strong> berhasil dihapus!";
$_SESSION['sukses_type'] = 'success';
redirect('../index.php?page=produk');
?>