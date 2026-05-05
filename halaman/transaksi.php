<?php
$page = 'transaksi';

// ==================== FILTER ====================
$tgl_mulai = $_GET['tgl_mulai'] ?? date('Y-m-d');
$tgl_akhir = $_GET['tgl_akhir'] ?? date('Y-m-d');
$cari = $_GET['cari'] ?? '';
$status_filter = $_GET['status'] ?? 'semua';
$currentPage = isset($_GET['page_num']) ? max(1, (int)$_GET['page_num']) : 1;
$perPage = 20;

// Build WHERE clause
$where = "WHERE DATE(t.created_at) BETWEEN '$tgl_mulai' AND '$tgl_akhir'";

if ($cari) {
    $cari_esc = mysqli_real_escape_string($conn, $cari);
    $where .= " AND (t.no_invoice LIKE '%$cari_esc%' OR p.nama LIKE '%$cari_esc%')";
}

if ($status_filter != 'semua') {
    $where .= " AND t.status = '$status_filter'";
}

// Hitung total
$totalQuery = mysqli_query($conn, "
    SELECT COUNT(*) as total, COALESCE(SUM(t.total),0) as total_nilai 
    FROM transaksi t 
    LEFT JOIN pelanggan p ON t.pelanggan_id = p.id 
    $where
");
$totalData = mysqli_fetch_assoc($totalQuery);
$totalRows = $totalData['total'];
$totalNilai = $totalData['total_nilai'];
$totalPages = ceil($totalRows / $perPage);
$start = ($currentPage - 1) * $perPage;

// Ambil data transaksi
$transaksi_list = mysqli_query($conn, "
    SELECT t.*, u.nama as kasir, p.nama as pelanggan_nama
    FROM transaksi t
    JOIN users u ON t.user_id = u.id
    LEFT JOIN pelanggan p ON t.pelanggan_id = p.id
    $where
    ORDER BY t.created_at DESC
    LIMIT $start, $perPage
");
?>

<div class="space-y-6">
    
    <!-- ==================== HEADER ==================== -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-dark">
                <i class="fas fa-history text-primary mr-2"></i>Riwayat Transaksi
            </h1>
            <p class="text-sm text-gray-500 mt-1">Lihat, cari, dan cetak ulang transaksi</p>
        </div>
        
        <!-- Quick Actions -->
        <div class="flex gap-2">
            <a href="?page=kasir" 
               class="inline-flex items-center gap-2 bg-green-500 text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-green-600 transition">
                <i class="fas fa-cash-register"></i> Transaksi Baru
            </a>
        </div>
    </div>

    <!-- ==================== FILTER CARD ==================== -->
    <div class="bg-white rounded-2xl shadow-sm p-5">
        <form method="get" class="space-y-4">
            <input type="hidden" name="page" value="transaksi">
            
            <!-- Row 1: Filter Utama -->
            <div class="flex flex-wrap gap-3 items-end">
                
                <!-- Tanggal Mulai -->
                <div>
                    <label class="text-xs text-gray-500 font-medium block mb-1">Dari Tanggal</label>
                    <input type="date" name="tgl_mulai" value="<?= $tgl_mulai ?>" 
                           class="px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition">
                </div>
                
                <!-- Tanggal Akhir -->
                <div>
                    <label class="text-xs text-gray-500 font-medium block mb-1">Sampai Tanggal</label>
                    <input type="date" name="tgl_akhir" value="<?= $tgl_akhir ?>" 
                           class="px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition">
                </div>
                
                <!-- Search -->
                <div class="flex-1 min-w-[200px]">
                    <label class="text-xs text-gray-500 font-medium block mb-1">Cari</label>
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400 text-sm"></i>
                        <input type="text" name="cari" value="<?= htmlspecialchars($cari) ?>" 
                               placeholder="No invoice atau nama pelanggan..."
                               class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition">
                    </div>
                </div>
                
                <!-- Status -->
                <div>
                    <label class="text-xs text-gray-500 font-medium block mb-1">Status</label>
                    <select name="status" class="px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none">
    <option value="semua" <?= $status_filter == 'semua' ? 'selected' : '' ?>>Semua Status</option>
    <option value="lunas" <?= $status_filter == 'lunas' ? 'selected' : '' ?>>✅ Lunas</option>
    <option value="piutang" <?= $status_filter == 'piutang' ? 'selected' : '' ?>>⚠️ Piutang</option>
    <option value="void" <?= $status_filter == 'void' ? 'selected' : '' ?>>🚫 Void</option>
</select>
                </div>
                
                <!-- Tombol -->
                <div class="flex gap-2">
                    <button type="submit" class="bg-primary text-white px-5 py-2.5 rounded-xl text-sm font-medium hover:bg-indigo-700 transition">
                        <i class="fas fa-filter mr-1"></i> Filter
                    </button>
                    <a href="?page=transaksi" class="px-4 py-2.5 border border-gray-200 rounded-xl text-sm text-gray-500 hover:bg-gray-50 transition">
                        <i class="fas fa-times mr-1"></i> Reset
                    </a>
                </div>
            </div>
            
            <!-- Row 2: Quick Filter -->
            <div class="flex gap-2 flex-wrap items-center pt-2 border-t border-gray-100">
                <span class="text-xs text-gray-400 mr-2">Cepat:</span>
                
                <a href="?page=transaksi&tgl_mulai=<?= date('Y-m-d') ?>&tgl_akhir=<?= date('Y-m-d') ?>" 
                   class="text-xs px-3 py-1.5 border border-gray-200 rounded-full hover:bg-primary hover:text-white hover:border-primary transition <?= ($tgl_mulai == date('Y-m-d') && $tgl_akhir == date('Y-m-d')) ? 'bg-primary text-white border-primary' : '' ?>">
                    Hari Ini
                </a>
                
                <a href="?page=transaksi&tgl_mulai=<?= date('Y-m-d', strtotime('-1 day')) ?>&tgl_akhir=<?= date('Y-m-d', strtotime('-1 day')) ?>" 
                   class="text-xs px-3 py-1.5 border border-gray-200 rounded-full hover:bg-primary hover:text-white hover:border-primary transition">
                    Kemarin
                </a>
                
                <a href="?page=transaksi&tgl_mulai=<?= date('Y-m-01') ?>&tgl_akhir=<?= date('Y-m-d') ?>" 
                   class="text-xs px-3 py-1.5 border border-gray-200 rounded-full hover:bg-primary hover:text-white hover:border-primary transition">
                    Bulan Ini
                </a>
                
                <a href="?page=transaksi&tgl_mulai=<?= date('Y-m-01', strtotime('-1 month')) ?>&tgl_akhir=<?= date('Y-m-t', strtotime('-1 month')) ?>" 
                   class="text-xs px-3 py-1.5 border border-gray-200 rounded-full hover:bg-primary hover:text-white hover:border-primary transition">
                    Bulan Lalu
                </a>
            </div>
        </form>
    </div>

    <!-- ==================== RINGKASAN ==================== -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl shadow-sm p-4 border-l-4 border-blue-500">
            <p class="text-xs text-gray-500 font-medium">Total Transaksi</p>
            <p class="text-2xl font-bold mt-1"><?= $totalRows ?></p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm p-4 border-l-4 border-green-500">
            <p class="text-xs text-gray-500 font-medium">Total Nilai</p>
            <p class="text-xl font-bold text-green-600 mt-1"><?= rupiah($totalNilai) ?></p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm p-4 border-l-4 border-purple-500">
            <p class="text-xs text-gray-500 font-medium">Rata-rata</p>
            <p class="text-xl font-bold text-purple-600 mt-1">
                <?= $totalRows > 0 ? rupiah(round($totalNilai / $totalRows)) : rupiah(0) ?>
            </p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm p-4 border-l-4 border-orange-500">
            <p class="text-xs text-gray-500 font-medium">Periode</p>
            <p class="text-sm font-bold text-orange-600 mt-1">
                <?= date('d/m/Y', strtotime($tgl_mulai)) ?> - <?= date('d/m/Y', strtotime($tgl_akhir)) ?>
            </p>
        </div>
    </div>

    <!-- ==================== INFO JUMLAH ==================== -->
    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">
            <?php if($totalRows > 0): ?>
            Menampilkan <span class="font-medium"><?= $start + 1 ?>-<?= min($totalRows, $start + $perPage) ?></span> 
            dari <span class="font-medium"><?= $totalRows ?></span> transaksi
            <?php else: ?>
            Tidak ada transaksi ditemukan
            <?php endif; ?>
        </p>
        
        <?php if($cari): ?>
        <span class="text-xs bg-blue-50 text-blue-600 px-3 py-1 rounded-full">
            Pencarian: "<?= htmlspecialchars($cari) ?>"
        </span>
        <?php endif; ?>
    </div>

    <!-- ==================== TABEL TRANSAKSI ==================== -->
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="text-left px-4 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Invoice</th>
                        <th class="text-left px-4 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="text-left px-4 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Pelanggan</th>
                        <th class="text-left px-4 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Kasir</th>
                        <th class="text-right px-4 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Total</th>
                        <th class="text-right px-4 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Bayar</th>
                        <th class="text-center px-4 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="text-center px-4 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php while($t = mysqli_fetch_assoc($transaksi_list)): ?>
                    <tr class="hover:bg-gray-50 transition group">
                        
                        <!-- Invoice -->
                        <td class="px-4 py-3">
                            <span class="font-mono text-sm font-medium text-dark"><?= $t['no_invoice'] ?></span>
                        </td>
                        
                        <!-- Tanggal -->
                        <td class="px-4 py-3">
                            <div class="text-sm text-gray-700"><?= date('d/m/Y', strtotime($t['created_at'])) ?></div>
                            <div class="text-xs text-gray-400"><?= date('H:i', strtotime($t['created_at'])) ?></div>
                        </td>
                        
                        <!-- Pelanggan -->
                        <td class="px-4 py-3">
                            <?php if($t['pelanggan_nama']): ?>
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <span class="text-xs font-bold text-blue-600">
                                        <?= strtoupper(substr($t['pelanggan_nama'], 0, 1)) ?>
                                    </span>
                                </div>
                                <span class="text-sm font-medium"><?= $t['pelanggan_nama'] ?></span>
                            </div>
                            <?php else: ?>
                            <span class="text-sm text-gray-400">Umum</span>
                            <?php endif; ?>
                        </td>
                        
                        <!-- Kasir -->
                        <td class="px-4 py-3 text-sm text-gray-500"><?= $t['kasir'] ?></td>
                        
                        <!-- Total -->
                        <td class="px-4 py-3 text-right">
                            <span class="font-bold text-sm"><?= rupiah($t['total']) ?></span>
                        </td>
                        
                        <!-- Bayar -->
                        <td class="px-4 py-3 text-right">
                            <div class="text-sm"><?= rupiah($t['bayar']) ?></div>
                            <?php if($t['kembalian'] > 0): ?>
                            <div class="text-xs text-green-600">Kembali: <?= rupiah($t['kembalian']) ?></div>
                            <?php endif; ?>
                            <?php if($t['kurang'] > 0): ?>
                            <div class="text-xs text-red-600">Kurang: <?= rupiah($t['kurang']) ?></div>
                            <?php endif; ?>
                        </td>
                        
                        <!-- Status -->
<td class="px-4 py-3 text-center">
    <?php if($t['status'] == 'lunas'): ?>
    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-700">✅ Lunas</span>
    <?php elseif($t['status'] == 'piutang'): ?>
    <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-700">⚠️ Piutang</span>
    <?php else: ?>
    <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-700">🚫 Void</span>
    <?php endif; ?>
</td>
                        
                        <!-- Aksi -->
<td class="px-4 py-3">
    <div class="flex justify-center gap-1">
        <button onclick="lihatDetail(<?= $t['id'] ?>)" 
                class="px-2.5 py-1.5 text-xs border border-gray-200 rounded-lg hover:bg-blue-50 hover:text-blue-600 transition"
                title="Lihat Detail">
            <i class="fas fa-eye"></i>
        </button>
        <button onclick="cetakStruk(<?= $t['id'] ?>)" 
                class="px-2.5 py-1.5 text-xs border border-gray-200 rounded-lg hover:bg-green-50 hover:text-green-600 transition"
                title="Cetak Struk">
            <i class="fas fa-print"></i>
        </button>
        
        <?php if($t['status'] != 'void'): ?>
        <!-- Tombol Void -->
        <button onclick="voidTransaksi(<?= $t['id'] ?>, '<?= $t['no_invoice'] ?>', <?= $t['total'] ?>)" 
                class="px-2.5 py-1.5 text-xs border border-red-200 text-red-500 rounded-lg hover:bg-red-500 hover:text-white transition"
                title="Void / Batalkan">
            <i class="fas fa-ban"></i>
        </button>
        <?php else: ?>
        <span class="px-2.5 py-1.5 text-xs text-gray-400">-</span>
        <?php endif; ?>
    </div>
</td>
                    </tr>
                    <?php endwhile; ?>
                    
                    <!-- Empty State -->
                    <?php if(mysqli_num_rows($transaksi_list) == 0): ?>
                    <tr>
                        <td colspan="8">
                            <div class="text-center py-16">
                                <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-receipt text-3xl text-gray-400"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-500 mb-2">Tidak Ada Transaksi</h3>
                                <p class="text-gray-400 text-sm mb-6">
                                    <?= $cari || $status_filter != 'semua' ? 'Tidak ada transaksi dengan filter tersebut' : 'Belum ada transaksi pada periode ini' ?>
                                </p>
                                <div class="flex gap-3 justify-center">
                                    <?php if($cari || $status_filter != 'semua'): ?>
                                    <a href="?page=transaksi" class="px-6 py-2 border border-gray-200 rounded-xl text-sm hover:bg-gray-50 transition">
                                        <i class="fas fa-times mr-1"></i> Reset Filter
                                    </a>
                                    <?php endif; ?>
                                    <a href="?page=kasir" class="px-6 py-2 bg-primary text-white rounded-xl text-sm font-medium hover:bg-indigo-700 transition">
                                        <i class="fas fa-cash-register mr-1"></i> Buat Transaksi
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ==================== PAGINATION ==================== -->
    <?php if($totalPages > 1): ?>
    <div class="flex items-center justify-between pt-4 border-t border-gray-200">
        <p class="text-sm text-gray-500">
            Halaman <span class="font-medium"><?= $currentPage ?></span> dari <span class="font-medium"><?= $totalPages ?></span>
        </p>
        
        <div class="flex gap-1">
            <!-- Previous -->
            <?php if($currentPage > 1): 
                $prevUrl = "?page=transaksi&page_num=" . ($currentPage - 1);
                if($tgl_mulai) $prevUrl .= "&tgl_mulai=" . urlencode($tgl_mulai);
                if($tgl_akhir) $prevUrl .= "&tgl_akhir=" . urlencode($tgl_akhir);
                if($cari) $prevUrl .= "&cari=" . urlencode($cari);
                if($status_filter != 'semua') $prevUrl .= "&status=" . $status_filter;
            ?>
            <a href="<?= $prevUrl ?>" class="px-3 py-2 border border-gray-200 rounded-lg text-sm hover:bg-gray-100 transition">
                <i class="fas fa-chevron-left text-xs"></i>
            </a>
            <?php else: ?>
            <span class="px-3 py-2 border border-gray-100 rounded-lg text-sm text-gray-300 cursor-not-allowed">
                <i class="fas fa-chevron-left text-xs"></i>
            </span>
            <?php endif; ?>
            
            <!-- Page Numbers -->
            <?php
            $startPage = max(1, $currentPage - 2);
            $endPage = min($totalPages, $currentPage + 2);
            
            if($startPage > 1): 
                $firstUrl = "?page=transaksi&page_num=1";
                if($tgl_mulai) $firstUrl .= "&tgl_mulai=" . urlencode($tgl_mulai);
                if($tgl_akhir) $firstUrl .= "&tgl_akhir=" . urlencode($tgl_akhir);
                if($cari) $firstUrl .= "&cari=" . urlencode($cari);
                if($status_filter != 'semua') $firstUrl .= "&status=" . $status_filter;
            ?>
            <a href="<?= $firstUrl ?>" class="px-3 py-2 border border-gray-200 rounded-lg text-sm hover:bg-gray-100 transition">1</a>
            <?php if($startPage > 2): ?>
            <span class="px-2 py-2 text-gray-400 text-sm">...</span>
            <?php endif; ?>
            <?php endif; ?>
            
            <?php for($i = $startPage; $i <= $endPage; $i++): 
                $pageUrl = "?page=transaksi&page_num=$i";
                if($tgl_mulai) $pageUrl .= "&tgl_mulai=" . urlencode($tgl_mulai);
                if($tgl_akhir) $pageUrl .= "&tgl_akhir=" . urlencode($tgl_akhir);
                if($cari) $pageUrl .= "&cari=" . urlencode($cari);
                if($status_filter != 'semua') $pageUrl .= "&status=" . $status_filter;
                
                $activeClass = $i == $currentPage ? 'bg-primary text-white border-primary shadow-lg shadow-indigo-200' : 'border-gray-200 hover:bg-gray-100';
            ?>
            <a href="<?= $pageUrl ?>" class="px-3 py-2 border rounded-lg text-sm <?= $activeClass ?> transition">
                <?= $i ?>
            </a>
            <?php endfor; ?>
            
            <?php if($endPage < $totalPages): 
                if($endPage < $totalPages - 1): ?>
                <span class="px-2 py-2 text-gray-400 text-sm">...</span>
                <?php endif;
                $lastUrl = "?page=transaksi&page_num=$totalPages";
                if($tgl_mulai) $lastUrl .= "&tgl_mulai=" . urlencode($tgl_mulai);
                if($tgl_akhir) $lastUrl .= "&tgl_akhir=" . urlencode($tgl_akhir);
                if($cari) $lastUrl .= "&cari=" . urlencode($cari);
                if($status_filter != 'semua') $lastUrl .= "&status=" . $status_filter;
            ?>
            <a href="<?= $lastUrl ?>" class="px-3 py-2 border border-gray-200 rounded-lg text-sm hover:bg-gray-100 transition"><?= $totalPages ?></a>
            <?php endif; ?>
            
            <!-- Next -->
            <?php if($currentPage < $totalPages): 
                $nextUrl = "?page=transaksi&page_num=" . ($currentPage + 1);
                if($tgl_mulai) $nextUrl .= "&tgl_mulai=" . urlencode($tgl_mulai);
                if($tgl_akhir) $nextUrl .= "&tgl_akhir=" . urlencode($tgl_akhir);
                if($cari) $nextUrl .= "&cari=" . urlencode($cari);
                if($status_filter != 'semua') $nextUrl .= "&status=" . $status_filter;
            ?>
            <a href="<?= $nextUrl ?>" class="px-3 py-2 border border-gray-200 rounded-lg text-sm hover:bg-gray-100 transition">
                <i class="fas fa-chevron-right text-xs"></i>
            </a>
            <?php else: ?>
            <span class="px-3 py-2 border border-gray-100 rounded-lg text-sm text-gray-300 cursor-not-allowed">
                <i class="fas fa-chevron-right text-xs"></i>
            </span>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

</div>

<!-- ==================== MODAL DETAIL TRANSAKSI ==================== -->
<div id="modal-detail" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-2xl max-h-[85vh] overflow-y-auto shadow-2xl transform transition-all">
        <div id="modal-detail-content">
            <!-- Konten akan diisi oleh AJAX -->
            <div class="flex items-center justify-center py-20">
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin text-3xl text-primary mb-3"></i>
                    <p class="text-gray-400">Memuat detail transaksi...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ==================== MODAL VOID ==================== -->
<div id="modal-void" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl">
        <div class="p-6">
            <div class="text-center mb-4">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-ban text-red-500 text-2xl"></i>
                </div>
                <h3 class="text-lg font-bold">Void Transaksi?</h3>
                <p class="text-sm text-gray-500 mt-1" id="void-info"></p>
            </div>
            
            <div class="bg-red-50 border border-red-200 rounded-xl p-3 mb-4">
                <p class="text-xs text-red-600">
                    ⚠️ <strong>Perhatian:</strong><br>
                    • Stok akan kembali otomatis<br>
                    • Piutang akan dibatalkan (jika ada)<br>
                    • Tidak bisa dibatalkan lagi
                </p>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-semibold mb-1">Alasan Void *</label>
                <input type="text" id="void-alasan" required 
                       class="w-full px-4 py-2 border rounded-xl text-sm focus:ring-2 focus:ring-red-500 outline-none"
                       placeholder="Alasan pembatalan...">
            </div>
            
            <input type="hidden" id="void-transaksi-id">
            
            <div class="flex gap-3">
                <button onclick="closeModalVoid()" class="flex-1 py-2.5 border rounded-xl font-medium hover:bg-gray-50">
                    Batal
                </button>
                <button onclick="konfirmasiVoid()" class="flex-1 py-2.5 bg-red-500 text-white rounded-xl font-semibold hover:bg-red-600">
                    <i class="fas fa-ban mr-1"></i> Void Transaksi
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// ==================== LIHAT DETAIL ====================
function lihatDetail(transaksi_id) {
    // Tampilkan modal
    const modal = document.getElementById('modal-detail');
    const content = document.getElementById('modal-detail-content');
    
    modal.classList.remove('hidden');
    content.innerHTML = `
        <div class="flex items-center justify-center py-20">
            <div class="text-center">
                <i class="fas fa-spinner fa-spin text-3xl text-primary mb-3"></i>
                <p class="text-gray-400">Memuat detail transaksi...</p>
            </div>
        </div>
    `;
    
    // Fetch detail
    fetch('ajax/get_transaksi_detail.php?id=' + transaksi_id)
        .then(response => response.text())
        .then(html => {
            content.innerHTML = html;
        })
        .catch(error => {
            content.innerHTML = `
                <div class="text-center py-20 text-red-500">
                    <i class="fas fa-exclamation-circle text-4xl mb-3"></i>
                    <p>Gagal memuat detail</p>
                </div>
            `;
        });
}

// ==================== CETAK STRUK ====================
function cetakStruk(transaksi_id) {
    // Buka di tab baru
    const url = 'halaman/struk.php?id=' + transaksi_id;
    window.open(url, '_blank', 'width=400,height=600');
}

// ==================== TUTUP MODAL ====================
// Klik di luar modal
document.getElementById('modal-detail').addEventListener('click', function(e) {
    if (e.target === this) {
        this.classList.add('hidden');
    }
});

// Tombol Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.getElementById('modal-detail').classList.add('hidden');
    }
});


// ==================== VOID TRANSAKSI ====================
function voidTransaksi(id, invoice, total) {
    document.getElementById('void-info').textContent = 'Invoice: ' + invoice + ' | Total: Rp ' + total.toLocaleString();
    document.getElementById('void-transaksi-id').value = id;
    document.getElementById('void-alasan').value = '';
    document.getElementById('modal-void').classList.remove('hidden');
    document.getElementById('void-alasan').focus();
}

function closeModalVoid() {
    document.getElementById('modal-void').classList.add('hidden');
}

function konfirmasiVoid() {
    const id = document.getElementById('void-transaksi-id').value;
    const alasan = document.getElementById('void-alasan').value.trim();
    
    if (!alasan) {
        alert('Alasan void wajib diisi!');
        document.getElementById('void-alasan').focus();
        return;
    }
    
    if (!confirm('Yakin void transaksi ini? Stok akan kembali otomatis.')) return;
    
    window.location.href = 'proses/transaksi_void.php?id=' + id + '&alasan=' + encodeURIComponent(alasan);
}

// Tutup modal void
document.getElementById('modal-void').addEventListener('click', function(e) {
    if (e.target === this) closeModalVoid();
});

// Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeModalVoid();
});
</script>