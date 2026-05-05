<?php
session_start();
require_once '../config/database.php';
require_once '../functions/helper.php';

if (!isset($_SESSION['user_id']) || !isAdmin()) {
    redirect('../index.php?page=login');
}

$id = $_GET['id'] ?? 0;

// Tidak bisa hapus diri sendiri
if ($id == $_SESSION['user_id']) {
    $_SESSION['error'] = 'Tidak bisa menghapus akun sendiri!';
    $_SESSION['error_type'] = 'danger';
    redirect('../index.php?page=users');
}

$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id = $id"));
if ($user) {
    mysqli_query($conn, "DELETE FROM users WHERE id = $id");
    mysqli_query($conn, "INSERT INTO log_aktivitas (user_id, aktivitas, detail) VALUES ({$_SESSION['user_id']}, 'Hapus User', 'Hapus user: {$user['nama']}')");
    $_SESSION['sukses'] = "User <strong>{$user['nama']}</strong> berhasil dihapus!";
}

$_SESSION['sukses_type'] = 'success';
redirect('../index.php?page=users');
?>