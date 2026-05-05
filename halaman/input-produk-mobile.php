<?php
$page = 'input-produk-mobile';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Input Produk Mobile</title>
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Library Barcode Scanner -->
    <script src="https://unpkg.com/html5-qrcode"></script>
    
    <style>
        * { -webkit-tap-highlight-color: transparent; }
        #reader { width: 100%; border-radius: 12px; overflow: hidden; }
        #reader video { border-radius: 12px; }
        .scanner-container { position: relative; }
        .scanner-overlay {
            position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
            width: 200px; height: 3px; background: red; box-shadow: 0 0 10px red;
            animation: scanLine 2s infinite;
        }
        @keyframes scanLine {
            0%, 100% { top: 30%; }
            50% { top: 70%; }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen pb-20">

<!-- ==================== HEADER ==================== -->
<div class="bg-dark text-white px-4 py-3 sticky top-0 z-50">
    <div class="flex items-center justify-between">
        <a href="?page=produk" class="text-white">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="font-bold">📦 Input Produk</h1>
        <div class="w-6"></div>
    </div>
</div>

<!-- ==================== CONTENT ==================== -->
<div class="max-w-lg mx-auto p-4 space-y-4">

    <!-- Tombol Scan Barcode -->
    <button id="btn-scan" onclick="startScanner()" 
            class="w-full bg-primary text-white py-4 rounded-2xl font-bold text-lg hover:bg-indigo-700 transition shadow-lg">
        <i class="fas fa-qrcode mr-2"></i> Scan Barcode
    </button>

    <!-- Area Scanner -->
    <div id="scanner-area" class="hidden">
        <div id="reader"></div>
        <button onclick="stopScanner()" class="w-full mt-2 bg-red-500 text-white py-2 rounded-xl text-sm">
            <i class="fas fa-times mr-1"></i> Tutup Scanner
        </button>
    </div>

    <!-- Form Input -->
    <form id="form-produk" action="proses/produk_simpan.php" method="post" enctype="multipart/form-data" class="space-y-4 bg-white rounded-2xl shadow-sm p-5">
        <input type="hidden" name="aksi" value="tambah">
        
        <!-- Kode Barcode -->
        <div>
            <label class="text-xs font-semibold text-gray-500 flex items-center gap-2 mb-1">
                <i class="fas fa-barcode"></i> Kode Barcode <span class="text-red-400">*</span>
            </label>
            <div class="flex gap-2">
                <input type="text" name="kode" id="kode" required 
                       class="flex-1 px-4 py-3 border rounded-xl font-mono text-sm focus:ring-2 focus:ring-primary outline-none"
                       placeholder="Scan atau ketik...">
                <button type="button" onclick="generateKode()" 
                        class="px-4 py-3 bg-gray-100 rounded-xl text-sm hover:bg-gray-200">
                    <i class="fas fa-magic"></i>
                </button>
            </div>
        </div>

        <!-- Nama Produk -->
        <div>
            <label class="text-xs font-semibold text-gray-500 flex items-center gap-2 mb-1">
                <i class="fas fa-tag"></i> Nama Produk <span class="text-red-400">*</span>
            </label>
            <input type="text" name="nama" id="nama" required 
                   class="w-full px-4 py-3 border rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none"
                   placeholder="Nama produk...">
        </div>

        <!-- Satuan -->
        <div>
            <label class="text-xs font-semibold text-gray-500 flex items-center gap-2 mb-1">
                <i class="fas fa-cube"></i> Satuan <span class="text-red-400">*</span>
            </label>
            <select name="satuan_dasar" id="satuan" class="w-full px-4 py-3 border rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none">
                <optgroup label="📦 Fisik">
                    <option value="pcs">Pcs</option>
                    <option value="bungkus">Bungkus</option>
                    <option value="kg">Kg</option>
                    <option value="gram">Gram</option>
                    <option value="liter">Liter</option>
                    <option value="ml">ml</option>
                    <option value="dus">Dus</option>
                    <option value="botol">Botol</option>
                    <option value="kaleng">Kaleng</option>
                    <option value="sachet">Sachet</option>
                </optgroup>
                <optgroup label="💰 PPOB">
                    <option value="transaksi">Transaksi</option>
                    <option value="pulsa">Pulsa</option>
                    <option value="token">Token</option>
                    <option value="tagihan">Tagihan</option>
                    <option value="topup">Topup</option>
                    <option value="transfer">Transfer</option>
                </optgroup>
            </select>
        </div>

        <!-- Harga Beli & Jual -->
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="text-xs font-semibold text-gray-500 flex items-center gap-2 mb-1">
                    <i class="fas fa-shopping-cart"></i> Harga Beli
                </label>
                <input type="number" name="harga_beli" id="harga_beli" value="0" required 
                       class="w-full px-4 py-3 border rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none"
                       placeholder="0">
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500 flex items-center gap-2 mb-1">
                    <i class="fas fa-tag"></i> Harga Jual
                </label>
                <input type="number" name="harga_jual" id="harga_jual" value="0" required 
                       class="w-full px-4 py-3 border rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none"
                       placeholder="0">
            </div>
        </div>

        <!-- Stok -->
        <div>
            <label class="text-xs font-semibold text-gray-500 flex items-center gap-2 mb-1">
                <i class="fas fa-boxes"></i> Stok Awal
            </label>
            <input type="number" name="stok_dasar" id="stok" value="0" min="-1" required 
                   class="w-full px-4 py-3 border rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none"
                   placeholder="0">
            <p class="text-xs text-gray-400 mt-1">Isi -1 untuk produk PPOB/virtual</p>
        </div>

        <!-- Foto Produk -->
        <div>
            <label class="text-xs font-semibold text-gray-500 flex items-center gap-2 mb-1">
                <i class="fas fa-camera"></i> Foto Produk
            </label>
            <div class="flex gap-2 mb-2">
                <button type="button" onclick="document.getElementById('kamera').click()" 
                        class="flex-1 py-3 bg-blue-500 text-white rounded-xl text-sm font-medium hover:bg-blue-600 transition">
                    <i class="fas fa-camera mr-1"></i> Ambil Foto
                </button>
                <button type="button" onclick="document.getElementById('galeri').click()" 
                        class="flex-1 py-3 bg-green-500 text-white rounded-xl text-sm font-medium hover:bg-green-600 transition">
                    <i class="fas fa-images mr-1"></i> Galeri
                </button>
            </div>
            <!-- Input kamera (langsung buka kamera) -->
            <input type="file" name="gambar" id="kamera" accept="image/*" capture="environment" 
                   class="hidden" onchange="previewFoto(this, 'preview-foto')">
            <!-- Input galeri -->
            <input type="file" id="galeri" accept="image/*" 
                   class="hidden" onchange="previewFoto(this, 'preview-foto')">
            
            <!-- Preview -->
            <div id="preview-container" class="hidden mt-2">
                <div class="relative inline-block">
                    <img id="preview-foto" src="" alt="Preview" class="w-32 h-32 object-cover rounded-xl border-2 border-dashed border-gray-300">
                    <button type="button" onclick="hapusFoto()" class="absolute -top-2 -right-2 bg-red-500 text-white w-6 h-6 rounded-full text-xs">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Tombol Simpan -->
        <button type="submit" name="simpan" class="w-full bg-primary text-white py-4 rounded-2xl font-bold text-lg hover:bg-indigo-700 transition shadow-lg shadow-indigo-200 mt-4">
            <i class="fas fa-save mr-2"></i> Simpan Produk
        </button>
    </form>

</div>

<script>
// ==================== BARCODE SCANNER ====================
let html5QrCode = null;

function startScanner() {
    document.getElementById('scanner-area').classList.remove('hidden');
    document.getElementById('btn-scan').classList.add('hidden');
    
    html5QrCode = new Html5Qrcode("reader");
    html5QrCode.start(
        { facingMode: "environment" },
        { fps: 10, qrbox: { width: 250, height: 150 } },
        (decodedText) => {
            // Berhasil scan
            document.getElementById('kode').value = decodedText;
            document.getElementById('nama').focus();
            stopScanner();
            // Vibrate (kalau HP support)
            if (navigator.vibrate) navigator.vibrate(200);
        },
        (errorMessage) => {
            // Scanning...
        }
    ).catch(err => {
        alert('Gagal membuka kamera: ' + err);
        stopScanner();
    });
}

function stopScanner() {
    if (html5QrCode) {
        html5QrCode.stop().then(() => {
            document.getElementById('scanner-area').classList.add('hidden');
            document.getElementById('btn-scan').classList.remove('hidden');
            html5QrCode = null;
        });
    }
}

// ==================== GENERATE KODE ====================
function generateKode() {
    const prefix = 'BRG';
    const random = Math.floor(Math.random() * 9000) + 1000;
    const timestamp = Date.now().toString().slice(-4);
    document.getElementById('kode').value = prefix + '-' + timestamp + random;
}

// ==================== PREVIEW FOTO ====================
function previewFoto(input, previewId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById(previewId).src = e.target.result;
            document.getElementById('preview-container').classList.remove('hidden');
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function hapusFoto() {
    document.getElementById('preview-foto').src = '';
    document.getElementById('preview-container').classList.add('hidden');
    document.getElementById('kamera').value = '';
    document.getElementById('galeri').value = '';
}

// ==================== AUTO FOKUS ====================
document.addEventListener('DOMContentLoaded', function() {
    // Fokus ke kode saat halaman dibuka
    setTimeout(() => {
        document.getElementById('btn-scan').focus();
    }, 500);
});

// Enter di kode → pindah ke nama
document.getElementById('kode').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        document.getElementById('nama').focus();
    }
});
</script>

</body>
</html>