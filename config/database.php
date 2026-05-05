<?php
date_default_timezone_set('Asia/Jakarta');


// Koneksi database
$host = '127.0.0.1';
$user = 'root';
$pass = '';
$db   = 'aplikasi_kasir';

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Set timezone
mysqli_query($conn, "SET time_zone = '+07:00'");

// ============================================
// KONFIGURASI TOKO
// ============================================
define('TOKO_NAMA', 'Toko Kelontong Jaya');
define('TOKO_ALAMAT', 'Jl. Merdeka No. 123, Jakarta');
define('TOKO_TELP', '0812-3456-7890');
define('APP_NAME', 'Kasir App');
define('APP_VERSION', '1.0.0');

// ============================================
// KONFIGURASI BACKUP
// ============================================
define('BACKUP_PATH', 'C:/Users/Toko/Google Drive/Backup Kasir/');
// Sesuaikan path dengan folder Google Drive kamu
// Contoh Linux/Mac: '/home/toko/Google Drive/Backup Kasir/'
?>