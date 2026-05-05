<?php
$page = 'analitik';

// ==================== FILTER ====================
$tgl_mulai = $_GET['tgl_mulai'] ?? date('Y-m-01');
$tgl_akhir = $_GET['tgl_akhir'] ?? date('Y-m-d');

$where = "WHERE DATE(t.created_at) BETWEEN '$tgl_mulai' AND '$tgl_akhir' AND t.status != 'void'";

// ==================== RINGKASAN UTAMA ====================
$ringkasan = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT 
        COUNT(t.id) as total_transaksi,
        COUNT(DISTINCT t.pelanggan_id) as total_pelanggan,
        COALESCE(SUM(t.total),0) as total_omset,
        (SELECT COALESCE(SUM(td2.qty_dasar),0) 
         FROM transaksi_detail td2 
         JOIN transaksi t2 ON td2.transaksi_id = t2.id 
         WHERE DATE(t2.created_at) BETWEEN '$tgl_mulai' AND '$tgl_akhir' 
         AND t2.status != 'void') as total_item
    FROM transaksi t
    $where
"));

// Keuntungan
$keuntungan = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT 
        COALESCE(SUM(
            CASE 
                WHEN td.tipe_harga = 'Paket' THEN 
                    td.subtotal - (td.qty_dasar * p.harga_beli)
                ELSE 
                    td.qty_dasar * (td.harga_satuan - p.harga_beli)
            END
        ),0) as total_keuntungan
    FROM transaksi_detail td
    JOIN transaksi t ON td.transaksi_id = t.id
    JOIN produk p ON td.produk_id = p.id
    $where
    AND td.produk_id > 0
"));

$margin = $ringkasan['total_omset'] > 0 ? round(($keuntungan['total_keuntungan'] / $ringkasan['total_omset']) * 100, 1) : 0;
$rata_transaksi = $ringkasan['total_transaksi'] > 0 ? round($ringkasan['total_omset'] / $ringkasan['total_transaksi']) : 0;

// ==================== JAM SIBUK ====================
$jam_sibuk = mysqli_query($conn, "
    SELECT 
        HOUR(t.created_at) as jam,
        COUNT(*) as total_transaksi,
        COALESCE(SUM(t.total),0) as total_omset
    FROM transaksi t
    $where
    GROUP BY HOUR(t.created_at)
    ORDER BY jam
");

$jam_max = 0;
$jam_data = [];
while($j = mysqli_fetch_assoc($jam_sibuk)) {
    $jam_data[$j['jam']] = $j;
    if ($j['total_transaksi'] > $jam_max) $jam_max = $j['total_transaksi'];
}

// ==================== HARI SIBUK ====================
$hari_sibuk = mysqli_query($conn, "
    SELECT 
        DAYNAME(t.created_at) as hari,
        COUNT(*) as total_transaksi,
        COALESCE(SUM(t.total),0) as total_omset
    FROM transaksi t
    $where
    GROUP BY DAYNAME(t.created_at)
    ORDER BY FIELD(DAYNAME(t.created_at), 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')
");

$hari_max = 0;
$hari_data = [];
$hari_labels = ['Monday'=>'Senin','Tuesday'=>'Selasa','Wednesday'=>'Rabu','Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu','Sunday'=>'Minggu'];
while($h = mysqli_fetch_assoc($hari_sibuk)) {
    $hari_data[$h['hari']] = $h;
    if ($h['total_transaksi'] > $hari_max) $hari_max = $h['total_transaksi'];
}

// ==================== TREN BULANAN (6 bulan) ====================
$tren_bulan = mysqli_query($conn, "
    SELECT 
        DATE_FORMAT(t.created_at, '%Y-%m') as bulan,
        COUNT(*) as total_transaksi,
        COALESCE(SUM(t.total),0) as total_omset
    FROM transaksi t
    WHERE t.created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) AND t.status != 'void'
    GROUP BY DATE_FORMAT(t.created_at, '%Y-%m')
    ORDER BY bulan ASC
");

$bulan_max_omset = 0;
$tren_data = [];
while($tb = mysqli_fetch_assoc($tren_bulan)) {
    $tren_data[$tb['bulan']] = $tb;
    if ($tb['total_omset'] > $bulan_max_omset) $bulan_max_omset = $tb['total_omset'];
}

// ==================== TOP PRODUK ====================
$top_produk = mysqli_query($conn, "
    SELECT 
        p.nama,
        SUM(td.qty_dasar) as total_qty,
        SUM(td.subtotal) as total_omset,
        COALESCE(SUM(
            CASE 
                WHEN td.tipe_harga = 'Paket' THEN 
                    td.subtotal - (td.qty_dasar * p.harga_beli)
                ELSE 
                    td.qty_dasar * (td.harga_satuan - p.harga_beli)
            END
        ),0) as total_keuntungan
    FROM transaksi_detail td
    JOIN transaksi t ON td.transaksi_id = t.id
    JOIN produk p ON td.produk_id = p.id
    $where
    GROUP BY p.id
    ORDER BY total_omset DESC
    LIMIT 10
");

// ==================== METODE PEMBAYARAN ====================
$metode_bayar = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT 
        SUM(CASE WHEN status = 'lunas' THEN 1 ELSE 0 END) as lunas,
        SUM(CASE WHEN status = 'piutang' THEN 1 ELSE 0 END) as piutang
    FROM transaksi t
    $where
"));
?>

<div class="space-y-6">
    
    <!-- ==================== HEADER ==================== -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-dark">
                <i class="fas fa-chart-pie text-primary mr-2"></i>Analitik Bisnis
            </h1>
            <p class="text-sm text-gray-500 mt-1">Insight performa toko Anda</p>
        </div>
        
        <div class="flex gap-2 no-print">
            <button onclick="window.print()" class="bg-green-500 text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-green-600 transition">
                <i class="fas fa-print mr-1"></i> Cetak
            </button>
        </div>
    </div>

    <!-- ==================== FILTER ==================== -->
    <div class="bg-white rounded-2xl shadow-sm p-4 no-print">
        <form method="get" class="flex flex-wrap gap-3 items-end">
            <input type="hidden" name="page" value="analitik">
            <div>
                <label class="text-xs text-gray-500 block mb-1">Dari</label>
                <input type="date" name="tgl_mulai" value="<?= $tgl_mulai ?>" class="px-3 py-2 border rounded-lg text-sm">
            </div>
            <div>
                <label class="text-xs text-gray-500 block mb-1">Sampai</label>
                <input type="date" name="tgl_akhir" value="<?= $tgl_akhir ?>" class="px-3 py-2 border rounded-lg text-sm">
            </div>
            <button type="submit" class="bg-primary text-white px-4 py-2 rounded-lg text-sm">Filter</button>
            <a href="?page=analitik" class="text-sm text-gray-400 py-2">Reset</a>
            
            <span class="text-xs text-gray-400 mx-2">|</span>
            <a href="?page=analitik&tgl_mulai=<?= date('Y-m-d') ?>&tgl_akhir=<?= date('Y-m-d') ?>" class="text-xs px-2 py-1 border rounded-full hover:bg-primary hover:text-white transition">Hari Ini</a>
            <a href="?page=analitik&tgl_mulai=<?= date('Y-m-d', strtotime('monday this week')) ?>&tgl_akhir=<?= date('Y-m-d') ?>" class="text-xs px-2 py-1 border rounded-full hover:bg-primary hover:text-white transition">Minggu Ini</a>
            <a href="?page=analitik&tgl_mulai=<?= date('Y-m-01') ?>&tgl_akhir=<?= date('Y-m-d') ?>" class="text-xs px-2 py-1 border rounded-full hover:bg-primary hover:text-white transition">Bulan Ini</a>
        </form>
    </div>

    <!-- ==================== RINGKASAN CARD ==================== -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl shadow-sm p-5 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500">💰 Omset</p>
                    <p class="text-xl font-bold"><?= rupiah($ringkasan['total_omset']) ?></p>
                </div>
                <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-dollar-sign text-blue-500"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-2xl shadow-sm p-5 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500">📈 Keuntungan</p>
                    <p class="text-xl font-bold text-green-600"><?= rupiah($keuntungan['total_keuntungan']) ?></p>
                    <p class="text-xs text-green-400">Margin: <?= $margin ?>%</p>
                </div>
                <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-chart-line text-green-500"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-2xl shadow-sm p-5 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500">🧾 Transaksi</p>
                    <p class="text-xl font-bold"><?= $ringkasan['total_transaksi'] ?> <span class="text-xs font-normal">kali</span></p>
                    <p class="text-xs text-purple-400">Rata2: <?= rupiah($rata_transaksi) ?></p>
                </div>
                <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-receipt text-purple-500"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-2xl shadow-sm p-5 border-l-4 border-orange-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500">👥 Pelanggan</p>
                    <p class="text-xl font-bold"><?= $ringkasan['total_pelanggan'] ?> <span class="text-xs font-normal">orang</span></p>
                    <p class="text-xs text-orange-400">Item: <?= number_format($ringkasan['total_item']) ?></p>
                </div>
                <div class="w-10 h-10 bg-orange-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-users text-orange-500"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== GRAFIK: JAM SIBUK + HARI SIBUK ==================== -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Jam Sibuk -->
        <div class="bg-white rounded-2xl shadow-sm p-6">
            <h3 class="font-bold text-dark mb-4">
                <i class="fas fa-clock text-blue-500 mr-2"></i>Jam Tersibuk
            </h3>
            <div class="space-y-2">
                <?php 
                for($i = 6; $i <= 22; $i++):
                    $label = sprintf('%02d:00', $i);
                    $trans = isset($jam_data[$i]) ? $jam_data[$i]['total_transaksi'] : 0;
                    $omset = isset($jam_data[$i]) ? $jam_data[$i]['total_omset'] : 0;
                    $width = $jam_max > 0 ? round(($trans / $jam_max) * 100) : 0;
                    $is_peak = $trans > 0 && $trans >= ($jam_max * 0.8);
                ?>
                <div class="flex items-center gap-3">
                    <span class="text-xs w-10 text-right <?= $is_peak ? 'font-bold text-red-500' : 'text-gray-400' ?>"><?= $label ?></span>
                    <div class="flex-1 bg-gray-100 rounded-full h-5 overflow-hidden">
                        <div class="h-full rounded-full transition-all <?= $is_peak ? 'bg-red-400' : ($width > 50 ? 'bg-blue-400' : 'bg-blue-300') ?>" 
                             style="width: <?= max($width, $trans > 0 ? 5 : 0) ?>%"></div>
                    </div>
                    <span class="text-xs w-16 text-gray-500"><?= $trans ?> trans</span>
                    <span class="text-xs w-20 text-right text-gray-400 hidden md:block"><?= $omset > 0 ? rupiah($omset) : '' ?></span>
                </div>
                <?php endfor; ?>
            </div>
            <?php if($jam_max == 0): ?>
            <p class="text-center text-gray-400 text-sm py-8">Belum ada data</p>
            <?php endif; ?>
        </div>

        <!-- Hari Sibuk -->
        <div class="bg-white rounded-2xl shadow-sm p-6">
            <h3 class="font-bold text-dark mb-4">
                <i class="fas fa-calendar text-green-500 mr-2"></i>Hari Tersibuk
            </h3>
            <div class="space-y-3">
                <?php 
                $day_order = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
                foreach($day_order as $day):
                    $label = $hari_labels[$day];
                    $trans = isset($hari_data[$day]) ? $hari_data[$day]['total_transaksi'] : 0;
                    $omset = isset($hari_data[$day]) ? $hari_data[$day]['total_omset'] : 0;
                    $width = $hari_max > 0 ? round(($trans / $hari_max) * 100) : 0;
                    $is_peak = $trans > 0 && $trans >= ($hari_max * 0.85);
                ?>
                <div class="flex items-center gap-3">
                    <span class="text-xs w-14 <?= $is_peak ? 'font-bold text-red-500' : 'text-gray-500' ?>"><?= $label ?></span>
                    <div class="flex-1 bg-gray-100 rounded-full h-6 overflow-hidden">
                        <div class="h-full rounded-full flex items-center justify-end px-2 transition-all <?= $is_peak ? 'bg-red-400' : ($width > 60 ? 'bg-green-400' : 'bg-green-300') ?>" 
                             style="width: <?= max($width, $trans > 0 ? 5 : 0) ?>%">
                            <span class="text-xs font-bold text-white <?= $width < 20 ? 'hidden' : '' ?>"><?= $trans ?></span>
                        </div>
                    </div>
                    <span class="text-xs w-24 text-right text-gray-500"><?= $omset > 0 ? rupiah($omset) : '' ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php if($hari_max == 0): ?>
            <p class="text-center text-gray-400 text-sm py-8">Belum ada data</p>
            <?php endif; ?>
        </div>

    </div>

    <!-- ==================== TREN BULANAN ==================== -->
    <?php if(count($tren_data) > 0): ?>
    <div class="bg-white rounded-2xl shadow-sm p-6">
        <h3 class="font-bold text-dark mb-4">
            <i class="fas fa-chart-bar text-purple-500 mr-2"></i>Tren Omset 6 Bulan Terakhir
        </h3>
        
        <div class="space-y-3">
            <?php foreach($tren_data as $tb): 
                $bulan_label = date('F Y', strtotime($tb['bulan'] . '-01'));
                $width = $bulan_max_omset > 0 ? round(($tb['total_omset'] / $bulan_max_omset) * 100) : 0;
                $is_highest = $tb['total_omset'] >= $bulan_max_omset;
            ?>
            <div class="flex items-center gap-3">
                <span class="text-xs w-24 <?= $is_highest ? 'font-bold text-primary' : 'text-gray-500' ?>"><?= $bulan_label ?></span>
                <div class="flex-1 bg-gray-100 rounded-full h-7 overflow-hidden">
                    <div class="h-full rounded-full bg-gradient-to-r from-primary to-indigo-400 flex items-center justify-end px-3 transition-all <?= $is_highest ? 'shadow-lg' : '' ?>" 
                         style="width: <?= max($width, 3) ?>%">
                        <span class="text-xs font-bold text-white"><?= rupiah($tb['total_omset']) ?></span>
                    </div>
                </div>
                <span class="text-xs w-16 text-gray-400"><?= $tb['total_transaksi'] ?> trans</span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- ==================== TOP PRODUK + METODE BAYAR ==================== -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Top 10 Produk -->
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="p-4 border-b">
                <h3 class="font-bold">🏆 Top 10 Produk</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs uppercase">#</th>
                            <th class="px-4 py-3 text-left text-xs uppercase">Produk</th>
                            <th class="px-4 py-3 text-center text-xs uppercase">Qty</th>
                            <th class="px-4 py-3 text-right text-xs uppercase">Omset</th>
                            <th class="px-4 py-3 text-right text-xs uppercase">Untung</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php $no = 1; while($tp = mysqli_fetch_assoc($top_produk)): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-center">
                                <span class="w-6 h-6 rounded-full inline-flex items-center justify-center text-xs font-bold <?= $no <= 3 ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-500' ?>">
                                    <?= $no ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 font-medium"><?= htmlspecialchars($tp['nama']) ?></td>
                            <td class="px-4 py-3 text-center"><?= number_format($tp['total_qty']) ?></td>
                            <td class="px-4 py-3 text-right"><?= rupiah($tp['total_omset']) ?></td>
                            <td class="px-4 py-3 text-right text-green-600"><?= rupiah($tp['total_keuntungan']) ?></td>
                        </tr>
                        <?php $no++; endwhile; ?>
                        <?php if(mysqli_num_rows($top_produk) == 0): ?>
                        <tr><td colspan="5" class="text-center py-8 text-gray-400">Tidak ada data</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Metode Pembayaran -->
        <div class="bg-white rounded-2xl shadow-sm p-6">
            <h3 class="font-bold text-dark mb-4">
                <i class="fas fa-credit-card text-blue-500 mr-2"></i>Metode Pembayaran
            </h3>
            
            <?php 
            $total_bayar = $metode_bayar['lunas'] + $metode_bayar['piutang'];
            $lunas_persen = $total_bayar > 0 ? round(($metode_bayar['lunas'] / $total_bayar) * 100) : 0;
            $piutang_persen = $total_bayar > 0 ? round(($metode_bayar['piutang'] / $total_bayar) * 100) : 0;
            ?>
            
            <div class="space-y-4">
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span>✅ Lunas</span>
                        <span class="font-bold"><?= $metode_bayar['lunas'] ?> (<?= $lunas_persen ?>%)</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-4">
                        <div class="bg-green-500 h-4 rounded-full" style="width: <?= $lunas_persen ?>%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span>⚠️ Piutang</span>
                        <span class="font-bold"><?= $metode_bayar['piutang'] ?> (<?= $piutang_persen ?>%)</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-4">
                        <div class="bg-yellow-500 h-4 rounded-full" style="width: <?= $piutang_persen ?>%"></div>
                    </div>
                </div>
            </div>
            
            <div class="mt-6 space-y-3">
                <div class="flex justify-between text-sm py-2 border-b">
                    <span>Total Transaksi</span>
                    <span class="font-bold"><?= $total_bayar ?></span>
                </div>
                <div class="flex justify-between text-sm py-2 border-b">
                    <span>Rata-rata Omset</span>
                    <span class="font-bold"><?= rupiah($rata_transaksi) ?></span>
                </div>
                <div class="flex justify-between text-sm py-2 border-b">
                    <span>Periode</span>
                    <span class="text-xs text-gray-400"><?= date('d/m/Y', strtotime($tgl_mulai)) ?> - <?= date('d/m/Y', strtotime($tgl_akhir)) ?></span>
                </div>
            </div>
        </div>

    </div>
</div>