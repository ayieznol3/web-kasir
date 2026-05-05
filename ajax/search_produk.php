<?php
require_once '../config/database.php';

$q = $_GET['q'] ?? '';
$q = mysqli_real_escape_string($conn, $q);

$result = mysqli_query($conn, "
    SELECT id, kode, nama, satuan_dasar, harga_jual, stok_dasar 
    FROM produk 
    WHERE (nama LIKE '%$q%' OR kode LIKE '%$q%') AND stok_dasar > 0
    LIMIT 10
");

$data = [];
while($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

header('Content-Type: application/json');
echo json_encode($data);
?>