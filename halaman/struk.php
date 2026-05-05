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

// Ambil sisa piutang pelanggan
$sisa_piutang = 0;
if ($transaksi['pelanggan_id']) {
    $piutang_data = mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT saldo_piutang FROM pelanggan WHERE id = {$transaksi['pelanggan_id']}
    "));
    $sisa_piutang = $piutang_data['saldo_piutang'] ?? 0;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk <?= $transaksi['no_invoice'] ?></title>
    <style>
        @media print {
            @page { 
                margin: 0; 
                size: 58mm auto;
            }
            body { 
                margin: 0; 
                padding: 3mm;
                width: 58mm;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .no-print { display: none !important; }
        }
        
        * {
            font-family: 'Courier New', monospace;
            font-size: 9px;
            line-height: 1.3;
            margin: 0;
            padding: 0;
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
        .line { 
            border-top: 1px dashed #000; 
            margin: 3px 0; 
            padding-top: 3px;
        }
        
        table { width: 100%; border-collapse: collapse; }
        table td { padding: 1px 0; vertical-align: top; }
        table td.col-qty { width: 18%; text-align: center; }
        table td.col-harga { width: 30%; text-align: right; }
        table td.col-total { width: 30%; text-align: right; padding-left: 2px; }
        
        .btn-print { 
            display: block; width: 100%; padding: 6px; margin: 5px 0;
            background: #6366f1; color: white; border: none; cursor: pointer;
            font-family: Arial, sans-serif; font-size: 11px; border-radius: 6px;
        }
        .btn-pdf { 
            display: block; width: 100%; padding: 6px; margin: 5px 0;
            background: #dc2626; color: white; border: none; cursor: pointer;
            font-family: Arial, sans-serif; font-size: 11px; border-radius: 6px;
        }
        .btn-close {
            display: block; width: 100%; padding: 6px; margin: 5px 0;
            background: #6b7280; color: white; border: none; cursor: pointer;
            font-family: Arial, sans-serif; font-size: 11px; border-radius: 6px;
        }
    </style>
</head>
<body>
    
    <!-- ==================== TOMBOL (NO PRINT) ==================== -->
    <button onclick="window.print()" class="btn-print no-print">🖨️ Cetak Thermal (58mm)</button>
    <button onclick="downloadPDF()" class="btn-pdf no-print">📥 Download PDF</button>
    
    <!-- ==================== HEADER ==================== -->
    <div class="center">
        <h3 style="font-size:11px; font-weight:bold; margin-bottom:1px;"><?= TOKO_NAMA ?></h3>
        <p style="font-size:7px;"><?= TOKO_ALAMAT ?></p>
        <p style="font-size:7px;">Telp: <?= TOKO_TELP ?></p>
    </div>
    
    <div class="line"></div>
    
    <!-- ==================== INFO TRANSAKSI ==================== -->
    <table>
        <tr><td style="width:30%;">No</td><td style="width:3%;">:</td><td class="bold" style="font-size:8px;"><?= $transaksi['no_invoice'] ?></td></tr>
        <tr><td>Tgl</td><td>:</td><td><?= date('d/m/y H:i', strtotime($transaksi['created_at'])) ?></td></tr>
        <tr><td>Kasir</td><td>:</td><td><?= $transaksi['kasir'] ?></td></tr>
        <?php if($transaksi['pelanggan']): ?>
        <tr><td>Plgn</td><td>:</td><td><?= $transaksi['pelanggan'] ?></td></tr>
        <?php endif; ?>
    </table>
    
    <div class="line"></div>
    
    <!-- ==================== ITEM ==================== -->
    <table>
        <?php while($d = mysqli_fetch_assoc($detail)): ?>
        <tr>
            <td colspan="4" style="font-size:8px; font-weight:bold;"><?= $d['nama_produk'] ?></td>
        </tr>
        <tr>
            <td class="col-qty"><?= $d['qty'] ?> <?= $d['satuan'] ?></td>
            <td style="width:3%;">×</td>
            <td class="col-harga"><?= number_format($d['harga_satuan']) ?></td>
            <td class="col-total bold"><?= number_format($d['subtotal']) ?></td>
        </tr>
        <?php if($d['tipe_harga'] == 'custom'): ?>
        <tr><td colspan="4" style="font-size:7px; font-style:italic;">*Custom</td></tr>
        <?php elseif($d['tipe_harga'] == 'override'): ?>
        <tr><td colspan="4" style="font-size:7px; font-style:italic;">*Harga diubah</td></tr>
        <?php endif; ?>
        <?php endwhile; ?>
    </table>
    
    <div class="line"></div>
    
    <!-- ==================== TOTAL ==================== -->
    <table>
        <tr>
            <td style="font-size:9px; font-weight:bold;">TOTAL</td>
            <td class="right bold" style="font-size:10px;">Rp <?= number_format($transaksi['total']) ?></td>
        </tr>
        <tr>
            <td>Bayar</td>
            <td class="right">Rp <?= number_format($transaksi['bayar']) ?></td>
        </tr>
        <?php if($transaksi['kembalian'] > 0): ?>
        <tr>
            <td>Kembali</td>
            <td class="right">Rp <?= number_format($transaksi['kembalian']) ?></td>
        </tr>
        <?php endif; ?>
        <?php if($transaksi['kurang'] > 0): ?>
        <tr>
            <td style="color:red;">Kurang</td>
            <td class="right" style="color:red;">Rp <?= number_format($transaksi['kurang']) ?></td>
        </tr>
        <?php endif; ?>
    </table>
    
    <div class="line"></div>
    
    <!-- ==================== STATUS ==================== -->
    <div class="center">
        <p style="font-size:8px; font-weight:bold;">Status: <?= strtoupper($transaksi['status']) ?></p>
        <?php if($transaksi['status'] == 'piutang'): ?>
        <p style="font-size:7px; color:red;">⚠️ Pembayaran belum lunas</p>
        <?php endif; ?>
    </div>
    
    <div class="line"></div>

<!-- ==================== PIUTANG REMINDER ==================== -->
<?php if ($transaksi['status'] == 'piutang' || $sisa_piutang > 0): ?>
<div class="center" style="border: 1px solid #000; padding: 3px; margin: 3px 0;">
    <?php if ($transaksi['status'] == 'piutang'): ?>
    <p style="font-size:8px; font-weight:bold;">⚠️ INVOICE INI BELUM LUNAS</p>
    <p style="font-size:8px;">Kurang: Rp <?= number_format($transaksi['kurang']) ?></p>
    <?php endif; ?>
    
    <?php if ($sisa_piutang > 0): ?>
    <p style="font-size:8px; font-weight:bold; margin-top:2px;">💳 TOTAL PIUTANG ANDA</p>
    <p style="font-size:10px; font-weight:bold;">Rp <?= number_format($sisa_piutang) ?></p>
    <p style="font-size:6px;">Harap segera dilunasi. Terima kasih.</p>
    <?php endif; ?>
</div>

<div class="line"></div>
<?php endif; ?>

<!-- ==================== FOOTER ==================== -->
<div class="center">
    
    <button onclick="window.print()" class="btn-print no-print">🖨️ Cetak Thermal (58mm)</button>
    <button onclick="downloadPDF()" class="btn-pdf no-print">📥 Download PDF</button>
    <button onclick="window.close()" class="btn-close no-print">❌ Tutup</button>
    
    <script>
        function downloadPDF() {
            const originalTitle = document.title;
            document.title = 'Struk_<?= $transaksi['no_invoice'] ?>';
            window.print();
            setTimeout(() => { document.title = originalTitle; }, 1000);
        }
    </script>
</body>
</html>