<?php
session_start();
require_once '../config/database.php';
require_once '../functions/helper.php';

// Cek apakah tombol login ditekan
if (!isset($_POST['login'])) {
    redirect('../index.php?page=login');
}

$username = esc($_POST['username']);
$password = $_POST['password'];

// Validasi input kosong
if (empty($username) || empty($password)) {
    $_SESSION['error'] = 'Username dan password wajib diisi!';
    redirect('../index.php?page=login');
}

// Cari user berdasarkan username
$query = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");
$user = mysqli_fetch_assoc($query);

// Cek apakah user ditemukan
if (!$user) {
    $_SESSION['error'] = 'Username tidak ditemukan!';
    redirect('../index.php?page=login');
}

// Verifikasi password
if (!password_verify($password, $user['password'])) {
    $_SESSION['error'] = 'Password salah!';
    redirect('../index.php?page=login');
}

// Login berhasil - Set session
$_SESSION['user_id']  = $user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['nama']     = $user['nama'];
$_SESSION['role']     = $user['role'];

// Catat log aktivitas
$user_id = $user['id'];
$nama = $user['nama'];
mysqli_query($conn, "INSERT INTO log_aktivitas (user_id, aktivitas, detail) VALUES ($user_id, 'Login', '$nama berhasil login')");

// Redirect sesuai role
if ($user['role'] == 'admin') {
    $_SESSION['sukses'] = "Selamat datang Admin, $nama!";
    redirect('../index.php?page=dashboard');
} else {
    $_SESSION['sukses'] = "Selamat datang, $nama!";
    redirect('../index.php?page=kasir');
}
?>