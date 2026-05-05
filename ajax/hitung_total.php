<?php
require_once '../config/database.php';
require_once '../functions/harga.php';

$input = json_decode(file_get_contents('php://input'), true);
$items = $input['items'] ?? [];

$total = 0;
$total_diskon = 0;

foreach ($items as $item) {
    $hasil = hitungHarga($item['produk_id'], $item['qty'], $item['satuan'] ?? 'dasar');
    $total += $hasil['total'];
    $total_diskon += $hasil['diskon'];
}

echo json_encode([
    'success' => true,
    'total' => $total,
    'total_diskon' => $total_diskon
]);
?>