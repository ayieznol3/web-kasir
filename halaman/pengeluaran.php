<?php
$page = 'pengeluaran';

// ==================== FILTER & PAGINATION ====================
$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');
$currentPage = isset($_GET['page_num']) ? max(1, (int)$_GET['page_num']) : 1;
$perPage = 20;

// Build WHERE
$where = "WHERE MONTH(p.created_at) = '$bulan' AND YEAR(p.created_at) = '$tahun'";

// Hitung total
$totalQuery = mysqli_query($conn, "SELECT COUNT(*) as total FROM pengeluaran p $where");
$totalRows = mysqli_fetch_assoc($totalQuery)['total'];
$totalPages = ceil($totalRows / $perPage);
$start = ($currentPage - 1) * $perPage;

// Ambil data pengeluaran
$riwayat = mysqli_query($conn, "
    SELECT p.*, u.nama as nama_user
    FROM pengeluaran p
    JOIN users u ON p.user_id = u.id
    $where
    ORDER BY p.created_at DESC
    LIMIT $start, $perPage
");

// Statistik
$total_bulan = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COALESCE(SUM(jumlah),0) as total, COUNT(*) as jumlah
    FROM pengeluaran p $where
"));

// Pengeluaran per kategori bulan ini
$per_kategori = mysqli_query($conn, "
    SELECT kategori, COALESCE(SUM(jumlah),0) as total, COUNT(*) as jumlah
    FROM pengeluaran p $where
    GROUP BY kategori
    ORDER BY total DESC
");

// List bulan untuk filter
$bulan_nama = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
               'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

// Kategori yang tersedia
$kategori_list = ['Listrik', 'Air', 'Internet', 'Gaji', 'Transport', 'Kebersihan', 'Sewa', 'Lainnya'];
?>

<div class="space-y-6">
    
    <!-- ==================== HEADER ==================== -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-dark">
                <i class="fas fa-money-bill-wave text-purple-500 mr-2"></i>Pengeluaran
            </h1>
            <p class="text-sm text-gray-500 mt-1">Catat biaya operasional toko</p>
        </div>
        <button onclick="showModalTambah()" 
                class="inline-flex items-center justify-center gap-2 bg-purple-500 text-white px-5 py-2.5 rounded-xl font-semibold hover:bg-purple-600 transition shadow-lg shadow-purple-200">
            <i class="fas fa-plus"></i> Tambah Pengeluaran
        </button>
    </div>

    <!-- ==================== STAT CARDS ==================== -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl shadow-sm p-5 border-l-4 border-purple-500 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Pengeluaran</p>
                    <p class="text-2xl font-bold text-purple-600 mt-1"><?= rupiah($total_bulan['total']) ?></p>
                    <p class="text-xs text-gray-400 mt-1"><?= $bulan_nama[(int)$bulan] ?> <?= $tahun ?></p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-money-bill-wave text-purple-500 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm p-5 border-l-4 border-blue-500 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Jumlah Transaksi</p>
                    <p class="text-2xl font-bold mt-1"><?= $total_bulan['jumlah'] ?> <span class="text-sm font-normal text-gray-400">kali</span></p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-list text-blue-500 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm p-5 border-l-4 border-green-500 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Rata-rata</p>
                    <p class="text-2xl font-bold text-green-600 mt-1">
                        <?= $total_bulan['jumlah'] > 0 ? rupiah(round($total_bulan['total'] / $total_bulan['jumlah'])) : rupiah(0) ?>
                    </p>
                    <p class="text-xs text-gray-400 mt-1">per transaksi</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-calculator text-green-500 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- ==================== FILTER & RIWAYAT ==================== -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm overflow-hidden">
            
            <!-- Filter Bulan -->
            <div class="p-4 border-b bg-gray-50">
                <form method="get" class="flex flex-wrap gap-3 items-end">
                    <input type="hidden" name="page" value="pengeluaran">
                    
                    <div>
                        <label class="text-xs text-gray-500 font-medium block mb-1">Bulan</label>
                        <select name="bulan" class="px-4 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-purple-500 outline-none">
                            <?php for($i = 1; $i <= 12; $i++): ?>
                            <option value="<?= $i ?>" <?= $bulan == $i ? 'selected' : '' ?>>
                                <?= $bulan_nama[$i] ?>
                            </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="text-xs text-gray-500 font-medium block mb-1">Tahun</label>
                        <input type="number" name="tahun" value="<?= $tahun ?>" 
                               class="px-4 py-2 border border-gray-200 rounded-xl text-sm w-24 focus:ring-2 focus:ring-purple-500 outline-none">
                    </div>
                    
                    <button type="submit" class="bg-primary text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-indigo-700 transition">
                        <i class="fas fa-filter mr-1"></i> Filter
                    </button>
                    
                    <a href="?page=pengeluaran" class="px-3 py-2 text-sm text-gray-400 hover:text-gray-600 transition">
                        <i class="fas fa-times"></i> Reset
                    </a>
                </form>
            </div>
            
            <!-- Info Jumlah -->
            <div class="px-6 py-3 border-b flex items-center justify-between">
                <p class="text-sm text-gray-500">
                    Total <span class="font-medium"><?= $totalRows ?></span> pengeluaran
                </p>
                <p class="text-sm font-bold text-purple-600"><?= rupiah($total_bulan['total']) ?></p>
            </div>
            
            <!-- Tabel Riwayat -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Tanggal</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Kategori</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Keterangan</th>
                            <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Jumlah</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">User</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php 
                        $running_total = 0;
                        while($r = mysqli_fetch_assoc($riwayat)): 
                            $running_total += $r['jumlah'];
                        ?>
                        <tr class="hover:bg-gray-50 transition text-sm">
                            <!-- Tanggal -->
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="font-medium"><?= date('d/m/Y', strtotime($r['created_at'])) ?></div>
                                <div class="text-xs text-gray-400"><?= date('H:i', strtotime($r['created_at'])) ?></div>
                            </td>
                            
                            <!-- Kategori -->
                            <td class="px-4 py-3">
                                <?php 
                                $icon = '📋';
                                $color = 'bg-gray-100 text-gray-700';
                                switch($r['kategori']) {
                                    case 'Listrik': $icon = '⚡'; $color = 'bg-yellow-100 text-yellow-700'; break;
                                    case 'Air': $icon = '💧'; $color = 'bg-blue-100 text-blue-700'; break;
                                    case 'Internet': $icon = '🌐'; $color = 'bg-indigo-100 text-indigo-700'; break;
                                    case 'Gaji': $icon = '💼'; $color = 'bg-green-100 text-green-700'; break;
                                    case 'Transport': $icon = '🚗'; $color = 'bg-orange-100 text-orange-700'; break;
                                    case 'Kebersihan': $icon = '🧹'; $color = 'bg-teal-100 text-teal-700'; break;
                                    case 'Sewa': $icon = '🏠'; $color = 'bg-red-100 text-red-700'; break;
                                }
                                ?>
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs rounded-full font-medium <?= $color ?>">
                                    <?= $icon ?> <?= $r['kategori'] ?>
                                </span>
                            </td>
                            
                            <!-- Keterangan -->
                            <td class="px-4 py-3">
                                <p class="text-gray-600 max-w-xs truncate" title="<?= htmlspecialchars($r['keterangan']) ?>">
                                    <?= htmlspecialchars($r['keterangan']) ?>
                                </p>
                            </td>
                            
                            <!-- Jumlah -->
                            <td class="px-4 py-3 text-right">
                                <span class="font-bold"><?= rupiah($r['jumlah']) ?></span>
                            </td>
                            
                            <!-- User -->
                            <td class="px-4 py-3">
                                <span class="text-xs text-gray-400"><?= $r['nama_user'] ?></span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        
                        <?php if(mysqli_num_rows($riwayat) == 0): ?>
                        <tr>
                            <td colspan="5">
                                <div class="text-center py-12 text-gray-400">
                                    <i class="fas fa-receipt text-3xl mb-2"></i>
                                    <p>Belum ada pengeluaran</p>
                                    <p class="text-sm mt-1">di bulan <?= $bulan_nama[(int)$bulan] ?> <?= $tahun ?></p>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                    
                    <?php if(mysqli_num_rows($riwayat) > 0): ?>
                    <tfoot class="bg-gray-50 border-t font-bold">
                        <tr>
                            <td class="px-4 py-3" colspan="3">TOTAL</td>
                            <td class="px-4 py-3 text-right text-purple-600"><?= rupiah($total_bulan['total']) ?></td>
                            <td></td>
                        </tr>
                    </tfoot>
                    <?php endif; ?>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if($totalPages > 1): ?>
            <div class="p-4 border-t flex items-center justify-between">
                <span class="text-xs text-gray-400">Hal <?= $currentPage ?> dari <?= $totalPages ?></span>
                <div class="flex gap-1">
                    <?php if($currentPage > 1): 
                        $prevUrl = "?page=pengeluaran&page_num=".($currentPage-1)."&bulan=$bulan&tahun=$tahun";
                    ?>
                    <a href="<?= $prevUrl ?>" class="px-3 py-1.5 border rounded-lg text-xs hover:bg-gray-100">&laquo; Prev</a>
                    <?php endif; ?>
                    
                    <?php
                    $startPage = max(1, $currentPage - 2);
                    $endPage = min($totalPages, $currentPage + 2);
                    for($i = $startPage; $i <= $endPage; $i++):
                        $pageUrl = "?page=pengeluaran&page_num=$i&bulan=$bulan&tahun=$tahun";
                        $active = $i == $currentPage ? 'bg-primary text-white border-primary' : 'hover:bg-gray-100';
                    ?>
                    <a href="<?= $pageUrl ?>" class="px-3 py-1.5 border rounded-lg text-xs <?= $active ?>"><?= $i ?></a>
                    <?php endfor; ?>
                    
                    <?php if($currentPage < $totalPages): 
                        $nextUrl = "?page=pengeluaran&page_num=".($currentPage+1)."&bulan=$bulan&tahun=$tahun";
                    ?>
                    <a href="<?= $nextUrl ?>" class="px-3 py-1.5 border rounded-lg text-xs hover:bg-gray-100">Next &raquo;</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- ==================== SIDEBAR: PER KATEGORI ==================== -->
        <div class="space-y-4">
            
            <!-- Chart Per Kategori -->
            <div class="bg-white rounded-2xl shadow-sm p-6">
                <h4 class="font-semibold text-dark mb-4">
                    <i class="fas fa-chart-pie text-purple-500 mr-2"></i>Per Kategori
                </h4>
                
                <?php if(mysqli_num_rows($per_kategori) > 0): ?>
                <div class="space-y-3">
                    <?php 
                    mysqli_data_seek($per_kategori, 0);
                    while($k = mysqli_fetch_assoc($per_kategori)): 
                        $persen = $total_bulan['total'] > 0 ? round(($k['total'] / $total_bulan['total']) * 100) : 0;
                        
                        $bar_color = 'bg-purple-500';
                        switch($k['kategori']) {
                            case 'Listrik': $bar_color = 'bg-yellow-500'; break;
                            case 'Air': $bar_color = 'bg-blue-500'; break;
                            case 'Internet': $bar_color = 'bg-indigo-500'; break;
                            case 'Gaji': $bar_color = 'bg-green-500'; break;
                            case 'Transport': $bar_color = 'bg-orange-500'; break;
                            case 'Kebersihan': $bar_color = 'bg-teal-500'; break;
                            case 'Sewa': $bar_color = 'bg-red-500'; break;
                        }
                    ?>
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="font-medium text-gray-600"><?= $k['kategori'] ?></span>
                            <span class="text-gray-500"><?= rupiah($k['total']) ?> (<?= $persen ?>%)</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="h-2 rounded-full <?= $bar_color ?> transition-all" style="width: <?= $persen ?>%"></div>
                        </div>
                        <p class="text-xs text-gray-400 mt-1"><?= $k['jumlah'] ?> transaksi</p>
                    </div>
                    <?php endwhile; ?>
                </div>
                <?php else: ?>
                <p class="text-center text-gray-400 text-sm py-4">Tidak ada data</p>
                <?php endif; ?>
            </div>
            
            <!-- Quick Info -->
            <div class="bg-white rounded-2xl shadow-sm p-6">
                <h4 class="font-semibold text-dark mb-3">
                    <i class="fas fa-info-circle text-blue-500 mr-2"></i>Info
                </h4>
                <div class="text-sm text-gray-500 space-y-2">
                    <p>📅 Periode: <strong><?= $bulan_nama[(int)$bulan] ?> <?= $tahun ?></strong></p>
                    <p>📊 Total: <strong><?= rupiah($total_bulan['total']) ?></strong></p>
                    <p>📝 Transaksi: <strong><?= $total_bulan['jumlah'] ?> kali</strong></p>
                    <p>💡 Rata-rata: <strong><?= $total_bulan['jumlah'] > 0 ? rupiah(round($total_bulan['total'] / $total_bulan['jumlah'])) : rupiah(0) ?></strong></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ==================== MODAL TAMBAH PENGELUARAN ==================== -->
<div id="modal-pengeluaran" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl transform transition-all">
        
        <!-- Modal Header -->
        <div class="p-6 border-b flex items-center justify-between">
            <h3 class="text-lg font-bold text-dark">
                <i class="fas fa-plus-circle text-purple-500 mr-2"></i>Tambah Pengeluaran
            </h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 text-xl leading-none transition">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <!-- Form -->
        <form action="proses/pengeluaran_simpan.php" method="post" class="p-6 space-y-4">
            
            <!-- Kategori -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    Kategori <span class="text-red-500">*</span>
                </label>
                <select name="kategori" required 
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none transition">
                    <option value="">-- Pilih Kategori --</option>
                    <?php foreach($kategori_list as $kat): ?>
                    <option value="<?= $kat ?>"><?= $kat ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Jumlah -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    Jumlah <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <span class="absolute left-4 top-2.5 text-gray-400 font-medium">Rp</span>
                    <input type="number" name="jumlah" required 
                           class="w-full pl-12 pr-4 py-2.5 border border-gray-200 rounded-xl text-lg font-bold focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none transition"
                           placeholder="0"
                           id="input-jumlah">
                </div>
            </div>
            
            <!-- Keterangan -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Keterangan</label>
                <textarea name="keterangan" rows="2" 
                          class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none transition resize-none"
                          placeholder="Deskripsi pengeluaran..."></textarea>
            </div>
            
            <!-- Info -->
            <div class="bg-purple-50 border border-purple-200 rounded-xl p-3 text-sm text-purple-700">
                <i class="fas fa-info-circle mr-1"></i>
                Pengeluaran ini akan tercatat di laporan arus kas dan laba rugi.
            </div>
            
            <!-- Tombol -->
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeModal()" 
                        class="flex-1 py-2.5 border border-gray-200 rounded-xl font-medium text-gray-600 hover:bg-gray-50 transition">
                    Batal
                </button>
                <button type="submit" class="flex-1 py-2.5 bg-purple-500 text-white rounded-xl font-semibold hover:bg-purple-600 transition">
                    <i class="fas fa-save mr-1"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// ==================== MODAL ====================
function showModalTambah() {
    document.getElementById('modal-pengeluaran').classList.remove('hidden');
    document.getElementById('input-jumlah').focus();
}

function closeModal() {
    document.getElementById('modal-pengeluaran').classList.add('hidden');
}

// Tutup modal dengan klik di luar
document.getElementById('modal-pengeluaran').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});

// Tutup dengan Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeModal();
});
</script>