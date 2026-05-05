<?php
session_start();
require_once '../config/database.php';
require_once '../functions/helper.php';
require_once '../functions/upload.php';

if (!isset($_SESSION['user_id']) || !isAdmin()) {
    redirect('../index.php?page=login');
}

$aksi = $_POST['aksi'] ?? 'tambah';
$id = $_POST['id'] ?? null;
$gambar_lama = $_POST['gambar_lama'] ?? null;

// Upload gambar jika ada
$upload_result = uploadGambar($_FILES['gambar'] ?? null, $gambar_lama);

if ($upload_result['success']) {
    $gambar = $upload_result['nama_file'];
} elseif (isset($_FILES['gambar']) && $_FILES['gambar']['error'] != 4) {
    $_SESSION['error'] = $upload_result['error'] ?? 'Gagal upload gambar';
    $_SESSION['error_type'] = 'danger';
    redirect('../index.php?page=produk-tambah');
} else {
    $gambar = $gambar_lama ?? 'default.png';
}

// Ambil data
$kode = esc($_POST['kode']);
$nama = esc($_POST['nama']);
$satuan_dasar = esc($_POST['satuan_dasar']);
$harga_beli = (int)$_POST['harga_beli'];
$harga_jual = (int)$_POST['harga_jual'];
$stok_dasar = (int)$_POST['stok_dasar'];

if ($aksi == 'edit' && $id) {
    // Update
    $sql = "UPDATE produk SET 
            kode = '$kode',
            nama = '$nama',
            gambar = '$gambar',
            satuan_dasar = '$satuan_dasar',
            harga_beli = $harga_beli,
            harga_jual = $harga_jual,
            stok_dasar = $stok_dasar
            WHERE id = $id";
    
    mysqli_query($conn, $sql);
    mysqli_query($conn, "INSERT INTO log_aktivitas (user_id, aktivitas, detail) VALUES ({$_SESSION['user_id']}, 'Edit Produk', 'Edit produk: $nama')");
    
    $_SESSION['sukses'] = "Produk <strong>$nama</strong> berhasil diupdate!";
    
} else {
    // Insert
    $sql = "INSERT INTO produk (kode, nama, gambar, satuan_dasar, harga_beli, harga_jual, stok_dasar) 
            VALUES ('$kode', '$nama', '$gambar', '$satuan_dasar', $harga_beli, $harga_jual, $stok_dasar)";
    
    mysqli_query($conn, $sql);
    mysqli_query($conn, "INSERT INTO log_aktivitas (user_id, aktivitas, detail) VALUES ({$_SESSION['user_id']}, 'Tambah Produk', 'Tambah produk: $nama')");
    
    $_SESSION['sukses'] = "Produk <strong>$nama</strong> berhasil ditambahkan!";
}

$_SESSION['sukses_type'] = 'success';
redirect('../index.php?page=produk');
?>