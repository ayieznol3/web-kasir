<?php
session_start();
require_once '../config/database.php';
require_once '../functions/helper.php';

$id = $_POST['id'] ?? null;
$nama = esc($_POST['nama']);
$no_hp = esc($_POST['no_hp']);
$alamat = esc($_POST['alamat']);

if ($id) {
    mysqli_query($conn, "UPDATE pelanggan SET nama='$nama', no_hp='$no_hp', alamat='$alamat' WHERE id=$id");
    $_SESSION['sukses'] = "Pelanggan diupdate!";
} else {
    mysqli_query($conn, "INSERT INTO pelanggan (nama, no_hp, alamat) VALUES ('$nama', '$no_hp', '$alamat')");
    $_SESSION['sukses'] = "Pelanggan ditambahkan!";
}

$_SESSION['sukses_type'] = 'success';
redirect('../index.php?page=pelanggan');
?>