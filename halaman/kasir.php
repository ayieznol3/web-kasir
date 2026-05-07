<?php
$page = 'kasir';

// Ambil produk fast moving (otomatis dari transaksi terlaris)
$fast_moving = mysqli_query($conn, "
    SELECT pr.*, 
           COALESCE(SUM(td.qty),0) as total_terjual
    FROM produk pr
    LEFT JOIN transaksi_detail td ON pr.id = td.produk_id
    LEFT JOIN transaksi t ON td.transaksi_id = t.id 
        AND DATE(t.created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    WHERE pr.stok_dasar > 0
    GROUP BY pr.id
    ORDER BY total_terjual DESC
    LIMIT 12
");

// Ambil produk PPOB/Jasa
$ppob_list = mysqli_query($conn, "
    SELECT * FROM produk 
    WHERE stok_dasar = -1 
    ORDER BY nama
");

// Ambil pelanggan
$pelanggan_list = mysqli_query($conn, "SELECT * FROM pelanggan ORDER BY nama");
?>
<style>
/* Keranjang ringkas */
.keranjang-compact .item-keranjang {
    padding: 6px 10px !important;
}
.keranjang-compact .item-keranjang p {
    margin-bottom: 1px !important;
}
.keranjang-compact .item-keranjang .btn-qty {
    width: 22px !important;
    height: 22px !important;
    font-size: 10px !important;
}</style>


<div class="flex gap-4 h-full">
    
    <!-- ==================== PANEL KIRI: PRODUK ==================== -->
    <div class="flex-1 bg-white rounded-2xl shadow-sm flex flex-col overflow-hidden">
        
        <!-- Tab: Barang & PPOB -->
        <div class="border-b flex">
            <button id="tab-barang" onclick="switchTab('barang')" 
                    class="flex-1 py-3 text-sm font-semibold border-b-2 border-primary text-primary transition">
                🛍️ Barang
            </button>
            <button id="tab-ppob" onclick="switchTab('ppob')" 
                    class="flex-1 py-3 text-sm font-semibold text-gray-400 hover:text-gray-600 transition">
                💰 PPOB
            </button>
        </div>
        
        <!-- Search + Barcode -->
        <div class="p-3 border-b space-y-2">
            <div class="relative">
                <i class="fas fa-search absolute left-3 top-3 text-gray-400 text-sm"></i>
                <input type="text" id="cari-produk" placeholder="🔍 Cari produk..." 
                       class="w-full pl-10 pr-4 py-2 border rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none"
                       oninput="searchProduk(this.value)">
            </div>
            <div class="relative" id="barcode-area" style="display:none;">
                <i class="fas fa-barcode absolute left-3 top-3 text-gray-400 text-sm"></i>
                <input type="text" id="barcode-input" placeholder="Scan barcode di sini..." 
                       class="w-full pl-10 pr-4 py-2 border border-green-300 rounded-xl text-sm focus:ring-2 focus:ring-green-500 outline-none"
                       onkeydown="handleBarcode(event)">
                <button onclick="toggleBarcode()" class="absolute right-2 top-2 text-xs text-gray-400 hover:text-red-500">✕</button>
            </div>
            <button onclick="toggleBarcode()" id="btn-barcode" class="text-xs text-gray-400 hover:text-primary transition">
                <i class="fas fa-barcode mr-1"></i> Scan Barcode
            </button>
        </div>
        
        <!-- Grid Fast Moving (Tab Barang) -->
        <div id="content-barang" class="flex-1 overflow-y-auto p-3">
            
            <!-- Tombol Produk Custom -->
            <div class="mb-3">
                <button onclick="showCustomProduk()" 
                        class="w-full flex items-center justify-center gap-2 p-3 border-2 border-dashed border-gray-300 rounded-xl text-gray-500 hover:border-primary hover:text-primary transition text-sm">
                    <i class="fas fa-plus-circle"></i> Produk Custom (Manual)
                </button>
            </div>

            <!-- Fast Moving -->
            <p class="text-xs text-gray-400 font-medium mb-2 px-1">⭐ Sering Dibeli</p>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-2 mb-4">
                <?php while($fm = mysqli_fetch_assoc($fast_moving)): ?>
                <button onclick="addToCart(
                    <?= $fm['id'] ?>, 
                    '<?= addslashes($fm['nama']) ?>', 
                    <?= $fm['harga_jual'] ?>, 
                    '<?= $fm['satuan_dasar'] ?>',
                    <?= $fm['stok_dasar'] ?>,
                    1,
                    'dasar',
                    <?= $fm['harga_beli'] ?>
                )" class="produk-item text-left p-2 border rounded-xl hover:border-primary hover:shadow transition group">
                    <div class="flex items-center gap-2">
                        <img src="<?= getGambar($fm['gambar']) ?>" class="w-10 h-10 object-cover rounded-lg flex-shrink-0" onerror="this.src='uploads/produk/default.png'">
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-medium truncate group-hover:text-primary"><?= $fm['nama'] ?></p>
                            <p class="text-sm font-bold"><?= rupiah($fm['harga_jual']) ?></p>
                            <p class="text-xs text-gray-400">Stok: <?= $fm['stok_dasar'] ?></p>
                        </div>
                    </div>
                </button>
                <?php endwhile; ?>
            </div>
                                  <!-- Hasil Pencarian -->
            <div id="search-results"></div>
            
            <p class="text-xs text-gray-400 text-center mt-4">
                <i class="fas fa-lightbulb mr-1"></i> Klik produk untuk langsung menambahkan ke keranjang
            </p>
        </div>  

        
        <!-- Grid PPOB (Tab PPOB) -->
        <div id="content-ppob" class="flex-1 overflow-y-auto p-3 hidden">
            <p class="text-xs text-gray-400 font-medium mb-2 px-1">💰 Layanan PPOB</p>
            <div class="grid grid-cols-2 gap-2">
                <?php while($pp = mysqli_fetch_assoc($ppob_list)): 
                    $need_input = $pp['harga_jual'] <= 0;
                ?>
                <button onclick="<?= $need_input ? "showPPOBInput({$pp['id']}, '".addslashes($pp['nama'])."', {$pp['harga_jual']})" : "addToCart({$pp['id']}, '".addslashes($pp['nama'])."', {$pp['harga_jual']}, '{$pp['satuan_dasar']}', -1, 1, 'dasar', 0)" ?>"
                        class="text-left p-3 border rounded-xl hover:border-primary hover:shadow transition group">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-<?= $need_input ? 'edit' : 'check' ?> text-purple-500"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-medium truncate group-hover:text-primary"><?= $pp['nama'] ?></p>
                            <?php if($pp['harga_jual'] > 0): ?>
                                <p class="text-sm font-bold">Admin: <?= rupiah($pp['harga_jual']) ?></p>
                            <?php else: ?>
                                <p class="text-xs text-orange-500">Input nominal</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </button>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
    
    <!-- ==================== PANEL KANAN: KERANJANG ==================== -->
    <div class="w-[420px] bg-white rounded-2xl shadow-sm flex flex-col overflow-hidden flex-shrink-0">
        
        <div class="p-4 bg-primary text-white flex items-center justify-between">
            <h2 class="font-bold text-lg">
                <i class="fas fa-shopping-cart mr-2"></i>Keranjang
                <span id="count-keranjang" class="bg-white text-primary px-2 py-0.5 rounded-full text-xs ml-1">0</span>
            </h2>
            <button onclick="clearCart()" class="text-sm text-white/80 hover:text-white transition">
                <i class="fas fa-trash mr-1"></i>Kosongkan
            </button>
        </div>
        
        <div class="flex-1 overflow-y-auto" id="keranjang-container">
            <div id="keranjang-kosong" class="text-center py-16 text-gray-400">
                <i class="fas fa-shopping-basket text-5xl mb-3"></i>
                <p>Keranjang kosong</p>
                <p class="text-sm mt-1">Klik produk untuk menambahkan</p>
            </div>
            <div id="keranjang-items" class="divide-y"></div>
        </div>
        
        <!-- ==================== PEMBAYARAN ==================== -->
        <div class="border-t p-2 space-y-1.5 bg-gray-50 text-xs">
            
            <!-- Metode Pembayaran -->
            <div class="flex gap-2">
                <button id="metode-cash" onclick="setMetode('cash')" 
                        class="flex-1 py-2.5 rounded-xl text-sm font-bold bg-green-500 text-white transition">
                    💵 Cash
                </button>
                <button id="metode-piutang" onclick="setMetode('piutang')" 
                        class="flex-1 py-2.5 rounded-xl text-sm font-bold bg-gray-200 text-gray-500 transition">
                    📋 Piutang
                </button>
            </div>
            
            <!-- Pelanggan -->
            <div>
                <!-- Live Search Pelanggan -->
<div class="relative">
    <input type="text" id="search-pelanggan" placeholder="👤 Cari pelanggan..." autocomplete="off"
           class="w-full px-3 py-2 border rounded-lg text-xs"
           oninput="searchPelanggan(this.value)"
           onfocus="searchPelanggan(this.value)">
    <input type="hidden" id="pelanggan-select" value="">
    
    <!-- Dropdown hasil -->
    <div id="pelanggan-results" class="hidden absolute z-20 w-full mt-1 bg-white border rounded-lg shadow-lg max-h-40 overflow-y-auto">
        <div class="px-3 py-2 text-xs text-gray-400 hover:bg-gray-50 cursor-pointer" onclick="pilihPelanggan('', 'Umum')">
            👤 Umum
        </div>
        <?php 
        mysqli_data_seek($pelanggan_list, 0);
        while($pl = mysqli_fetch_assoc($pelanggan_list)): 
        ?>
        <div class="px-3 py-2 text-xs hover:bg-gray-50 cursor-pointer flex justify-between" 
             onclick="pilihPelanggan('<?= $pl['id'] ?>', '<?= addslashes($pl['nama']) ?>')"
             data-nama="<?= strtolower($pl['nama']) ?>">
            <span><?= $pl['nama'] ?></span>
            <?php if($pl['saldo_piutang'] > 0): ?>
            <span class="text-red-500 text-[10px]">Piutang: <?= rupiah($pl['saldo_piutang']) ?></span>
            <?php endif; ?>
        </div>
        <?php endwhile; ?>
    </div>
</div>
                <p id="pelanggan-warning" class="hidden text-xs text-red-500 mt-1">⚠️ Piutang wajib memilih pelanggan!</p>
            </div>
            
            <!-- Total -->
            <div class="flex justify-between items-center text-lg">
                <span class="font-bold">TOTAL</span>
                <span class="text-2xl font-bold text-primary" id="grand-total">Rp 0</span>
            </div>
            
            <!-- Bayar (hanya untuk Cash) -->
            <div id="input-bayar-area">
                <input type="number" id="input-bayar" placeholder="Jumlah bayar..."
                       class="w-full px-4 py-3 border rounded-xl text-lg font-bold focus:ring-2 focus:ring-primary outline-none"
                       onkeyup="hitungKembalian()">
            </div>
            
            <!-- Bayar Sebagian (untuk Piutang) -->
            <div id="input-bayar-piutang-area" class="hidden">
                <input type="number" id="input-bayar-piutang" placeholder="Bayar sebagian (opsional)..."
                       class="w-full px-4 py-3 border rounded-xl text-lg font-bold focus:ring-2 focus:ring-primary outline-none"
                       onkeyup="hitungKembalian()">
            </div>
            
            <!-- Info -->
            <div id="info-pembayaran" class="hidden text-sm"></div>
            
            <!-- Tombol -->
            <button id="btn-bayar" onclick="prosesPembayaran()" disabled
                    class="w-full py-3 rounded-xl font-bold text-lg hover:bg-green-600 disabled:bg-gray-300 disabled:cursor-not-allowed transition"
                    style="background-color: #22c55e; color: white;">
                <i class="fas fa-check-circle mr-2"></i>Proses Pembayaran
            </button>
        </div>
    </div>
</div>

<!-- ==================== MODAL INPUT PPOB ==================== -->
<div id="modal-ppob" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-sm p-6">
        <h3 class="font-bold text-lg mb-4" id="ppob-title">Input Nominal</h3>
        <input type="hidden" id="ppob-produk-id">
        <input type="hidden" id="ppob-nama">
        <div class="space-y-3">
            <div>
                <label class="text-xs text-gray-500">Nominal Transaksi</label>
                <input type="number" id="ppob-nominal" class="w-full px-4 py-2 border rounded-xl text-lg font-bold" placeholder="Rp 0" oninput="calcPPOB()">
            </div>
            <div>
                <label class="text-xs text-gray-500">Biaya Admin</label>
                <input type="number" id="ppob-admin" value="0" class="w-full px-4 py-2 border rounded-xl text-lg font-bold" placeholder="Rp 0" oninput="calcPPOB()">
            </div>
            <div class="bg-gray-100 p-3 rounded-xl text-center">
                <p class="text-xs text-gray-500">Total Dibayar</p>
                <p class="text-xl font-bold text-primary" id="ppob-total">Rp 0</p>
            </div>
        </div>
        <div class="flex gap-3 mt-4">
            <button onclick="closePPOB()" class="flex-1 py-2 border rounded-xl">Batal</button>
            <button onclick="addPPOB()" class="flex-1 py-2 bg-primary text-white rounded-xl font-semibold">Tambah</button>
        </div>
    </div>
</div>

<!-- ==================== MODAL PRODUK CUSTOM ==================== -->
<div id="modal-custom" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-sm p-6">
        <h3 class="font-bold text-lg mb-4">
            <i class="fas fa-plus-circle text-primary mr-2"></i>Produk Custom
        </h3>
        <div class="space-y-3">
            <div>
                <label class="text-xs text-gray-500">Nama Barang *</label>
                <input type="text" id="custom-nama" class="w-full px-4 py-2 border rounded-xl text-sm" placeholder="Nama barang...">
            </div>
            <div>
                <label class="text-xs text-gray-500">Harga Jual *</label>
                <input type="number" id="custom-harga" class="w-full px-4 py-2 border rounded-xl text-lg font-bold" placeholder="Rp 0">
            </div>
            <div>
                <label class="text-xs text-gray-500">Qty</label>
                <input type="number" id="custom-qty" value="1" min="1" class="w-full px-4 py-2 border rounded-xl text-sm">
            </div>
        </div>
        <div class="flex gap-3 mt-4">
            <button onclick="closeCustom()" class="flex-1 py-2 border rounded-xl">Batal</button>
            <button onclick="addCustom()" class="flex-1 py-2 bg-primary text-white rounded-xl font-semibold">Tambah</button>
        </div>
    </div>
</div>

<!-- ==================== MODAL EDIT HARGA ==================== -->
<div id="modal-edit-harga" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-xs p-6">
        <h3 class="font-bold text-lg mb-4">
            <i class="fas fa-tag text-primary mr-2"></i>Edit Harga
        </h3>
        <p class="text-sm text-gray-500 mb-2" id="edit-harga-nama">-</p>
        <p class="text-xs text-gray-400 mb-3">Harga normal: <span id="edit-harga-normal">Rp 0</span></p>
        <div class="space-y-3">
            <div>
                <label class="text-xs text-gray-500">Harga Jual Baru</label>
                <input type="number" id="edit-harga-input" class="w-full px-4 py-2 border rounded-xl text-lg font-bold" placeholder="Rp 0">
            </div>
        </div>
        <div class="flex gap-3 mt-4">
            <button onclick="closeEditHarga()" class="flex-1 py-2 border rounded-xl">Batal</button>
            <button onclick="saveEditHarga()" class="flex-1 py-2 bg-primary text-white rounded-xl font-semibold">Simpan</button>
        </div>
    </div>
</div>



<script>
// ==================== DATA PRODUK GLOBAL ====================
const allProduk = [
    <?php 
    $all_produk = mysqli_query($conn, "SELECT id, kode, nama, gambar, satuan_dasar, harga_jual, stok_dasar, harga_beli FROM produk WHERE stok_dasar != 0 ORDER BY nama");
    $produk_arr = []; while($p = mysqli_fetch_assoc($all_produk)) $produk_arr[] = $p;
    foreach($produk_arr as $p):
    ?>
    { 
        id: <?= $p['id'] ?>, 
        kode: <?= json_encode($p['kode']) ?>, 
        nama: <?= json_encode($p['nama']) ?>, 
        gambar: <?= json_encode($p['gambar']) ?>,
        satuan: <?= json_encode($p['satuan_dasar']) ?>, 
        harga: <?= $p['harga_jual'] ?>, 
        stok: <?= $p['stok_dasar'] ?>, 
        hpp: <?= $p['harga_beli'] ?> 
    },
    <?php endforeach; ?>
];

// ==================== GLOBAL STATE ====================
let cart = [];
let activeTab = 'barang';
let editHargaIndex = null;
let metodeBayar = 'cash';

// ==================== SWEET ALERT HELPER ====================
function swalSukses(title, text = '') {
    return Swal.fire({ icon: 'success', title, text, timer: 2000, showConfirmButton: false });
}

function swalError(title, text = '') {
    return Swal.fire({ icon: 'error', title, text, timer: 2500, showConfirmButton: false });
}

function swalWarning(title, text = '') {
    return Swal.fire({ icon: 'warning', title, text, timer: 2500, showConfirmButton: false });
}

function swalKonfirmasi(title, text, confirmText = 'Ya', cancelText = 'Batal') {
    return Swal.fire({
        title, text,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: confirmText,
        cancelButtonText: cancelText,
        confirmButtonColor: '#6366f1',
        cancelButtonColor: '#6b7280'
    });
}

function swalLoading(title = 'Memproses...') {
    return Swal.fire({ title, allowOutsideClick: false, didOpen: () => Swal.showLoading() });
}

// ==================== METODE BAYAR ====================
function setMetode(metode) {
    metodeBayar = metode;
    document.getElementById('metode-cash').className = metode === 'cash' 
        ? 'flex-1 py-2.5 rounded-xl text-sm font-bold bg-green-500 text-white transition'
        : 'flex-1 py-2.5 rounded-xl text-sm font-bold bg-gray-200 text-gray-500 transition';
    document.getElementById('metode-piutang').className = metode === 'piutang'
        ? 'flex-1 py-2.5 rounded-xl text-sm font-bold bg-yellow-500 text-white transition'
        : 'flex-1 py-2.5 rounded-xl text-sm font-bold bg-gray-200 text-gray-500 transition';
    
    document.getElementById('input-bayar-area').classList.toggle('hidden', metode === 'piutang');
    document.getElementById('input-bayar-piutang-area').classList.toggle('hidden', metode === 'cash');
    document.getElementById('pelanggan-warning').classList.toggle('hidden', metode === 'cash');
    
    if (metode === 'piutang') {
        document.getElementById('input-bayar').value = '';
        document.getElementById('input-bayar-piutang').value = '';
    }
    hitungKembalian();
}

// ==================== HITUNG KEMBALIAN ====================
function hitungKembalian() {
    const grandTotal = cart.reduce((sum, item) => sum + item.subtotal, 0);
    const info = document.getElementById('info-pembayaran');
    const pelanggan = document.getElementById('pelanggan-select').value;
    
    if (grandTotal === 0) { info.classList.add('hidden'); return; }
    
    if (metodeBayar === 'cash') {
        const bayar = parseInt(document.getElementById('input-bayar').value) || 0;
        info.classList.remove('hidden');
        if (bayar <= 0) {
            info.innerHTML = '<span class="text-gray-400">Masukkan jumlah bayar</span>';
        } else if (bayar >= grandTotal) {
            info.innerHTML = '<span class="text-green-600 font-bold">✅ Kembalian: Rp ' + (bayar - grandTotal).toLocaleString() + '</span>';
        } else if (pelanggan) {
            info.innerHTML = '<span class="text-yellow-600 font-bold">⚠️ Kurang: Rp ' + (grandTotal - bayar).toLocaleString() + ' (masuk Piutang)</span>';
        } else {
            info.innerHTML = '<span class="text-red-600 font-bold">❌ Bayar kurang! Pilih pelanggan untuk piutang.</span>';
        }
    } else {
        const bayar = parseInt(document.getElementById('input-bayar-piutang').value) || 0;
        info.classList.remove('hidden');
        if (!pelanggan) {
            info.innerHTML = '<span class="text-red-600 font-bold">❌ Harus pilih pelanggan!</span>';
        } else if (bayar >= grandTotal) {
            info.innerHTML = '<span class="text-green-600 font-bold">✅ Lunas! Kembalian: Rp ' + (bayar - grandTotal).toLocaleString() + '</span>';
        } else if (bayar > 0) {
            info.innerHTML = '<span class="text-yellow-600 font-bold">📋 Bayar sebagian. Piutang: Rp ' + (grandTotal - bayar).toLocaleString() + '</span>';
        } else {
            info.innerHTML = '<span class="text-yellow-600 font-bold">📋 Full Piutang: Rp ' + grandTotal.toLocaleString() + '</span>';
        }
    }
}

function updatePelanggan() {
    hitungKembalian();
    
    // Update tampilan warning piutang
    const pelangganId = document.getElementById('pelanggan-select').value;
    const warning = document.getElementById('pelanggan-warning');
    if (metodeBayar === 'piutang' && !pelangganId) {
        warning.classList.remove('hidden');
    } else {
        warning.classList.add('hidden');
    }
}

// ==================== PROSES PEMBAYARAN ====================
async function prosesPembayaran() {
    const grandTotal = cart.reduce((sum, item) => sum + item.subtotal, 0);
    const pelanggan = document.getElementById('pelanggan-select').value;
    let bayar = 0;
    
    if (metodeBayar === 'cash') {
        bayar = parseInt(document.getElementById('input-bayar').value) || 0;
        if (bayar <= 0) { swalWarning('Masukkan Jumlah Bayar!'); return; }
        if (bayar < grandTotal && !pelanggan) { swalError('Pilih Pelanggan!', 'Piutang wajib memilih pelanggan'); return; }
    } else {
        bayar = parseInt(document.getElementById('input-bayar-piutang').value) || 0;
        if (!pelanggan) { swalError('Piutang Wajib Pilih Pelanggan!'); return; }
    }
    
    const result = await Swal.fire({
        title: 'Konfirmasi Pembayaran',
        html: `
            <div style="text-align:left; line-height:1.8;">
                <p>💰 Total: <b>Rp ${grandTotal.toLocaleString()}</b></p>
                <p>💵 Bayar: <b>Rp ${bayar.toLocaleString()}</b></p>
                ${bayar >= grandTotal 
                    ? '<p style="color:green;">✅ Kembalian: <b>Rp ' + (bayar - grandTotal).toLocaleString() + '</b></p>'
                    : '<p style="color:#e67e22;">⚠️ Piutang: <b>Rp ' + (grandTotal - bayar).toLocaleString() + '</b></p>'}
                <p>📋 Metode: <b>${metodeBayar === 'cash' ? 'Cash' : 'Piutang'}</b></p>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '✅ Proses',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#22c55e',
        cancelButtonColor: '#6b7280'
    });
    
    if (!result.isConfirmed) return;
    
    swalLoading('Memproses Pembayaran...');
    
    const formData = new FormData();
    formData.append('total', grandTotal);
    formData.append('bayar', bayar);
    formData.append('pelanggan_id', pelanggan);
    formData.append('metode', metodeBayar);
    formData.append('cart', JSON.stringify(cart));
    
    try {
        const res = await fetch('proses/transaksi_simpan.php', { method: 'POST', body: formData });
        const data = await res.json();
        Swal.close();
        
        if (data.success) {
            await swalSukses('Transaksi Berhasil! ✅');
            
            const cetak = await Swal.fire({
                title: 'Cetak Struk?',
                text: 'Ingin mencetak struk sekarang?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: '🖨️ Cetak',
                cancelButtonText: 'Tutup',
                confirmButtonColor: '#6366f1'
            });
            
            if (cetak.isConfirmed) {
                window.open('halaman/struk.php?id=' + data.transaksi_id, '_blank');
            }
            
            cart = [];
            renderCart();
            setTimeout(() => location.reload(), 500);
        } else {
            swalError('Gagal!', data.message);
        }
    } catch (err) {
        Swal.close();
        swalError('Error!', err.message);
    }
}

// ==================== TAB SWITCH ====================
function switchTab(tab) {
    activeTab = tab;
    document.getElementById('tab-barang').className = tab === 'barang' 
        ? 'flex-1 py-3 text-sm font-semibold border-b-2 border-primary text-primary transition'
        : 'flex-1 py-3 text-sm font-semibold text-gray-400 hover:text-gray-600 transition';
    document.getElementById('tab-ppob').className = tab === 'ppob'
        ? 'flex-1 py-3 text-sm font-semibold border-b-2 border-primary text-primary transition'
        : 'flex-1 py-3 text-sm font-semibold text-gray-400 hover:text-gray-600 transition';
    document.getElementById('content-barang').classList.toggle('hidden', tab !== 'barang');
    document.getElementById('content-ppob').classList.toggle('hidden', tab !== 'ppob');
}

// ==================== ADD TO CART ====================
function addToCart(produkId, nama, harga, satuan, stok, qty, satuanType, hargaBeli = 0) {
    const existingIndex = cart.findIndex(item => item.produkId === produkId && item.satuanType === satuanType && !item.isPPOB && !item.isCustom);
    if (existingIndex >= 0) {
        if (cart[existingIndex].qty + qty > stok && stok > 0) { swalWarning('Stok Tidak Cukup!', 'Maksimal: ' + stok); return; }
        cart[existingIndex].qty += qty;
        cart[existingIndex].subtotal = cart[existingIndex].harga * cart[existingIndex].qty;
    } else {
        if (qty > stok && stok > 0) { swalWarning('Stok Tidak Cukup!', 'Maksimal: ' + stok); return; }
        cart.push({ produkId, nama, harga, hargaNormal: harga, hargaEcer: harga, hargaBeli, hargaOverride: false, satuan, satuanType, qty, stok, isPPOB: false, isCustom: false, subtotal: harga * qty });
    }
    renderCart();
}

// ==================== PPOB ====================
function showPPOBInput(produkId, nama, defaultAdmin) {
    document.getElementById('ppob-produk-id').value = produkId;
    document.getElementById('ppob-nama').value = nama;
    document.getElementById('ppob-title').textContent = nama;
    document.getElementById('ppob-nominal').value = '';
    document.getElementById('ppob-admin').value = defaultAdmin > 0 ? defaultAdmin : '';
    document.getElementById('ppob-total').textContent = 'Rp 0';
    document.getElementById('modal-ppob').classList.remove('hidden');
    document.getElementById('ppob-nominal').focus();
}
function closePPOB() { document.getElementById('modal-ppob').classList.add('hidden'); }
function calcPPOB() {
    document.getElementById('ppob-total').textContent = 'Rp ' + ((parseInt(document.getElementById('ppob-nominal').value)||0) + (parseInt(document.getElementById('ppob-admin').value)||0)).toLocaleString();
}
function addPPOB() {
    const produkId = parseInt(document.getElementById('ppob-produk-id').value);
    const nama = document.getElementById('ppob-nama').value;
    const nominal = parseInt(document.getElementById('ppob-nominal').value) || 0;
    const admin = parseInt(document.getElementById('ppob-admin').value) || 0;
    cart.push({ produkId, nama, nominal, admin, harga: nominal+admin, qty:1, isPPOB:true, isCustom:false, subtotal:nominal+admin });
    closePPOB(); renderCart();
}

// ==================== CUSTOM ====================
function showCustomProduk() { document.getElementById('modal-custom').classList.remove('hidden'); document.getElementById('custom-nama').focus(); }
function closeCustom() { document.getElementById('modal-custom').classList.add('hidden'); document.getElementById('custom-nama').value=''; document.getElementById('custom-harga').value=''; document.getElementById('custom-qty').value=1; }
function addCustom() {
    const nama = document.getElementById('custom-nama').value.trim();
    const harga = parseInt(document.getElementById('custom-harga').value) || 0;
    const qty = parseInt(document.getElementById('custom-qty').value) || 1;
    if (!nama) { swalWarning('Nama Barang Harus Diisi!'); return; }
    if (harga <= 0) { swalWarning('Harga Harus Lebih dari 0!'); return; }
    cart.push({ produkId: 0, nama, harga, hargaNormal: harga, qty, isPPOB: false, isCustom: true, subtotal: harga * qty });
    closeCustom(); renderCart();
}

// ==================== EDIT HARGA ====================
function showEditHarga(index) {
    const item = cart[index]; if (item.isPPOB || item.isCustom) return;
    editHargaIndex = index;
    document.getElementById('edit-harga-nama').textContent = item.nama;
    document.getElementById('edit-harga-normal').textContent = 'Rp ' + (item.hargaNormal || item.harga).toLocaleString();
    document.getElementById('edit-harga-input').value = item.harga;
    document.getElementById('modal-edit-harga').classList.remove('hidden');
    document.getElementById('edit-harga-input').focus(); document.getElementById('edit-harga-input').select();
}
function closeEditHarga() { document.getElementById('modal-edit-harga').classList.add('hidden'); editHargaIndex = null; }
async function saveEditHarga() {
    const hargaBaru = parseInt(document.getElementById('edit-harga-input').value) || 0;
    if (hargaBaru <= 0) { swalWarning('Harga Harus Lebih dari 0!'); return; }
    if (editHargaIndex !== null) {
        const item = cart[editHargaIndex];
        if (item.hargaBeli && hargaBaru < item.hargaBeli) {
            const result = await swalKonfirmasi('Harga di Bawah HPP!', 'HPP: Rp ' + item.hargaBeli.toLocaleString() + '. Tetap lanjutkan?', 'Lanjutkan');
            if (!result.isConfirmed) return;
        }
        item.harga = hargaBaru; item.hargaOverride = true; item.subtotal = hargaBaru * item.qty; renderCart();
    }
    closeEditHarga();
}

function updateQty(index, delta) {
    const item = cart[index]; if (item.isPPOB) return;
    const newQty = item.qty + delta; if (newQty < 1) return;
    if (!item.isCustom && item.stok > 0 && newQty > item.stok) { swalWarning('Stok Maksimal: ' + item.stok); return; }
    item.qty = newQty; item.subtotal = item.harga * newQty; renderCart();
}

// ==================== RENDER KERANJANG ====================

function renderCart() {
    const container = document.getElementById('keranjang-items');
    const empty = document.getElementById('keranjang-kosong');
    const count = document.getElementById('count-keranjang');
    const btnBayar = document.getElementById('btn-bayar');
    
    if (cart.length === 0) {
        container.innerHTML = '';
        empty.classList.remove('hidden');
        count.textContent = '0';
        document.getElementById('grand-total').textContent = 'Rp 0';
        btnBayar.disabled = true;
        document.getElementById('input-bayar').value = '';
        document.getElementById('info-pembayaran').classList.add('hidden');
        return;
    }
    
    empty.classList.add('hidden');
    count.textContent = cart.length;
    btnBayar.disabled = false;
    
    let html = '';
    let grandTotal = 0;
    
    cart.forEach((item, index) => {
        const subtotal = item.isPPOB ? (item.nominal + item.admin) : (item.harga * item.qty);
        item.subtotal = subtotal;
        grandTotal += subtotal;
        
        html += `
            <div class="py-2 px-2 border-b border-gray-100 hover:bg-gray-50 transition">
                
                <!-- Baris 1: Nama + Hapus -->
                <div class="flex items-center justify-between mb-1">
                    <p class="text-xs font-medium truncate flex-1">
                        ${item.nama}
                        ${item.isCustom ? '<span class="text-[9px] bg-orange-100 text-orange-600 px-1 rounded">Custom</span>' : ''}
                        ${item.hargaOverride ? '<span class="text-[9px] bg-yellow-100 text-yellow-600 px-1 rounded">Ubah</span>' : ''}
                    </p>
                    <button onclick="removeItem(${index})" class="text-gray-300 hover:text-red-500 text-xs ml-1 flex-shrink-0">✕</button>
                </div>
                
                <!-- Baris 2: Detail & Subtotal -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-1 flex-wrap text-[10px] text-gray-500">
                        ${item.isPPOB ? `
                            <span>Admin: Rp ${item.admin.toLocaleString()}</span>
                        ` : `
                            ${!item.isCustom ? `
                            <button onclick="updateQty(${index}, -1)" class="w-4 h-4 bg-gray-200 rounded-full text-[10px] leading-none hover:bg-gray-300">−</button>
                            <span class="font-bold text-gray-700 w-4 text-center">${item.qty}</span>
                            <button onclick="updateQty(${index}, 1)" class="w-4 h-4 bg-gray-200 rounded-full text-[10px] leading-none hover:bg-gray-300">+</button>
                            ` : ''}
                            <span>× ${item.satuanType !== 'dasar' ? item.satuanType : (item.satuan || 'pcs')}</span>
                            <span>· Rp ${item.harga.toLocaleString()}</span>
                            
                            ${!item.isCustom ? `
                            <select onchange="changeSatuan(${index}, this.value)" 
                                    class="text-[9px] border rounded px-1 py-0.5 bg-white ml-1"
                                    id="satuan-select-${index}">
                                <option value="dasar">${item.satuan || 'pcs'}</option>
                            </select>
                            <button onclick="showEditHarga(${index})" class="text-gray-400 hover:text-blue-500 text-[10px]" title="Edit Harga">✏️</button>
                            ` : '<span class="text-[9px] text-orange-500">Manual</span>'}
                        `}
                    </div>
                    <span class="text-xs font-bold text-gray-800 flex-shrink-0 ml-2">Rp ${subtotal.toLocaleString()}</span>
                </div>
                
            </div>`;
    });
    
    container.innerHTML = html;
    document.getElementById('grand-total').textContent = 'Rp ' + grandTotal.toLocaleString();
    
    cart.forEach((item, index) => {
        if (!item.isPPOB && !item.isCustom && item.produkId > 0) {
            setTimeout(() => loadSatuanOptions(index, item.produkId), 100);
        }
    });
    
    document.getElementById('input-bayar').value = '';
    document.getElementById('input-bayar-piutang').value = '';
    document.getElementById('info-pembayaran').classList.add('hidden');
}

// ==================== LOAD SATUAN ====================
function loadSatuanOptions(index, produkId) {
    fetch('ajax/get_satuan.php?produk_id='+produkId).then(res=>res.json()).then(data=>{
        const select = document.getElementById('satuan-select-'+index); if (!select) return;
        select.innerHTML = ''; select.appendChild(new Option(cart[index].satuan+' (ecer)', 'dasar'));
        data.forEach(s => { const o = new Option(s.nama_satuan+' ('+s.isi_satuan+')', s.nama_satuan); o.dataset.isi = s.isi_satuan; o.dataset.harga = s.harga_jual||(s.isi_satuan*(cart[index].hargaEcer||cart[index].harga)); if (cart[index].satuanType===s.nama_satuan) o.selected = true; select.appendChild(o); });
    });
}

function changeSatuan(index, satuanType) {
    const item = cart[index]; if (item.isPPOB||item.isCustom) return;
    const select = document.getElementById('satuan-select-'+index); if (!select) return;
    if (satuanType==='dasar') { item.harga = item.hargaEcer||item.harga; item.satuanType = 'dasar'; }
    else { if (!item.hargaEcer) item.hargaEcer = item.harga; const opt = select.options[select.selectedIndex]; item.satuanType = satuanType; item.harga = parseInt(opt.dataset.harga); item.qty = 1; }
    item.subtotal = item.harga * item.qty; renderCart();
}

function removeItem(index) { cart.splice(index, 1); renderCart(); }

async function clearCart() {
    if (cart.length === 0) return;
    const result = await swalKonfirmasi('Kosongkan Keranjang?', 'Semua item akan dihapus', 'Ya, Kosongkan');
    if (result.isConfirmed) { cart = []; renderCart(); swalSukses('Dikosongkan!'); }
}


// ==================== SEARCH PRODUK (LIVE - DENGAN GAMBAR) ====================
function searchProduk(keyword) {
    const resultsDiv = document.getElementById('search-results');
    const fastMoving = document.querySelector('#content-barang .grid');
    const fastMovingTitle = document.querySelector('#content-barang p:first-of-type');
    
    // ============ DETEKSI BARCODE (KODE PERSIS) ============
    // Jika input adalah kode barcode yang persis, langsung add to cart
    if (keyword.length >= 3) {
        const exactMatch = allProduk.find(p => p.kode === keyword);
        if (exactMatch) {
            addToCart(exactMatch.id, exactMatch.nama, exactMatch.harga, exactMatch.satuan, exactMatch.stok, 1, 'dasar', exactMatch.hpp);
            document.getElementById('cari-produk').value = '';
            resultsDiv.innerHTML = '';
            if (fastMoving) fastMoving.style.display = '';
            if (fastMovingTitle) fastMovingTitle.style.display = '';
            // Fokus kembali untuk scan berikutnya
            document.getElementById('cari-produk').focus();
            return;
        }
    }
    
    // ============ SEARCH BIASA ============
    if (keyword.length < 2) {
        resultsDiv.innerHTML = '';
        if (fastMoving) fastMoving.style.display = '';
        if (fastMovingTitle) fastMovingTitle.style.display = '';
        return;
    }
    
    if (fastMoving) fastMoving.style.display = 'none';
    if (fastMovingTitle) fastMovingTitle.style.display = 'none';
    
    const kw = keyword.toLowerCase();
    const filtered = allProduk.filter(p => 
        p.nama.toLowerCase().includes(kw) || p.kode.toLowerCase().includes(kw)
    );
    
    if (filtered.length === 0) {
        resultsDiv.innerHTML = '<p class="text-xs text-gray-400 text-center py-4">❌ Tidak ditemukan</p>';
        return;
    }
    
    const hasil = filtered.slice(0, 20);
    
    let html = '<p class="text-xs text-gray-400 mb-2 px-1">🔍 Ditemukan <b>' + filtered.length + '</b> produk</p>';
    html += '<div class="grid grid-cols-2 gap-2">';
    
    hasil.forEach(p => {
        const gambar = p.gambar || 'default.png';
        html += `
            <button onclick="addToCart(${p.id}, '${p.nama.replace(/'/g, "\\'")}', ${p.harga}, '${p.satuan}', ${p.stok}, 1, 'dasar', ${p.hpp})"
                    class="flex items-center gap-3 p-2 border rounded-xl hover:border-primary transition text-left">
                <img src="uploads/produk/${gambar}" 
                     alt="${p.nama}"
                     class="w-12 h-12 object-cover rounded-lg flex-shrink-0"
                     onerror="this.src='uploads/produk/default.png'">
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-medium truncate">${p.nama}</p>
                    <div class="flex items-center justify-between mt-1">
                        <p class="text-sm font-bold">Rp ${p.harga.toLocaleString()}</p>
                        <p class="text-xs text-gray-400">Stok: ${p.stok}</p>
                    </div>
                </div>
            </button>
        `;
    });
    
    html += '</div>';
    resultsDiv.innerHTML = html;
}

// ==================== LIVE SEARCH PELANGGAN ====================
function searchPelanggan(keyword) {
    const resultsDiv = document.getElementById('pelanggan-results');
    const items = resultsDiv.querySelectorAll('div[data-nama]');
    
    if (keyword.length === 0) {
        // Tampilkan semua
        items.forEach(item => item.style.display = '');
        resultsDiv.classList.remove('hidden');
        return;
    }
    
    const kw = keyword.toLowerCase();
    let found = 0;
    
    items.forEach(item => {
        const nama = item.dataset.nama;
        if (nama && nama.includes(kw)) {
            item.style.display = '';
            found++;
        } else {
            item.style.display = 'none';
        }
    });
    
    resultsDiv.classList.toggle('hidden', found === 0);
}

function pilihPelanggan(id, nama) {
    document.getElementById('pelanggan-select').value = id;
    document.getElementById('search-pelanggan').value = nama;
    document.getElementById('pelanggan-results').classList.add('hidden');
    updatePelanggan();
}

// Sembunyikan dropdown saat klik di luar
document.addEventListener('click', function(e) {
    const search = document.getElementById('search-pelanggan');
    const results = document.getElementById('pelanggan-results');
    if (e.target !== search && !results.contains(e.target)) {
        results.classList.add('hidden');
    }
});

// Tampilkan dropdown saat fokus
document.getElementById('search-pelanggan').addEventListener('focus', function() {
    document.getElementById('pelanggan-results').classList.remove('hidden');
});

// ==================== KEYBOARD ====================
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') { closePPOB(); closeCustom(); closeEditHarga(); document.getElementById('cari-produk').focus(); }
    if (e.key === 'F2') { e.preventDefault(); document.getElementById('cari-produk').focus(); }
    if (e.key === 'F8') { e.preventDefault(); document.getElementById('input-bayar').focus(); }
});
document.getElementById('modal-ppob').addEventListener('click', function(e) { if (e.target===this) closePPOB(); });
document.getElementById('modal-custom').addEventListener('click', function(e) { if (e.target===this) closeCustom(); });
document.getElementById('modal-edit-harga').addEventListener('click', function(e) { if (e.target===this) closeEditHarga(); });
</script>