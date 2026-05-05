<?php
session_start();
require_once '../config/database.php';
require_once '../functions/helper.php';

if (!isset($_SESSION['user_id'])) redirect('../index.php?page=login');

$user_id = $_SESSION['user_id'];
$produk_id = (int)$_POST['produk_id'];
$qty = (int)$_POST['qty'];
$total_harga = (int)$_POST['total_harga'];
$supplier = esc($_POST['supplier']);
$keterangan = esc($_POST['keterangan']);

if ($qty <= 0 || $total_harga <= 0) {
    $_SESSION['error'] = 'Data tidak valid!';
    $_SESSION['error_type'] = 'danger';
    redirect('../index.php?page=restok');
}

$harga_satuan = round($total_harga / $qty);

mysqli_begin_transaction($conn);

try {
    // 1. Insert pembelian
    mysqli_query($conn, "INSERT INTO pembelian (user_id, produk_id, supplier, qty, total_harga, harga_satuan, keterangan) 
                         VALUES ($user_id, $produk_id, '$supplier', $qty, $total_harga, $harga_satuan, '$keterangan')");
    $pembelian_id = mysqli_insert_id($conn);
    
    // 2. Update stok
    mysqli_query($conn, "UPDATE produk SET stok_dasar = stok_dasar + $qty WHERE id = $produk_id");
    
    // 3. Update harga beli rata-rata
    $produk = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM produk WHERE id = $produk_id"));
    $stok_lama = $produk['stok_dasar'] - $qty;
    $total_lama = $stok_lama * $produk['harga_beli'];
    $total_baru = $qty * $harga_satuan;
    $harga_rata = round(($total_lama + $total_baru) / ($stok_lama + $qty));
    mysqli_query($conn, "UPDATE produk SET harga_beli = $harga_rata WHERE id = $produk_id");
    
    // 4. Mutasi stok
    mysqli_query($conn, "INSERT INTO mutasi_stok (produk_id, pembelian_id, qty_masuk, keterangan) 
                         VALUES ($produk_id, $pembelian_id, $qty, 'Restok: $keterangan')");
    
    // 5. Log
    mysqli_query($conn, "INSERT INTO log_aktivitas (user_id, aktivitas, detail) 
                         VALUES ($user_id, 'Restok', 'Restok {$produk['nama']}: +$qty, Rp $total_harga')");
    
    mysqli_commit($conn);
    $_SESSION['sukses'] = "Restok berhasil! Stok {$produk['nama']} +$qty";
    $_SESSION['sukses_type'] = 'success';
    
} catch (Exception $e) {
    mysqli_rollback($conn);
    $_SESSION['error'] = 'Gagal: ' . $e->getMessage();
    $_SESSION['error_type'] = 'danger';
}

redirect('../index.php?page=restok');
?>