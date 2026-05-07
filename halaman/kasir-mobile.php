<?php
$page = 'kasir-mobile';
$pelanggan_list = mysqli_query($conn, "SELECT * FROM pelanggan ORDER BY nama");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Kasir Mobile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#6366f1',
                        dark: '#1e293b'
                    }
                }
            }
        }
    </script>
    <script src="https://unpkg.com/html5-qrcode"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { -webkit-tap-highlight-color: transparent; }
        .produk-grid { max-height: 35vh; overflow-y: auto; }
        .keranjang-area { max-height: 35vh; overflow-y: auto; }
        .tab-btn.active { border-bottom: 2px solid #6366f1; color: #6366f1; font-weight: 600; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">

<!-- ==================== HEADER ==================== -->
<div class="bg-dark text-white px-4 py-3 sticky top-0 z-50">
    <div class="flex items-center justify-between">
        <a href="?page=kasir" class="text-white text-sm"><i class="fas fa-desktop mr-1"></i> Desktop</a>
        <h1 class="font-bold">🛒 Kasir Mobile</h1>
        <span class="text-sm font-bold" id="header-total">Rp 0</span>
    </div>
</div>

<!-- ==================== TAB ==================== -->
<div class="bg-white flex border-b sticky top-12 z-40">
    <button onclick="switchTab('barang')" id="tab-barang" class="tab-btn active flex-1 py-3 text-sm text-center transition">🛍️ Barang</button>
    <button onclick="switchTab('ppob')" id="tab-ppob" class="tab-btn flex-1 py-3 text-sm text-center text-gray-400 transition">💰 PPOB</button>
</div>

<!-- ==================== SEARCH + SCAN ==================== -->
<div class="px-3 py-2 bg-white border-b sticky top-[96px] z-30">
    <div class="flex gap-2">
        <div class="relative flex-1">
            <i class="fas fa-search absolute left-3 top-3 text-gray-400 text-sm"></i>
            <input type="text" id="cari-produk" placeholder="🔍 Cari atau scan barcode..." 
                   class="w-full pl-10 pr-4 py-2.5 border rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none"
                   oninput="searchProduk(this.value)">
        </div>
        <button onclick="startScanner()" class="px-4 py-2.5 bg-primary text-white rounded-xl text-sm"><i class="fas fa-barcode"></i></button>
    </div>
    <div id="debug-info" class="text-xs text-gray-400 mt-1 text-center"></div>
    <div id="scanner-area" class="hidden mt-2">
        <div id="reader" class="rounded-xl overflow-hidden"></div>
        <button onclick="stopScanner()" class="w-full mt-2 bg-red-500 text-white py-2 rounded-xl text-sm"><i class="fas fa-times mr-1"></i> Tutup Scanner</button>
    </div>
</div>

<!-- ==================== CONTENT BARANG ==================== -->
<!-- ==================== CONTENT BARANG ==================== -->
<div id="content-barang" class="px-3 py-2">
    <!-- Fast Moving & All Products (List) -->
    <p class="text-xs text-gray-400 font-medium mb-2 px-1">📦 Produk paling laku</p>
    <div class="produk-grid space-y-1" id="fast-moving">
        <?php 
        $all_produk_list = mysqli_query($conn, "
            SELECT pr.*, COALESCE(SUM(td.qty),0) as total_terjual
            FROM produk pr
            LEFT JOIN transaksi_detail td ON pr.id = td.produk_id
            LEFT JOIN transaksi t ON td.transaksi_id = t.id AND DATE(t.created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            WHERE pr.stok_dasar != 0
            GROUP BY pr.id 
            ORDER BY total_terjual DESC, pr.nama ASC 
            LIMIT 50
        ");
        while($p = mysqli_fetch_assoc($all_produk_list)): 
            $gambar = $p['gambar'] ?: 'default.png';
        ?>
        <button onclick="addToCart(<?= $p['id'] ?>, '<?= addslashes($p['nama']) ?>', <?= $p['harga_jual'] ?>, '<?= $p['satuan_dasar'] ?>', <?= $p['stok_dasar'] ?>, 1, 'dasar', <?= $p['harga_beli'] ?>)" 
                class="w-full flex items-center gap-3 p-2.5 bg-white border rounded-xl hover:border-primary active:bg-gray-50 transition text-left">
            <img src="uploads/produk/<?= $gambar ?>" 
                 alt="<?= htmlspecialchars($p['nama']) ?>"
                 class="w-12 h-12 object-cover rounded-lg flex-shrink-0"
                 onerror="this.src='uploads/produk/default.png'">
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium truncate"><?= htmlspecialchars($p['nama']) ?></p>
                <div class="flex items-center justify-between mt-1">
                    <div>
                        <p class="text-sm font-bold text-primary"><?= rupiah($p['harga_jual']) ?></p>
                        <?php if($p['total_terjual'] > 0): ?>
                        <p class="text-[10px] text-gray-400">🔥 <?= $p['total_terjual'] ?> terjual</p>
                        <?php endif; ?>
                    </div>
                    <div class="text-right">
                        <p class="text-xs <?= $p['stok_dasar'] <= 10 ? 'text-red-500 font-bold' : 'text-gray-400' ?>">
                            Stok: <?= $p['stok_dasar'] ?> <?= $p['satuan_dasar'] ?>
                        </p>
                        <?php if($p['total_terjual'] > 20): ?>
                        <span class="text-[9px] bg-yellow-100 text-yellow-700 px-1.5 py-0.5 rounded-full">⭐ Best</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </button>
        <?php endwhile; ?>
    </div>
    <div id="search-results"></div>
    <button onclick="showCustomProduk()" class="w-full mt-2 py-2 border-2 border-dashed border-gray-300 rounded-xl text-gray-400 text-sm hover:border-primary hover:text-primary transition">
        <i class="fas fa-plus-circle mr-1"></i> Produk Custom
    </button>
</div>

<!-- ==================== CONTENT PPOB ==================== -->
<div id="content-ppob" class="px-3 py-2 hidden">
    <p class="text-xs text-gray-400 font-medium mb-2">💰 Layanan PPOB</p>
    <div class="grid grid-cols-2 gap-2 produk-grid">
        <?php 
        $ppob_list = mysqli_query($conn, "SELECT * FROM produk WHERE stok_dasar = -1 ORDER BY nama");
        while($pp = mysqli_fetch_assoc($ppob_list)): $need_input = $pp['harga_jual'] <= 0;
        ?>
        <button onclick="<?= $need_input ? "showPPOBInput({$pp['id']}, '".addslashes($pp['nama'])."', {$pp['harga_jual']})" : "addToCart({$pp['id']}, '".addslashes($pp['nama'])."', {$pp['harga_jual']}, '{$pp['satuan_dasar']}', -1, 1, 'dasar', 0)" ?>"
                class="bg-white border rounded-xl p-3 text-left hover:border-primary active:bg-gray-50 transition">
            <p class="text-xs font-medium"><?= $pp['nama'] ?></p>
            <?php if($pp['harga_jual'] > 0): ?><p class="text-sm font-bold text-primary">Admin: <?= rupiah($pp['harga_jual']) ?></p>
            <?php else: ?><p class="text-xs text-orange-500">Input nominal</p><?php endif; ?>
        </button>
        <?php endwhile; ?>
    </div>
</div>

<!-- ==================== KERANJANG ==================== -->
<div class="bg-white border-t mx-3 mt-3 rounded-t-2xl shadow-lg">
    <div class="p-3 flex items-center justify-between border-b">
        <h3 class="font-bold text-sm">📋 Keranjang <span id="count-keranjang" class="bg-primary text-white px-2 py-0.5 rounded-full text-xs">0</span></h3>
        <button onclick="clearCart()" class="text-xs text-red-400"><i class="fas fa-trash mr-1"></i> Kosongkan</button>
    </div>
    <div class="keranjang-area px-3" id="keranjang-container">
        <div id="keranjang-kosong" class="text-center py-8 text-gray-400 text-sm">Keranjang kosong</div>
        <div id="keranjang-items" class="divide-y"></div>
    </div>
    
    <!-- ==================== PEMBAYARAN ==================== -->
    <div class="p-3 border-t space-y-2">
        <!-- Metode Bayar -->
        <div class="flex gap-2">
            <button id="metode-cash-m" onclick="setMetode('cash')" class="flex-1 py-2 rounded-xl text-xs font-bold bg-green-500 text-white transition">💵 Cash</button>
            <button id="metode-piutang-m" onclick="setMetode('piutang')" class="flex-1 py-2 rounded-xl text-xs font-bold bg-gray-200 text-gray-500 transition">📋 Piutang</button>
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
    
    <div id="pelanggan-results" class="hidden absolute z-20 w-full mt-1 bg-white border rounded-lg shadow-lg max-h-36 overflow-y-auto">
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
            <span class="text-red-500 text-[10px]"><?= rupiah($pl['saldo_piutang']) ?></span>
            <?php endif; ?>
        </div>
        <?php endwhile; ?>
    </div>
</div>
            <p id="pelanggan-warning-m" class="hidden text-xs text-red-500 mt-1">⚠️ Piutang wajib pilih pelanggan!</p>
        </div>
        
        <div class="flex justify-between items-center">
            <span class="font-bold text-sm">TOTAL</span>
            <span class="text-xl font-bold text-primary" id="grand-total">Rp 0</span>
        </div>
        
        <!-- Bayar Cash -->
        <div id="input-bayar-area-m">
            <input type="number" id="input-bayar" placeholder="Jumlah bayar..."
                   class="w-full px-4 py-3 border rounded-xl text-lg font-bold text-center focus:ring-2 focus:ring-primary outline-none"
                   onkeyup="hitungKembalian()">
        </div>
        
        <!-- Bayar Piutang -->
        <div id="input-bayar-piutang-area-m" class="hidden">
            <input type="number" id="input-bayar-piutang" placeholder="Bayar sebagian (opsional)..."
                   class="w-full px-4 py-3 border rounded-xl text-lg font-bold text-center focus:ring-2 focus:ring-primary outline-none"
                   onkeyup="hitungKembalian()">
        </div>
        
        <div id="info-pembayaran-m" class="hidden text-center text-xs"></div>
        
        <button id="btn-bayar" onclick="prosesPembayaran()" disabled
                class="w-full bg-green-500 text-white py-3 rounded-xl font-bold hover:bg-green-600 disabled:bg-gray-300 disabled:cursor-not-allowed transition">
            <i class="fas fa-check-circle mr-1"></i> Proses Pembayaran
        </button>
    </div>
</div>

<!-- ==================== MODAL PPOB ==================== -->
<div id="modal-ppob" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-end justify-center">
    <div class="bg-white rounded-t-2xl w-full p-5">
        <h3 class="font-bold text-lg mb-3" id="ppob-title">Input Nominal</h3>
        <input type="hidden" id="ppob-produk-id">
        <div class="space-y-3">
            <input type="number" id="ppob-nominal" class="w-full px-4 py-3 border rounded-xl text-lg font-bold text-center" placeholder="Nominal" oninput="calcPPOB()">
            <input type="number" id="ppob-admin" value="0" class="w-full px-4 py-3 border rounded-xl text-lg font-bold text-center" placeholder="Biaya Admin" oninput="calcPPOB()">
            <div class="bg-gray-100 p-3 rounded-xl text-center"><p class="text-xl font-bold text-primary" id="ppob-total">Rp 0</p></div>
        </div>
        <div class="flex gap-3 mt-4">
            <button onclick="closePPOB()" class="flex-1 py-3 border rounded-xl">Batal</button>
            <button onclick="addPPOB()" class="flex-1 py-3 bg-primary text-white rounded-xl font-bold">Tambah</button>
        </div>
    </div>
</div>

<!-- ==================== MODAL CUSTOM ==================== -->
<div id="modal-custom" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-end justify-center">
    <div class="bg-white rounded-t-2xl w-full p-5">
        <h3 class="font-bold text-lg mb-3">➕ Produk Custom</h3>
        <div class="space-y-3">
            <input type="text" id="custom-nama" class="w-full px-4 py-3 border rounded-xl" placeholder="Nama barang">
            <input type="number" id="custom-harga" class="w-full px-4 py-3 border rounded-xl text-lg font-bold text-center" placeholder="Harga Jual">
            <input type="number" id="custom-qty" value="1" min="1" class="w-full px-4 py-3 border rounded-xl text-center" placeholder="Qty">
        </div>
        <div class="flex gap-3 mt-4">
            <button onclick="closeCustom()" class="flex-1 py-3 border rounded-xl">Batal</button>
            <button onclick="addCustom()" class="flex-1 py-3 bg-primary text-white rounded-xl font-bold">Tambah</button>
        </div>
    </div>
</div>

<!-- ==================== MODAL EDIT HARGA ==================== -->
<div id="modal-edit-harga" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-end justify-center">
    <div class="bg-white rounded-t-2xl w-full p-5">
        <h3 class="font-bold text-lg mb-3">✏️ Edit Harga</h3>
        <p class="text-sm text-gray-500" id="edit-harga-nama">-</p>
        <p class="text-xs text-gray-400 mb-3">Normal: <span id="edit-harga-normal">Rp 0</span></p>
        <input type="number" id="edit-harga-input" class="w-full px-4 py-3 border rounded-xl text-lg font-bold text-center" placeholder="Harga baru">
        <div class="flex gap-3 mt-4">
            <button onclick="closeEditHarga()" class="flex-1 py-3 border rounded-xl">Batal</button>
            <button onclick="saveEditHarga()" class="flex-1 py-3 bg-primary text-white rounded-xl font-bold">Simpan</button>
        </div>
    </div>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
let cart = [], activeTab = 'barang', editHargaIndex = null, metodeBayar = 'cash', html5QrCode = null;

// ==================== SWEET ALERT HELPER ====================
function swalSukses(title, text = '') { return Swal.fire({ icon: 'success', title, text, timer: 2000, showConfirmButton: false }); }
function swalError(title, text = '') { return Swal.fire({ icon: 'error', title, text, timer: 2500, showConfirmButton: false }); }
function swalWarning(title, text = '') { return Swal.fire({ icon: 'warning', title, text, timer: 2500, showConfirmButton: false }); }
function swalKonfirmasi(title, text, confirmText = 'Ya', cancelText = 'Batal') {
    return Swal.fire({ title, text, icon: 'question', showCancelButton: true, confirmButtonText: confirmText, cancelButtonText: cancelText, confirmButtonColor: '#6366f1', cancelButtonColor: '#6b7280' });
}
function swalLoading(title = 'Memproses...') { return Swal.fire({ title, allowOutsideClick: false, didOpen: () => Swal.showLoading() }); }

// ==================== METODE BAYAR ====================
function setMetode(metode) {
    metodeBayar = metode;
    document.getElementById('metode-cash-m').className = metode === 'cash' ? 'flex-1 py-2 rounded-xl text-xs font-bold bg-green-500 text-white transition' : 'flex-1 py-2 rounded-xl text-xs font-bold bg-gray-200 text-gray-500 transition';
    document.getElementById('metode-piutang-m').className = metode === 'piutang' ? 'flex-1 py-2 rounded-xl text-xs font-bold bg-yellow-500 text-white transition' : 'flex-1 py-2 rounded-xl text-xs font-bold bg-gray-200 text-gray-500 transition';
    document.getElementById('input-bayar-area-m').classList.toggle('hidden', metode === 'piutang');
    document.getElementById('input-bayar-piutang-area-m').classList.toggle('hidden', metode === 'cash');
    document.getElementById('pelanggan-warning-m').classList.toggle('hidden', metode === 'cash');
    if (metode === 'piutang') { document.getElementById('input-bayar').value = ''; document.getElementById('input-bayar-piutang').value = ''; }
    hitungKembalian();
}

// ==================== HITUNG KEMBALIAN ====================
function hitungKembalian() {
    const grandTotal = cart.reduce((sum, item) => sum + item.subtotal, 0);
    const info = document.getElementById('info-pembayaran-m');
    const pelanggan = document.getElementById('pelanggan-select').value;
    if (grandTotal === 0) { info.classList.add('hidden'); return; }
    
    if (metodeBayar === 'cash') {
        const bayar = parseInt(document.getElementById('input-bayar').value) || 0;
        info.classList.remove('hidden');
        if (bayar <= 0) info.innerHTML = '<span class="text-gray-400">Masukkan jumlah bayar</span>';
        else if (bayar >= grandTotal) info.innerHTML = '<span class="text-green-600 font-bold">✅ Kembalian: Rp ' + (bayar - grandTotal).toLocaleString() + '</span>';
        else if (pelanggan) info.innerHTML = '<span class="text-yellow-600 font-bold">⚠️ Kurang: Rp ' + (grandTotal - bayar).toLocaleString() + ' (Piutang)</span>';
        else info.innerHTML = '<span class="text-red-600 font-bold">❌ Pilih pelanggan untuk piutang!</span>';
    } else {
        const bayar = parseInt(document.getElementById('input-bayar-piutang').value) || 0;
        info.classList.remove('hidden');
        if (!pelanggan) info.innerHTML = '<span class="text-red-600 font-bold">❌ Harus pilih pelanggan!</span>';
        else if (bayar >= grandTotal) info.innerHTML = '<span class="text-green-600 font-bold">✅ Lunas! Kembalian: Rp ' + (bayar - grandTotal).toLocaleString() + '</span>';
        else if (bayar > 0) info.innerHTML = '<span class="text-yellow-600 font-bold">📋 Bayar sebagian. Piutang: Rp ' + (grandTotal - bayar).toLocaleString() + '</span>';
        else info.innerHTML = '<span class="text-yellow-600 font-bold">📋 Full Piutang: Rp ' + grandTotal.toLocaleString() + '</span>';
    }
}
function updatePelanggan() {
    hitungKembalian();
    const pelangganId = document.getElementById('pelanggan-select').value;
    const warning = document.getElementById('pelanggan-warning-m');
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
            <div style="text-align:left; line-height:1.8; font-size:14px;">
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
            
            if (cetak.isConfirmed) window.open('halaman/struk.php?id=' + data.transaksi_id, '_blank');
            
            cart = []; renderCart();
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
    document.getElementById('tab-barang').className = tab === 'barang' ? 'tab-btn active flex-1 py-3 text-sm text-center transition' : 'tab-btn flex-1 py-3 text-sm text-center text-gray-400 transition';
    document.getElementById('tab-ppob').className = tab === 'ppob' ? 'tab-btn active flex-1 py-3 text-sm text-center transition' : 'tab-btn flex-1 py-3 text-sm text-center text-gray-400 transition';
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
    const p = parseInt(document.getElementById('ppob-produk-id').value);
    const n = document.getElementById('ppob-title').textContent;
    const nom = parseInt(document.getElementById('ppob-nominal').value) || 0;
    const adm = parseInt(document.getElementById('ppob-admin').value) || 0;
    cart.push({ produkId: p, nama: n, nominal: nom, admin: adm, harga: nom+adm, qty: 1, isPPOB: true, isCustom: false, subtotal: nom+adm });
    closePPOB(); renderCart();
}

// ==================== CUSTOM ====================
function showCustomProduk() { document.getElementById('modal-custom').classList.remove('hidden'); document.getElementById('custom-nama').focus(); }
function closeCustom() { document.getElementById('modal-custom').classList.add('hidden'); document.getElementById('custom-nama').value=''; document.getElementById('custom-harga').value=''; document.getElementById('custom-qty').value=1; }
function addCustom() {
    const n = document.getElementById('custom-nama').value.trim();
    const h = parseInt(document.getElementById('custom-harga').value) || 0;
    const q = parseInt(document.getElementById('custom-qty').value) || 1;
    if (!n) { swalWarning('Nama Barang Harus Diisi!'); return; }
    if (h <= 0) { swalWarning('Harga Harus Lebih dari 0!'); return; }
    cart.push({ produkId: 0, nama: n, harga: h, hargaNormal: h, qty: q, isPPOB: false, isCustom: true, subtotal: h*q });
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
    document.getElementById('edit-harga-input').focus();
}
function closeEditHarga() { document.getElementById('modal-edit-harga').classList.add('hidden'); editHargaIndex = null; }
async function saveEditHarga() {
    const h = parseInt(document.getElementById('edit-harga-input').value) || 0;
    if (h <= 0) { swalWarning('Harga Harus Lebih dari 0!'); return; }
    if (editHargaIndex !== null) {
        const item = cart[editHargaIndex];
        if (item.hargaBeli && h < item.hargaBeli) {
            const result = await swalKonfirmasi('Harga di Bawah HPP!', 'HPP: Rp ' + item.hargaBeli.toLocaleString() + '. Tetap lanjutkan?', 'Lanjutkan');
            if (!result.isConfirmed) return;
        }
        item.harga = h; item.hargaOverride = true; item.subtotal = h * item.qty; renderCart();
    }
    closeEditHarga();
}

function updateQty(index, delta) {
    const item = cart[index]; if (item.isPPOB) return;
    const q = item.qty + delta; if (q < 1) return;
    if (!item.isCustom && item.stok > 0 && q > item.stok) { swalWarning('Stok Maksimal: ' + item.stok); return; }
    item.qty = q; item.subtotal = item.harga * q; renderCart();
}

// ==================== RENDER KERANJANG ====================
// ==================== RENDER KERANJANG ====================
// ==================== RENDER KERANJANG MOBILE ====================
function renderCart() {
    const container = document.getElementById('keranjang-items');
    const empty = document.getElementById('keranjang-kosong');
    const count = document.getElementById('count-keranjang');
    const btn = document.getElementById('btn-bayar');
    
    if (cart.length === 0) {
        container.innerHTML = '';
        empty.classList.remove('hidden');
        count.textContent = '0';
        document.getElementById('grand-total').textContent = 'Rp 0';
        document.getElementById('header-total').textContent = 'Rp 0';
        btn.disabled = true;
        document.getElementById('input-bayar').value = '';
        document.getElementById('info-pembayaran-m').classList.add('hidden');
        return;
    }
    
    empty.classList.add('hidden');
    count.textContent = cart.length;
    btn.disabled = false;
    
    let html = '';
    let grandTotal = 0;
    
    cart.forEach((item, index) => {
        const subtotal = item.isPPOB ? (item.nominal + item.admin) : (item.harga * item.qty);
        item.subtotal = subtotal;
        grandTotal += subtotal;
        
        html += `
            <div class="py-2 px-1 border-b border-gray-100 hover:bg-gray-50 transition">
                
                <!-- Baris 1: Nama + Hapus -->
                <div class="flex items-center justify-between mb-1">
                    <p class="text-[12px] font-medium truncate flex-1">
                        ${item.nama}
                        ${item.isCustom ? '<span class="text-[10px] bg-orange-100 text-orange-600 px-1 rounded">Custom</span>' : ''}
                        ${item.hargaOverride ? '<span class="text-[10px] bg-yellow-100 text-yellow-600 px-1 rounded">Ubah</span>' : ''}
                    </p>
                    <button onclick="removeItem(${index})" class="text-gray-300 hover:text-red-500 text-xs ml-1 flex-shrink-0"> ✕ </button>
                </div>
                
                <!-- Baris 2: Detail & Subtotal -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-0.5 flex-wrap text-[10px] text-gray-500">
                        ${item.isPPOB ? `
                            <span>Admin: Rp ${item.admin.toLocaleString()}</span>
                        ` : `
                            ${!item.isCustom ? `
                            <button onclick="updateQty(${index}, -1)" class="w-4 h-4 bg-gray-200 rounded-full text-[10px] leading-none hover:bg-gray-300"> − </button>
                            <span class="font-bold text-gray-700 w-3 text-center">${item.qty}</span>
                            <button onclick="updateQty(${index}, 1)" class="w-4 h-4 bg-gray-200 rounded-full text-[10px] leading-none hover:bg-gray-300"> + </button>
                            ` : ''}
                            <span>× ${item.satuanType !== 'dasar' ? item.satuanType : (item.satuan || 'pcs')}</span>
                            <span>· Rp ${item.harga.toLocaleString()}</span>
                            
                            ${!item.isCustom ? `
                            <select onchange="changeSatuan(${index}, this.value)" 
                                    class="text-[9px] border rounded px-0.5 py-0.5 bg-white ml-0.5"
                                    id="satuan-m-${index}">
                                <option value="dasar">${item.satuan || 'pcs'}</option>
                            </select>
                            <button onclick="showEditHarga(${index})" class="text-gray-400 hover:text-blue-500 text-[9px]" title="Edit Harga">-✏️-</button>
                            ` : '<span class="text-[9px] text-orange-500">Manual</span>'}
                        `}
                    </div>
                    <span class="text-[11px] font-bold text-gray-800 flex-shrink-0 ml-1">Rp ${subtotal.toLocaleString()}</span>
                </div>
                
            </div>`;
    });
    
    container.innerHTML = html;
    document.getElementById('grand-total').textContent = 'Rp ' + grandTotal.toLocaleString();
    document.getElementById('header-total').textContent = 'Rp ' + grandTotal.toLocaleString();
    
    cart.forEach((item, index) => {
        if (!item.isPPOB && !item.isCustom && item.produkId > 0) {
            setTimeout(() => loadSatuanOptions(index, item.produkId), 100);
        }
    });
    
    document.getElementById('input-bayar').value = '';
    document.getElementById('input-bayar-piutang').value = '';
    document.getElementById('info-pembayaran-m').classList.add('hidden');
}

// ==================== SATUAN ====================
function changeSatuan(index, t) {
    const item = cart[index]; if (item.isPPOB || item.isCustom) return;
    const s = document.getElementById('satuan-m-'+index); if (!s) return;
    if (t === 'dasar') { item.harga = item.hargaEcer || item.harga; item.satuanType = 'dasar'; }
    else { if (!item.hargaEcer) item.hargaEcer = item.harga; const o = s.options[s.selectedIndex]; item.satuanType = t; item.harga = parseInt(o.dataset.harga); item.qty = 1; }
    item.subtotal = item.harga * item.qty; renderCart();
}
function loadSatuanOptions(index, pid) {
    fetch('ajax/get_satuan.php?produk_id='+pid).then(r=>r.json()).then(d=>{
        const s = document.getElementById('satuan-m-'+index); if (!s) return;
        s.innerHTML = ''; s.appendChild(new Option(cart[index].satuan+' (ecer)', 'dasar'));
        d.forEach(x=>{ const o = new Option(x.nama_satuan+' ('+x.isi_satuan+')', x.nama_satuan); o.dataset.isi = x.isi_satuan; o.dataset.harga = x.harga_jual||(x.isi_satuan*(cart[index].hargaEcer||cart[index].harga)); if(cart[index].satuanType===x.nama_satuan)o.selected=true; s.appendChild(o); });
    });
}

function removeItem(index) { cart.splice(index, 1); renderCart(); }

async function clearCart() {
    if (cart.length === 0) return;
    const result = await swalKonfirmasi('Kosongkan Keranjang?', 'Semua item akan dihapus', 'Ya, Kosongkan');
    if (result.isConfirmed) { cart = []; renderCart(); swalSukses('Dikosongkan!'); }
}

// ==================== SEARCH ====================
// ==================== SEARCH ====================
// ==================== SEARCH (DETEKSI BARCODE + LIVE SEARCH) ====================
function searchProduk(keyword) {
    const resultsDiv = document.getElementById('search-results');
    const fastMoving = document.getElementById('fast-moving');
    
    // ============ DETEKSI BARCODE (KODE PERSIS) ============
    if (keyword.length >= 3) {
        const exactMatch = allProduk.find(p => p.kode === keyword);
        if (exactMatch) {
            addToCart(exactMatch.id, exactMatch.nama, exactMatch.harga, exactMatch.satuan, exactMatch.stok, 1, 'dasar', exactMatch.hpp);
            document.getElementById('cari-produk').value = '';
            resultsDiv.innerHTML = '';
            if (fastMoving) fastMoving.style.display = '';
            document.getElementById('cari-produk').focus();
            // Vibrate sebagai feedback (HP support)
            if (navigator.vibrate) navigator.vibrate(100);
            return;
        }
    }
    
    // ============ SEARCH BIASA ============
    if (keyword.length < 2) { 
        resultsDiv.innerHTML = ''; 
        if (fastMoving) fastMoving.style.display = ''; 
        return; 
    }
    
    if (fastMoving) fastMoving.style.display = 'none';
    
    const kw = keyword.toLowerCase();
    const filtered = allProduk.filter(p => 
        p.nama.toLowerCase().includes(kw) || p.kode.toLowerCase().includes(kw)
    );
    
    if (filtered.length === 0) { 
        resultsDiv.innerHTML = '<p class="text-xs text-gray-400 text-center py-4">❌ Tidak ditemukan</p>'; 
        return; 
    }
    
    const hasil = filtered.slice(0, 15);
    
    let html = '<p class="text-xs text-gray-400 mb-2 px-1">🔍 Ditemukan <b>' + filtered.length + '</b> produk</p>';
    html += '<div class="space-y-1">';
    
    hasil.forEach(p => {
        const gambar = p.gambar || 'default.png';
        html += `
            <button onclick="addToCart(${p.id}, '${p.nama.replace(/'/g, "\\'")}', ${p.harga}, '${p.satuan}', ${p.stok}, 1, 'dasar', ${p.hpp})"
                    class="w-full flex items-center gap-3 p-2 bg-white border rounded-xl hover:border-primary active:bg-gray-50 transition text-left">
                <img src="uploads/produk/${gambar}" 
                     alt="${p.nama}"
                     class="w-10 h-10 object-cover rounded-lg flex-shrink-0"
                     onerror="this.src='uploads/produk/default.png'">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium truncate">${p.nama}</p>
                    <div class="flex items-center justify-between mt-1">
                        <p class="text-sm font-bold text-primary">Rp ${p.harga.toLocaleString()}</p>
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

document.addEventListener('click', function(e) {
    const search = document.getElementById('search-pelanggan');
    const results = document.getElementById('pelanggan-results');
    if (e.target !== search && !results.contains(e.target)) {
        results.classList.add('hidden');
    }
});

document.getElementById('search-pelanggan').addEventListener('focus', function() {
    document.getElementById('pelanggan-results').classList.remove('hidden');
});


// ==================== BARCODE SCANNER ====================
function startScanner() {
    document.getElementById('scanner-area').classList.remove('hidden');
    html5QrCode = new Html5Qrcode("reader");
    html5QrCode.start({ facingMode: "environment" }, { fps: 10, qrbox: { width: 250, height: 150 } },
        (decodedText) => {
            const found = allProduk.find(p => p.kode === decodedText);
            if (found) {
                addToCart(found.id, found.nama, found.harga, found.satuan, found.stok, 1, 'dasar', found.hpp);
                if (navigator.vibrate) navigator.vibrate(200);
            } else {
                swalError('Kode Tidak Ditemukan!', 'Kode: ' + decodedText);
            }
            stopScanner();
        }, (errorMessage) => {})
    .catch(err => { swalError('Gagal Buka Kamera!', 'Pastikan pakai HTTPS dan izinkan kamera.'); stopScanner(); });
}
function stopScanner() { if (html5QrCode) { html5QrCode.stop().then(() => { document.getElementById('scanner-area').classList.add('hidden'); html5QrCode = null; }); } }

// Keyboard shortcut
document.addEventListener('keydown', function(e) { if (e.key === 'Escape') { closePPOB(); closeCustom(); closeEditHarga(); stopScanner(); document.getElementById('cari-produk').focus(); } });
</script>
</body>
</html>