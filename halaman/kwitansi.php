<?php
require_once '../config/database.php';
require_once '../functions/helper.php';

$id = $_GET['id'] ?? 0;

$piutang = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT p.*, pl.nama as pelanggan, pl.no_hp, u.nama as user_nama
    FROM piutang p
    JOIN pelanggan pl ON p.pelanggan_id = pl.id
    JOIN users u ON p.user_id = u.id
    WHERE p.id = $id
"));

if (!$piutang) { echo "Data tidak ditemukan"; exit; }

if ($piutang['tipe'] == 'pembayaran') {
    $judul = 'PEMBAYARAN PIUTANG';
    $icon = '💳';
} else {
    $judul = 'PINJAMAN';
    $icon = '📋';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kwitansi <?= $piutang['no_referensi'] ?></title>
    <style>
        @media print {
            @page { margin: 0; size: 58mm auto; }
            body { margin: 0; padding: 3mm; width: 58mm; }
            .no-print { display: none !important; }
        }
        
        * {
            font-family: 'Courier New', monospace;
            font-size: 9px;
            line-height: 1.3;
            margin: 0; padding: 0;
            box-sizing: border-box;
        }
        
        body {
            max-width: 58mm;
            margin: 0 auto;
            padding: 3mm;
            color: #000;
        }
        
        .center { text-align: center; }
        .right { text-align: right; }
        .bold { font-weight: bold; }
        .line { border-top: 1px dashed #000; margin: 3px 0; padding-top: 3px; }
        .double-line { border-top: 1px solid #000; border-bottom: 1px solid #000; padding: 3px 0; margin: 3px 0; }
        
        table { width: 100%; border-collapse: collapse; }
        table td { padding: 1px 0; vertical-align: top; }
        
        .btn-print { 
            display: block; width: 100%; padding: 6px; margin: 5px 0;
            background: #6366f1; color: white; border: none; cursor: pointer;
            font-family: Arial, sans-serif; font-size: 10px; border-radius: 6px;
        }
    </style>
</head>
<body>
    
    <button onclick="window.print()" class="btn-print no-print">🖨️ Cetak</button>
    
    <!-- ==================== HEADER ==================== -->
    <div class="center">
        <h3 style="font-size:11px; font-weight:bold; margin-bottom:1px;"><?= TOKO_NAMA ?></h3>
        <p style="font-size:7px;"><?= TOKO_ALAMAT ?></p>
        <p style="font-size:7px;">Telp: <?= TOKO_TELP ?></p>
    </div>
    
    <div class="line"></div>
    
    <div class="center">
        <p style="font-size:10px; font-weight:bold;"><?= $icon ?> <?= $judul ?></p>
    </div>
    
    <div class="line"></div>
    
    <!-- ==================== INFO ==================== -->
    <table>
        <tr><td style="width:30%;">No</td><td style="width:3%;">:</td><td class="bold"><?= $piutang['no_referensi'] ?></td></tr>
        <tr><td>Tgl</td><td>:</td><td><?= date('d/m/y H:i', strtotime($piutang['created_at'])) ?></td></tr>
        <tr><td>Nama</td><td>:</td><td class="bold"><?= htmlspecialchars($piutang['pelanggan']) ?></td></tr>
        <?php if($piutang['no_hp']): ?>
        <tr><td>HP</td><td>:</td><td><?= htmlspecialchars($piutang['no_hp']) ?></td></tr>
        <?php endif; ?>
    </table>
    
    <div class="line"></div>
    
    <!-- ==================== DETAIL ==================== -->
    <table>
        <tr><td>Keterangan</td><td>:</td><td><?= htmlspecialchars($piutang['keterangan']) ?></td></tr>
    </table>
    
    <div class="double-line">
        <table>
            <tr>
                <td style="font-size:9px; font-weight:bold;">JUMLAH</td>
                <td class="right bold" style="font-size:10px;">Rp <?= number_format($piutang['jumlah']) ?></td>
            </tr>
        </table>
    </div>
    
    <div class="line"></div>
    
    <!-- ==================== SALDO ==================== -->
    <table>
        <tr>
            <td>Saldo Sebelum</td>
            <td class="right">Rp <?= number_format($piutang['saldo_sebelum']) ?></td>
        </tr>
        <tr>
            <td style="font-weight:bold;">Saldo Sekarang</td>
            <td class="right bold">Rp <?= number_format($piutang['saldo_sesudah']) ?></td>
        </tr>
    </table>
    
    <div class="line"></div>
    
    <!-- ==================== FOOTER ==================== -->
    <div class="center">
        <?php if($piutang['tipe'] == 'pembayaran'): ?>
        <p style="font-size:7px;">Pembayaran piutang telah diterima</p>
        <?php else: ?>
        <p style="font-size:7px;">Pinjaman telah dicatat</p>
        <?php endif; ?>
        <p style="font-size:7px;">Kasir: <?= $piutang['user_nama'] ?></p>
        <p style="font-size:7px; margin-top:3px;"><?= date('d/m/Y H:i') ?></p>
    </div>
    
    <button onclick="window.print()" class="btn-print no-print">🖨️ Cetak Lagi</button>
</body>
</html>