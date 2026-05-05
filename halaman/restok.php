<?php
$page = 'restok';

// ==================== PAGINATION RIWAYAT ====================
$currentPage = isset($_GET['page_num']) ? max(1, (int)$_GET['page_num']) : 1;
$perPage = 15;

// Filter
$tgl_mulai = $_GET['tgl_mulai'] ?? date('Y-m-01');
$tgl_akhir = $_GET['tgl_akhir'] ?? date('Y-m-d');

$where = "WHERE DATE(p.created_at) BETWEEN '$tgl_mulai' AND '$tgl_akhir'";

// Total riwayat
$totalQuery = mysqli_query($conn, "SELECT COUNT(*) as total FROM pembelian p $where");
$totalRows = mysqli_fetch_assoc($totalQuery)['total'];
$totalPages = ceil($totalRows / $perPage);
$start = ($currentPage - 1) * $perPage;

// Ambil riwayat restok
$riwayat = mysqli_query($conn, "
    SELECT p.*, pr.nama as nama_produk, pr.satuan_dasar, pr.stok_dasar as stok_sekarang, u.nama as nama_user
    FROM pembelian p
    JOIN produk pr ON p.produk_id = pr.id
    JOIN users u ON p.user_id = u.id
    $where
    ORDER BY p.created_at DESC
    LIMIT $start, $perPage
");

// Statistik
$total_restok_bulan = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COALESCE(SUM(total_harga),0) as total, COUNT(*) as jumlah 
    FROM pembelian 
    WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())
"));

// Stok menipis
$stok_tipis = mysqli_query($conn, "
    SELECT * FROM produk 
    WHERE stok_dasar <= 10 AND stok_dasar > 0 
    ORDER BY stok_dasar ASC 
    LIMIT 5
");

// Stok habis
$stok_habis = mysqli_query($conn, "
    SELECT * FROM produk 
    WHERE stok_dasar = 0 
    ORDER BY nama ASC 
    LIMIT 5
");
?>

<div class="space-y-6">
    
    <!-- ==================== HEADER ==================== -->
    <div>
        <h1 class="text-2xl font-bold text-dark">
            <i class="fas fa-truck-loading text-green-500 mr-2"></i>Restok Produk
        </h1>
        <p class="text-sm text-gray-500 mt-1">Pembelian stok barang dari supplier</p>
    </div>

    <!-- ==================== ALERT STOK ==================== -->
    <?php if(mysqli_num_rows($stok_tipis) > 0 || mysqli_num_rows($stok_habis) > 0): ?>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        
        <?php if(mysqli_num_rows($stok_tipis) > 0): ?>
        <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-4">
            <div class="flex items-center gap-2 mb-3">
                <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                <h3 class="font-semibold text-yellow-700">⚠️ Stok Menipis</h3>
            </div>
            <div class="space-y-2">
                <?php while($s = mysqli_fetch_assoc($stok_tipis)): ?>
                <div class="flex items-center justify-between bg-white rounded-xl p-2 px-3">
                    <span class="text-sm font-medium"><?= $s['nama'] ?></span>
                    <span class="text-sm font-bold text-yellow-600">
                        <?= $s['stok_dasar'] ?> <?= $s['satuan_dasar'] ?>
                    </span>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if(mysqli_num_rows($stok_habis) > 0): ?>
        <div class="bg-red-50 border border-red-200 rounded-2xl p-4">
            <div class="flex items-center gap-2 mb-3">
                <i class="fas fa-times-circle text-red-600"></i>
                <h3 class="font-semibold text-red-700">❌ Stok Habis</h3>
            </div>
            <div class="space-y-2">
                <?php while($s = mysqli_fetch_assoc($stok_habis)): ?>
                <div class="flex items-center justify-between bg-white rounded-xl p-2 px-3">
                    <span class="text-sm font-medium"><?= $s['nama'] ?></span>
                    <span class="text-sm font-bold text-red-600">Habis</span>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
        <?php endif; ?>
        
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- ==================== FORM RESTOK (LIVE SEARCH) ==================== -->
        <div class="bg-white rounded-2xl shadow-sm">
            <div class="p-6 border-b flex items-center gap-4">
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-plus text-green-500 text-xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-lg text-dark">Tambah Restok</h3>
                    <p class="text-sm text-gray-400">Cari produk & input pembelian</p>
                </div>
            </div>
            
            <form action="proses/restok_simpan.php" method="post" class="p-6 space-y-4">
                
                <!-- ==================== LIVE SEARCH PRODUK ==================== -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        Cari Produk <span class="text-red-500">*</span>
                    </label>
                    
                    <!-- Input Search -->
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400 text-sm"></i>
                        <input type="text" 
                               id="search-produk" 
                               placeholder="Ketik nama produk..." 
                               autocomplete="off"
                               class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-green-500 focus:border-transparent outline-none transition"
                               onkeyup="searchProduk(this.value)"
                               onfocus="searchProduk(this.value)">
                        
                        <!-- Dropdown hasil pencarian -->
                        <div id="search-results" 
                             class="hidden absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-lg max-h-60 overflow-y-auto">
                        </div>
                    </div>
                    
                    <!-- Hidden input untuk produk_id -->
                    <input type="hidden" name="produk_id" id="selected-produk-id" required>
                    
                    <!-- Produk Terpilih -->
                    <div id="selected-produk" class="hidden mt-3 bg-blue-50 border border-blue-200 rounded-xl p-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-box text-blue-500"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-blue-700" id="selected-nama">-</p>
                                    <p class="text-xs text-blue-500">
                                        Stok: <span id="selected-stok">0</span> | 
                                        HPP: <span id="selected-hpp">Rp 0</span>
                                    </p>
                                </div>
                            </div>
                            <button type="button" onclick="clearProduk()" class="text-red-400 hover:text-red-600">
                                <i class="fas fa-times-circle"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Jumlah & Total Harga -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            Jumlah <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="qty" id="restok-qty" required min="1"
                               class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-green-500 focus:border-transparent outline-none transition"
                               placeholder="Qty"
                               onkeyup="hitungPreview()">
                        <p class="text-xs text-gray-400 mt-1" id="label-satuan">Satuan</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            Total Harga <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-4 top-2.5 text-gray-400 font-medium">Rp</span>
                            <input type="number" name="total_harga" id="restok-total" required
                                   class="w-full pl-12 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-green-500 focus:border-transparent outline-none transition"
                                   placeholder="Total dari supplier"
                                   onkeyup="hitungPreview()">
                        </div>
                    </div>
                </div>
                
                <!-- Preview Kalkulasi -->
                <div id="preview-harga" class="hidden bg-green-50 border border-green-200 rounded-xl p-4">
                    <div class="text-center">
                        <p class="text-xs text-green-600 uppercase">Harga per Satuan</p>
                        <p class="text-2xl font-bold text-green-700" id="harga-satuan">-</p>
                        <p class="text-xs text-green-500 mt-1" id="preview-kalkulasi"></p>
                        <p class="text-xs text-green-500 mt-1" id="preview-stok-after"></p>
                    </div>
                </div>
                
                <!-- Supplier -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        <i class="fas fa-store text-gray-400 mr-1"></i> Supplier (opsional)
                    </label>
                    <input type="text" name="supplier" 
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-green-500 focus:border-transparent outline-none transition"
                           placeholder="Nama supplier / toko">
                </div>
                
                <!-- Keterangan -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Keterangan</label>
                    <textarea name="keterangan" rows="2" 
                              class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-green-500 focus:border-transparent outline-none transition resize-none"
                              placeholder="Catatan tambahan..."></textarea>
                </div>
                
                <!-- Submit -->
                <button type="submit" class="w-full bg-green-500 text-white py-3 rounded-xl font-bold text-lg hover:bg-green-600 transition shadow-lg shadow-green-200">
                    <i class="fas fa-save mr-2"></i> Simpan Restok
                </button>
            </form>
        </div>
        
        <!-- ==================== RIWAYAT RESTOK ==================== -->
        <div class="bg-white rounded-2xl shadow-sm flex flex-col">
            <div class="p-6 border-b flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-lg text-dark">
                        <i class="fas fa-history text-gray-400 mr-2"></i>Riwayat Restok
                    </h3>
                    <p class="text-sm text-gray-400">
                        <?= rupiah($total_restok_bulan['total']) ?> bulan ini
                    </p>
                </div>
                <span class="text-sm text-gray-400"><?= $totalRows ?> restok</span>
            </div>
            
            <!-- Filter Tanggal -->
            <div class="px-6 py-3 border-b bg-gray-50">
                <form method="get" class="flex gap-2 items-center">
                    <input type="hidden" name="page" value="restok">
                    <input type="date" name="tgl_mulai" value="<?= $tgl_mulai ?>" 
                           class="px-3 py-1.5 border border-gray-200 rounded-lg text-xs">
                    <span class="text-xs text-gray-400">s/d</span>
                    <input type="date" name="tgl_akhir" value="<?= $tgl_akhir ?>" 
                           class="px-3 py-1.5 border border-gray-200 rounded-lg text-xs">
                    <button type="submit" class="px-3 py-1.5 bg-primary text-white rounded-lg text-xs">Filter</button>
                </form>
            </div>
            
            <!-- List Riwayat -->
            <div class="flex-1 overflow-y-auto max-h-[500px] divide-y divide-gray-100">
                <?php while($r = mysqli_fetch_assoc($riwayat)): ?>
                <div class="p-4 hover:bg-gray-50 transition">
                    <div class="flex justify-between items-start">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="font-semibold text-dark truncate"><?= $r['nama_produk'] ?></p>
                                <span class="px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-700 font-medium">
                                    +<?= $r['qty'] ?> <?= $r['satuan_dasar'] ?>
                                </span>
                            </div>
                            <div class="mt-1 text-sm text-gray-500">
                                <span><?= $r['qty'] ?> × <?= rupiah($r['harga_satuan']) ?> = <?= rupiah($r['total_harga']) ?></span>
                            </div>
                            <div class="mt-1 flex flex-wrap gap-2 text-xs">
                                <?php if($r['supplier']): ?>
                                <span class="text-gray-400"><i class="fas fa-store mr-1"></i><?= htmlspecialchars($r['supplier']) ?></span>
                                <?php endif; ?>
                                <?php if($r['keterangan']): ?>
                                <span class="text-gray-400">"<?= htmlspecialchars($r['keterangan']) ?>"</span>
                                <?php endif; ?>
                            </div>
                            <div class="mt-2 flex items-center gap-3 text-xs text-gray-400">
                                <span><i class="fas fa-user mr-1"></i><?= $r['nama_user'] ?></span>
                                <span><i class="fas fa-clock mr-1"></i><?= date('d/m/Y H:i', strtotime($r['created_at'])) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
                
                <?php if(mysqli_num_rows($riwayat) == 0): ?>
                <div class="text-center py-16 text-gray-400">
                    <i class="fas fa-truck-loading text-4xl mb-3"></i>
                    <p>Belum ada riwayat restok</p>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Pagination -->
            <?php if($totalPages > 1): ?>
            <div class="p-4 border-t">
                <div class="flex items-center justify-between">
                    <span class="text-xs text-gray-400">Hal <?= $currentPage ?>/<?= $totalPages ?></span>
                    <div class="flex gap-1">
                        <?php if($currentPage > 1): ?>
                        <a href="?page=restok&page_num=<?= $currentPage-1 ?>&tgl_mulai=<?= $tgl_mulai ?>&tgl_akhir=<?= $tgl_akhir ?>" 
                           class="px-2 py-1 border rounded text-xs hover:bg-gray-100">&laquo;</a>
                        <?php endif; ?>
                        <span class="px-3 py-1 bg-primary text-white rounded text-xs"><?= $currentPage ?></span>
                        <?php if($currentPage < $totalPages): ?>
                        <a href="?page=restok&page_num=<?= $currentPage+1 ?>&tgl_mulai=<?= $tgl_mulai ?>&tgl_akhir=<?= $tgl_akhir ?>" 
                           class="px-2 py-1 border rounded text-xs hover:bg-gray-100">&raquo;</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
    </div>
</div>

<script>
// Data produk (diisi oleh PHP)
const produkData = [
    <?php
    $all_produk = mysqli_query($conn, "SELECT id, kode, nama, satuan_dasar, harga_beli, stok_dasar FROM produk ORDER BY nama");
    $produk_array = [];
    while($p = mysqli_fetch_assoc($all_produk)) {
        $produk_array[] = $p;
    }
    foreach($produk_array as $p):
    ?>
    {
        id: <?= $p['id'] ?>,
        kode: <?= json_encode($p['kode']) ?>,
        nama: <?= json_encode($p['nama']) ?>,
        satuan: <?= json_encode($p['satuan_dasar']) ?>,
        harga_beli: <?= $p['harga_beli'] ?>,
        stok: <?= $p['stok_dasar'] ?>
    },
    <?php endforeach; ?>
];

let selectedProdukData = null;

// ==================== LIVE SEARCH ====================
function searchProduk(keyword) {
    const resultsDiv = document.getElementById('search-results');
    
    if (keyword.length < 1) {
        resultsDiv.classList.add('hidden');
        return;
    }
    
    const filtered = produkData.filter(p => 
        p.nama.toLowerCase().includes(keyword.toLowerCase()) ||
        p.kode.toLowerCase().includes(keyword.toLowerCase())
    );
    
    if (filtered.length === 0) {
        resultsDiv.innerHTML = '<div class="p-4 text-center text-sm text-gray-400">Tidak ditemukan</div>';
        resultsDiv.classList.remove('hidden');
        return;
    }
    
    let html = '';
    filtered.forEach(p => {
        const stokColor = p.stok <= 10 ? 'text-red-500' : 'text-gray-400';
        html += `
            <div class="p-3 hover:bg-blue-50 cursor-pointer border-b last:border-0 transition flex items-center gap-3"
                 onclick="pilihProduk(${p.id}, '${p.nama.replace(/'/g, "\\'")}', '${p.satuan}', ${p.harga_beli}, ${p.stok})">
                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-box text-green-500 text-xs"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium truncate">${p.nama}</p>
                    <p class="text-xs text-gray-400">
                        Kode: ${p.kode} | Stok: <span class="${stokColor} font-medium">${p.stok} ${p.satuan}</span>
                    </p>
                </div>
                <div class="text-right flex-shrink-0">
                    <p class="text-xs text-gray-400">HPP</p>
                    <p class="text-sm font-bold">Rp ${p.harga_beli.toLocaleString()}</p>
                </div>
            </div>
        `;
    });
    
    resultsDiv.innerHTML = html;
    resultsDiv.classList.remove('hidden');
}

// ==================== PILIH PRODUK ====================
function pilihProduk(id, nama, satuan, hpp, stok) {
    selectedProdukData = { id, nama, satuan, hpp, stok };
    
    document.getElementById('selected-produk-id').value = id;
    document.getElementById('selected-produk').classList.remove('hidden');
    document.getElementById('selected-nama').textContent = nama;
    document.getElementById('selected-stok').textContent = stok + ' ' + satuan;
    document.getElementById('selected-hpp').textContent = 'Rp ' + hpp.toLocaleString();
    document.getElementById('label-satuan').textContent = 'Jumlah dalam ' + satuan;
    
    // Clear search
    document.getElementById('search-produk').value = nama;
    document.getElementById('search-results').classList.add('hidden');
    
    // Focus ke input qty
    document.getElementById('restok-qty').focus();
    
    hitungPreview();
}

// ==================== CLEAR PRODUK ====================
function clearProduk() {
    selectedProdukData = null;
    document.getElementById('selected-produk-id').value = '';
    document.getElementById('selected-produk').classList.add('hidden');
    document.getElementById('search-produk').value = '';
    document.getElementById('restok-qty').value = '';
    document.getElementById('restok-total').value = '';
    document.getElementById('preview-harga').classList.add('hidden');
    document.getElementById('label-satuan').textContent = 'Satuan';
    document.getElementById('search-produk').focus();
}

// ==================== HITUNG PREVIEW ====================
function hitungPreview() {
    const qty = parseInt(document.getElementById('restok-qty').value) || 0;
    const total = parseInt(document.getElementById('restok-total').value) || 0;
    const preview = document.getElementById('preview-harga');
    
    if (qty > 0 && total > 0) {
        const hargaSatuan = Math.round(total / qty);
        
        document.getElementById('harga-satuan').textContent = 'Rp ' + hargaSatuan.toLocaleString();
        document.getElementById('preview-kalkulasi').textContent = 
            'Rp ' + total.toLocaleString() + ' ÷ ' + qty + ' = Rp ' + hargaSatuan.toLocaleString() + ' per satuan';
        
        if (selectedProdukData) {
            const stokAfter = selectedProdukData.stok + qty;
            document.getElementById('preview-stok-after').textContent = 
                'Stok setelah restok: ' + stokAfter + ' ' + selectedProdukData.satuan;
        }
        
        preview.classList.remove('hidden');
    } else {
        preview.classList.add('hidden');
    }
}

// ==================== TUTUP DROPDOWN KALAU KLIK DI LUAR ====================
document.addEventListener('click', function(e) {
    const search = document.getElementById('search-produk');
    const results = document.getElementById('search-results');
    
    if (e.target !== search && !results.contains(e.target)) {
        results.classList.add('hidden');
    }
});

// Enter di search = pilih produk pertama
document.getElementById('search-produk').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        const firstResult = document.querySelector('#search-results .hover\\:bg-blue-50');
        if (firstResult) {
            firstResult.click();
        }
    }
});
</script>