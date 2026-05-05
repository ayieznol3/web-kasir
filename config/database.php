<?php
date_default_timezone_set('Asia/Jakarta');

// Koneksi database
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'aplikasi_kasir';

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

mysqli_query($conn, "SET time_zone = '+07:00'");

// ============================================
// KONFIGURASI APLIKASI (WAJIB!)
// ============================================
define('APP_NAME', 'Kasir App');
define('APP_VERSION', '1.0.0');

// ============================================
// BACA PENGATURAN DARI DATABASE
// ============================================
$pengaturan = mysqli_query($conn, "SELECT kunci, nilai FROM pengaturan");
$setting = [];
if ($pengaturan && mysqli_num_rows($pengaturan) > 0) {
    while($row = mysqli_fetch_assoc($pengaturan)) {
        $setting[$row['kunci']] = $row['nilai'];
    }
}

define('TOKO_NAMA', $setting['toko_nama'] ?? 'Toko Kelontong Jaya');
define('TOKO_ALAMAT', $setting['toko_alamat'] ?? 'Jl. Merdeka No. 123');
define('TOKO_TELP', $setting['toko_telp'] ?? '0812-3456-7890');
define('STRUK_FOOTER', $setting['struk_footer'] ?? 'Terima kasih telah berbelanja');
define('PRINTER_UKURAN', $setting['printer_ukuran'] ?? '58mm');
define('BACKUP_PATH', $setting['backup_path'] ?? 'backups/');
?>