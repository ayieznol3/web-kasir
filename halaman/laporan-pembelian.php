<?php
$page = 'laporan';

// ==================== FILTER ====================
$tgl_mulai = $_GET['tgl_mulai'] ?? date('Y-m-01');
$tgl_akhir = $_GET['tgl_akhir'] ?? date('Y-m-d');
$supplier_filter = $_GET['supplier'] ?? '';
$produk_filter = $_GET['produk_id'] ?? '';

// ==================== RINGKASAN ====================
$ringkasan = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT 
        COUNT(*) as total_transaksi,
        COALESCE(SUM(total_harga),0) as total_pembelian,
        COALESCE(SUM(qty),0) as total_qty,
        COALESCE(AVG(harga_satuan),0) as rata_harga
    FROM pembelian p
    WHERE DATE(p.created_at) BETWEEN '$tgl_mulai' AND '$tgl_akhir'
    " . ($supplier_filter ? " AND p.supplier = '$supplier_filter'" : "") . "
    " . ($produk_filter ? " AND p.produk_id = '$produk_filter'" : "") . "
"));

// ==================== LIST PEMBELIAN ====================
$pembelian_list = mysqli_query($conn, "
    SELECT 
        p.*, 
        pr.nama as nama_produk, 
        pr.satuan_dasar,
        pr.stok_dasar as stok_sekarang,
        u.nama as nama_user
    FROM pembelian p
    JOIN produk pr ON p.produk_id = pr.id
    JOIN users u ON p.user_id = u.id
    WHERE DATE(p.created_at) BETWEEN '$tgl_mulai' AND '$tgl_akhir'
    " . ($supplier_filter ? " AND p.supplier = '$supplier_filter'" : "") . "
    " . ($produk_filter ? " AND p.produk_id = '$produk_filter'" : "") . "
    ORDER BY p.created_at DESC
");

// ==================== PER PRODUK ====================
$per_produk = mysqli_query($conn, "
    SELECT 
        pr.nama,
        pr.satuan_dasar,
        COUNT(p.id) as frekuensi,
        COALESCE(SUM(p.qty),0) as total_qty,
        COALESCE(SUM(p.total_harga),0) as total_pembelian,
        COALESCE(AVG(p.harga_satuan),0) as rata_harga,
        MAX(p.harga_satuan) as harga_tertinggi,
        MIN(p.harga_satuan) as harga_terendah
    FROM pembelian p
    JOIN produk pr ON p.produk_id = pr.id
    WHERE DATE(p.created_at) BETWEEN '$tgl_mulai' AND '$tgl_akhir'
    " . ($produk_filter ? " AND p.produk_id = '$produk_filter'" : "") . "
    GROUP BY pr.id
    ORDER BY total_pembelian DESC
    LIMIT 10
");

// ==================== PER SUPPLIER ====================
$per_supplier = mysqli_query($conn, "
    SELECT 
        supplier,
        COUNT(*) as frekuensi,
        COALESCE(SUM(total_harga),0) as total_pembelian,
        COALESCE(SUM(qty),0) as total_qty,
        MAX(created_at) as terakhir_beli
    FROM pembelian
    WHERE DATE(created_at) BETWEEN '$tgl_mulai' AND '$tgl_akhir'
    AND supplier IS NOT NULL AND supplier != ''
    " . ($supplier_filter ? " AND supplier = '$supplier_filter'" : "") . "
    GROUP BY supplier
    ORDER BY total_pembelian DESC
");

// ==================== PER BULAN (GRAFIK) ====================
$per_bulan = mysqli_query($conn, "
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as bulan,
        COUNT(*) as frekuensi,
        COALESCE(SUM(total_harga),0) as total_pembelian,
        COALESCE(SUM(qty),0) as total_qty
    FROM pembelian
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY bulan ASC
");

// ==================== TREND HARGA PER PRODUK (jika filter produk) ====================
if ($produk_filter) {
    $trend_harga = mysqli_query($conn, "
        SELECT 
            created_at,
            qty,
            harga_satuan,
            total_harga,
            supplier
        FROM pembelian
        WHERE produk_id = '$produk_filter'
        AND DATE(created_at) BETWEEN '$tgl_mulai' AND '$tgl_akhir'
        ORDER BY created_at ASC
    ");
}

// List produk untuk filter
$produk_list = mysqli_query($conn, "SELECT id, nama FROM produk ORDER BY nama");

// List supplier untuk filter
$supplier_list = mysqli_query($conn, "SELECT DISTINCT supplier FROM pembelian WHERE supplier IS NOT NULL AND supplier != '' ORDER BY supplier");
?>

<div class="space-y-6">
    
    <!-- ==================== HEADER ==================== -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-dark">
                <i class="fas fa-shopping-cart text-blue-500 mr-2"></i>Laporan Pembelian
            </h1>
            <p class="text-sm text-gray-500 mt-1">Analisis pembelian stok & supplier</p>
        </div>
        
        <a href="?page=restok" class="inline-flex items-center gap-2 bg-green-500 text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-green-600 transition">
            <i class="fas fa-plus"></i> Restok Baru
        </a>
    </div>

    <!-- ==================== FILTER ==================== -->
    <div class="bg-white rounded-2xl shadow-sm p-4">
        <form method="get" class="flex flex-wrap gap-3 items-end">
            <input type="hidden" name="page" value="laporan">
            <input type="hidden" name="jenis" value="pembelian">
            
            <div>
                <label class="text-xs text-gray-500 block mb-1">Dari</label>
                <input type="date" name="tgl_mulai" value="<?= $tgl_mulai ?>" class="px-3 py-2 border rounded-lg text-sm">
            </div>
            <div>
                <label class="text-xs text-gray-500 block mb-1">Sampai</label>
                <input type="date" name="tgl_akhir" value="<?= $tgl_akhir ?>" class="px-3 py-2 border rounded-lg text-sm">
            </div>
            <div>
                <label class="text-xs text-gray-500 block mb-1">Produk</label>
                <select name="produk_id" class="px-3 py-2 border rounded-lg text-sm">
                    <option value="">Semua Produk</option>
                    <?php while($pl = mysqli_fetch_assoc($produk_list)): ?>
                    <option value="<?= $pl['id'] ?>" <?= $produk_filter == $pl['id'] ? 'selected' : '' ?>><?= $pl['nama'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div>
                <label class="text-xs text-gray-500 block mb-1">Supplier</label>
                <select name="supplier" class="px-3 py-2 border rounded-lg text-sm">
                    <option value="">Semua Supplier</option>
                    <?php while($s = mysqli_fetch_assoc($supplier_list)): ?>
                    <option value="<?= htmlspecialchars($s['supplier']) ?>" <?= $supplier_filter == $s['supplier'] ? 'selected' : '' ?>><?= $s['supplier'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <button type="submit" class="bg-primary text-white px-4 py-2 rounded-lg text-sm">Filter</button>
            <a href="?page=laporan&jenis=pembelian" class="text-sm text-gray-400 py-2">Reset</a>
        </form>
    </div>

    <!-- ==================== RINGKASAN ==================== -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl shadow-sm p-5 border-l-4 border-blue-500">
            <p class="text-xs text-gray-500">Total Pembelian</p>
            <p class="text-2xl font-bold text-blue-600"><?= rupiah($ringkasan['total_pembelian']) ?></p>
            <p class="text-xs text-gray-400"><?= $ringkasan['total_transaksi'] ?> transaksi</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm p-5 border-l-4 border-green-500">
            <p class="text-xs text-gray-500">Total Qty</p>
            <p class="text-2xl font-bold text-green-600"><?= number_format($ringkasan['total_qty']) ?></p>
            <p class="text-xs text-gray-400">unit</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm p-5 border-l-4 border-purple-500">
            <p class="text-xs text-gray-500">Rata-rata Harga</p>
            <p class="text-2xl font-bold text-purple-600"><?= rupiah(round($ringkasan['rata_harga'])) ?></p>
            <p class="text-xs text-gray-400">per satuan</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm p-5 border-l-4 border-orange-500">
            <p class="text-xs text-gray-500">Periode</p>
            <p class="text-sm font-bold text-orange-600"><?= date('d/m/Y', strtotime($tgl_mulai)) ?> - <?= date('d/m/Y', strtotime($tgl_akhir)) ?></p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- ==================== PER PRODUK ==================== -->
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="p-4 border-b bg-blue-50">
                <h3 class="font-bold text-blue-700">
                    <i class="fas fa-box mr-2"></i>Pembelian Per Produk (Top 10)
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs uppercase">Produk</th>
                            <th class="px-4 py-3 text-center text-xs uppercase">Frekuensi</th>
                            <th class="px-4 py-3 text-right text-xs uppercase">Total Qty</th>
                            <th class="px-4 py-3 text-right text-xs uppercase">Total</th>
                            <th class="px-4 py-3 text-right text-xs uppercase">Rata Harga</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php while($pp = mysqli_fetch_assoc($per_produk)): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium"><?= $pp['nama'] ?></td>
                            <td class="px-4 py-3 text-center"><?= $pp['frekuensi'] ?>×</td>
                            <td class="px-4 py-3 text-right"><?= number_format($pp['total_qty']) ?> <?= $pp['satuan_dasar'] ?></td>
                            <td class="px-4 py-3 text-right font-bold"><?= rupiah($pp['total_pembelian']) ?></td>
                            <td class="px-4 py-3 text-right"><?= rupiah(round($pp['rata_harga'])) ?></td>
                        </tr>
                        <?php endwhile; ?>
                        <?php if(mysqli_num_rows($per_produk) == 0): ?>
                        <tr><td colspan="5" class="text-center py-8 text-gray-400">Tidak ada data</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ==================== PER SUPPLIER ==================== -->
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="p-4 border-b bg-green-50">
                <h3 class="font-bold text-green-700">
                    <i class="fas fa-store mr-2"></i>Pembelian Per Supplier
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs uppercase">Supplier</th>
                            <th class="px-4 py-3 text-center text-xs uppercase">Frekuensi</th>
                            <th class="px-4 py-3 text-right text-xs uppercase">Total</th>
                            <th class="px-4 py-3 text-left text-xs uppercase">Terakhir</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php while($ps = mysqli_fetch_assoc($per_supplier)): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium"><?= htmlspecialchars($ps['supplier']) ?></td>
                            <td class="px-4 py-3 text-center"><?= $ps['frekuensi'] ?>×</td>
                            <td class="px-4 py-3 text-right font-bold"><?= rupiah($ps['total_pembelian']) ?></td>
                            <td class="px-4 py-3 text-xs text-gray-400"><?= date('d/m/Y', strtotime($ps['terakhir_beli'])) ?></td>
                        </tr>
                        <?php endwhile; ?>
                        <?php if(mysqli_num_rows($per_supplier) == 0): ?>
                        <tr><td colspan="4" class="text-center py-8 text-gray-400">Tidak ada data supplier</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ==================== PER BULAN (TREND) ==================== -->
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden lg:col-span-2">
            <div class="p-4 border-b bg-purple-50">
                <h3 class="font-bold text-purple-700">
                    <i class="fas fa-chart-line mr-2"></i>Tren Pembelian 12 Bulan Terakhir
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs uppercase">Bulan</th>
                            <th class="px-4 py-3 text-center text-xs uppercase">Frekuensi</th>
                            <th class="px-4 py-3 text-right text-xs uppercase">Total Qty</th>
                            <th class="px-4 py-3 text-right text-xs uppercase">Total Pembelian</th>
                            <th class="px-4 py-3 text-right text-xs uppercase">Rata-rata/Transaksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php 
                        $bulan_labels = [];
                        while($pb = mysqli_fetch_assoc($per_bulan)): 
                            $rata = $pb['frekuensi'] > 0 ? round($pb['total_pembelian'] / $pb['frekuensi']) : 0;
                            $bulan_labels[] = $pb;
                        ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium"><?= date('F Y', strtotime($pb['bulan'] . '-01')) ?></td>
                            <td class="px-4 py-3 text-center"><?= $pb['frekuensi'] ?>×</td>
                            <td class="px-4 py-3 text-right"><?= number_format($pb['total_qty']) ?></td>
                            <td class="px-4 py-3 text-right font-bold"><?= rupiah($pb['total_pembelian']) ?></td>
                            <td class="px-4 py-3 text-right"><?= rupiah($rata) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ==================== TREND HARGA (JIKA FILTER PRODUK) ==================== -->
        <?php if($produk_filter && isset($trend_harga) && mysqli_num_rows($trend_harga) > 0): 
            $produk_nama = mysqli_fetch_assoc(mysqli_query($conn, "SELECT nama FROM produk WHERE id='$produk_filter'"));
        ?>
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden lg:col-span-2">
            <div class="p-4 border-b bg-orange-50">
                <h3 class="font-bold text-orange-700">
                    <i class="fas fa-chart-bar mr-2"></i>Tren Harga: <?= $produk_nama['nama'] ?>
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs uppercase">Tanggal</th>
                            <th class="px-4 py-3 text-center text-xs uppercase">Qty</th>
                            <th class="px-4 py-3 text-right text-xs uppercase">Harga Satuan</th>
                            <th class="px-4 py-3 text-right text-xs uppercase">Total</th>
                            <th class="px-4 py-3 text-left text-xs uppercase">Supplier</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php 
                        mysqli_data_seek($trend_harga, 0);
                        $prev_harga = 0;
                        while($th = mysqli_fetch_assoc($trend_harga)): 
                            $naik = $prev_harga > 0 && $th['harga_satuan'] > $prev_harga;
                            $turun = $prev_harga > 0 && $th['harga_satuan'] < $prev_harga;
                            $prev_harga = $th['harga_satuan'];
                        ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3"><?= date('d/m/Y', strtotime($th['created_at'])) ?></td>
                            <td class="px-4 py-3 text-center"><?= $th['qty'] ?></td>
                            <td class="px-4 py-3 text-right">
                                <?= rupiah($th['harga_satuan']) ?>
                                <?php if($naik): ?><span class="text-red-500 text-xs">↑</span><?php endif; ?>
                                <?php if($turun): ?><span class="text-green-500 text-xs">↓</span><?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-right"><?= rupiah($th['total_harga']) ?></td>
                            <td class="px-4 py-3 text-xs"><?= htmlspecialchars($th['supplier'] ?? '-') ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- ==================== DETAIL PEMBELIAN ==================== -->
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden lg:col-span-2">
            <div class="p-4 border-b">
                <h3 class="font-bold">📋 Detail Pembelian</h3>
            </div>
            <div class="overflow-x-auto max-h-96 overflow-y-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 sticky top-0">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs uppercase">Tanggal</th>
                            <th class="px-4 py-3 text-left text-xs uppercase">Produk</th>
                            <th class="px-4 py-3 text-center text-xs uppercase">Qty</th>
                            <th class="px-4 py-3 text-right text-xs uppercase">Harga</th>
                            <th class="px-4 py-3 text-right text-xs uppercase">Total</th>
                            <th class="px-4 py-3 text-left text-xs uppercase">Supplier</th>
                            <th class="px-4 py-3 text-left text-xs uppercase">User</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php while($pb = mysqli_fetch_assoc($pembelian_list)): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 whitespace-nowrap"><?= date('d/m/Y H:i', strtotime($pb['created_at'])) ?></td>
                            <td class="px-4 py-3 font-medium"><?= $pb['nama_produk'] ?></td>
                            <td class="px-4 py-3 text-center"><?= $pb['qty'] ?> <?= $pb['satuan_dasar'] ?></td>
                            <td class="px-4 py-3 text-right"><?= rupiah($pb['harga_satuan']) ?></td>
                            <td class="px-4 py-3 text-right font-bold"><?= rupiah($pb['total_harga']) ?></td>
                            <td class="px-4 py-3 text-xs"><?= htmlspecialchars($pb['supplier'] ?? '-') ?></td>
                            <td class="px-4 py-3 text-xs text-gray-400"><?= $pb['nama_user'] ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>