<?php
session_start();
require_once '../config/database.php';
require_once '../functions/helper.php';

if (!isset($_SESSION['user_id'])) {
    redirect('../index.php?page=login');
}

$user_id = $_SESSION['user_id'];
$pelanggan_id = (int)$_POST['pelanggan_id'];
$jumlah = (int)$_POST['jumlah'];
$keterangan = mysqli_real_escape_string($conn, $_POST['keterangan']);

// Validasi
if ($jumlah < 1000 || $pelanggan_id <= 0) {
    $_SESSION['error'] = 'Data tidak valid!';
    $_SESSION['error_type'] = 'danger';
    redirect("../index.php?page=piutang-detail&id=$pelanggan_id");
}

// Ambil saldo sebelum
$pl = mysqli_fetch_assoc(mysqli_query($conn, "SELECT saldo_piutang, nama FROM pelanggan WHERE id = $pelanggan_id"));
$saldo_sebelum = $pl['saldo_piutang'];
$saldo_sesudah = $saldo_sebelum + $jumlah;

// Generate no referensi
$no_ref = 'PINJ-' . date('Ymd') . '-' . sprintf('%03d', rand(1, 999));

mysqli_begin_transaction($conn);

try {
    // Insert piutang tipe pinjaman
    mysqli_query($conn, "
        INSERT INTO piutang (pelanggan_id, no_referensi, tipe, jumlah, saldo_sebelum, saldo_sesudah, keterangan, user_id) 
        VALUES ($pelanggan_id, '$no_ref', 'pinjaman', $jumlah, $saldo_sebelum, $saldo_sesudah, '$keterangan', $user_id)
    ");
    
    // Update saldo pelanggan
    mysqli_query($conn, "UPDATE pelanggan SET saldo_piutang = $saldo_sesudah WHERE id = $pelanggan_id");
    
    // Log aktivitas
    mysqli_query($conn, "
        INSERT INTO log_aktivitas (user_id, aktivitas, detail) 
        VALUES ($user_id, 'Pinjaman Manual', 'Pinjaman untuk {$pl['nama']}: Rp $jumlah, $keterangan')
    ");
    
    mysqli_commit($conn);
    
    $_SESSION['sukses'] = "Pinjaman untuk <strong>{$pl['nama']}</strong> sebesar <?= rupiah($jumlah) ?> berhasil dicatat!";
    $_SESSION['sukses_type'] = 'success';
    
} catch (Exception $e) {
    mysqli_rollback($conn);
    $_SESSION['error'] = 'Gagal: ' . $e->getMessage();
    $_SESSION['error_type'] = 'danger';
}

redirect("../index.php?page=piutang-detail&id=$pelanggan_id");
?>