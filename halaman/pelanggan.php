<?php
$page = 'pelanggan';

// ==================== SEARCH & PAGINATION ====================
$search = $_GET['search'] ?? '';
$currentPage = isset($_GET['page_num']) ? max(1, (int)$_GET['page_num']) : 1;
$perPage = 15;

// Build WHERE
$where = "WHERE 1=1";
if ($search) {
    $search_esc = mysqli_real_escape_string($conn, $search);
    $where .= " AND (nama LIKE '%$search_esc%' OR no_hp LIKE '%$search_esc%' OR alamat LIKE '%$search_esc%')";
}

// Hitung total
$totalQuery = mysqli_query($conn, "SELECT COUNT(*) as total FROM pelanggan $where");
$totalRows = mysqli_fetch_assoc($totalQuery)['total'];
$totalPages = ceil($totalRows / $perPage);
$start = ($currentPage - 1) * $perPage;

// Ambil data
$pelanggan_list = mysqli_query($conn, "
    SELECT * FROM pelanggan 
    $where 
    ORDER BY nama ASC 
    LIMIT $start, $perPage
");

// Total piutang keseluruhan
$total_piutang = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(saldo_piutang),0) as total FROM pelanggan"));
?>

<div class="space-y-6">
    
    <!-- ==================== HEADER ==================== -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-dark">
                <i class="fas fa-users text-primary mr-2"></i>Pelanggan
            </h1>
            <p class="text-sm text-gray-500 mt-1">Kelola data pelanggan dan pantau piutang</p>
        </div>
        <button onclick="showModalTambah()" 
                class="inline-flex items-center justify-center gap-2 bg-primary text-white px-5 py-2.5 rounded-xl font-semibold hover:bg-indigo-700 transition shadow-lg shadow-indigo-200">
            <i class="fas fa-plus"></i> Tambah Pelanggan
        </button>
    </div>

    <!-- ==================== STAT CARDS ==================== -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl shadow-sm p-5 border-l-4 border-blue-500 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Pelanggan</p>
                    <p class="text-2xl font-bold mt-1"><?= $totalRows ?> <span class="text-sm font-normal text-gray-400">orang</span></p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-users text-blue-500 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm p-5 border-l-4 border-yellow-500 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Piutang</p>
                    <p class="text-2xl font-bold mt-1 <?= $total_piutang['total'] > 0 ? 'text-yellow-600' : 'text-green-600' ?>">
                        <?= rupiah($total_piutang['total']) ?>
                    </p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-hand-holding-usd text-yellow-500 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm p-5 border-l-4 border-green-500 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Pelanggan Aktif</p>
                    <p class="text-2xl font-bold mt-1">
                        <?php 
                        $aktif = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pelanggan WHERE saldo_piutang > 0"));
                        echo $aktif['total'];
                        ?> 
                        <span class="text-sm font-normal text-gray-400">piutang</span>
                    </p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-user-check text-green-500 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== SEARCH ==================== -->
    <div class="bg-white rounded-2xl shadow-sm p-4">
        <form method="get" class="flex flex-wrap gap-3 items-end">
            <input type="hidden" name="page" value="pelanggan">
            
            <div class="flex-1 min-w-[200px]">
                <label class="text-xs text-gray-500 font-medium block mb-1">Cari Pelanggan</label>
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400 text-sm"></i>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                           placeholder="Nama, nomor HP, atau alamat..."
                           class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition">
                </div>
            </div>
            
            <button type="submit" class="bg-primary text-white px-5 py-2.5 rounded-xl text-sm font-medium hover:bg-indigo-700 transition">
                <i class="fas fa-search mr-1"></i> Cari
            </button>
            
            <?php if($search): ?>
            <a href="?page=pelanggan" class="px-4 py-2.5 border border-gray-200 rounded-xl text-sm text-gray-500 hover:bg-gray-50 transition">
                <i class="fas fa-times mr-1"></i> Reset
            </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- ==================== INFO JUMLAH ==================== -->
    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">
            <?php if($totalRows > 0): ?>
            Menampilkan <span class="font-medium"><?= $start + 1 ?>-<?= min($totalRows, $start + $perPage) ?></span> 
            dari <span class="font-medium"><?= $totalRows ?></span> pelanggan
            <?php else: ?>
            Tidak ada pelanggan ditemukan
            <?php endif; ?>
        </p>
        
        <?php if($search): ?>
        <span class="text-xs bg-blue-50 text-blue-600 px-3 py-1 rounded-full">
            Pencarian: "<?= htmlspecialchars($search) ?>"
        </span>
        <?php endif; ?>
    </div>

    <!-- ==================== TABEL PELANGGAN ==================== -->
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="text-left px-4 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Pelanggan</th>
                        <th class="text-left px-4 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Kontak</th>
                        <th class="text-left px-4 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Alamat</th>
                        <th class="text-right px-4 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Piutang</th>
                        <th class="text-center px-4 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php while($pl = mysqli_fetch_assoc($pelanggan_list)): ?>
                    <tr class="hover:bg-gray-50 transition group">
                        
                        <!-- Nama -->
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm
                                    <?= $pl['saldo_piutang'] > 0 ? 'bg-yellow-100 text-yellow-600' : 'bg-gray-200 text-gray-600' ?>">
                                    <?= strtoupper(substr($pl['nama'], 0, 1)) ?>
                                </div>
                                <div>
                                    <p class="font-medium text-dark"><?= htmlspecialchars($pl['nama']) ?></p>
                                    <p class="text-xs text-gray-400">ID: <?= $pl['id'] ?></p>
                                </div>
                            </div>
                        </td>
                        
                        <!-- Kontak -->
                        <td class="px-4 py-3">
                            <?php if($pl['no_hp']): ?>
                            <div class="flex items-center gap-1 text-sm">
                                <i class="fas fa-phone text-gray-400 text-xs"></i>
                                <span><?= htmlspecialchars($pl['no_hp']) ?></span>
                            </div>
                            <?php else: ?>
                            <span class="text-sm text-gray-400">-</span>
                            <?php endif; ?>
                        </td>
                        
                        <!-- Alamat -->
                        <td class="px-4 py-3">
                            <?php if($pl['alamat']): ?>
                            <p class="text-sm text-gray-600 max-w-xs truncate" title="<?= htmlspecialchars($pl['alamat']) ?>">
                                <?= htmlspecialchars($pl['alamat']) ?>
                            </p>
                            <?php else: ?>
                            <span class="text-sm text-gray-400">-</span>
                            <?php endif; ?>
                        </td>
                        
                        <!-- Piutang -->
                        <td class="px-4 py-3 text-right">
                            <?php if($pl['saldo_piutang'] > 0): ?>
                            <div>
                                <p class="font-bold text-red-600"><?= rupiah($pl['saldo_piutang']) ?></p>
                                <p class="text-xs text-red-400">Belum lunas</p>
                            </div>
                            <?php else: ?>
                            <div>
                                <p class="font-bold text-green-600">Rp 0</p>
                                <p class="text-xs text-green-400">Lunas</p>
                            </div>
                            <?php endif; ?>
                        </td>
                        
                        <!-- Aksi -->
                        <td class="px-4 py-3">
                            <div class="flex justify-center gap-1">
                                <button onclick="editPelanggan(
                                    <?= $pl['id'] ?>, 
                                    '<?= addslashes(htmlspecialchars($pl['nama'])) ?>', 
                                    '<?= addslashes(htmlspecialchars($pl['no_hp'] ?? '')) ?>', 
                                    '<?= addslashes(htmlspecialchars($pl['alamat'] ?? '')) ?>'
                                )" 
                                        class="px-2.5 py-1.5 text-xs border border-gray-200 rounded-lg hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 transition"
                                        title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                
                                <a href="?page=piutang-detail&id=<?= $pl['id'] ?>" 
                                   class="px-2.5 py-1.5 text-xs border border-gray-200 rounded-lg hover:bg-yellow-50 hover:text-yellow-600 hover:border-yellow-200 transition"
                                   title="Lihat Piutang">
                                    <i class="fas fa-hand-holding-usd"></i>
                                </a>
                                
                                <?php if($pl['saldo_piutang'] <= 0): ?>
                                <a href="proses/pelanggan_hapus.php?id=<?= $pl['id'] ?>" 
                                   class="px-2.5 py-1.5 text-xs border border-red-200 text-red-500 rounded-lg hover:bg-red-500 hover:text-white hover:border-red-500 transition"
                                   title="Hapus"
                                   onclick="return confirm('Hapus pelanggan <?= addslashes(htmlspecialchars($pl['nama'])) ?>?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    
                    <!-- Empty State -->
                    <?php if(mysqli_num_rows($pelanggan_list) == 0): ?>
                    <tr>
                        <td colspan="5">
                            <div class="text-center py-16">
                                <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-user-slash text-3xl text-gray-400"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-500 mb-2">Tidak Ada Pelanggan</h3>
                                <p class="text-gray-400 text-sm mb-6">
                                    <?= $search ? "Tidak ada pelanggan dengan kata kunci \"$search\"" : 'Belum ada pelanggan yang ditambahkan' ?>
                                </p>
                                <div class="flex gap-3 justify-center">
                                    <?php if($search): ?>
                                    <a href="?page=pelanggan" class="px-6 py-2 border border-gray-200 rounded-xl text-sm hover:bg-gray-50 transition">
                                        <i class="fas fa-times mr-1"></i> Reset
                                    </a>
                                    <?php endif; ?>
                                    <button onclick="showModalTambah()" class="px-6 py-2 bg-primary text-white rounded-xl text-sm font-medium hover:bg-indigo-700 transition">
                                        <i class="fas fa-plus mr-1"></i> Tambah Pelanggan
                                    </button>
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
                $prevUrl = "?page=pelanggan&page_num=" . ($currentPage - 1);
                if($search) $prevUrl .= "&search=" . urlencode($search);
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
                $firstUrl = "?page=pelanggan&page_num=1";
                if($search) $firstUrl .= "&search=" . urlencode($search);
            ?>
            <a href="<?= $firstUrl ?>" class="px-3 py-2 border border-gray-200 rounded-lg text-sm hover:bg-gray-100 transition">1</a>
            <?php if($startPage > 2): ?>
            <span class="px-2 py-2 text-gray-400 text-sm">...</span>
            <?php endif; ?>
            <?php endif; ?>
            
            <?php for($i = $startPage; $i <= $endPage; $i++): 
                $pageUrl = "?page=pelanggan&page_num=$i";
                if($search) $pageUrl .= "&search=" . urlencode($search);
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
                $lastUrl = "?page=pelanggan&page_num=$totalPages";
                if($search) $lastUrl .= "&search=" . urlencode($search);
            ?>
            <a href="<?= $lastUrl ?>" class="px-3 py-2 border border-gray-200 rounded-lg text-sm hover:bg-gray-100 transition"><?= $totalPages ?></a>
            <?php endif; ?>
            
            <!-- Next -->
            <?php if($currentPage < $totalPages): 
                $nextUrl = "?page=pelanggan&page_num=" . ($currentPage + 1);
                if($search) $nextUrl .= "&search=" . urlencode($search);
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

<!-- ==================== MODAL TAMBAH / EDIT ==================== -->
<div id="modal-pelanggan" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-lg shadow-2xl transform transition-all">
        
        <!-- Modal Header -->
        <div class="p-6 border-b flex items-center justify-between">
            <h3 class="text-lg font-bold text-dark" id="modal-title">
                <i class="fas fa-user-plus text-primary mr-2"></i>Tambah Pelanggan
            </h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 text-xl leading-none transition">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <!-- Form -->
        <form action="proses/pelanggan_simpan.php" method="post" class="p-6 space-y-4">
            <input type="hidden" name="id" id="pelanggan-id">
            
            <!-- Nama -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    Nama <span class="text-red-500">*</span>
                </label>
                <input type="text" name="nama" id="pelanggan-nama" required 
                       class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition"
                       placeholder="Nama lengkap pelanggan">
            </div>
            
            <!-- No HP -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    <i class="fas fa-phone text-gray-400 mr-1"></i> Nomor HP
                </label>
                <input type="text" name="no_hp" id="pelanggan-hp" 
                       class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition"
                       placeholder="0812-3456-7890">
            </div>
            
            <!-- Alamat -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    <i class="fas fa-map-marker-alt text-gray-400 mr-1"></i> Alamat
                </label>
                <textarea name="alamat" id="pelanggan-alamat" rows="2" 
                          class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition resize-none"
                          placeholder="Alamat lengkap (opsional)"></textarea>
            </div>
            
            <!-- Info -->
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-3 text-sm text-blue-700">
                <i class="fas fa-info-circle mr-1"></i>
                Saldo piutang awal akan diatur ke Rp 0. Piutang bisa ditambahkan nanti.
            </div>
            
            <!-- Tombol -->
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeModal()" 
                        class="flex-1 py-2.5 border border-gray-200 rounded-xl font-medium text-gray-600 hover:bg-gray-50 transition">
                    Batal
                </button>
                <button type="submit" name="simpan" 
                        class="flex-1 py-2.5 bg-primary text-white rounded-xl font-semibold hover:bg-indigo-700 transition">
                    <i class="fas fa-save mr-1"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// ==================== MODAL FUNCTIONS ====================
function showModalTambah() {
    document.getElementById('modal-title').innerHTML = '<i class="fas fa-user-plus text-primary mr-2"></i>Tambah Pelanggan';
    document.getElementById('pelanggan-id').value = '';
    document.getElementById('pelanggan-nama').value = '';
    document.getElementById('pelanggan-hp').value = '';
    document.getElementById('pelanggan-alamat').value = '';
    document.getElementById('modal-pelanggan').classList.remove('hidden');
    document.getElementById('pelanggan-nama').focus();
}

function editPelanggan(id, nama, hp, alamat) {
    document.getElementById('modal-title').innerHTML = '<i class="fas fa-user-edit text-primary mr-2"></i>Edit Pelanggan';
    document.getElementById('pelanggan-id').value = id;
    document.getElementById('pelanggan-nama').value = nama;
    document.getElementById('pelanggan-hp').value = hp;
    document.getElementById('pelanggan-alamat').value = alamat;
    document.getElementById('modal-pelanggan').classList.remove('hidden');
    document.getElementById('pelanggan-nama').focus();
}

function closeModal() {
    document.getElementById('modal-pelanggan').classList.add('hidden');
}

// Tutup modal dengan klik di luar
document.getElementById('modal-pelanggan').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

// Tutup modal dengan Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
    }
});
</script>