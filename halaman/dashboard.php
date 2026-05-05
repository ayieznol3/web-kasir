<?php
$page = 'dashboard';

// ==================== RINGKASAN HARI INI ====================
$hari_ini = date('Y-m-d');
$kemarin = date('Y-m-d', strtotime('-1 day'));

// Omset hari ini
$omset_hariini = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COALESCE(SUM(total),0) as total, COUNT(*) as transaksi
    FROM transaksi WHERE DATE(created_at) = '$hari_ini' AND status != 'void'
"));

// Omset kemarin (untuk perbandingan)
$omset_kemarin = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COALESCE(SUM(total),0) as total FROM transaksi 
    WHERE DATE(created_at) = '$kemarin' AND status != 'void'
"));

// Keuntungan hari ini
$untung_hariini = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COALESCE(SUM(
        CASE 
            WHEN td.tipe_harga = 'Paket' THEN 
                td.subtotal - (td.qty_dasar * p.harga_beli)
            ELSE 
                td.qty_dasar * (td.harga_satuan - p.harga_beli)
        END
    ),0) as total
    FROM transaksi_detail td
    JOIN transaksi t ON td.transaksi_id = t.id
    JOIN produk p ON td.produk_id = p.id
    WHERE DATE(t.created_at) = '$hari_ini' AND t.status != 'void' AND td.produk_id > 0
"));


// Pelanggan aktif hari ini
$pelanggan_hariini = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(DISTINCT pelanggan_id) as total FROM transaksi
    WHERE DATE(created_at) = '$hari_ini' AND status != 'void' AND pelanggan_id IS NOT NULL
"));

// ==================== STOK MENIPIS ====================
$stok_tipis = mysqli_num_rows(mysqli_query($conn, "
    SELECT id FROM produk WHERE stok_dasar <= 10 AND stok_dasar >= 0
"));

// ==================== PIUTANG ====================
$piutang_info = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT 
        COALESCE(SUM(saldo_piutang),0) as total,
        COUNT(*) as jumlah
    FROM pelanggan WHERE saldo_piutang > 0
"));

$piutang_terbesar = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT nama, saldo_piutang FROM pelanggan 
    WHERE saldo_piutang > 0 ORDER BY saldo_piutang DESC LIMIT 1
"));

// Piutang terlama
$piutang_terlama = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT p.nama, DATEDIFF(CURDATE(), MIN(pt.created_at)) as hari
    FROM piutang pt
    JOIN pelanggan p ON pt.pelanggan_id = p.id
    WHERE pt.tipe != 'pembayaran' AND p.saldo_piutang > 0
    GROUP BY pt.pelanggan_id
    ORDER BY hari DESC LIMIT 1
"));

// ==================== JAM SIBUK HARI INI ====================
$jam_sibuk = mysqli_query($conn, "
    SELECT HOUR(created_at) as jam, COUNT(*) as total
    FROM transaksi
    WHERE DATE(created_at) = '$hari_ini' AND status != 'void'
    GROUP BY HOUR(created_at) ORDER BY jam
");
$jam_max = 0;
$jam_data = [];
while($j = mysqli_fetch_assoc($jam_sibuk)) {
    $jam_data[$j['jam']] = $j['total'];
    if ($j['total'] > $jam_max) $jam_max = $j['total'];
}

// ==================== 7 HARI TERAKHIR ====================
$mingguan = mysqli_query($conn, "
    SELECT DATE(created_at) as tgl, COUNT(*) as total, COALESCE(SUM(total),0) as omset
    FROM transaksi
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) AND status != 'void'
    GROUP BY DATE(created_at) ORDER BY tgl
");
$minggu_max = 0;
$minggu_data = [];
while($m = mysqli_fetch_assoc($mingguan)) {
    $minggu_data[$m['tgl']] = $m;
    if ($m['total'] > $minggu_max) $minggu_max = $m['total'];
}

// ==================== TOP 5 PRODUK ====================
$top_produk = mysqli_query($conn, "
    SELECT p.nama, SUM(td.qty) as total_qty, SUM(td.subtotal) as total_omset
    FROM transaksi_detail td
    JOIN transaksi t ON td.transaksi_id = t.id
    JOIN produk p ON td.produk_id = p.id
    WHERE DATE(t.created_at) = '$hari_ini' AND t.status != 'void'
    GROUP BY p.id ORDER BY total_qty DESC LIMIT 5
");

// ==================== TRANSAKSI TERBARU ====================
$transaksi_terbaru = mysqli_query($conn, "
    SELECT t.*, p.nama as pelanggan
    FROM transaksi t
    LEFT JOIN pelanggan p ON t.pelanggan_id = p.id
    WHERE DATE(t.created_at) = '$hari_ini' AND t.status != 'void'
    ORDER BY t.created_at DESC LIMIT 5
");

// ==================== PERBANDINGAN BULANAN ====================
$bulan_ini = date('m');
$bulan_lalu = date('m', strtotime('-1 month'));
$tahun_ini = date('Y');

$perbandingan = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT 
        (SELECT COALESCE(SUM(total),0) FROM transaksi WHERE MONTH(created_at)='$bulan_ini' AND YEAR(created_at)='$tahun_ini' AND status!='void') as omset_ini,
        (SELECT COUNT(*) FROM transaksi WHERE MONTH(created_at)='$bulan_ini' AND YEAR(created_at)='$tahun_ini' AND status!='void') as trans_ini,
        (SELECT COALESCE(SUM(total),0) FROM transaksi WHERE MONTH(created_at)='$bulan_lalu' AND YEAR(created_at)='$tahun_ini' AND status!='void') as omset_lalu,
        (SELECT COUNT(*) FROM transaksi WHERE MONTH(created_at)='$bulan_lalu' AND YEAR(created_at)='$tahun_ini' AND status!='void') as trans_lalu
"));

// Hitung persentase perubahan
$omset_change = $perbandingan['omset_lalu'] > 0 ? round((($perbandingan['omset_ini'] - $perbandingan['omset_lalu']) / $perbandingan['omset_lalu']) * 100, 1) : 0;
$trans_change = $perbandingan['trans_lalu'] > 0 ? round((($perbandingan['trans_ini'] - $perbandingan['trans_lalu']) / $perbandingan['trans_lalu']) * 100, 1) : 0;

// Omset change hari ini vs kemarin
$omset_harian_change = $omset_kemarin['total'] > 0 ? round((($omset_hariini['total'] - $omset_kemarin['total']) / $omset_kemarin['total']) * 100, 1) : 0;
?>

<div class="space-y-6">
    
    <!-- ==================== SAPAAN + JAM ==================== -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-dark">
                👋 Selamat <?= date('H') < 12 ? 'Pagi' : (date('H') < 15 ? 'Siang' : (date('H') < 19 ? 'Sore' : 'Malam')) ?>, <?= $_SESSION['nama'] ?>!
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                <i class="fas fa-calendar mr-1"></i> <?= date('l, d F Y') ?> · 
                <i class="fas fa-clock mr-1"></i> <span id="jam-dashboard">00:00</span>
            </p>
        </div>
        
        <!-- Quick Actions -->
        <div class="flex gap-2 no-print">
            <a href="?page=kasir" class="px-4 py-2 bg-primary text-white rounded-xl text-sm font-medium hover:bg-indigo-700 transition">
                <i class="fas fa-cash-register mr-1"></i> Kasir
            </a>
            <a href="?page=transaksi" class="px-4 py-2 border rounded-xl text-sm hover:bg-gray-100 transition">
                <i class="fas fa-history mr-1"></i> Transaksi
            </a>
            <a href="?page=restok" class="px-4 py-2 border rounded-xl text-sm hover:bg-gray-100 transition">
                <i class="fas fa-truck-loading mr-1"></i> Restok
            </a>
        </div>
    </div>

    <!-- ==================== CARD UTAMA ==================== -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
        
        <!-- Omset -->
        <div class="bg-white rounded-2xl shadow-sm p-4 border-l-4 border-blue-500 hover:shadow-md transition">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs text-gray-500 font-medium">💰 Omset Hari Ini</span>
                <i class="fas fa-dollar-sign text-blue-400"></i>
            </div>
            <p class="text-xl font-bold text-dark"><?= rupiah($omset_hariini['total']) ?></p>
            <p class="text-xs mt-1 <?= $omset_harian_change >= 0 ? 'text-green-500' : 'text-red-500' ?>">
                <?= $omset_harian_change >= 0 ? '↑' : '↓' ?> <?= abs($omset_harian_change) ?>% dari kemarin
            </p>
        </div>

        <!-- Keuntungan -->
        <div class="bg-white rounded-2xl shadow-sm p-4 border-l-4 border-green-500 hover:shadow-md transition">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs text-gray-500 font-medium">📈 Keuntungan</span>
                <i class="fas fa-chart-line text-green-400"></i>
            </div>
            <p class="text-xl font-bold text-green-600"><?= rupiah($untung_hariini['total']) ?></p>
            <p class="text-xs text-gray-400 mt-1">
                Margin: <?= $omset_hariini['total'] > 0 ? round(($untung_hariini['total'] / $omset_hariini['total']) * 100, 1) : 0 ?>%
            </p>
        </div>

        <!-- Transaksi -->
        <div class="bg-white rounded-2xl shadow-sm p-4 border-l-4 border-purple-500 hover:shadow-md transition">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs text-gray-500 font-medium">🧾 Transaksi</span>
                <i class="fas fa-receipt text-purple-400"></i>
            </div>
            <p class="text-xl font-bold"><?= $omset_hariini['transaksi'] ?> <span class="text-xs text-gray-400">kali</span></p>
            <p class="text-xs text-gray-400 mt-1">
                Rata2: <?= $omset_hariini['transaksi'] > 0 ? rupiah(round($omset_hariini['total'] / $omset_hariini['transaksi'])) : rupiah(0) ?>
            </p>
        </div>

        <!-- Pelanggan -->
        <div class="bg-white rounded-2xl shadow-sm p-4 border-l-4 border-orange-500 hover:shadow-md transition">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs text-gray-500 font-medium">👥 Pelanggan</span>
                <i class="fas fa-users text-orange-400"></i>
            </div>
            <p class="text-xl font-bold"><?= $pelanggan_hariini['total'] ?> <span class="text-xs text-gray-400">orang</span></p>
            <p class="text-xs text-gray-400 mt-1">Aktif hari ini</p>
        </div>

        <!-- Stok Menipis -->
        <div class="bg-white rounded-2xl shadow-sm p-4 border-l-4 <?= $stok_tipis > 0 ? 'border-red-500' : 'border-green-500' ?> hover:shadow-md transition">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs text-gray-500 font-medium">⚠️ Stok Menipis</span>
                <i class="fas fa-exclamation-triangle <?= $stok_tipis > 0 ? 'text-red-400' : 'text-green-400' ?>"></i>
            </div>
            <p class="text-xl font-bold <?= $stok_tipis > 0 ? 'text-red-600' : 'text-green-600' ?>">
                <?= $stok_tipis ?> <span class="text-xs text-gray-400">item</span>
            </p>
            <?php if($stok_tipis > 0): ?>
            <a href="?page=restok" class="text-xs text-primary hover:underline mt-1 inline-block">Restok sekarang →</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- ==================== ROW 2: PIUTANG + PERBANDINGAN ==================== -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        
        <!-- Piutang -->
        <div class="bg-white rounded-2xl shadow-sm p-5 border-l-4 border-red-500">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-bold text-dark">💳 Piutang</h3>
                <a href="?page=piutang" class="text-xs text-primary hover:underline">Lihat Semua →</a>
            </div>
            <p class="text-3xl font-bold text-red-600"><?= rupiah($piutang_info['total']) ?></p>
            <p class="text-sm text-gray-500 mt-1"><?= $piutang_info['jumlah'] ?> pelanggan belum lunas</p>
            <div class="mt-3 space-y-1 text-sm">
                <?php if($piutang_terbesar): ?>
                <p class="text-gray-500">
                    Terbesar: <strong><?= $piutang_terbesar['nama'] ?></strong> (<?= rupiah($piutang_terbesar['saldo_piutang']) ?>)
                </p>
                <?php endif; ?>
                <?php if($piutang_terlama): ?>
                <p class="text-gray-500">
                    Terlama: <strong><?= $piutang_terlama['nama'] ?></strong> (<?= $piutang_terlama['hari'] ?> hari)
                </p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Perbandingan Bulanan -->
        <div class="bg-white rounded-2xl shadow-sm p-5">
            <h3 class="font-bold text-dark mb-3">📊 Bulan Ini vs Bulan Lalu</h3>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Omset</span>
                    <span class="text-sm font-bold">
                        <?= rupiah($perbandingan['omset_ini']) ?>
                        <span class="<?= $omset_change >= 0 ? 'text-green-500' : 'text-red-500' ?>">
                            (<?= $omset_change >= 0 ? '↑' : '↓' ?> <?= abs($omset_change) ?>%)
                        </span>
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Transaksi</span>
                    <span class="text-sm font-bold">
                        <?= $perbandingan['trans_ini'] ?> kali
                        <span class="<?= $trans_change >= 0 ? 'text-green-500' : 'text-red-500' ?>">
                            (<?= $trans_change >= 0 ? '↑' : '↓' ?> <?= abs($trans_change) ?>%)
                        </span>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== ROW 3: JAM SIBUK + 7 HARI ==================== -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        
        <!-- Jam Sibuk -->
        <div class="bg-white rounded-2xl shadow-sm p-5">
            <h3 class="font-bold text-dark mb-3">⏰ Jam Sibuk Hari Ini</h3>
            <?php if($jam_max > 0): ?>
            <div class="space-y-1.5">
                <?php for($i = 6; $i <= 22; $i++): 
                    $trans = $jam_data[$i] ?? 0;
                    $width = $jam_max > 0 ? round(($trans / $jam_max) * 100) : 0;
                    $is_peak = $trans > 0 && $trans >= ($jam_max * 0.8);
                ?>
                <div class="flex items-center gap-2">
                    <span class="text-xs w-10 text-right <?= $is_peak ? 'font-bold text-red-500' : 'text-gray-400' ?>"><?= sprintf('%02d',$i) ?>:00</span>
                    <div class="flex-1 bg-gray-100 rounded-full h-4 overflow-hidden">
                        <div class="h-full rounded-full <?= $is_peak ? 'bg-red-400' : 'bg-blue-300' ?>" style="width: <?= max($width, $trans > 0 ? 5 : 0) ?>%"></div>
                    </div>
                    <span class="text-xs w-12 text-gray-400"><?= $trans > 0 ? $trans.' trans' : '' ?></span>
                </div>
                <?php endfor; ?>
            </div>
            <?php else: ?>
            <p class="text-center text-gray-400 py-6">Belum ada transaksi hari ini</p>
            <?php endif; ?>
        </div>

        <!-- 7 Hari Terakhir -->
        <div class="bg-white rounded-2xl shadow-sm p-5">
            <h3 class="font-bold text-dark mb-3">📅 7 Hari Terakhir</h3>
            <?php if($minggu_max > 0): ?>
            <div class="space-y-2">
                <?php 
                $hari_names = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
                for($i = 6; $i >= 0; $i--): 
                    $tgl = date('Y-m-d', strtotime("-$i days"));
                    $data = $minggu_data[$tgl] ?? ['total' => 0, 'omset' => 0];
                    $width = $minggu_max > 0 ? round(($data['total'] / $minggu_max) * 100) : 0;
                    $is_today = $tgl == $hari_ini;
                    $hari_idx = date('w', strtotime($tgl));
                ?>
                <div class="flex items-center gap-2">
                    <span class="text-xs w-14 <?= $is_today ? 'font-bold text-primary' : 'text-gray-500' ?>">
                        <?= $hari_names[$hari_idx] ?>
                    </span>
                    <div class="flex-1 bg-gray-100 rounded-full h-5 overflow-hidden">
                        <div class="h-full rounded-full flex items-center justify-end px-2 <?= $is_today ? 'bg-primary' : ($width > 60 ? 'bg-blue-400' : 'bg-blue-300') ?>" 
                             style="width: <?= max($width, $data['total'] > 0 ? 5 : 0) ?>%">
                            <span class="text-xs font-bold text-white <?= $width < 20 ? 'hidden' : '' ?>"><?= $data['total'] ?></span>
                        </div>
                    </div>
                    <span class="text-xs w-20 text-right text-gray-400"><?= $data['omset'] > 0 ? rupiah($data['omset']) : '' ?></span>
                </div>
                <?php endfor; ?>
            </div>
            <?php else: ?>
            <p class="text-center text-gray-400 py-6">Belum ada data</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- ==================== ROW 4: TOP PRODUK + TRANSAKSI TERBARU ==================== -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        
        <!-- Top 5 Produk -->
        <div class="bg-white rounded-2xl shadow-sm p-5">
            <h3 class="font-bold text-dark mb-3">🏆 Top 5 Produk Hari Ini</h3>
            <?php if(mysqli_num_rows($top_produk) > 0): ?>
            <div class="space-y-2">
                <?php $no = 1; while($tp = mysqli_fetch_assoc($top_produk)): ?>
                <div class="flex items-center gap-3">
                    <span class="w-6 h-6 rounded-full inline-flex items-center justify-center text-xs font-bold <?= $no <= 3 ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-500' ?>"><?= $no ?></span>
                    <span class="text-sm flex-1 truncate"><?= $tp['nama'] ?></span>
                    <span class="text-xs text-gray-400"><?= $tp['total_qty'] ?>x</span>
                    <span class="text-sm font-bold"><?= rupiah($tp['total_omset']) ?></span>
                </div>
                <?php $no++; endwhile; ?>
            </div>
            <?php else: ?>
            <p class="text-center text-gray-400 py-6">Belum ada transaksi</p>
            <?php endif; ?>
        </div>

        <!-- Transaksi Terbaru -->
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="p-5 border-b flex justify-between items-center">
                <h3 class="font-bold">🧾 Transaksi Terbaru</h3>
                <a href="?page=transaksi" class="text-xs text-primary hover:underline">Lihat Semua →</a>
            </div>
            <?php if(mysqli_num_rows($transaksi_terbaru) > 0): ?>
            <div class="divide-y">
                <?php while($t = mysqli_fetch_assoc($transaksi_terbaru)): ?>
                <div class="p-3 hover:bg-gray-50 flex items-center gap-3">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium truncate"><?= $t['no_invoice'] ?></p>
                        <p class="text-xs text-gray-400">
                            <?= date('H:i', strtotime($t['created_at'])) ?> - 
                            <?= $t['pelanggan'] ?? 'Umum' ?>
                        </p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="font-bold text-sm"><?= rupiah($t['total']) ?></p>
                        <span class="text-xs px-2 py-0.5 rounded-full <?= $t['status'] == 'lunas' ? 'bg-green-100 text-green-600' : ($t['status'] == 'piutang' ? 'bg-yellow-100 text-yellow-600' : 'bg-red-100 text-red-600') ?>">
                            <?= $t['status'] ?>
                        </span>
                    </div>
                    <div class="flex gap-1 flex-shrink-0">
                        <button onclick="lihatDetail(<?= $t['id'] ?>)" 
                                class="px-2 py-1 text-xs border rounded hover:bg-blue-50 hover:text-blue-600 transition" 
                                title="Detail">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button onclick="cetakStruk(<?= $t['id'] ?>)" 
                                class="px-2 py-1 text-xs border rounded hover:bg-green-50 hover:text-green-600 transition" 
                                title="Cetak">
                            <i class="fas fa-print"></i>
                        </button>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            <?php else: ?>
            <p class="text-center text-gray-400 py-8">Belum ada transaksi</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ==================== MODAL DETAIL TRANSAKSI ==================== -->
<div id="modal-detail" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-2xl max-h-[85vh] overflow-y-auto shadow-2xl">
        <div id="modal-detail-content">
            <div class="flex items-center justify-center py-20">
                <i class="fas fa-spinner fa-spin text-3xl text-primary"></i>
            </div>
        </div>
    </div>
</div>

<script>
// ==================== JAM REALTIME ====================
function updateJam() {
    const now = new Date();
    document.getElementById('jam-dashboard').textContent = 
        now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
}
setInterval(updateJam, 1000);
updateJam();

// ==================== DETAIL TRANSAKSI ====================
function lihatDetail(id) {
    const modal = document.getElementById('modal-detail');
    const content = document.getElementById('modal-detail-content');
    modal.classList.remove('hidden');
    content.innerHTML = '<div class="flex items-center justify-center py-20"><i class="fas fa-spinner fa-spin text-3xl text-primary"></i></div>';
    fetch('ajax/get_transaksi_detail.php?id=' + id)
        .then(res => res.text())
        .then(html => { content.innerHTML = html; });
}

function cetakStruk(id) {
    window.open('halaman/struk.php?id=' + id, '_blank', 'width=400,height=600');
}

document.getElementById('modal-detail').addEventListener('click', function(e) {
    if (e.target === this) this.classList.add('hidden');
});
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') document.getElementById('modal-detail').classList.add('hidden');
});
</script>