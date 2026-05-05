<?php
session_start();
require_once '../config/database.php';
require_once '../functions/helper.php';

$pelanggan_id = (int)$_POST['pelanggan_id'];
$jumlah = (int)$_POST['jumlah'];
$keterangan = esc($_POST['keterangan']);
$user_id = $_SESSION['user_id'];

// Ambil saldo sebelumnya
$pl = mysqli_fetch_assoc(mysqli_query($conn, "SELECT saldo_piutang FROM pelanggan WHERE id=$pelanggan_id"));
$saldo_sebelum = $pl['saldo_piutang'];
$saldo_sesudah = $saldo_sebelum - $jumlah;

// Insert piutang tipe pembayaran
mysqli_query($conn, "INSERT INTO piutang (pelanggan_id, no_referensi, tipe, jumlah, saldo_sebelum, saldo_sesudah, keterangan, user_id) 
                     VALUES ($pelanggan_id, 'BYR-" . date('YmdHi') . "', 'pembayaran', $jumlah, $saldo_sebelum, $saldo_sesudah, '$keterangan', $user_id)");

// Update saldo pelanggan
mysqli_query($conn, "UPDATE pelanggan SET saldo_piutang = $saldo_sesudah WHERE id = $pelanggan_id");

// Log
mysqli_query($conn, "INSERT INTO log_aktivitas (user_id, aktivitas, detail) VALUES ($user_id, 'Bayar Piutang', 'Pembayaran Rp $jumlah dari pelanggan ID $pelanggan_id')");

$_SESSION['sukses'] = "Pembayaran piutang berhasil!";
$_SESSION['sukses_type'] = 'success';
redirect("../index.php?page=piutang-detail&id=$pelanggan_id");
?>