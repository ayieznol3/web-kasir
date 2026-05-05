<?php
session_start();
require_once '../config/database.php';
require_once '../functions/helper.php';

$id = $_GET['id'] ?? 0;

// Cek piutang
$pl = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM pelanggan WHERE id=$id"));
if ($pl['saldo_piutang'] > 0) {
    $_SESSION['error'] = "Tidak bisa hapus! Pelanggan masih punya piutang Rp " . number_format($pl['saldo_piutang']);
    $_SESSION['error_type'] = 'danger';
} else {
    mysqli_query($conn, "DELETE FROM pelanggan WHERE id=$id");
    $_SESSION['sukses'] = "Pelanggan dihapus!";
    $_SESSION['sukses_type'] = 'success';
}

redirect('../index.php?page=pelanggan');
?>