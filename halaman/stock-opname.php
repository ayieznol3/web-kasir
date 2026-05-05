<?php
$page = 'stock-opname';

// Ambil data produk untuk search
$produk_list = mysqli_query($conn, "SELECT * FROM produk WHERE stok_dasar >= 0 ORDER BY nama");

// Riwayat opname
$riwayat = mysqli_query($conn, "
    SELECT so.*, pr.nama as nama_produk, pr.satuan_dasar, u.nama as nama_user
    FROM stock_opname so
    JOIN produk pr ON so.produk_id = pr.id
    JOIN users u ON so.user_id = u.id
    ORDER BY so.created_at DESC
    LIMIT 30
");
?>

<div class="space-y-6">
    
    <!-- Header -->
    <div>
        <h1 class="text-2xl font-bold text-dark">
            <i class="fas fa-clipboard-check text-orange-500 mr-2"></i>Stock Opname
        </h1>
        <p class="text-sm text-gray-500 mt-1">Penyesuaian stok: rusak, kadaluarsa, hilang, atau koreksi</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- ==================== FORM OPNAME ==================== -->
        <div class="bg-white rounded-2xl shadow-sm">
            <div class="p-6 border-b flex items-center gap-4">
                <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-edit text-orange-500 text-xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-lg">Input Opname</h3>
                    <p class="text-sm text-gray-400">Hitung stok fisik & bandingkan</p>
                </div>
            </div>
            
            <form action="proses/opname_simpan.php" method="post" class="p-6 space-y-4">
                
                <!-- Search Produk -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Cari Produk</label>
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400 text-sm"></i>
                        <input type="text" id="search-opname" placeholder="Ketik nama produk..." autocomplete="off"
                               class="w-full pl-10 pr-4 py-2.5 border rounded-xl text-sm focus:ring-2 focus:ring-orange-500 outline-none"
                               onkeyup="searchOpname(this.value)">
                        <div id="opname-results" class="hidden absolute z-10 w-full mt-1 bg-white border rounded-xl shadow-lg max-h-48 overflow-y-auto"></div>
                    </div>
                    <input type="hidden" name="produk_id" id="opname-produk-id" required>
                </div>
                
                <!-- Produk Terpilih -->
                <div id="opname-selected" class="hidden bg-blue-50 border border-blue-200 rounded-xl p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-semibold text-blue-700" id="opname-nama">-</p>
                            <p class="text-sm text-blue-500">
                                Stok Sistem: <span id="opname-stok-sistem" class="font-bold">0</span> 
                                <span id="opname-satuan">pcs</span>
                            </p>
                        </div>
                        <button type="button" onclick="clearOpname()" class="text-red-400 hover:text-red-600">
                            <i class="fas fa-times-circle"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Stok Nyata -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Stok Nyata (Fisik) *</label>
                    <input type="number" name="stok_nyata" id="stok-nyata" required min="0"
                           class="w-full px-4 py-2.5 border rounded-xl text-lg font-bold focus:ring-2 focus:ring-orange-500 outline-none"
                           onkeyup="hitungSelisih()" placeholder="0">
                </div>
                
                <!-- Preview Selisih -->
                <div id="preview-selisih" class="hidden bg-gray-50 rounded-xl p-4 text-center">
                    <p class="text-xs text-gray-500 uppercase">Selisih</p>
                    <p class="text-2xl font-bold" id="selisih-text">0</p>
                    <p class="text-xs" id="selisih-tipe"></p>
                    <p class="text-xs text-red-500 mt-1 hidden" id="kerugian-text"></p>
                </div>
                
                <!-- Alasan -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Alasan *</label>
                    <select name="alasan" required class="w-full px-4 py-2.5 border rounded-xl text-sm focus:ring-2 focus:ring-orange-500 outline-none">
                        <option value="">-- Pilih Alasan --</option>
                        <option value="Rusak">Barang Rusak</option>
                        <option value="Kadaluarsa">Kadaluarsa</option>
                        <option value="Hilang">Hilang / Tidak Ditemukan</option>
                        <option value="Koreksi Lebih">Koreksi (Stok Lebih)</option>
                        <option value="Koreksi Kurang">Koreksi (Stok Kurang)</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>
                
                <!-- Keterangan -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Keterangan</label>
                    <textarea name="keterangan" rows="2" 
                              class="w-full px-4 py-2.5 border rounded-xl text-sm focus:ring-2 focus:ring-orange-500 outline-none resize-none"
                              placeholder="Detail tambahan..."></textarea>
                </div>
                
                <!-- Submit -->
                <button type="submit" class="w-full bg-orange-500 text-white py-3 rounded-xl font-bold hover:bg-orange-600 transition shadow-lg shadow-orange-200">
                    <i class="fas fa-save mr-2"></i> Simpan Opname
                </button>
            </form>
        </div>
        
        <!-- ==================== RIWAYAT OPNAME ==================== -->
        <div class="bg-white rounded-2xl shadow-sm flex flex-col">
            <div class="p-6 border-b">
                <h3 class="font-bold text-lg">
                    <i class="fas fa-history text-gray-400 mr-2"></i>Riwayat Opname
                </h3>
            </div>
            
            <div class="flex-1 overflow-y-auto max-h-[600px] divide-y">
                <?php while($r = mysqli_fetch_assoc($riwayat)): 
                    $is_kurang = $r['tipe'] == 'kurang';
                ?>
                <div class="p-4 hover:bg-gray-50 transition">
                    <div class="flex justify-between items-start">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="font-semibold text-sm"><?= $r['nama_produk'] ?></p>
                                <span class="px-2 py-0.5 text-xs rounded-full font-medium <?= $is_kurang ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' ?>">
                                    <?= $is_kurang ? $r['selisih'] : '+'.$r['selisih'] ?> <?= $r['satuan_dasar'] ?>
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">
                                Sistem: <?= $r['stok_sistem'] ?> → Nyata: <?= $r['stok_nyata'] ?> | 
                                Alasan: <?= $r['alasan'] ?>
                            </p>
                            <?php if($r['kerugian'] > 0): ?>
                            <p class="text-xs text-red-500 mt-1">Kerugian: <?= rupiah($r['kerugian']) ?></p>
                            <?php endif; ?>
                            <div class="flex gap-3 text-xs text-gray-400 mt-2">
                                <span><i class="fas fa-user mr-1"></i><?= $r['nama_user'] ?></span>
                                <span><i class="fas fa-clock mr-1"></i><?= date('d/m/Y H:i', strtotime($r['created_at'])) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
                
                <?php if(mysqli_num_rows($riwayat) == 0): ?>
                <div class="text-center py-16 text-gray-400">
                    <i class="fas fa-clipboard-check text-4xl mb-3"></i>
                    <p>Belum ada riwayat opname</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
    </div>
</div>

<script>
// Data produk (dari PHP)
const produkData = [
    <?php 
    $all = mysqli_query($conn, "SELECT id, kode, nama, satuan_dasar, harga_beli, stok_dasar FROM produk WHERE stok_dasar >= 0 ORDER BY nama");
    $arr = [];
    while($p = mysqli_fetch_assoc($all)) $arr[] = $p;
    foreach($arr as $p):
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

let selectedOpnameProduk = null;

function searchOpname(keyword) {
    const results = document.getElementById('opname-results');
    if (keyword.length < 1) { results.classList.add('hidden'); return; }
    
    const filtered = produkData.filter(p => 
        p.nama.toLowerCase().includes(keyword.toLowerCase()) ||
        p.kode.toLowerCase().includes(keyword.toLowerCase())
    );
    
    if (filtered.length === 0) {
        results.innerHTML = '<div class="p-4 text-sm text-gray-400">Tidak ditemukan</div>';
        results.classList.remove('hidden');
        return;
    }
    
    let html = '';
    filtered.forEach(p => {
        html += `
            <div class="p-3 hover:bg-blue-50 cursor-pointer border-b last:border-0 flex items-center gap-3"
                 onclick="pilihOpname(${p.id}, '${p.nama.replace(/'/g, "\\'")}', '${p.satuan}', ${p.harga_beli}, ${p.stok})">
                <div class="flex-1">
                    <p class="text-sm font-medium">${p.nama}</p>
                    <p class="text-xs text-gray-400">Stok: ${p.stok} ${p.satuan} | HPP: Rp ${p.harga_beli.toLocaleString()}</p>
                </div>
            </div>
        `;
    });
    
    results.innerHTML = html;
    results.classList.remove('hidden');
}

function pilihOpname(id, nama, satuan, hpp, stok) {
    selectedOpnameProduk = { id, nama, satuan, hpp, stok };
    document.getElementById('opname-produk-id').value = id;
    document.getElementById('opname-nama').textContent = nama;
    document.getElementById('opname-stok-sistem').textContent = stok;
    document.getElementById('opname-satuan').textContent = satuan;
    document.getElementById('opname-selected').classList.remove('hidden');
    document.getElementById('search-opname').value = nama;
    document.getElementById('opname-results').classList.add('hidden');
    document.getElementById('stok-nyata').focus();
}

function clearOpname() {
    selectedOpnameProduk = null;
    document.getElementById('opname-produk-id').value = '';
    document.getElementById('opname-selected').classList.add('hidden');
    document.getElementById('search-opname').value = '';
    document.getElementById('stok-nyata').value = '';
    document.getElementById('preview-selisih').classList.add('hidden');
}

function hitungSelisih() {
    if (!selectedOpnameProduk) return;
    
    const nyata = parseInt(document.getElementById('stok-nyata').value) || 0;
    const sistem = selectedOpnameProduk.stok;
    const selisih = nyata - sistem;
    
    const preview = document.getElementById('preview-selisih');
    const kerugianEl = document.getElementById('kerugian-text');
    
    preview.classList.remove('hidden');
    document.getElementById('selisih-text').textContent = (selisih >= 0 ? '+' : '') + selisih;
    
    if (selisih < 0) {
        document.getElementById('selisih-text').className = 'text-2xl font-bold text-red-600';
        document.getElementById('selisih-tipe').textContent = '⚠️ Stok Berkurang';
        document.getElementById('selisih-tipe').className = 'text-xs text-red-500';
        
        // Hitung kerugian
        const kerugian = Math.abs(selisih) * selectedOpnameProduk.hpp;
        kerugianEl.textContent = '💰 Kerugian: Rp ' + kerugian.toLocaleString();
        kerugianEl.classList.remove('hidden');
    } else if (selisih > 0) {
        document.getElementById('selisih-text').className = 'text-2xl font-bold text-green-600';
        document.getElementById('selisih-tipe').textContent = '✅ Stok Bertambah';
        document.getElementById('selisih-tipe').className = 'text-xs text-green-500';
        kerugianEl.classList.add('hidden');
    } else {
        document.getElementById('selisih-text').className = 'text-2xl font-bold text-gray-600';
        document.getElementById('selisih-tipe').textContent = 'Tidak ada perubahan';
        document.getElementById('selisih-tipe').className = 'text-xs text-gray-500';
        kerugianEl.classList.add('hidden');
    }
}

// Tutup dropdown klik di luar
document.addEventListener('click', function(e) {
    const results = document.getElementById('opname-results');
    const search = document.getElementById('search-opname');
    if (e.target !== search && !results.contains(e.target)) {
        results.classList.add('hidden');
    }
});
</script>