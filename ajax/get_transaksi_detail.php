<?php
require_once '../config/database.php';
require_once '../functions/helper.php';

$id = $_GET['id'] ?? 0;

// Header transaksi
$transaksi = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT t.*, u.nama as kasir, p.nama as pelanggan, p.no_hp, p.alamat
    FROM transaksi t
    JOIN users u ON t.user_id = u.id
    LEFT JOIN pelanggan p ON t.pelanggan_id = p.id
    WHERE t.id = $id
"));

if (!$transaksi) {
    echo "<div class='p-8 text-center text-gray-400'>Transaksi tidak ditemukan</div>";
    exit;
}

// Detail item
$detail = mysqli_query($conn, "
    SELECT td.*, pr.nama as nama_produk, pr.satuan_dasar
    FROM transaksi_detail td
    JOIN produk pr ON td.produk_id = pr.id
    WHERE td.transaksi_id = $id
");
?>

<div class="p-6">
    <!-- Header -->
    <div class="flex justify-between items-start mb-6">
        <div>
            <h3 class="text-lg font-bold">🧾 Detail Transaksi</h3>
            <p class="text-sm text-gray-500 font-mono"><?= $transaksi['no_invoice'] ?></p>
        </div>
        <button onclick="document.getElementById('modal-detail').classList.add('hidden')" 
                class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
    </div>

    <!-- Info -->
    <div class="grid grid-cols-2 gap-4 mb-6 bg-gray-50 rounded-xl p-4">
        <div>
            <p class="text-xs text-gray-500">Tanggal</p>
            <p class="text-sm font-medium"><?= date('d/m/Y H:i', strtotime($transaksi['created_at'])) ?></p>
        </div>
        <div>
            <p class="text-xs text-gray-500">Kasir</p>
            <p class="text-sm font-medium"><?= $transaksi['kasir'] ?></p>
        </div>
        <div>
            <p class="text-xs text-gray-500">Pelanggan</p>
            <p class="text-sm font-medium"><?= $transaksi['pelanggan'] ?? 'Umum' ?></p>
        </div>
        <div>
            <p class="text-xs text-gray-500">Status</p>
            <span class="px-2 py-0.5 text-xs rounded-full <?= $transaksi['status'] == 'lunas' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' ?>">
                <?= ucfirst($transaksi['status']) ?>
            </span>
        </div>
    </div>

    <!-- Tabel Item -->
    <div class="mb-6">
        <h4 class="font-semibold text-sm mb-3">Item Dibeli</h4>
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left px-3 py-2 text-xs uppercase">Produk</th>
                    <th class="text-center px-3 py-2 text-xs uppercase">Qty</th>
                    <th class="text-right px-3 py-2 text-xs uppercase">Harga</th>
                    <th class="text-right px-3 py-2 text-xs uppercase">Subtotal</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <?php $total = 0; while($d = mysqli_fetch_assoc($detail)): $total += $d['subtotal']; ?>
                <tr>
                    <td class="px-3 py-2 font-medium"><?= $d['nama_produk'] ?></td>
                    <td class="px-4 py-3 text-center">
    <?= $d['qty'] ?> <?= $d['satuan'] ?>
    <?php if($d['qty_dasar'] > 0 && $d['qty_dasar'] != $d['qty']): ?>
    <br><small class="text-gray-400">(<?= $d['qty_dasar'] ?> pcs)</small>
    <?php endif; ?>
</td>
                  
                    <td class="px-3 py-2 text-right"><?= rupiah($d['harga_satuan']) ?></td>
                    <td class="px-3 py-2 text-right font-medium"><?= rupiah($d['subtotal']) ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
            <tfoot class="border-t">
                <tr class="font-bold text-lg">
                    <td colspan="3" class="px-3 py-3 text-right">TOTAL</td>
                    <td class="px-3 py-3 text-right text-primary"><?= rupiah($transaksi['total']) ?></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Pembayaran -->
    <div class="bg-gray-50 rounded-xl p-4 space-y-2">
        <div class="flex justify-between">
            <span class="text-sm text-gray-500">Total</span>
            <span class="font-bold"><?= rupiah($transaksi['total']) ?></span>
        </div>
        <div class="flex justify-between">
            <span class="text-sm text-gray-500">Bayar</span>
            <span class="font-bold"><?= rupiah($transaksi['bayar']) ?></span>
        </div>
        <?php if($transaksi['kembalian'] > 0): ?>
        <div class="flex justify-between text-green-600">
            <span class="text-sm">Kembalian</span>
            <span class="font-bold"><?= rupiah($transaksi['kembalian']) ?></span>
        </div>
        <?php endif; ?>
        <?php if($transaksi['kurang'] > 0): ?>
        <div class="flex justify-between text-red-600">
            <span class="text-sm">Kurang (Piutang)</span>
            <span class="font-bold"><?= rupiah($transaksi['kurang']) ?></span>
        </div>
        <?php endif; ?>
    </div>

    <!-- Print button -->
    <div class="mt-6 text-center">
        <button onclick="cetakStruk(<?= $id ?>)" 
                class="bg-green-500 text-white px-6 py-2 rounded-xl hover:bg-green-600 transition">
            <i class="fas fa-print mr-2"></i> Cetak Ulang Struk
        </button>
    </div>
</div>