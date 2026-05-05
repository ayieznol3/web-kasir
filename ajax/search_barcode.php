<?php
require_once '../config/database.php';

$barcode = $_GET['barcode'] ?? '';
$barcode = mysqli_real_escape_string($conn, $barcode);

$result = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT id, kode, nama, satuan_dasar, harga_jual, stok_dasar 
    FROM produk 
    WHERE kode = '$barcode' AND stok_dasar > 0
"));

if ($result) {
    $result['found'] = true;
    echo json_encode($result);
} else {
    echo json_encode(['found' => false]);
}
?>