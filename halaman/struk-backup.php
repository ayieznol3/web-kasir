<?php
require_once '../config/database.php';
require_once '../functions/helper.php';

$id = $_GET['id'] ?? 0;

$transaksi = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT t.*, u.nama as kasir, p.nama as pelanggan
    FROM transaksi t
    JOIN users u ON t.user_id = u.id
    LEFT JOIN pelanggan p ON t.pelanggan_id = p.id
    WHERE t.id = $id
"));

if (!$transaksi) { echo "Transaksi tidak ditemukan"; exit; }

$detail = mysqli_query($conn, "
    SELECT td.*, pr.nama as nama_produk
    FROM transaksi_detail td
    JOIN produk pr ON td.produk_id = pr.id
    WHERE td.transaksi_id = $id
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk <?= $transaksi['no_invoice'] ?></title>
    <style>
        @media print {
            @page { margin: 0; size: 80mm auto; }
            body { margin: 0; padding: 10px; }
            .no-print { display: none; }
        }
        * { font-family: 'Courier New', monospace; font-size: 12px; }
        body { max-width: 300px; margin: 0 auto; padding: 10px; }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .line { border-top: 1px dashed #000; margin: 5px 0; }
        .table { width: 100%; }
        .table td { padding: 2px 0; }
        .right { text-align: right; }
        .btn-print { 
            display: block; width: 100%; padding: 10px; margin: 10px 0;
            background: #6366f1; color: white; border: none; cursor: pointer;
            font-family: Arial; font-size: 14px; border-radius: 10px;
        }
    </style>
</head>
<body>
    
    <button onclick="window.print()" class="btn-print no-print">🖨️ Cetak Struk</button>
    
   <div class="center">
    <h3 style="font-size:16px; margin:0;"><?= defined('TOKO_NAMA') ? TOKO_NAMA : 'Toko Saya' ?></h3>
    <p style="font-size:10px; margin:2px 0;"><?= defined('TOKO_ALAMAT') ? TOKO_ALAMAT : 'Alamat Toko' ?></p>
    <p style="font-size:10px; margin:2px 0;">Telp: <?= defined('TOKO_TELP') ? TOKO_TELP : '-' ?></p>
</div>
    
    <div class="line"></div>
    
    <table>
        <tr><td>No</td><td>:</td><td class="bold"><?= $transaksi['no_invoice'] ?></td></tr>
        <tr><td>Tgl</td><td>:</td><td><?= date('d/m/Y H:i', strtotime($transaksi['created_at'])) ?></td></tr>
        <tr><td>Kasir</td><td>:</td><td><?= $transaksi['kasir'] ?></td></tr>
        <?php if($transaksi['pelanggan']): ?>
        <tr><td>Pelanggan</td><td>:</td><td><?= $transaksi['pelanggan'] ?></td></tr>
        <?php endif; ?>
    </table>
    
    <div class="line"></div>
    
    <table class="table">
    <?php while($d = mysqli_fetch_assoc($detail)): ?>
    <tr>
        <td colspan="3">
            <strong><?= $d['nama_produk'] ?></strong>
            <?php if($d['tipe_harga'] == 'PPOB'): ?>
                <br><small>
                    <?php 
                    // Cek apakah ada nominal
                    $ppob_info = json_decode('"' . $d['satuan'] . '"', true);
                    ?>
                    Admin: Rp <?= number_format($d['harga_satuan']) ?>
                </small>
            <?php else: ?>
                <br><small><?= $d['qty'] ?> <?= $d['satuan'] ?> × Rp <?= number_format($d['harga_satuan']) ?></small>
            <?php endif; ?>
        </td>
        <td class="right"><?= number_format($d['subtotal']) ?></td>
    </tr>
    <?php endwhile; ?>
</table>
    
    <div class="line"></div>
    
    <table>
        <tr><td>TOTAL</td><td class="right bold">Rp <?= number_format($transaksi['total']) ?></td></tr>
        <tr><td>Bayar</td><td class="right">Rp <?= number_format($transaksi['bayar']) ?></td></tr>
        <?php if($transaksi['kembalian'] > 0): ?>
        <tr><td>Kembali</td><td class="right">Rp <?= number_format($transaksi['kembalian']) ?></td></tr>
        <?php endif; ?>
        <?php if($transaksi['kurang'] > 0): ?>
        <tr><td>Kurang</td><td class="right" style="color:red;">Rp <?= number_format($transaksi['kurang']) ?></td></tr>
        <?php endif; ?>
    </table>
    
    <div class="line"></div>
    
    <div class="center">
        <p style="font-size:10px;">Status: <?= strtoupper($transaksi['status']) ?></p>
        <p style="font-size:10px;">Terima kasih telah berbelanja</p>
        <p style="font-size:10px;">Barang yang sudah dibeli tidak dapat ditukar</p>
    </div>
    
    <button onclick="window.print()" class="btn-print no-print">🖨️ Cetak Lagi</button>
    <button onclick="window.close()" class="btn-print no-print" style="background:#ef4444;">Tutup</button>
    
    <script>
        // Auto print saat halaman dibuka
        window.onload = function() {
            // Uncomment untuk auto print:
            // window.print();
        }
    </script>
</body>
</html>