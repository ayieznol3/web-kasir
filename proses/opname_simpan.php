<?php
session_start();
require_once '../config/database.php';
require_once '../functions/helper.php';

if (!isset($_SESSION['user_id']) || !isAdmin()) {
    redirect('../index.php?page=login');
}

$user_id = $_SESSION['user_id'];
$produk_id = (int)$_POST['produk_id'];
$stok_nyata = (int)$_POST['stok_nyata'];
$alasan = mysqli_real_escape_string($conn, $_POST['alasan']);
$keterangan = mysqli_real_escape_string($conn, $_POST['keterangan']);

// Ambil stok sistem
$produk = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM produk WHERE id = $produk_id"));
$stok_sistem = $produk['stok_dasar'];
$selisih = $stok_nyata - $stok_sistem;
$tipe = $selisih < 0 ? 'kurang' : 'tambah';

// Hitung kerugian (hanya jika stok berkurang)
$kerugian = 0;
if ($selisih < 0) {
    $kerugian = abs($selisih) * $produk['harga_beli'];
}

mysqli_begin_transaction($conn);

try {
    // 1. Insert stock_opname
    $sql = "INSERT INTO stock_opname (produk_id, user_id, stok_sistem, stok_nyata, selisih, tipe, alasan, keterangan, kerugian) 
            VALUES ($produk_id, $user_id, $stok_sistem, $stok_nyata, $selisih, '$tipe', '$alasan', '$keterangan', $kerugian)";
    mysqli_query($conn, $sql);
    $opname_id = mysqli_insert_id($conn);
    
    // 2. Update stok produk
    mysqli_query($conn, "UPDATE produk SET stok_dasar = $stok_nyata WHERE id = $produk_id");
    
    // 3. Mutasi stok
    if ($selisih < 0) {
        mysqli_query($conn, "INSERT INTO mutasi_stok (produk_id, qty_keluar, keterangan) 
                             VALUES ($produk_id, " . abs($selisih) . ", 'Opname: $alasan')");
    } elseif ($selisih > 0) {
        mysqli_query($conn, "INSERT INTO mutasi_stok (produk_id, qty_masuk, keterangan) 
                             VALUES ($produk_id, $selisih, 'Opname: $alasan')");
    }
    
    // 4. Catat kerugian di pengeluaran (jika stok berkurang)
    if ($kerugian > 0) {
        mysqli_query($conn, "INSERT INTO pengeluaran (user_id, kategori, jumlah, keterangan) 
                             VALUES ($user_id, 'Kerugian Stok', $kerugian, 'Opname: {$produk['nama']} - $alasan (".abs($selisih)." {$produk['satuan_dasar']})')");
    }
    
    // 5. Log aktivitas
    $log_detail = "Opname: {$produk['nama']} | Sistem: $stok_sistem → Nyata: $stok_nyata | Selisih: $selisih | Alasan: $alasan";
    if ($kerugian > 0) $log_detail .= " | Kerugian: Rp $kerugian";
    mysqli_query($conn, "INSERT INTO log_aktivitas (user_id, aktivitas, detail) VALUES ($user_id, 'Stock Opname', '$log_detail')");
    
    mysqli_commit($conn);
    
    $_SESSION['sukses'] = "Opname berhasil! {$produk['nama']}: $stok_sistem → $stok_nyata";
    $_SESSION['sukses_type'] = 'success';
    
} catch (Exception $e) {
    mysqli_rollback($conn);
    $_SESSION['error'] = 'Gagal: ' . $e->getMessage();
    $_SESSION['error_type'] = 'danger';
}

redirect('../index.php?page=stock-opname');
?>