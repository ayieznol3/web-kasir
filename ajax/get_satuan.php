<?php
require_once '../config/database.php';

$produk_id = $_GET['produk_id'] ?? 0;

$query = mysqli_query($conn, "SELECT * FROM satuan WHERE produk_id = $produk_id ORDER BY isi_satuan ASC");
$data = [];

while($row = mysqli_fetch_assoc($query)) {
    $data[] = $row;
}

header('Content-Type: application/json');
echo json_encode($data);
?>

