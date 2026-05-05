<?php
$page = 'produk';

// ==================== SEARCH & FILTER ====================
$search = $_GET['search'] ?? '';
$filter_stok = $_GET['stok'] ?? 'semua';
$currentPage = isset($_GET['page_num']) ? max(1, (int)$_GET['page_num']) : 1;
$perPage = 12;

// Build WHERE clause
$where = "WHERE 1=1";
if ($search) {
    $search_esc = mysqli_real_escape_string($conn, $search);
    $where .= " AND (p.nama LIKE '%$search_esc%' OR p.kode LIKE '%$search_esc%')";
}
if ($filter_stok == 'tipis') {
    $where .= " AND p.stok_dasar <= 10 AND p.stok_dasar >= 0";
} elseif ($filter_stok == 'habis') {
    $where .= " AND p.stok_dasar = 0";
} elseif ($filter_stok == 'tersedia') {
    $where .= " AND p.stok_dasar > 10 AND p.stok_dasar >= 0";
}

// Hitung total data
$totalQuery = mysqli_query($conn, "SELECT COUNT(*) as total FROM produk p $where");
$totalRows = mysqli_fetch_assoc($totalQuery)['total'];
$totalPages = ceil($totalRows / $perPage);
$start = ($currentPage - 1) * $perPage;

// Ambil data produk dengan LIMIT
$produk_list = mysqli_query($conn, "
    SELECT p.*, 
           (SELECT COUNT(*) FROM satuan WHERE produk_id = p.id) as jml_satuan,
           (SELECT COUNT(*) FROM grosir WHERE produk_id = p.id) as jml_grosir
    FROM produk p 
    $where
    ORDER BY p.nama ASC
    LIMIT $start, $perPage
");

// Hitung untuk statistik
$total_produk = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM produk"))['total'];
$total_stok = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(stok_dasar),0) as total FROM produk"))['total'];
$total_nilai = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(stok_dasar * harga_beli),0) as total FROM produk"))['total'];
?>

<div class="space-y-6">
    
    <!-- ==================== HEADER ==================== -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-dark">
                <i class="fas fa-box text-primary mr-2"></i>Produk
            </h1>
            <p class="text-sm text-gray-500 mt-1">Kelola semua produk, satuan, dan harga grosir</p>
        </div>
        <div class="flex gap-2">
            <a href="?page=produk-tambah" 
               class="inline-flex items-center justify-center gap-2 bg-primary text-white px-5 py-2.5 rounded-xl font-semibold hover:bg-indigo-700 transition shadow-lg shadow-indigo-200">
                <i class="fas fa-plus"></i> Tambah Produk
            </a>
            <a href="?page=input-produk-mobile" 
   class="inline-flex items-center gap-2 bg-green-500 text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-green-600 transition">
    <i class="fas fa-mobile-alt"></i> Input via HP
</a>
        </div>
    </div>

    <!-- ==================== STAT CARDS ==================== -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl shadow-sm p-5 border-l-4 border-blue-500 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Produk</p>
                    <p class="text-2xl font-bold mt-1"><?= $total_produk ?> <span class="text-sm font-normal text-gray-400">item</span></p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-box text-blue-500 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm p-5 border-l-4 border-green-500 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Stok</p>
                    <p class="text-2xl font-bold mt-1"><?= number_format($total_stok) ?> <span class="text-sm font-normal text-gray-400">unit</span></p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-cubes text-green-500 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm p-5 border-l-4 border-purple-500 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Nilai Stok (HPP)</p>
                    <p class="text-2xl font-bold mt-1"><?= rupiah($total_nilai) ?></p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-dollar-sign text-purple-500 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== SEARCH & FILTER ==================== -->
    <div class="bg-white rounded-2xl shadow-sm p-4">
        <form method="get" class="flex flex-wrap gap-3 items-end">
            <input type="hidden" name="page" value="produk">
            
            <!-- Search -->
            <div class="flex-1 min-w-[200px]">
                <label class="text-xs text-gray-500 font-medium block mb-1">Cari Produk</label>
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400 text-sm"></i>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                           placeholder="Nama atau kode produk..."
                           class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition">
                </div>
            </div>
            
            <!-- Filter Stok -->
            <div>
                <label class="text-xs text-gray-500 font-medium block mb-1">Filter Stok</label>
                <select name="stok" class="px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none">
                    <option value="semua" <?= $filter_stok == 'semua' ? 'selected' : '' ?>>Semua Stok</option>
                    <option value="tersedia" <?= $filter_stok == 'tersedia' ? 'selected' : '' ?>>Stok Tersedia (>10)</option>
                    <option value="tipis" <?= $filter_stok == 'tipis' ? 'selected' : '' ?>>Stok Menipis (1-10)</option>
                    <option value="habis" <?= $filter_stok == 'habis' ? 'selected' : '' ?>>Stok Habis (0)</option>
                </select>
            </div>
            
            <!-- Tombol -->
            <div class="flex gap-2">
                <button type="submit" class="bg-primary text-white px-5 py-2.5 rounded-xl text-sm font-medium hover:bg-indigo-700 transition">
                    <i class="fas fa-filter mr-1"></i> Terapkan
                </button>
                <?php if($search || $filter_stok != 'semua'): ?>
                <a href="?page=produk" class="px-4 py-2.5 border border-gray-200 rounded-xl text-sm text-gray-500 hover:bg-gray-50 transition">
                    <i class="fas fa-times mr-1"></i> Reset
                </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- ==================== INFO JUMLAH ==================== -->
    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">
            <?php if($totalRows > 0): ?>
            Menampilkan <span class="font-medium"><?= $start + 1 ?>-<?= min($totalRows, $start + $perPage) ?></span> 
            dari <span class="font-medium"><?= $totalRows ?></span> produk
            <?php else: ?>
            Tidak ada produk ditemukan
            <?php endif; ?>
        </p>
        
        <?php if($search): ?>
        <span class="text-xs bg-blue-50 text-blue-600 px-3 py-1 rounded-full">
            Pencarian: "<?= htmlspecialchars($search) ?>"
        </span>
        <?php endif; ?>
    </div>

    <!-- ==================== GRID PRODUK ==================== -->
    <div id="produk-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        
        <?php while($p = mysqli_fetch_assoc($produk_list)): 
            // Tentukan border color berdasarkan stok
            if($p['stok_dasar'] <= 0) {
                $border_class = 'border-red-300';
                $badge_stok = '<span class="absolute top-2 left-2 bg-red-500 text-white text-xs px-2 py-1 rounded-full font-medium">Habis</span>';
            } elseif($p['stok_dasar'] <= 10) {
                $border_class = 'border-yellow-300';
                $badge_stok = '<span class="absolute top-2 left-2 bg-yellow-500 text-white text-xs px-2 py-1 rounded-full font-medium">Tinggal '.$p['stok_dasar'].'</span>';
            } else {
                $border_class = 'border-gray-200';
                $badge_stok = '';
            }
        ?>
        <div class="produk-card bg-white rounded-2xl shadow-sm border-2 <?= $border_class ?> overflow-hidden hover:shadow-lg transition group">
            
            <!-- Gambar -->
            <div class="aspect-square bg-gray-100 overflow-hidden relative">
                <img src="<?= getGambar($p['gambar']) ?>" 
                     alt="<?= htmlspecialchars($p['nama']) ?>"
                     class="w-full h-full object-cover group-hover:scale-110 transition duration-300"
                     onerror="this.src='uploads/produk/default.png'">
                
                <!-- Badge Stok -->
                <?= $badge_stok ?>
                
                <!-- Badge Paket -->
                <?php if($p['jml_satuan'] > 0 || $p['jml_grosir'] > 0): ?>
                <span class="absolute top-2 right-2 bg-primary text-white text-xs px-2 py-1 rounded-full font-medium">
                    <?= ($p['jml_satuan'] + $p['jml_grosir']) ?> paket
                </span>
                <?php endif; ?>
            </div>
            
            <!-- Info Produk -->
            <div class="p-4">
                <p class="text-xs text-gray-400 font-mono"><?= htmlspecialchars($p['kode']) ?></p>
                <h3 class="font-semibold text-dark truncate mt-1" title="<?= htmlspecialchars($p['nama']) ?>">
                    <?= htmlspecialchars($p['nama']) ?>
                </h3>
                
                <!-- Harga & Stok -->
                <div class="grid grid-cols-2 gap-3 mt-3">
                    <div>
                        <p class="text-xs text-gray-400">Harga Jual</p>
                        <p class="font-bold text-primary"><?= rupiah($p['harga_jual']) ?></p>
                        <p class="text-xs text-gray-400">/<?= $p['satuan_dasar'] ?></p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-gray-400">Stok</p>
                        <p class="font-bold <?= $p['stok_dasar'] <= 10 ? 'text-red-500' : 'text-dark' ?>">
                            <?= $p['stok_dasar'] ?>
                        </p>
                        <p class="text-xs text-gray-400"><?= $p['satuan_dasar'] ?></p>
                    </div>
                </div>
                
                <!-- HPP -->
                <div class="mt-2 flex items-center gap-2">
                    <span class="text-xs text-gray-400">HPP:</span>
                    <span class="text-xs font-medium text-gray-500"><?= rupiah($p['harga_beli']) ?></span>
                </div>
                
                <!-- Action Buttons -->
                <div class="flex gap-2 mt-4 pt-3 border-t border-gray-100">
                    <a href="?page=produk-edit&id=<?= $p['id'] ?>" 
                       class="flex-1 text-center py-2 text-sm border border-gray-200 rounded-lg hover:bg-primary hover:text-white hover:border-primary transition group/btn">
                        <i class="fas fa-edit mr-1 group-hover/btn:text-white"></i> Edit
                    </a>
                    <a href="?page=satuan&produk_id=<?= $p['id'] ?>" 
                       class="flex-1 text-center py-2 text-sm border border-green-200 text-green-600 rounded-lg hover:bg-green-500 hover:text-white hover:border-green-500 transition">
                        <i class="fas fa-layer-group mr-1"></i> Paket
                    </a>
                    <button onclick="hapusProduk(<?= $p['id'] ?>, '<?= addslashes(htmlspecialchars($p['nama'])) ?>')" 
                            class="px-3 py-2 text-sm border border-red-200 text-red-500 rounded-lg hover:bg-red-500 hover:text-white hover:border-red-500 transition"
                            title="Hapus">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
        
        <!-- Empty State -->
        <?php if(mysqli_num_rows($produk_list) == 0): ?>
        <div class="col-span-full">
            <div class="text-center py-16">
                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-box-open text-4xl text-gray-400"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-500 mb-2">Tidak Ada Produk</h3>
                <p class="text-gray-400 text-sm mb-6">
                    <?= $search ? "Tidak ada produk dengan kata kunci \"$search\"" : 'Belum ada produk yang ditambahkan' ?>
                </p>
                <div class="flex gap-3 justify-center">
                    <?php if($search || $filter_stok != 'semua'): ?>
                    <a href="?page=produk" class="px-6 py-2 border border-gray-200 rounded-xl text-sm hover:bg-gray-50 transition">
                        <i class="fas fa-times mr-1"></i> Reset Filter
                    </a>
                    <?php endif; ?>
                    <a href="?page=produk-tambah" class="px-6 py-2 bg-primary text-white rounded-xl text-sm font-medium hover:bg-indigo-700 transition">
                        <i class="fas fa-plus mr-1"></i> Tambah Produk
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- ==================== PAGINATION ==================== -->
    <?php if($totalPages > 1): ?>
    <div class="flex items-center justify-between mt-6 pt-4 border-t border-gray-200">
        <p class="text-sm text-gray-500">
            Halaman <span class="font-medium"><?= $currentPage ?></span> dari <span class="font-medium"><?= $totalPages ?></span>
        </p>
        
        <div class="flex gap-1">
            <!-- Previous -->
            <?php if($currentPage > 1): 
                $prevUrl = "?page=produk&page_num=" . ($currentPage - 1);
                if($search) $prevUrl .= "&search=" . urlencode($search);
                if($filter_stok != 'semua') $prevUrl .= "&stok=" . $filter_stok;
            ?>
            <a href="<?= $prevUrl ?>" class="px-3 py-2 border border-gray-200 rounded-lg text-sm hover:bg-gray-100 transition">
                <i class="fas fa-chevron-left"></i>
            </a>
            <?php else: ?>
            <span class="px-3 py-2 border border-gray-100 rounded-lg text-sm text-gray-300 cursor-not-allowed">
                <i class="fas fa-chevron-left"></i>
            </span>
            <?php endif; ?>
            
            <!-- Page Numbers -->
            <?php
            $startPage = max(1, $currentPage - 2);
            $endPage = min($totalPages, $currentPage + 2);
            
            if($startPage > 1): 
                $firstUrl = "?page=produk&page_num=1";
                if($search) $firstUrl .= "&search=" . urlencode($search);
                if($filter_stok != 'semua') $firstUrl .= "&stok=" . $filter_stok;
            ?>
            <a href="<?= $firstUrl ?>" class="px-3 py-2 border border-gray-200 rounded-lg text-sm hover:bg-gray-100 transition">1</a>
            <?php if($startPage > 2): ?>
            <span class="px-2 py-2 text-gray-400 text-sm">...</span>
            <?php endif; ?>
            <?php endif; ?>
            
            <?php for($i = $startPage; $i <= $endPage; $i++): 
                $pageUrl = "?page=produk&page_num=$i";
                if($search) $pageUrl .= "&search=" . urlencode($search);
                if($filter_stok != 'semua') $pageUrl .= "&stok=" . $filter_stok;
                
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
                $lastUrl = "?page=produk&page_num=$totalPages";
                if($search) $lastUrl .= "&search=" . urlencode($search);
                if($filter_stok != 'semua') $lastUrl .= "&stok=" . $filter_stok;
            ?>
            <a href="<?= $lastUrl ?>" class="px-3 py-2 border border-gray-200 rounded-lg text-sm hover:bg-gray-100 transition"><?= $totalPages ?></a>
            <?php endif; ?>
            
            <!-- Next -->
            <?php if($currentPage < $totalPages): 
                $nextUrl = "?page=produk&page_num=" . ($currentPage + 1);
                if($search) $nextUrl .= "&search=" . urlencode($search);
                if($filter_stok != 'semua') $nextUrl .= "&stok=" . $filter_stok;
            ?>
            <a href="<?= $nextUrl ?>" class="px-3 py-2 border border-gray-200 rounded-lg text-sm hover:bg-gray-100 transition">
                <i class="fas fa-chevron-right"></i>
            </a>
            <?php else: ?>
            <span class="px-3 py-2 border border-gray-100 rounded-lg text-sm text-gray-300 cursor-not-allowed">
                <i class="fas fa-chevron-right"></i>
            </span>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

</div>

<!-- ==================== MODAL HAPUS ==================== -->
<div id="modal-hapus" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl transform transition-all">
        <div class="p-6 text-center">
            <!-- Icon -->
            <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-exclamation-triangle text-red-500 text-3xl"></i>
            </div>
            
            <h3 class="text-xl font-bold text-dark mb-2">Hapus Produk?</h3>
            <p class="text-gray-500 text-sm mb-2" id="teks-hapus">Produk ini akan dihapus permanen</p>
            <p class="text-xs text-red-400 mb-6">⚠️ Produk yang sudah ada transaksi tidak bisa dihapus</p>
            
            <div class="flex gap-3">
                <button onclick="closeModalHapus()" 
                        class="flex-1 py-2.5 border border-gray-200 rounded-xl font-medium hover:bg-gray-50 transition">
                    Batal
                </button>
                <a href="#" id="btn-hapus" 
                   class="flex-1 py-2.5 bg-red-500 text-white rounded-xl font-medium hover:bg-red-600 transition">
                    <i class="fas fa-trash mr-1"></i> Hapus
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// ==================== HAPUS PRODUK ====================
function hapusProduk(id, nama) {
    document.getElementById('teks-hapus').textContent = '"' + nama + '" akan dihapus permanen';
    document.getElementById('btn-hapus').href = 'proses/produk_hapus.php?id=' + id;
    document.getElementById('modal-hapus').classList.remove('hidden');
}

function closeModalHapus() {
    document.getElementById('modal-hapus').classList.add('hidden');
}

// Tutup modal dengan klik di luar
document.getElementById('modal-hapus').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModalHapus();
    }
});

// Tutup modal dengan Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModalHapus();
    }
});
</script>