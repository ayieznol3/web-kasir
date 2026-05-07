<?php
session_start();
require_once '../config/database.php';
require_once '../functions/helper.php';

if (!isset($_SESSION['user_id']) || !isAdmin()) {
    redirect('../index.php?page=login');
}

$id = $_POST['id'] ?? null;
$nama = mysqli_real_escape_string($conn, $_POST['nama']);
$username = mysqli_real_escape_string($conn, $_POST['username']);
$password = $_POST['password'];
$role = mysqli_real_escape_string($conn, $_POST['role']);

// Validasi
if (empty($nama) || empty($username)) {
    $_SESSION['error'] = 'Nama dan username wajib diisi!';
    $_SESSION['error_type'] = 'danger';
    redirect('../index.php?page=users');
}

if (!$id && empty($password)) {
    $_SESSION['error'] = 'Password wajib diisi untuk user baru!';
    $_SESSION['error_type'] = 'danger';
    redirect('../index.php?page=users');
}

// Cek username duplikat
if ($id) {
    $cek = mysqli_query($conn, "SELECT id FROM users WHERE username = '$username' AND id != $id");
} else {
    $cek = mysqli_query($conn, "SELECT id FROM users WHERE username = '$username'");
}

if (mysqli_num_rows($cek) > 0) {
    $_SESSION['error'] = 'Username sudah digunakan!';
    $_SESSION['error_type'] = 'danger';
    redirect('../index.php?page=users');
}

if ($id) {
    // Update
    if (!empty($password)) {
        $pass_hash = password_hash($password, PASSWORD_DEFAULT);
        mysqli_query($conn, "UPDATE users SET nama='$nama', username='$username', password='$pass_hash', role='$role' WHERE id=$id");
    } else {
        mysqli_query($conn, "UPDATE users SET nama='$nama', username='$username', role='$role' WHERE id=$id");
    }
    mysqli_query($conn, "INSERT INTO log_aktivitas (user_id, aktivitas, detail) VALUES ({$_SESSION['user_id']}, 'Edit User', 'Edit user: $nama')");
    $_SESSION['sukses'] = "User <strong>$nama</strong> berhasil diupdate!";
} else {
    // Insert
    $pass_hash = password_hash($password, PASSWORD_DEFAULT);
    mysqli_query($conn, "INSERT INTO users (nama, username, password, role) VALUES ('$nama', '$username', '$pass_hash', '$role')");
    mysqli_query($conn, "INSERT INTO log_aktivitas (user_id, aktivitas, detail) VALUES ({$_SESSION['user_id']}, 'Tambah User', 'Tambah user: $nama ($role)')");
    $_SESSION['sukses'] = "User <strong>$nama</strong> berhasil ditambahkan!";
}

$_SESSION['sukses_type'] = 'success';
redirect('../index.php?page=users');
?>