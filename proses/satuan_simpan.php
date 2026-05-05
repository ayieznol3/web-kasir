<?php
session_start();
require_once '../config/database.php';
require_once '../functions/helper.php';

if (!isset($_SESSION['user_id']) || !isAdmin()) {
    redirect('../index.php?page=login');
}

$produk_id = (int)$_POST['produk_id'];
$nama_satuan = esc($_POST['nama_satuan']);
$isi_satuan = (int)$_POST['isi_satuan'];
$harga_jual = $_POST['harga_jual'] !== '' ? (int)$_POST['harga_jual'] : 'NULL';

$sql = "INSERT INTO satuan (produk_id, nama_satuan, isi_satuan, harga_jual) 
        VALUES ($produk_id, '$nama_satuan', $isi_satuan, $harga_jual)";
mysqli_query($conn, $sql);

// Log
$produk = mysqli_fetch_assoc(mysqli_query($conn, "SELECT nama FROM produk WHERE id = $produk_id"));
mysqli_query($conn, "INSERT INTO log_aktivitas (user_id, aktivitas, detail) 
                     VALUES ({$_SESSION['user_id']}, 'Tambah Satuan', 'Satuan $nama_satuan untuk {$produk['nama']}')");

$_SESSION['sukses'] = "Satuan <strong>$nama_satuan</strong> berhasil ditambahkan!";
$_SESSION['sukses_type'] = 'success';
redirect("../index.php?page=satuan&produk_id=$produk_id");
?>