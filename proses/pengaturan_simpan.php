<?php
session_start();
require_once '../config/database.php';
require_once '../functions/helper.php';

if (!isset($_SESSION['user_id']) || !isAdmin()) {
    redirect('../index.php?page=login');
}

$data = [
    'toko_nama' => $_POST['toko_nama'] ?? '',
    'toko_alamat' => $_POST['toko_alamat'] ?? '',
    'toko_telp' => $_POST['toko_telp'] ?? '',
    'struk_footer' => $_POST['struk_footer'] ?? '',
    'printer_ukuran' => $_POST['printer_ukuran'] ?? '58mm',
    'printer_auto' => $_POST['printer_auto'] ?? '0',
    'backup_path' => $_POST['backup_path'] ?? 'backups/',
];

foreach ($data as $kunci => $nilai) {
    $nilai = mysqli_real_escape_string($conn, $nilai);
    mysqli_query($conn, "
        INSERT INTO pengaturan (kunci, nilai) VALUES ('$kunci', '$nilai')
        ON DUPLICATE KEY UPDATE nilai = '$nilai'
    ");
}

// Log
mysqli_query($conn, "INSERT INTO log_aktivitas (user_id, aktivitas, detail) VALUES ({$_SESSION['user_id']}, 'Pengaturan', 'Update pengaturan toko')");

$_SESSION['sukses'] = "Pengaturan berhasil disimpan!";
$_SESSION['sukses_type'] = 'success';
redirect('../index.php?page=pengaturan');
?>