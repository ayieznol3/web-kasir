<?php
session_start();
require_once '../config/database.php';
require_once '../functions/helper.php';

if (!isset($_SESSION['user_id']) || !isAdmin()) {
    redirect('../index.php?page=login');
}

$produk_id = (int)$_POST['produk_id'];
$min_qty = (int)$_POST['min_qty'];
$tipe_diskon = esc($_POST['tipe_diskon']);
$nilai_diskon = (float)$_POST['nilai_diskon'];

$sql = "INSERT INTO grosir (produk_id, min_qty, tipe_diskon, nilai_diskon) 
        VALUES ($produk_id, $min_qty, '$tipe_diskon', $nilai_diskon)";
mysqli_query($conn, $sql);

$produk = mysqli_fetch_assoc(mysqli_query($conn, "SELECT nama FROM produk WHERE id = $produk_id"));
mysqli_query($conn, "INSERT INTO log_aktivitas (user_id, aktivitas, detail) 
                     VALUES ({$_SESSION['user_id']}, 'Tambah Grosir', 'Grosir min $min_qty untuk {$produk['nama']}')");

$_SESSION['sukses'] = "Aturan grosir berhasil ditambahkan!";
$_SESSION['sukses_type'] = 'success';
redirect("../index.php?page=satuan&produk_id=$produk_id");
?>