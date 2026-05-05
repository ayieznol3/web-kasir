<?php
session_start();
require_once '../config/database.php';
require_once '../functions/helper.php';

$id = $_GET['id'] ?? 0;
$produk_id = $_GET['produk_id'] ?? 0;

mysqli_query($conn, "DELETE FROM satuan WHERE id = $id");
mysqli_query($conn, "INSERT INTO log_aktivitas (user_id, aktivitas, detail) VALUES ({$_SESSION['user_id']}, 'Hapus Satuan', 'Hapus satuan ID: $id')");

$_SESSION['sukses'] = "Satuan berhasil dihapus!";
$_SESSION['sukses_type'] = 'success';
redirect("../index.php?page=satuan&produk_id=$produk_id");
?>