<?php
$page = 'laporan';

// Nilai stok (hanya fisik)
$nilai_stok = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT 
        COUNT(*) as total_produk,
        COALESCE(SUM(stok_dasar),0) as total_stok,
        COALESCE(SUM(stok_dasar * harga_beli),0) as total_nilai
    FROM produk
    WHERE stok_dasar >= 0
"));

// Mutasi terbaru
$mutasi = mysqli_query($conn, "
    SELECT m.*, p.nama as nama_produk, p.satuan_dasar,
           t.no_invoice,
           DATE_FORMAT(m.created_at, '%d/%m %H:%i') as waktu
    FROM mutasi_stok m
    JOIN produk p ON m.produk_id = p.id
    LEFT JOIN transaksi t ON m.transaksi_id = t.id
    WHERE p.stok_dasar >= 0
    ORDER BY m.created_at DESC
    LIMIT 30
");

// Stok semua produk (hanya fisik)
$stok_produk = mysqli_query($conn, "
    SELECT *, (stok_dasar * harga_beli) as nilai_stok
    FROM produk 
    WHERE stok_dasar >= 0
    ORDER BY stok_dasar ASC
");
?>

<div class="space-y-6">
    <h1 class="text-2xl font-bold text-dark">
        <i class="fas fa-boxes text-primary mr-2"></i>Laporan Stok
    </h1>

    <!-- Ringkasan -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl shadow-sm p-6 border-l-4 border-blue-500">
            <p class="text-sm text-gray-500">Total Produk</p>
            <p class="text-3xl font-bold"><?= $nilai_stok['total_produk'] ?> <span class="text-sm font-normal text-gray-400">item</span></p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm p-6 border-l-4 border-green-500">
            <p class="text-sm text-gray-500">Total Qty Stok</p>
            <p class="text-3xl font-bold"><?= number_format($nilai_stok['total_stok']) ?> <span class="text-sm font-normal text-gray-400">unit</span></p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm p-6 border-l-4 border-purple-500">
            <p class="text-sm text-gray-500">Nilai Stok (HPP)</p>
            <p class="text-3xl font-bold text-purple-600"><?= rupiah($nilai_stok['total_nilai']) ?></p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Daftar Stok -->
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="p-4 border-b">
                <h3 class="font-bold">Stok Semua Produk</h3>
            </div>
            <div class="max-h-96 overflow-y-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 sticky top-0">
                        <tr>
                            <th class="px-4 py-3 text-left">Produk</th>
                            <th class="px-4 py-3 text-center">Stok</th>
                            <th class="px-4 py-3 text-right">HPP</th>
                            <th class="px-4 py-3 text-right">Nilai</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php while($s = mysqli_fetch_assoc($stok_produk)): ?>
                        <tr class="<?= $s['stok_dasar'] <= 10 ? 'bg-red-50' : '' ?>">
                            <td class="px-4 py-2 font-medium"><?= $s['nama'] ?></td>
                            <td class="px-4 py-2 text-center">
                                <span class="<?= $s['stok_dasar'] <= 10 ? 'text-red-600 font-bold' : '' ?>">
                                    <?= $s['stok_dasar'] ?> <?= $s['satuan_dasar'] ?>
                                </span>
                            </td>
                            <td class="px-4 py-2 text-right"><?= rupiah($s['harga_beli']) ?></td>
                            <td class="px-4 py-2 text-right"><?= rupiah($s['nilai_stok']) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Mutasi Stok -->
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="p-4 border-b">
                <h3 class="font-bold">Mutasi Stok Terbaru</h3>
            </div>
            <div class="max-h-96 overflow-y-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 sticky top-0">
                        <tr>
                            <th class="px-3 py-3 text-left">Waktu</th>
                            <th class="px-3 py-3 text-left">Produk</th>
                            <th class="px-3 py-3 text-center">Masuk</th>
                            <th class="px-3 py-3 text-center">Keluar</th>
                            <th class="px-3 py-3 text-left">Ket</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php while($m = mysqli_fetch_assoc($mutasi)): ?>
                        <tr>
                            <td class="px-3 py-2 text-xs"><?= $m['waktu'] ?></td>
                            <td class="px-3 py-2 text-xs font-medium"><?= $m['nama_produk'] ?></td>
                            <td class="px-3 py-2 text-center">
                                <?php if($m['qty_masuk'] > 0): ?>
                                <span class="text-green-600 font-bold">+<?= $m['qty_masuk'] ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="px-3 py-2 text-center">
                                <?php if($m['qty_keluar'] > 0): ?>
                                <span class="text-red-600 font-bold">-<?= $m['qty_keluar'] ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="px-3 py-2 text-xs text-gray-400">
                                <?= $m['no_invoice'] ?? $m['keterangan'] ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>