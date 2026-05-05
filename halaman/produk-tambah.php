<?php
$page = 'produk';
$title = 'Tambah Produk Baru';
?>
<div class="max-w-3xl mx-auto space-y-6">
    
    <!-- Breadcrumb -->
    <div class="flex items-center gap-2 text-sm text-gray-500">
        <a href="?page=produk" class="hover:text-primary transition">
            <i class="fas fa-box mr-1"></i> Produk
        </a>
        <i class="fas fa-chevron-right text-xs"></i>
        <span class="text-gray-700 font-medium">Tambah Produk</span>
    </div>

    <!-- Card Form -->
    <div class="bg-white rounded-2xl shadow-sm">
        
        <!-- Card Header -->
        <div class="p-6 border-b flex items-center gap-4">
            <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center">
                <i class="fas fa-plus text-primary text-xl"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold text-dark">Tambah Produk Baru</h1>
                <p class="text-sm text-gray-400">Isi data produk dengan lengkap</p>
            </div>
        </div>
        
        <!-- Form -->
        <form action="proses/produk_simpan.php" method="post" enctype="multipart/form-data" class="p-6 space-y-6">
            <input type="hidden" name="aksi" value="tambah">
            
            <!-- ==================== UPLOAD GAMBAR ==================== -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-3">
                    <i class="fas fa-image text-gray-400 mr-1"></i> Gambar Produk
                </label>
                <div class="flex items-start gap-6">
                    <div class="flex-shrink-0">
                        <img id="preview-img" 
                             src="uploads/produk/default.png" 
                             alt="Preview"
                             class="w-32 h-32 object-cover rounded-2xl border-2 border-dashed border-gray-300">
                    </div>
                    <div class="flex-1">
                        <input type="file" name="gambar" id="input-gambar" accept="image/*"
                               class="w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-5 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-white hover:file:bg-indigo-700 transition cursor-pointer"
                               onchange="previewGambar(this)">
                        <p class="text-xs text-gray-400 mt-2">
                            <i class="fas fa-info-circle mr-1"></i> Format: JPG, PNG, GIF, WebP. Max 2MB
                        </p>
                    </div>
                </div>
            </div>
            
            <hr>
            
            <!-- ==================== KODE BARCODE ==================== -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-barcode text-gray-400 mr-1"></i> Kode / Barcode <span class="text-red-500">*</span>
                </label>
                <div class="flex gap-2">
                    <div class="relative flex-1">
                        <input type="text" name="kode" id="input-kode" required 
                               class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition font-mono"
                               placeholder="Scan atau ketik kode barcode">
                        <i class="fas fa-barcode absolute left-3 top-3 text-gray-400"></i>
                    </div>
                    <button type="button" onclick="generateKode()" 
                            class="px-4 py-2.5 bg-gray-100 text-gray-600 rounded-xl text-sm hover:bg-gray-200 transition flex-shrink-0"
                            title="Generate kode otomatis">
                        <i class="fas fa-magic mr-1"></i> Generate
                    </button>
                </div>
                <p class="text-xs text-gray-400 mt-1">
                    <i class="fas fa-info-circle mr-1"></i> Kode ini digunakan untuk scan barcode di kasir. Harus unik.
                </p>
            </div>
            
            <!-- ==================== NAMA PRODUK ==================== -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Nama Produk <span class="text-red-500">*</span>
                </label>
                <input type="text" name="nama" id="input-nama" required 
                       class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition"
                       placeholder="Nama produk...">
            </div>
            
            <!-- ==================== SATUAN & STOK ==================== -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Satuan Dasar <span class="text-red-500">*</span>
                    </label>
                    <select name="satuan_dasar" id="input-satuan" 
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition"
                            onchange="handleSatuanChange()">
                        <optgroup label="📦 Fisik">
                            <option value="pcs">Pcs (Pieces)</option>
                            <option value="bungkus">Bungkus</option>
                            <option value="kg">Kg (Kilogram)</option>
                            <option value="gram">Gram</option>
                            <option value="liter">Liter</option>
                            <option value="ml">ml</option>
                            <option value="dus">Dus</option>
                            <option value="botol">Botol</option>
                            <option value="kaleng">Kaleng</option>
                            <option value="sachet">Sachet</option>
                            <option value="karung">Karung</option>
                        </optgroup>
                        <optgroup label="💰 PPOB / Jasa">
                            <option value="transaksi">Transaksi</option>
                            <option value="pulsa">Pulsa</option>
                            <option value="token">Token</option>
                            <option value="tagihan">Tagihan</option>
                            <option value="topup">Topup</option>
                            <option value="transfer">Transfer</option>
                            <option value="jasa">Jasa / Service</option>
                        </optgroup>
                    </select>
                    <p class="text-xs text-gray-400 mt-1" id="satuan-hint">Satuan terkecil untuk stok dan penjualan ecer</p>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Stok Awal <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="stok_dasar" id="input-stok" value="0" min="-1" required 
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition">
                    <p class="text-xs text-gray-400 mt-1" id="stok-hint">
                        <span id="stok-text">Jumlah stok awal</span>
                    </p>
                </div>
            </div>
            
            <!-- ==================== HARGA ==================== -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Harga Beli / HPP <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-4 top-2.5 text-gray-400 font-medium">Rp</span>
                        <input type="number" name="harga_beli" id="input-harga-beli" value="0" required 
                               class="w-full pl-12 pr-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition"
                               placeholder="0">
                    </div>
                    <p class="text-xs text-gray-400 mt-1">Harga modal per satuan. Isi 0 untuk PPOB.</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Harga Jual <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-4 top-2.5 text-gray-400 font-medium">Rp</span>
                        <input type="number" name="harga_jual" id="input-harga-jual" value="0" required 
                               class="w-full pl-12 pr-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition"
                               placeholder="0">
                    </div>
                    <p class="text-xs text-gray-400 mt-1" id="harga-hint">
                        Harga jual eceran. Isi 0 untuk harga dinamis (PPOB).
                    </p>
                </div>
            </div>
            
            <!-- ==================== INFO PPOB ==================== -->
            <div id="info-ppob" class="hidden bg-purple-50 border border-purple-200 rounded-xl p-4 text-sm text-purple-700">
                <i class="fas fa-info-circle mr-1"></i>
                <strong>Mode PPOB:</strong> Stok diatur ke <strong>-1</strong> (virtual). 
                Harga jual bisa diisi biaya admin default atau <strong>0</strong> untuk dinamis.
            </div>
            
            <!-- ==================== SUBMIT ==================== -->
            <div class="flex justify-end gap-3 pt-4 border-t">
                <a href="?page=produk" class="px-6 py-2.5 border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50 transition font-medium">
                    <i class="fas fa-arrow-left mr-1"></i> Batal
                </a>
                <button type="submit" name="simpan" class="px-8 py-2.5 bg-primary text-white rounded-xl font-semibold hover:bg-indigo-700 transition shadow-lg shadow-indigo-200">
                    <i class="fas fa-save mr-2"></i> Simpan Produk
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// ==================== PREVIEW GAMBAR ====================
function previewGambar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview-img').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// ==================== HANDLE SATUAN CHANGE ====================
function handleSatuanChange() {
    const satuan = document.getElementById('input-satuan').value;
    const ppobSatuan = ['transaksi', 'pulsa', 'token', 'tagihan', 'topup', 'transfer', 'jasa'];
    const isPPOB = ppobSatuan.includes(satuan);
    
    const infoPPOB = document.getElementById('info-ppob');
    const inputStok = document.getElementById('input-stok');
    const inputHargaBeli = document.getElementById('input-harga-beli');
    const inputHargaJual = document.getElementById('input-harga-jual');
    const stokText = document.getElementById('stok-text');
    const hargaHint = document.getElementById('harga-hint');
    
    if (isPPOB) {
        // Mode PPOB
        infoPPOB.classList.remove('hidden');
        inputStok.value = -1;
        inputStok.readOnly = true;
        stokText.textContent = 'Otomatis -1 (virtual/unlimited)';
        stokText.className = 'text-purple-500';
        hargaHint.textContent = 'Biaya admin default. Isi 0 untuk dinamis saat transaksi.';
        hargaHint.className = 'text-xs text-purple-500 mt-1';
    } else {
        // Mode Fisik
        infoPPOB.classList.add('hidden');
        inputStok.readOnly = false;
        if (inputStok.value == -1) inputStok.value = 0;
        stokText.textContent = 'Jumlah stok awal';
        stokText.className = 'text-gray-400';
        hargaHint.textContent = 'Harga jual eceran. Isi 0 untuk harga dinamis (PPOB).';
        hargaHint.className = 'text-xs text-gray-400 mt-1';
    }
}

// ==================== GENERATE KODE OTOMATIS ====================
function generateKode() {
    const prefix = 'BRG';
    const random = Math.floor(Math.random() * 9000) + 1000;
    const timestamp = Date.now().toString().slice(-4);
    document.getElementById('input-kode').value = prefix + '-' + timestamp + random;
    document.getElementById('input-kode').focus();
}

// ==================== FOKUS BARCODE ====================
// Auto focus ke input kode saat halaman load
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('input-kode').focus();
});

// Enter di input kode → pindah ke input nama
document.getElementById('input-kode').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        document.getElementById('input-nama').focus();
    }
});

// ==================== TIPS ====================
console.log('💡 Tips: Gunakan barcode scanner untuk mengisi kode produk secara otomatis.');
console.log('💡 Scanner akan otomatis menekan Enter setelah scan.');
</script>