<?php
$page = 'program-thr';

// ==================== FILTER PERIODE ====================
$tgl_mulai = $_GET['tgl_mulai'] ?? date('Y-01-01');
$tgl_akhir = $_GET['tgl_akhir'] ?? date('Y-m-d');
$persentase = $_GET['persen'] ?? 5; // Default 5%

// ==================== AMBIL DATA PELANGGAN ====================
$pelanggan_data = mysqli_query($conn, "
    SELECT 
        p.id,
        p.nama,
        p.no_hp,
        p.saldo_piutang,
        COUNT(t.id) as total_transaksi,
        COALESCE(SUM(t.total),0) as total_belanja,
        SUM(CASE WHEN t.status = 'lunas' THEN 1 ELSE 0 END) as transaksi_lunas,
        ROUND(
            SUM(CASE WHEN t.status = 'lunas' THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(t.id), 0), 
            1
        ) as persen_lunas,
        COALESCE(SUM(td.qty * (td.harga_satuan - pr.harga_beli)), 0) as total_keuntungan,
        MAX(t.created_at) as terakhir_belanja,
        DATEDIFF(CURDATE(), MAX(t.created_at)) as hari_terakhir,
        -- Cek utang macet
        COALESCE(MAX(CASE WHEN pt.tipe != 'pembayaran' AND pt.created_at < DATE_SUB(CURDATE(), INTERVAL 30 DAY) 
              AND pt.saldo_sesudah > 0 THEN 1 ELSE 0 END), 0) as ada_utang_macet
    FROM pelanggan p
    JOIN transaksi t ON p.id = t.pelanggan_id
    JOIN transaksi_detail td ON t.id = td.transaksi_id
    JOIN produk pr ON td.produk_id = pr.id
    LEFT JOIN piutang pt ON p.id = pt.pelanggan_id
    WHERE p.nama != 'Umum'
        AND DATE(t.created_at) BETWEEN '$tgl_mulai' AND '$tgl_akhir' AND t.status != 'void'
    GROUP BY p.id
    HAVING total_transaksi >= 2
    ORDER BY total_keuntungan DESC
");

// ==================== KATEGORIKAN ====================
$loyal1 = []; // Diamond
$loyal2 = []; // Platinum
$loyal3 = []; // Gold
$loyal4 = []; // Silver
$loyal5 = []; // Bronze
$tidak_loyal = [];

while($pl = mysqli_fetch_assoc($pelanggan_data)) {
    $skor = 0;
    
    // Hitung kategori
    if ($pl['total_belanja'] >= 10000000 && $pl['total_transaksi'] >= 50 && $pl['persen_lunas'] >= 95) {
        $loyal1[] = $pl;
    } elseif ($pl['total_belanja'] >= 5000000 && $pl['total_transaksi'] >= 30 && $pl['persen_lunas'] >= 85) {
        $loyal2[] = $pl;
    } elseif ($pl['total_belanja'] >= 2000000 && $pl['total_transaksi'] >= 15 && $pl['persen_lunas'] >= 75) {
        $loyal3[] = $pl;
    } elseif ($pl['total_belanja'] >= 500000 && $pl['total_transaksi'] >= 5 && $pl['persen_lunas'] >= 60) {
        $loyal4[] = $pl;
    } elseif ($pl['total_belanja'] >= 100000 && $pl['total_transaksi'] >= 2 && $pl['persen_lunas'] >= 50) {
        $loyal5[] = $pl;
    } else {
        $tidak_loyal[] = $pl;
    }
}

// Hitung total per kategori
function hitungTotalKategori($data) {
    $total_keuntungan = 0;
    $total_thr = 0;
    foreach ($data as $d) {
        $total_keuntungan += $d['total_keuntungan'];
    }
    return [
        'jumlah' => count($data),
        'keuntungan' => $total_keuntungan,
    ];
}

$kategori_list = [
    ['nama' => '💎 Loyal 1 (Diamond)', 'data' => $loyal1, 'icon' => 'diamond', 'color' => 'blue'],
    ['nama' => '👑 Loyal 2 (Platinum)', 'data' => $loyal2, 'icon' => 'crown', 'color' => 'purple'],
    ['nama' => '🥇 Loyal 3 (Gold)', 'data' => $loyal3, 'icon' => 'medal', 'color' => 'yellow'],
    ['nama' => '🥈 Loyal 4 (Silver)', 'data' => $loyal4, 'icon' => 'star', 'color' => 'gray'],
    ['nama' => '🥉 Loyal 5 (Bronze)', 'data' => $loyal5, 'icon' => 'award', 'color' => 'orange'],
];

// Total keseluruhan
$total_semua_keuntungan = 0;
$total_semua_thr = 0;
$total_semua_pelanggan = 0;
foreach ($kategori_list as $kat) {
    $stat = hitungTotalKategori($kat['data']);
    $total_semua_keuntungan += $stat['keuntungan'];
    $total_semua_pelanggan += $stat['jumlah'];
}
$total_semua_thr = $total_semua_keuntungan * ($persentase / 100);
?>

<div class="space-y-6">
    
    <!-- ==================== HEADER ==================== -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-dark">
                <i class="fas fa-gift text-red-500 mr-2"></i>Program THR Pelanggan Loyal
            </h1>
            <p class="text-sm text-gray-500 mt-1">Hitung THR berdasarkan loyalitas & keuntungan</p>
        </div>
        
        <!-- Tombol Cetak -->
        <button onclick="window.print()" class="inline-flex items-center gap-2 bg-green-500 text-white px-5 py-2.5 rounded-xl font-semibold hover:bg-green-600 transition no-print">
            <i class="fas fa-print"></i> Cetak Laporan
        </button>
    </div>

    <!-- ==================== FILTER ==================== -->
    <div class="bg-white rounded-2xl shadow-sm p-5 no-print">
        <form method="get" class="flex flex-wrap gap-3 items-end">
            <input type="hidden" name="page" value="program-thr">
            
            <div>
                <label class="text-xs text-gray-500 font-medium block mb-1">Periode Mulai</label>
                <input type="date" name="tgl_mulai" value="<?= $tgl_mulai ?>" 
                       class="px-4 py-2.5 border rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none">
            </div>
            <div>
                <label class="text-xs text-gray-500 font-medium block mb-1">Periode Akhir</label>
                <input type="date" name="tgl_akhir" value="<?= $tgl_akhir ?>" 
                       class="px-4 py-2.5 border rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none">
            </div>
            <div>
                <label class="text-xs text-gray-500 font-medium block mb-1">Persentase THR (%)</label>
                <div class="relative">
                    <input type="number" name="persen" value="<?= $persentase ?>" min="0" max="100" step="0.5"
                           class="w-24 pl-4 pr-8 py-2.5 border rounded-xl text-sm text-center focus:ring-2 focus:ring-primary outline-none font-bold">
                    <span class="absolute right-3 top-2.5 text-gray-400">%</span>
                </div>
                <p class="text-xs text-gray-400 mt-1">dari keuntungan</p>
            </div>
            <button type="submit" class="bg-primary text-white px-5 py-2.5 rounded-xl text-sm font-medium hover:bg-indigo-700 transition">
                <i class="fas fa-filter mr-1"></i> Tampilkan
            </button>
            <a href="?page=program-thr" class="px-3 py-2.5 text-sm text-gray-400 hover:text-gray-600">Reset</a>
        </form>
    </div>

    <!-- ==================== RINGKASAN ==================== -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl shadow-sm p-5 border-l-4 border-blue-500">
            <p class="text-sm text-gray-500">Total Pelanggan Loyal</p>
            <p class="text-3xl font-bold mt-1"><?= $total_semua_pelanggan ?> <span class="text-sm font-normal text-gray-400">orang</span></p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm p-5 border-l-4 border-green-500">
            <p class="text-sm text-gray-500">Total Keuntungan</p>
            <p class="text-3xl font-bold text-green-600 mt-1"><?= rupiah($total_semua_keuntungan) ?></p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm p-5 border-l-4 border-red-500">
            <p class="text-sm text-gray-500">Total Budget THR (<?= $persentase ?>%)</p>
            <p class="text-3xl font-bold text-red-600 mt-1"><?= rupiah($total_semua_thr) ?></p>
        </div>
    </div>

    <!-- ==================== TABEL PER KATEGORI ==================== -->
    <?php foreach ($kategori_list as $kat): ?>
    <?php $stat = hitungTotalKategori($kat['data']); ?>
    <?php if (count($kat['data']) > 0): ?>
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden <?= $kat['color'] == 'gray' ? 'print:break-inside-avoid' : '' ?>">
        
        <!-- Header Kategori -->
        <div class="p-4 border-b bg-<?= $kat['color'] ?>-50 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-lg text-<?= $kat['color'] ?>-700">
                    <i class="fas fa-<?= $kat['icon'] ?> mr-2"></i><?= $kat['nama'] ?>
                </h3>
                <p class="text-xs text-<?= $kat['color'] ?>-500">
                    <?= $stat['jumlah'] ?> pelanggan · 
                    Keuntungan: <?= rupiah($stat['keuntungan']) ?> · 
                    THR (<?= $persentase ?>%): <?= rupiah($stat['keuntungan'] * ($persentase / 100)) ?>
                </p>
            </div>
            <span class="text-xs text-gray-400">Total THR: <?= rupiah($stat['keuntungan'] * ($persentase / 100)) ?></span>
        </div>
        
        <!-- Tabel -->
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">No</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Pelanggan</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Total Belanja</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Transaksi</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Lunas</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Keuntungan</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase">THR (<?= $persentase ?>%)</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php $no = 1; foreach ($kat['data'] as $pl): 
                        $thr = $pl['total_keuntungan'] * ($persentase / 100);
                        
                        // Warna status
                        $status_class = 'bg-green-100 text-green-700';
                        $status_text = 'Aktif';
                        if ($pl['hari_terakhir'] > 90) {
                            $status_class = 'bg-red-100 text-red-700';
                            $status_text = 'Pasif';
                        } elseif ($pl['hari_terakhir'] > 30) {
                            $status_class = 'bg-yellow-100 text-yellow-700';
                            $status_text = 'Jarang';
                        }
                        if ($pl['ada_utang_macet']) {
                            $status_class = 'bg-red-100 text-red-700';
                            $status_text = '⚠️ Utang Macet';
                        }
                    ?>
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 text-center"><?= $no++ ?></td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-<?= $kat['color'] ?>-100 flex items-center justify-center font-bold text-xs text-<?= $kat['color'] ?>-600">
                                    <?= strtoupper(substr($pl['nama'], 0, 1)) ?>
                                </div>
                                <div>
                                    <p class="font-medium truncate"><?= htmlspecialchars($pl['nama']) ?></p>
                                    <p class="text-xs text-gray-400">Terakhir: <?= $pl['hari_terakhir'] ?> hari lalu</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-right font-bold"><?= rupiah($pl['total_belanja']) ?></td>
                        <td class="px-4 py-3 text-center font-bold"><?= $pl['total_transaksi'] ?>x</td>
                        <td class="px-4 py-3 text-center">
                            <span class="font-bold <?= $pl['persen_lunas'] >= 80 ? 'text-green-600' : 'text-yellow-600' ?>">
                                <?= $pl['persen_lunas'] ?>%
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right font-bold text-purple-600"><?= rupiah($pl['total_keuntungan']) ?></td>
                        <td class="px-4 py-3 text-right font-bold text-red-600"><?= rupiah($thr) ?></td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-1 text-xs rounded-full font-medium <?= $status_class ?>">
                                <?= $status_text ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="bg-gray-50 font-bold">
                    <tr>
                        <td class="px-4 py-3" colspan="5">TOTAL <?= $stat['jumlah'] ?> PELANGGAN</td>
                        <td class="px-4 py-3 text-right text-purple-600"><?= rupiah($stat['keuntungan']) ?></td>
                        <td class="px-4 py-3 text-right text-red-600"><?= rupiah($stat['keuntungan'] * ($persentase / 100)) ?></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <?php endif; ?>
    <?php endforeach; ?>

    <!-- ==================== TIDAK LOYAL (OPTIONAL) ==================== -->
    <?php if (count($tidak_loyal) > 0 && ($_GET['show_all'] ?? 0) == 1): ?>
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden opacity-60">
        <div class="p-4 border-b bg-gray-50">
            <h3 class="font-bold text-gray-600">😔 Belum Masuk Kategori Loyal (<?= count($tidak_loyal) ?> pelanggan)</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs uppercase">Pelanggan</th>
                        <th class="px-4 py-3 text-right text-xs uppercase">Belanja</th>
                        <th class="px-4 py-3 text-center text-xs uppercase">Trans</th>
                        <th class="px-4 py-3 text-center text-xs uppercase">Lunas</th>
                        <th class="px-4 py-3 text-right text-xs uppercase">Untung</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php foreach ($tidak_loyal as $tl): ?>
                    <tr>
                        <td class="px-4 py-3 font-medium"><?= htmlspecialchars($tl['nama']) ?></td>
                        <td class="px-4 py-3 text-right"><?= rupiah($tl['total_belanja']) ?></td>
                        <td class="px-4 py-3 text-center"><?= $tl['total_transaksi'] ?>x</td>
                        <td class="px-4 py-3 text-center"><?= $tl['persen_lunas'] ?>%</td>
                        <td class="px-4 py-3 text-right"><?= rupiah($tl['total_keuntungan']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Tombol tampil semua -->
    <?php if (count($tidak_loyal) > 0 && ($_GET['show_all'] ?? 0) != 1): ?>
    <div class="text-center no-print">
        <a href="?page=program-thr&tgl_mulai=<?= $tgl_mulai ?>&tgl_akhir=<?= $tgl_akhir ?>&persen=<?= $persentase ?>&show_all=1" 
           class="text-sm text-gray-400 hover:text-primary transition">
            <i class="fas fa-chevron-down mr-1"></i> Tampilkan <?= count($tidak_loyal) ?> pelanggan yang belum loyal
        </a>
    </div>
    <?php endif; ?>

    <!-- ==================== INFO ==================== -->
    <div class="bg-blue-50 border border-blue-200 rounded-2xl p-5 text-sm text-blue-700">
        <h4 class="font-bold mb-2"><i class="fas fa-info-circle mr-2"></i>Kriteria Loyalitas</h4>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div>
                <p class="font-semibold">💎 Loyal 1</p>
                <p class="text-xs">Belanja &gt; 10jt · Transaksi &gt; 50x · Lunas &gt; 95%</p>
            </div>
            <div>
                <p class="font-semibold">👑 Loyal 2</p>
                <p class="text-xs">Belanja &gt; 5jt · Transaksi &gt; 30x · Lunas &gt; 85%</p>
            </div>
            <div>
                <p class="font-semibold">🥇 Loyal 3</p>
                <p class="text-xs">Belanja &gt; 2jt · Transaksi &gt; 15x · Lunas &gt; 75%</p>
            </div>
            <div>
                <p class="font-semibold">🥈 Loyal 4</p>
                <p class="text-xs">Belanja &gt; 500rb · Transaksi &gt; 5x · Lunas &gt; 60%</p>
            </div>
            <div>
                <p class="font-semibold">🥉 Loyal 5</p>
                <p class="text-xs">Belanja &gt; 100rb · Transaksi &gt; 2x · Lunas &gt; 50%</p>
            </div>
            <div>
                <p class="font-semibold text-red-500">⚠️ Utang Macet</p>
                <p class="text-xs">Utang &gt; 30 hari = tidak layak THR</p>
            </div>
        </div>
    </div>

</div>

<!-- Print CSS -->
<style>
@media print {
    .no-print { display: none !important; }
    body { background: white !important; }
    .bg-white { box-shadow: none !important; border: 1px solid #ddd !important; }
    @page { margin: 1cm; }
}
</style>