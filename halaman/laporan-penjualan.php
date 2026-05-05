<?php
$page = 'laporan';

// Filter tanggal
$tgl_mulai = $_GET['tgl_mulai'] ?? date('Y-m-01');
$tgl_akhir = $_GET['tgl_akhir'] ?? date('Y-m-d');

// Statistik
$stats = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT 
        COUNT(*) as total_transaksi,
        COALESCE(SUM(total),0) as total_penjualan,
        COALESCE(SUM(CASE WHEN status='lunas' THEN total ELSE 0 END),0) as total_lunas,
        COALESCE(SUM(CASE WHEN status='piutang' THEN kurang ELSE 0 END),0) as total_piutang,
        COALESCE(AVG(total),0) as rata_transaksi
    FROM transaksi 
    WHERE DATE(created_at) BETWEEN '$tgl_mulai' AND '$tgl_akhir' 
        AND status != 'void'
"));

// List transaksi
$transaksi = mysqli_query($conn, "
    SELECT t.*, u.nama as kasir, p.nama as pelanggan
    FROM transaksi t
    JOIN users u ON t.user_id = u.id
    LEFT JOIN pelanggan p ON t.pelanggan_id = p.id
    WHERE DATE(t.created_at) BETWEEN '$tgl_mulai' AND '$tgl_akhir' 
        AND t.status != 'void'
    ORDER BY t.created_at DESC
");

// Top produk (pakai qty_dasar)
$top_produk = mysqli_query($conn, "
    SELECT pr.nama, 
           SUM(td.qty_dasar) as total_qty, 
           SUM(td.subtotal) as total_nilai
    FROM transaksi_detail td
    JOIN transaksi t ON td.transaksi_id = t.id
    JOIN produk pr ON td.produk_id = pr.id
    WHERE DATE(t.created_at) BETWEEN '$tgl_mulai' AND '$tgl_akhir' 
        AND t.status != 'void'
    GROUP BY pr.id
    ORDER BY total_qty DESC
    LIMIT 10
");
?>

<div class="space-y-6">
    <h1 class="text-2xl font-bold text-dark">
        <i class="fas fa-chart-line text-primary mr-2"></i>Laporan Penjualan
    </h1>

    <!-- Filter -->
    <div class="bg-white rounded-2xl shadow-sm p-4">
        <form method="get" class="flex flex-wrap gap-4 items-end">
            <input type="hidden" name="page" value="laporan">
            <input type="hidden" name="jenis" value="penjualan">
            
            <div>
                <label class="text-xs text-gray-500 block mb-1">Dari Tanggal</label>
                <input type="date" name="tgl_mulai" value="<?= $tgl_mulai ?>" 
                       class="px-4 py-2 border rounded-xl focus:ring-2 focus:ring-primary outline-none">
            </div>
            <div>
                <label class="text-xs text-gray-500 block mb-1">Sampai Tanggal</label>
                <input type="date" name="tgl_akhir" value="<?= $tgl_akhir ?>" 
                       class="px-4 py-2 border rounded-xl focus:ring-2 focus:ring-primary outline-none">
            </div>
            <button type="submit" class="bg-primary text-white px-6 py-2 rounded-xl hover:bg-indigo-700 transition">
                <i class="fas fa-filter mr-1"></i> Filter
            </button>
            <a href="?page=laporan&jenis=penjualan" class="text-gray-400 hover:text-gray-600 text-sm py-2">
                Reset
            </a>
        </form>
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="bg-white rounded-2xl shadow-sm p-4 border-l-4 border-blue-500">
            <p class="text-xs text-gray-500">Total Transaksi</p>
            <p class="text-2xl font-bold"><?= $stats['total_transaksi'] ?></p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm p-4 border-l-4 border-green-500">
            <p class="text-xs text-gray-500">Total Penjualan</p>
            <p class="text-xl font-bold"><?= rupiah($stats['total_penjualan']) ?></p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm p-4 border-l-4 border-emerald-500">
            <p class="text-xs text-gray-500">Lunas</p>
            <p class="text-xl font-bold text-green-600"><?= rupiah($stats['total_lunas']) ?></p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm p-4 border-l-4 border-yellow-500">
            <p class="text-xs text-gray-500">Piutang</p>
            <p class="text-xl font-bold text-yellow-600"><?= rupiah($stats['total_piutang']) ?></p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm p-4 border-l-4 border-purple-500">
            <p class="text-xs text-gray-500">Rata-rata</p>
            <p class="text-xl font-bold"><?= rupiah(round($stats['rata_transaksi'])) ?></p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Tabel Transaksi -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="p-4 border-b">
                <h3 class="font-bold">Daftar Transaksi</h3>
                <p class="text-xs text-gray-400"><?= tgl_indo($tgl_mulai) ?> - <?= tgl_indo($tgl_akhir) ?></p>
            </div>
            <div class="overflow-x-auto max-h-96 overflow-y-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 sticky top-0">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs uppercase">Invoice</th>
                            <th class="px-4 py-3 text-left text-xs uppercase">Tgl</th>
                            <th class="px-4 py-3 text-left text-xs uppercase">Pelanggan</th>
                            <th class="px-4 py-3 text-right text-xs uppercase">Total</th>
                            <th class="px-4 py-3 text-center text-xs uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php $total = 0; while($t = mysqli_fetch_assoc($transaksi)): $total += $t['total']; ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 font-medium"><?= $t['no_invoice'] ?></td>
                            <td class="px-4 py-2"><?= date('d/m H:i', strtotime($t['created_at'])) ?></td>
                            <td class="px-4 py-2"><?= $t['pelanggan'] ?? 'Umum' ?></td>
                            <td class="px-4 py-2 text-right font-medium"><?= rupiah($t['total']) ?></td>
                            <td class="px-4 py-2 text-center">
                                <span class="px-2 py-0.5 text-xs rounded-full <?= $t['status'] == 'lunas' ? 'bg-green-100 text-green-600' : ($t['status'] == 'piutang' ? 'bg-yellow-100 text-yellow-600' : 'bg-red-100 text-red-600') ?>">
                                    <?= $t['status'] ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                    <tfoot class="bg-gray-50 font-bold">
                        <tr>
                            <td class="px-4 py-2" colspan="3">TOTAL</td>
                            <td class="px-4 py-2 text-right"><?= rupiah($total) ?></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Top Produk -->
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="p-4 border-b">
                <h3 class="font-bold">🏆 Produk Terlaris</h3>
            </div>
            <div class="p-4 space-y-3">
                <?php 
                $no = 1;
                while($tp = mysqli_fetch_assoc($top_produk)): 
                    $medali = $no == 1 ? '🥇' : ($no == 2 ? '🥈' : ($no == 3 ? '🥉' : $no));
                ?>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="w-8 text-center font-bold text-lg"><?= $medali ?></span>
                        <div>
                            <p class="font-medium text-sm"><?= $tp['nama'] ?></p>
                            <p class="text-xs text-gray-400"><?= $tp['total_qty'] ?> pcs terjual</p>
                        </div>
                    </div>
                    <p class="font-bold text-sm"><?= rupiah($tp['total_nilai']) ?></p>
                </div>
                <?php $no++; endwhile; ?>
            </div>
        </div>
    </div>
</div>