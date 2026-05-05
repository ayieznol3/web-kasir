<?php
session_start();
require_once '../config/database.php';

// Catat log
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $nama = $_SESSION['nama'];
    mysqli_query($conn, "INSERT INTO log_aktivitas (user_id, aktivitas, detail) VALUES ($user_id, 'Logout', '$nama logout')");
}

// Hapus semua session
session_destroy();

// Redirect ke login
header('Location: ../index.php?page=login');
exit;
?>