<?php
session_start();
require_once '../config/database.php';
require_once '../functions/helper.php';

if (!isset($_SESSION['user_id'])) {
    redirect('../index.php?page=login');
}

$user_id = $_SESSION['user_id'];
$kategori = mysqli_real_escape_string($conn, $_POST['kategori']);
$jumlah = (int)$_POST['jumlah'];
$keterangan = mysqli_real_escape_string($conn, $_POST['keterangan']);

if ($jumlah <= 0) {
    $_SESSION['error'] = 'Jumlah harus lebih dari 0!';
    $_SESSION['error_type'] = 'danger';
    redirect('../index.php?page=pengeluaran');
}

mysqli_query($conn, "INSERT INTO pengeluaran (user_id, kategori, jumlah, keterangan) 
                     VALUES ($user_id, '$kategori', $jumlah, '$keterangan')");

mysqli_query($conn, "INSERT INTO log_aktivitas (user_id, aktivitas, detail) 
                     VALUES ($user_id, 'Pengeluaran', 'Kategori: $kategori, Jumlah: Rp $jumlah')");

$_SESSION['sukses'] = "Pengeluaran <strong>$kategori</strong> sebesar <?= rupiah($jumlah) ?> berhasil dicatat!";
$_SESSION['sukses_type'] = 'success';

redirect('../index.php?page=pengeluaran');
?>