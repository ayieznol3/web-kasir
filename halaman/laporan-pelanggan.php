<?php
$page = 'laporan';

// ==================== FILTER ====================
$tahun = $_GET['tahun'] ?? date('Y');
$bulan = $_GET['bulan'] ?? 'semua';

// Build where clause
$where_tgl = "";
if ($bulan != 'semua') {
    $where_tgl = "AND MONTH(t.created_at) = '$bulan' AND YEAR(t.created_at) = '$tahun'";
} else {
    $where_tgl = "AND YEAR(t.created_at) = '$tahun'";
}

$bulan_nama = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
               'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

// ==================== 1. PELANGGAN PALING AKTIF (Transaksi Terbanyak) ====================
$pelanggan_aktif = mysqli_query($conn, "
    SELECT 
        p.id, p.nama, p.no_hp,
        COUNT(t.id) as total_transaksi,
        COALESCE(SUM(t.total),0) as total_belanja,
        MAX(t.created_at) as terakhir_belanja
    FROM pelanggan p
    JOIN transaksi t ON p.id = t.pelanggan_id
    WHERE p.nama != 'Umum' 
        $where_tgl 
        AND t.status != 'void'
    GROUP BY p.id
    ORDER BY total_transaksi DESC
    LIMIT 10
");

// ==================== 2. PELANGGAN DENGAN UTANG TERBANYAK ====================
$pelanggan_utang = mysqli_query($conn, "
    SELECT 
        p.id, p.nama, p.no_hp,
        p.saldo_piutang,
        COALESCE(SUM(CASE WHEN pt.tipe != 'pembayaran' THEN pt.jumlah ELSE 0 END),0) as total_hutang,
        COALESCE(SUM(CASE WHEN pt.tipe = 'pembayaran' THEN pt.jumlah ELSE 0 END),0) as total_bayar,
        COUNT(CASE WHEN pt.tipe != 'pembayaran' THEN 1 END) as jumlah_hutang,
        COUNT(CASE WHEN pt.tipe = 'pembayaran' THEN 1 END) as jumlah_bayar,
        MAX(pt.created_at) as terakhir_aktivitas
    FROM pelanggan p
    JOIN piutang pt ON p.id = pt.pelanggan_id
    WHERE p.saldo_piutang > 0
    GROUP BY p.id
    ORDER BY saldo_piutang DESC
    LIMIT 10
");

// ==================== 3. PELANGGAN TERBAIK (Bayar Tepat Waktu) ====================
$pelanggan_baik = mysqli_query($conn, "
    SELECT 
        p.id, p.nama, p.no_hp,
        COUNT(t.id) as total_transaksi,
        COALESCE(SUM(t.total),0) as total_belanja,
        SUM(CASE WHEN t.status = 'lunas' THEN 1 ELSE 0 END) as transaksi_lunas,
        SUM(CASE WHEN t.status = 'piutang' THEN 1 ELSE 0 END) as transaksi_piutang,
        ROUND(
            SUM(CASE WHEN t.status = 'lunas' THEN 1 ELSE 0 END) * 100.0 / COUNT(t.id), 1
        ) as persen_lunas
    FROM pelanggan p
    JOIN transaksi t ON p.id = t.pelanggan_id
    WHERE p.nama != 'Umum' 
        $where_tgl 
        AND t.status != 'void'
    GROUP BY p.id
    HAVING COUNT(t.id) >= 3
    ORDER BY persen_lunas DESC, total_belanja DESC
    LIMIT 10
");

// ==================== 4. PELANGGAN PALING MENGUNTUNGKAN ====================
$pelanggan_untung = mysqli_query($conn, "
    SELECT 
        pl.id, pl.nama, pl.no_hp,
        COUNT(t.id) as total_transaksi,
        COALESCE(SUM(t.total),0) as total_belanja,
        COALESCE(SUM(
            CASE 
                WHEN td.tipe_harga = 'Paket' THEN 
                    td.subtotal - (td.qty_dasar * pr.harga_beli)
                ELSE 
                    td.qty_dasar * (td.harga_satuan - pr.harga_beli)
            END
        ),0) as total_keuntungan,
        ROUND(
            COALESCE(SUM(
                CASE 
                    WHEN td.tipe_harga = 'Paket' THEN 
                        td.subtotal - (td.qty_dasar * pr.harga_beli)
                    ELSE 
                        td.qty_dasar * (td.harga_satuan - pr.harga_beli)
                END
            ),0) * 100.0 / NULLIF(COALESCE(SUM(t.total),0), 0), 1
        ) as margin_persen
    FROM pelanggan pl
    JOIN transaksi t ON pl.id = t.pelanggan_id
    JOIN transaksi_detail td ON t.id = td.transaksi_id
    JOIN produk pr ON td.produk_id = pr.id
    WHERE pl.nama != 'Umum' 
        $where_tgl 
        AND t.status != 'void'
    GROUP BY pl.id
    ORDER BY total_keuntungan DESC
    LIMIT 10
");
// ==================== 5. PELANGGAN SERING UTANG (Frekuensi) ====================
$pelanggan_sering_utang = mysqli_query($conn, "
    SELECT 
        p.id, p.nama, p.no_hp,
        p.saldo_piutang,
        COUNT(pt.id) as total_catatan_utang,
        COUNT(CASE WHEN pt.tipe = 'transaksi' THEN 1 END) as utang_dari_transaksi,
        COUNT(CASE WHEN pt.tipe = 'pinjaman' THEN 1 END) as utang_manual,
        DATEDIFF(NOW(), MIN(pt.created_at)) as hari_pertama_utang,
        MAX(pt.created_at) as utang_terakhir
    FROM pelanggan p
    JOIN piutang pt ON p.id = pt.pelanggan_id
    WHERE pt.tipe != 'pembayaran'
    GROUP BY p.id
    HAVING total_catatan_utang >= 2
    ORDER BY total_catatan_utang DESC
    LIMIT 10
");

// ==================== RINGKASAN ====================
$ringkasan = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT 
        COUNT(DISTINCT p.id) as total_pelanggan_aktif,
        COALESCE(SUM(t.total),0) as total_penjualan,
        COUNT(t.id) as total_transaksi
    FROM pelanggan p
    JOIN transaksi t ON p.id = t.pelanggan_id
    WHERE p.nama != 'Umum' 
        $where_tgl 
        AND t.status != 'void'
"));
?>

<div class="space-y-6">
    
    <!-- ==================== HEADER ==================== -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-dark">
                <i class="fas fa-user-chart text-primary mr-2"></i>Laporan Pelanggan
            </h1>
            <p class="text-sm text-gray-500 mt-1">Analisis perilaku dan kontribusi pelanggan</p>
        </div>
        
        <!-- Filter -->
        <div class="flex gap-2">
            <select id="bulan-filter" class="px-4 py-2 border rounded-xl text-sm" onchange="filterLaporan()">
                <option value="semua" <?= $bulan == 'semua' ? 'selected' : '' ?>>Semua Bulan</option>
                <?php for($i = 1; $i <= 12; $i++): ?>
                <option value="<?= $i ?>" <?= $bulan == $i ? 'selected' : '' ?>><?= $bulan_nama[$i] ?></option>
                <?php endfor; ?>
            </select>
            <select id="tahun-filter" class="px-4 py-2 border rounded-xl text-sm" onchange="filterLaporan()">
                <?php for($y = date('Y'); $y >= 2020; $y--): ?>
                <option value="<?= $y ?>" <?= $tahun == $y ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
        </div>
    </div>

    <!-- ==================== RINGKASAN ==================== -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl shadow-sm p-5 border-l-4 border-blue-500">
            <p class="text-sm text-gray-500">Pelanggan Aktif</p>
            <p class="text-2xl font-bold"><?= $ringkasan['total_pelanggan_aktif'] ?> <span class="text-sm font-normal text-gray-400">orang</span></p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm p-5 border-l-4 border-green-500">
            <p class="text-sm text-gray-500">Total Transaksi</p>
            <p class="text-2xl font-bold"><?= $ringkasan['total_transaksi'] ?> <span class="text-sm font-normal text-gray-400">kali</span></p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm p-5 border-l-4 border-purple-500">
            <p class="text-sm text-gray-500">Total Penjualan ke Pelanggan</p>
            <p class="text-2xl font-bold text-purple-600"><?= rupiah($ringkasan['total_penjualan']) ?></p>
        </div>
    </div>

    <!-- ==================== GRID LAPORAN ==================== -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- 1. PELANGGAN PALING AKTIF -->
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="p-4 border-b bg-blue-50">
                <h3 class="font-bold text-blue-700 flex items-center gap-2">
                    <i class="fas fa-star"></i> Pelanggan Paling Aktif
                </h3>
                <p class="text-xs text-blue-500">Berdasarkan jumlah transaksi</p>
            </div>
            <div class="p-4">
                <?php if(mysqli_num_rows($pelanggan_aktif) > 0): ?>
                <div class="space-y-3">
                    <?php 
                    $no = 1;
                    while($pa = mysqli_fetch_assoc($pelanggan_aktif)): 
                        $medali = $no == 1 ? '🥇' : ($no == 2 ? '🥈' : ($no == 3 ? '🥉' : $no));
                    ?>
                    <div class="flex items-center gap-3 p-3 rounded-xl <?= $no <= 3 ? 'bg-yellow-50 border border-yellow-100' : 'hover:bg-gray-50' ?>">
                        <span class="text-2xl w-10 text-center"><?= $medali ?></span>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-sm truncate"><?= htmlspecialchars($pa['nama']) ?></p>
                            <p class="text-xs text-gray-400">
                                <?= $pa['total_transaksi'] ?> transaksi · 
                                Terakhir: <?= date('d/m/Y', strtotime($pa['terakhir_belanja'])) ?>
                            </p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <p class="font-bold text-sm"><?= rupiah($pa['total_belanja']) ?></p>
                        </div>
                    </div>
                    <?php $no++; endwhile; ?>
                </div>
                <?php else: ?>
                <p class="text-center text-gray-400 py-8">Belum ada data</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- 2. PELANGGAN DENGAN UTANG TERBANYAK -->
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="p-4 border-b bg-red-50">
                <h3 class="font-bold text-red-700 flex items-center gap-2">
                    <i class="fas fa-exclamation-circle"></i> Pelanggan Utang Terbanyak
                </h3>
                <p class="text-xs text-red-500">Berdasarkan sisa piutang</p>
            </div>
            <div class="p-4">
                <?php if(mysqli_num_rows($pelanggan_utang) > 0): ?>
                <div class="space-y-3">
                    <?php 
                    $no = 1;
                    while($pu = mysqli_fetch_assoc($pelanggan_utang)): 
                    ?>
                    <div class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50">
                        <span class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm
                            <?= $pu['saldo_piutang'] > 500000 ? 'bg-red-100 text-red-600' : 'bg-yellow-100 text-yellow-600' ?>">
                            <?= strtoupper(substr($pu['nama'], 0, 1)) ?>
                        </span>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-sm truncate"><?= htmlspecialchars($pu['nama']) ?></p>
                            <p class="text-xs text-gray-400">
                                <?= $pu['jumlah_hutang'] ?>× hutang · 
                                <?= $pu['jumlah_bayar'] ?>× bayar
                            </p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <p class="font-bold text-sm text-red-600"><?= rupiah($pu['saldo_piutang']) ?></p>
                            <p class="text-xs text-gray-400">Sisa</p>
                        </div>
                    </div>
                    <?php $no++; endwhile; ?>
                </div>
                <?php else: ?>
                <p class="text-center text-gray-400 py-8">🎉 Tidak ada piutang!</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- 3. PELANGGAN TERBAIK (Bayar Tepat Waktu) -->
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="p-4 border-b bg-green-50">
                <h3 class="font-bold text-green-700 flex items-center gap-2">
                    <i class="fas fa-check-circle"></i> Pelanggan Terbaik
                </h3>
                <p class="text-xs text-green-500">Persentase pembayaran lunas tertinggi</p>
            </div>
            <div class="p-4">
                <?php if(mysqli_num_rows($pelanggan_baik) > 0): ?>
                <div class="space-y-3">
                    <?php 
                    $no = 1;
                    while($pb = mysqli_fetch_assoc($pelanggan_baik)): 
                        $medali = $no == 1 ? '🥇' : ($no == 2 ? '🥈' : ($no == 3 ? '🥉' : $no));
                    ?>
                    <div class="flex items-center gap-3 p-3 rounded-xl <?= $no <= 3 ? 'bg-green-50 border border-green-100' : 'hover:bg-gray-50' ?>">
                        <span class="text-2xl w-10 text-center"><?= $medali ?></span>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-sm truncate"><?= htmlspecialchars($pb['nama']) ?></p>
                            <p class="text-xs text-gray-400">
                                <?= $pb['transaksi_lunas'] ?>/<?= $pb['total_transaksi'] ?> lunas
                            </p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <p class="font-bold text-sm text-green-600"><?= $pb['persen_lunas'] ?>%</p>
                            <div class="w-20 bg-gray-200 rounded-full h-1.5 mt-1">
                                <div class="bg-green-500 h-1.5 rounded-full" style="width: <?= $pb['persen_lunas'] ?>%"></div>
                            </div>
                        </div>
                    </div>
                    <?php $no++; endwhile; ?>
                </div>
                <?php else: ?>
                <p class="text-center text-gray-400 py-8">Belum cukup data (min 3 transaksi)</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- 4. PELANGGAN PALING MENGUNTUNGKAN -->
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="p-4 border-b bg-purple-50">
                <h3 class="font-bold text-purple-700 flex items-center gap-2">
                    <i class="fas fa-chart-line"></i> Pelanggan Paling Untung
                </h3>
                <p class="text-xs text-purple-500">Berdasarkan total keuntungan (Laba Kotor)</p>
            </div>
            <div class="p-4">
                <?php if(mysqli_num_rows($pelanggan_untung) > 0): ?>
                <div class="space-y-3">
                    <?php 
                    $no = 1;
                    while($punt = mysqli_fetch_assoc($pelanggan_untung)): 
                        $medali = $no == 1 ? '🥇' : ($no == 2 ? '🥈' : ($no == 3 ? '🥉' : $no));
                    ?>
                    <div class="flex items-center gap-3 p-3 rounded-xl <?= $no <= 3 ? 'bg-purple-50 border border-purple-100' : 'hover:bg-gray-50' ?>">
                        <span class="text-2xl w-10 text-center"><?= $medali ?></span>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-sm truncate"><?= htmlspecialchars($punt['nama']) ?></p>
                            <p class="text-xs text-gray-400">
                                <?= $punt['total_transaksi'] ?> transaksi · 
                                Belanja: <?= rupiah($punt['total_belanja']) ?>
                            </p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <p class="font-bold text-sm text-purple-600"><?= rupiah($punt['total_keuntungan']) ?></p>
                            <p class="text-xs <?= $punt['margin_persen'] > 20 ? 'text-green-500' : 'text-gray-400' ?>">
                                Margin: <?= $punt['margin_persen'] ?>%
                            </p>
                        </div>
                    </div>
                    <?php $no++; endwhile; ?>
                </div>
                <?php else: ?>
                <p class="text-center text-gray-400 py-8">Belum ada data</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- 5. PELANGGAN SERING UTANG -->
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden lg:col-span-2">
            <div class="p-4 border-b bg-orange-50">
                <h3 class="font-bold text-orange-700 flex items-center gap-2">
                    <i class="fas fa-clock"></i> Pelanggan Sering Berhutang
                </h3>
                <p class="text-xs text-orange-500">Frekuensi catatan hutang (transaksi & manual)</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Pelanggan</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Total Catatan</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Dari Transaksi</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Pinjaman Manual</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Sisa Utang</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Utang Sejak</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php while($psu = mysqli_fetch_assoc($pelanggan_sering_utang)): ?>
                        <tr class="hover:bg-gray-50 text-sm">
                            <td class="px-4 py-3 font-medium"><?= htmlspecialchars($psu['nama']) ?></td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-1 rounded-full text-xs font-bold bg-orange-100 text-orange-700">
                                    <?= $psu['total_catatan_utang'] ?>×
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center"><?= $psu['utang_dari_transaksi'] ?>×</td>
                            <td class="px-4 py-3 text-center"><?= $psu['utang_manual'] ?>×</td>
                            <td class="px-4 py-3 text-right font-bold <?= $psu['saldo_piutang'] > 0 ? 'text-red-600' : 'text-green-600' ?>">
                                <?= rupiah($psu['saldo_piutang']) ?>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-400">
                                <?= floor($psu['hari_pertama_utang']) ?> hari
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        
                        <?php if(mysqli_num_rows($pelanggan_sering_utang) == 0): ?>
                        <tr>
                            <td colspan="6" class="text-center py-8 text-gray-400">Data tidak tersedia</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<script>
function filterLaporan() {
    const bulan = document.getElementById('bulan-filter').value;
    const tahun = document.getElementById('tahun-filter').value;
    window.location = `?page=laporan&jenis=pelanggan&bulan=${bulan}&tahun=${tahun}`;
}
</script>