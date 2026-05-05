<?php
$page = 'laporan-keuangan';

// ==================== FILTER ====================
$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');
$tab = $_GET['tab'] ?? 'harian';

$bulan_nama = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
               'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

// ==================== TAB HARIAN ====================
$harian_data = [];
$harian_total = ['transaksi' => 0, 'omset' => 0, 'untung' => 0, 'item' => 0];
$max_harian = ['omset' => 0, 'tgl' => 0];
$min_harian = ['omset' => PHP_INT_MAX, 'tgl' => 0];

$jumlah_hari = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);

for ($tgl = 1; $tgl <= $jumlah_hari; $tgl++) {
    $date = sprintf('%04d-%02d-%02d', $tahun, $bulan, $tgl);
    
    $hari = mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT 
            (SELECT COUNT(*) FROM transaksi WHERE DATE(created_at) = '$date' AND status != 'void') as total_transaksi,
            (SELECT COALESCE(SUM(total),0) FROM transaksi WHERE DATE(created_at) = '$date' AND status != 'void') as total_omset,
            COALESCE(SUM(
                CASE 
                    WHEN td.tipe_harga = 'Paket' THEN 
                        td.subtotal - (td.qty_dasar * p.harga_beli)
                    ELSE 
                        td.qty_dasar * (td.harga_satuan - p.harga_beli)
                END
            ),0) as total_untung,
            COALESCE(SUM(td.qty_dasar),0) as total_item
        FROM transaksi t
        LEFT JOIN transaksi_detail td ON t.id = td.transaksi_id
        LEFT JOIN produk p ON td.produk_id = p.id
        WHERE DATE(t.created_at) = '$date' AND t.status != 'void'
    "));
    
    $hari['tgl'] = $tgl;
    $hari['omset'] = (int)$hari['total_omset'];
    $hari['untung'] = (int)$hari['total_untung'];
    $hari['trans'] = (int)$hari['total_transaksi'];
    $hari['item'] = (int)$hari['total_item'];
    $hari['margin'] = $hari['omset'] > 0 ? round(($hari['untung'] / $hari['omset']) * 100, 1) : 0;
    
    $harian_data[] = $hari;
    
    if ($hari['trans'] > 0) {
        $harian_total['transaksi'] += $hari['trans'];
        $harian_total['omset'] += $hari['omset'];
        $harian_total['untung'] += $hari['untung'];
        $harian_total['item'] += $hari['item'];
        
        if ($hari['omset'] > $max_harian['omset']) $max_harian = ['omset' => $hari['omset'], 'tgl' => $tgl];
        if ($hari['omset'] < $min_harian['omset']) $min_harian = ['omset' => $hari['omset'], 'tgl' => $tgl];
    }
}
$harian_total['margin'] = $harian_total['omset'] > 0 ? round(($harian_total['untung'] / $harian_total['omset']) * 100, 1) : 0;

// ==================== TAB BULANAN ====================
$bulanan_data = [];
$bulanan_total = ['transaksi' => 0, 'omset' => 0, 'untung' => 0, 'item' => 0];
$max_bulanan = ['omset' => 0, 'bulan' => ''];
$min_bulanan = ['omset' => PHP_INT_MAX, 'bulan' => ''];

for ($bln = 1; $bln <= 12; $bln++) {
    $bulan_row = mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT 
            (SELECT COUNT(*) FROM transaksi WHERE MONTH(created_at) = '$bln' AND YEAR(created_at) = '$tahun' AND status != 'void') as total_transaksi,
            (SELECT COALESCE(SUM(total),0) FROM transaksi WHERE MONTH(created_at) = '$bln' AND YEAR(created_at) = '$tahun' AND status != 'void') as total_omset,
            COALESCE(SUM(
                CASE 
                    WHEN td.tipe_harga = 'Paket' THEN 
                        td.subtotal - (td.qty_dasar * p.harga_beli)
                    ELSE 
                        td.qty_dasar * (td.harga_satuan - p.harga_beli)
                END
            ),0) as total_untung,
            COALESCE(SUM(td.qty_dasar),0) as total_item
        FROM transaksi t
        LEFT JOIN transaksi_detail td ON t.id = td.transaksi_id
        LEFT JOIN produk p ON td.produk_id = p.id
        WHERE MONTH(t.created_at) = '$bln' AND YEAR(t.created_at) = '$tahun' AND t.status != 'void'
    "));
    
    $bulan_row['bulan'] = $bln;
    $bulan_row['nama_bulan'] = $bulan_nama[$bln];
    $bulan_row['omset'] = (int)$bulan_row['total_omset'];
    $bulan_row['untung'] = (int)$bulan_row['total_untung'];
    $bulan_row['trans'] = (int)$bulan_row['total_transaksi'];
    $bulan_row['item'] = (int)$bulan_row['total_item'];
    $bulan_row['margin'] = $bulan_row['omset'] > 0 ? round(($bulan_row['untung'] / $bulan_row['omset']) * 100, 1) : 0;
    
    $bulanan_data[] = $bulan_row;
    
    if ($bulan_row['trans'] > 0) {
        $bulanan_total['transaksi'] += $bulan_row['trans'];
        $bulanan_total['omset'] += $bulan_row['omset'];
        $bulanan_total['untung'] += $bulan_row['untung'];
        $bulanan_total['item'] += $bulan_row['item'];
        
        if ($bulan_row['omset'] > $max_bulanan['omset']) $max_bulanan = ['omset' => $bulan_row['omset'], 'bulan' => $bulan_nama[$bln]];
        if ($bulan_row['omset'] < $min_bulanan['omset'] && $bulan_row['omset'] > 0) $min_bulanan = ['omset' => $bulan_row['omset'], 'bulan' => $bulan_nama[$bln]];
    }
}
$bulanan_total['margin'] = $bulanan_total['omset'] > 0 ? round(($bulanan_total['untung'] / $bulanan_total['omset']) * 100, 1) : 0;
?>

<div class="space-y-6">
    
    <!-- ==================== HEADER ==================== -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-dark">
                <i class="fas fa-chart-line text-primary mr-2"></i>Laporan Keuangan
            </h1>
            <p class="text-sm text-gray-500 mt-1">Ringkasan pendapatan & keuntungan</p>
        </div>
        <button onclick="window.print()" class="bg-green-500 text-white px-5 py-2.5 rounded-xl text-sm font-medium hover:bg-green-600 transition no-print">
            <i class="fas fa-print mr-1"></i> Cetak
        </button>
    </div>

    <!-- ==================== TABS ==================== -->
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="border-b flex no-print">
            <a href="?page=laporan-keuangan&tab=harian&bulan=<?= $bulan ?>&tahun=<?= $tahun ?>" 
               class="flex-1 py-3 text-sm font-semibold text-center transition <?= $tab == 'harian' ? 'border-b-2 border-primary text-primary' : 'text-gray-400 hover:text-gray-600' ?>">
                📅 Harian
            </a>
            <a href="?page=laporan-keuangan&tab=bulanan&tahun=<?= $tahun ?>" 
               class="flex-1 py-3 text-sm font-semibold text-center transition <?= $tab == 'bulanan' ? 'border-b-2 border-primary text-primary' : 'text-gray-400 hover:text-gray-600' ?>">
                📆 Bulanan
            </a>
        </div>

        <!-- ==================== FILTER ==================== -->
        <div class="p-4 border-b no-print">
            <form method="get" class="flex flex-wrap gap-3 items-end">
                <input type="hidden" name="page" value="laporan-keuangan">
                <input type="hidden" name="tab" value="<?= $tab ?>">
                
                <?php if($tab == 'harian'): ?>
                <div>
                    <label class="text-xs text-gray-500 block mb-1">Bulan</label>
                    <select name="bulan" class="px-3 py-2 border rounded-lg text-sm">
                        <?php for($i = 1; $i <= 12; $i++): ?>
                        <option value="<?= $i ?>" <?= $bulan == $i ? 'selected' : '' ?>><?= $bulan_nama[$i] ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <?php endif; ?>
                
                <div>
                    <label class="text-xs text-gray-500 block mb-1">Tahun</label>
                    <input type="number" name="tahun" value="<?= $tahun ?>" class="px-3 py-2 border rounded-lg text-sm w-24">
                </div>
                <button type="submit" class="bg-primary text-white px-4 py-2 rounded-lg text-sm">Tampilkan</button>
            </form>
        </div>

        <!-- ==================== RINGKASAN ==================== -->
        <?php 
        $ringkasan = $tab == 'harian' ? $harian_total : $bulanan_total;
        $periode_text = $tab == 'harian' ? $bulan_nama[(int)$bulan] . ' ' . $tahun : 'Tahun ' . $tahun;
        ?>
        
        <div class="p-4 bg-gray-50 border-b">
            <p class="text-sm font-semibold text-dark">📊 Ringkasan <?= $periode_text ?></p>
        </div>
        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-0 divide-x divide-y">
            <div class="p-5 text-center">
                <p class="text-xs text-gray-500">💰 Omset</p>
                <p class="text-xl font-bold text-blue-600"><?= rupiah($ringkasan['omset']) ?></p>
            </div>
            <div class="p-5 text-center">
                <p class="text-xs text-gray-500">📈 Keuntungan</p>
                <p class="text-xl font-bold text-green-600"><?= rupiah($ringkasan['untung']) ?></p>
                <p class="text-xs text-green-400">Margin: <?= $ringkasan['margin'] ?>%</p>
            </div>
            <div class="p-5 text-center">
                <p class="text-xs text-gray-500">🧾 Transaksi</p>
                <p class="text-xl font-bold"><?= $ringkasan['transaksi'] ?> <span class="text-xs text-gray-400">kali</span></p>
            </div>
            <div class="p-5 text-center">
                <p class="text-xs text-gray-500">📦 Item Terjual</p>
                <p class="text-xl font-bold"><?= number_format($ringkasan['item']) ?> <span class="text-xs text-gray-400">pcs</span></p>
            </div>
        </div>

        <!-- ==================== TABEL ==================== -->
        <div class="overflow-x-auto">
            <?php if($tab == 'harian'): ?>
            
            <!-- ==================== TABEL HARIAN ==================== -->
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase w-16">Tgl</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Transaksi</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Omset</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Keuntungan</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase w-16">Margin</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php foreach($harian_data as $h): 
                        $is_max = $h['tgl'] == $max_harian['tgl'] && $h['trans'] > 0;
                        $is_min = $h['tgl'] == $min_harian['tgl'] && $h['trans'] > 0;
                        $bg = $is_max ? 'bg-green-50' : ($is_min ? 'bg-red-50' : '');
                    ?>
                    <tr class="hover:bg-gray-50 transition <?= $bg ?>">
                        <td class="px-4 py-2 text-center font-medium">
                            <?= $h['tgl'] ?>
                            <?= $is_max ? '🔴' : '' ?>
                            <?= $is_min ? '⚪' : '' ?>
                        </td>
                        <td class="px-4 py-2 text-center"><?= $h['trans'] > 0 ? $h['trans'] : '-' ?></td>
                        <td class="px-4 py-2 text-right font-medium"><?= $h['omset'] > 0 ? rupiah($h['omset']) : '-' ?></td>
                        <td class="px-4 py-2 text-right <?= $h['untung'] > 0 ? 'text-green-600' : '' ?>">
                            <?= $h['untung'] != 0 ? rupiah($h['untung']) : '-' ?>
                        </td>
                        <td class="px-4 py-2 text-center"><?= $h['margin'] > 0 ? $h['margin'].'%' : '-' ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="bg-gray-100 font-bold border-t">
                    <tr>
                        <td class="px-4 py-3 text-center">TOTAL</td>
                        <td class="px-4 py-3 text-center"><?= $harian_total['transaksi'] ?></td>
                        <td class="px-4 py-3 text-right text-blue-600"><?= rupiah($harian_total['omset']) ?></td>
                        <td class="px-4 py-3 text-right text-green-600"><?= rupiah($harian_total['untung']) ?></td>
                        <td class="px-4 py-3 text-center"><?= $harian_total['margin'] ?>%</td>
                    </tr>
                </tfoot>
            </table>
            
            <?php else: ?>
            
            <!-- ==================== TABEL BULANAN ==================== -->
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Bulan</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Transaksi</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Omset</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Keuntungan</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase w-16">Margin</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php foreach($bulanan_data as $b): 
                        $is_max = $b['bulan'] == array_search($max_bulanan['bulan'], $bulan_nama) && $b['trans'] > 0;
                        $is_min = $b['bulan'] == array_search($min_bulanan['bulan'], $bulan_nama) && $b['trans'] > 0;
                        $bg = $is_max ? 'bg-green-50' : ($is_min ? 'bg-red-50' : '');
                    ?>
                    <tr class="hover:bg-gray-50 transition <?= $bg ?>">
                        <td class="px-4 py-3 font-medium">
                            <?= $b['nama_bulan'] ?>
                            <?= $is_max ? '🔴' : '' ?>
                            <?= $is_min ? '⚪' : '' ?>
                        </td>
                        <td class="px-4 py-3 text-center"><?= $b['trans'] > 0 ? $b['trans'] : '-' ?></td>
                        <td class="px-4 py-3 text-right font-medium"><?= $b['omset'] > 0 ? rupiah($b['omset']) : '-' ?></td>
                        <td class="px-4 py-3 text-right <?= $b['untung'] > 0 ? 'text-green-600' : '' ?>">
                            <?= $b['untung'] != 0 ? rupiah($b['untung']) : '-' ?>
                        </td>
                        <td class="px-4 py-3 text-center"><?= $b['margin'] > 0 ? $b['margin'].'%' : '-' ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="bg-gray-100 font-bold border-t">
                    <tr>
                        <td class="px-4 py-3">TOTAL</td>
                        <td class="px-4 py-3 text-center"><?= $bulanan_total['transaksi'] ?></td>
                        <td class="px-4 py-3 text-right text-blue-600"><?= rupiah($bulanan_total['omset']) ?></td>
                        <td class="px-4 py-3 text-right text-green-600"><?= rupiah($bulanan_total['untung']) ?></td>
                        <td class="px-4 py-3 text-center"><?= $bulanan_total['margin'] ?>%</td>
                    </tr>
                </tfoot>
            </table>
            
            <!-- ==================== GRAFIK BULANAN ==================== -->
            <div class="p-6 border-t">
                <h4 class="font-bold text-dark mb-4">📈 Grafik Omset Bulanan <?= $tahun ?></h4>
                <div class="space-y-2">
                    <?php 
                    $grafik_max = $max_bulanan['omset'] > 0 ? $max_bulanan['omset'] : 1;
                    foreach($bulanan_data as $b): 
                        $width = round(($b['omset'] / $grafik_max) * 100);
                    ?>
                    <div class="flex items-center gap-3">
                        <span class="text-xs w-20 text-gray-500"><?= substr($b['nama_bulan'], 0, 3) ?></span>
                        <div class="flex-1 bg-gray-100 rounded-full h-6 overflow-hidden">
                            <div class="h-full rounded-full flex items-center justify-end px-2 transition-all <?= $b['omset'] >= $grafik_max ? 'bg-green-400' : ($width > 60 ? 'bg-blue-400' : 'bg-blue-300') ?>" 
                                 style="width: <?= max($width, $b['omset'] > 0 ? 3 : 0) ?>%">
                                <span class="text-xs font-bold text-white <?= $width < 15 ? 'hidden' : '' ?>"><?= $b['omset'] > 0 ? rupiah($b['omset']) : '' ?></span>
                            </div>
                        </div>
                        <span class="text-xs w-16 text-gray-400"><?= $b['trans'] > 0 ? $b['trans'].' trans' : '' ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- ==================== LEGENDA ==================== -->
        <div class="p-3 border-t bg-gray-50 text-xs text-gray-400 flex gap-4">
            <span>🔴 Tertinggi</span>
            <span>⚪ Terendah</span>
        </div>
    </div>
</div>