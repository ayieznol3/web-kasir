<?php
session_start();
require_once '../config/database.php';
require_once '../functions/helper.php';

if (!isset($_SESSION['user_id'])) {
    redirect('../index.php?page=login');
}

$user_id = $_SESSION['user_id'];
$transaksi_id = (int)$_GET['id'];
$alasan = mysqli_real_escape_string($conn, $_GET['alasan'] ?? 'Tidak ada alasan');

// Ambil data transaksi
$t = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM transaksi WHERE id = $transaksi_id"));

if (!$t) {
    $_SESSION['error'] = 'Transaksi tidak ditemukan!';
    $_SESSION['error_type'] = 'danger';
    redirect('../index.php?page=transaksi');
}

if ($t['status'] == 'void') {
    $_SESSION['error'] = 'Transaksi sudah di-void sebelumnya!';
    $_SESSION['error_type'] = 'danger';
    redirect('../index.php?page=transaksi');
}

mysqli_begin_transaction($conn);

try {
    // 1. Update status transaksi jadi void
    mysqli_query($conn, "UPDATE transaksi SET status = 'void' WHERE id = $transaksi_id");
    
    // 2. Kembalikan stok
  $detail = mysqli_query($conn, "SELECT td.*, p.stok_dasar FROM transaksi_detail td JOIN produk p ON td.produk_id = p.id WHERE td.transaksi_id = $transaksi_id");
        while ($d = mysqli_fetch_assoc($detail)) {
        $qty_kembali = $d['qty_dasar'] > 0 ? $d['qty_dasar'] : $d['qty'];
    
        mysqli_query($conn, "UPDATE produk SET stok_dasar = stok_dasar + $qty_kembali WHERE id = {$d['produk_id']}");
    
        mysqli_query($conn, "INSERT INTO mutasi_stok (produk_id, transaksi_id, qty_masuk, keterangan) 
                         VALUES ({$d['produk_id']}, $transaksi_id, $qty_kembali, 'Void transaksi: {$t['no_invoice']}')");
}
    
    // 3. Batalkan piutang jika ada
    $piutang = mysqli_query($conn, "SELECT * FROM piutang WHERE transaksi_id = $transaksi_id AND tipe = 'transaksi'");
    if ($p = mysqli_fetch_assoc($piutang)) {
        // Update saldo pelanggan
        mysqli_query($conn, "UPDATE pelanggan SET saldo_piutang = saldo_piutang - {$p['jumlah']} WHERE id = {$p['pelanggan_id']}");
        
        // Catat pembatalan piutang
        $saldo_sekarang = mysqli_fetch_assoc(mysqli_query($conn, "SELECT saldo_piutang FROM pelanggan WHERE id = {$p['pelanggan_id']}"))['saldo_piutang'];
        mysqli_query($conn, "INSERT INTO piutang (pelanggan_id, transaksi_id, no_referensi, tipe, jumlah, saldo_sebelum, saldo_sesudah, keterangan, user_id) 
                             VALUES ({$p['pelanggan_id']}, $transaksi_id, 'VOID-{$t['no_invoice']}', 'pembayaran', {$p['jumlah']}, " . ($saldo_sekarang + $p['jumlah']) . ", $saldo_sekarang, 'Void transaksi: $alasan', $user_id)");
    }
    
    // 4. Log aktivitas
    mysqli_query($conn, "INSERT INTO log_aktivitas (user_id, aktivitas, detail) 
                         VALUES ($user_id, 'Void Transaksi', 'Void transaksi {$t['no_invoice']}: $alasan')");
    
    mysqli_commit($conn);
    
    $_SESSION['sukses'] = "Transaksi <strong>{$t['no_invoice']}</strong> berhasil di-void!";
    $_SESSION['sukses_type'] = 'success';
    
} catch (Exception $e) {
    mysqli_rollback($conn);
    $_SESSION['error'] = 'Gagal void: ' . $e->getMessage();
    $_SESSION['error_type'] = 'danger';
}

redirect('../index.php?page=transaksi');
?>