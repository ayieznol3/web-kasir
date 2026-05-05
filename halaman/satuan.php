<?php
$page = 'produk';
$produk_id = $_GET['produk_id'] ?? 0;

// Ambil data produk
$produk = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM produk WHERE id = $produk_id"));

if (!$produk) {
    echo "<script>alert('Produk tidak ditemukan!'); window.location='?page=produk';</script>";
    exit;
}

// Ambil data satuan yang sudah ada
$satuan_list = mysqli_query($conn, "SELECT * FROM satuan WHERE produk_id = $produk_id ORDER BY isi_satuan ASC");

// Ambil data grosir yang sudah ada
$grosir_list = mysqli_query($conn, "SELECT * FROM grosir WHERE produk_id = $produk_id ORDER BY min_qty ASC");

$title = 'Atur Paket & Grosir: ' . $produk['nama'];
?>

<div class="max-w-5xl mx-auto space-y-6">
    
    <!-- Breadcrumb -->
    <div class="flex items-center gap-2 text-sm text-gray-500">
        <a href="?page=produk" class="hover:text-primary">Produk</a>
        <i class="fas fa-chevron-right text-xs"></i>
        <span class="text-gray-700 font-medium">Atur Paket: <?= $produk['nama'] ?></span>
    </div>

    <!-- Info Produk -->
    <div class="bg-white rounded-2xl shadow-sm p-6">
        <div class="flex items-center gap-4">
            <img src="<?= getGambar($produk['gambar']) ?>" class="w-16 h-16 object-cover rounded-xl" onerror="this.src='uploads/produk/default.png'">
            <div>
                <h2 class="text-xl font-bold"><?= $produk['nama'] ?></h2>
                <p class="text-sm text-gray-500">
                    Harga Ecer: <?= rupiah($produk['harga_jual']) ?> / <?= $produk['satuan_dasar'] ?> | 
                    Stok: <?= $produk['stok_dasar'] ?> <?= $produk['satuan_dasar'] ?>
                </p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- ==================== PANEL SATUAN (PAKET) ==================== -->
        <div class="bg-white rounded-2xl shadow-sm">
            <div class="p-6 border-b flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-lg flex items-center gap-2">
                        <i class="fas fa-layer-group text-primary"></i> Satuan / Paket
                    </h3>
                    <p class="text-xs text-gray-400 mt-1">Contoh: 1 Pak = 10 Bungkus, 1 Dus = 40 Pcs</p>
                </div>
            </div>
            
            <div class="p-6">
                <!-- Form tambah satuan -->
                <form action="proses/satuan_simpan.php" method="post" class="bg-gray-50 rounded-xl p-4 mb-4 space-y-3">
                    <input type="hidden" name="produk_id" value="<?= $produk_id ?>">
                    <p class="text-sm font-semibold text-gray-700">+ Tambah Satuan Baru</p>
                    
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="text-xs text-gray-500">Nama Satuan</label>
                            <input type="text" name="nama_satuan" required 
                                   class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-primary outline-none"
                                   placeholder="Pak / Dus / Slop">
                        </div>
                        <div>
                            <label class="text-xs text-gray-500">Isi (<?= $produk['satuan_dasar'] ?>)</label>
                            <input type="number" name="isi_satuan" required min="1"
                                   class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-primary outline-none"
                                   placeholder="10">
                        </div>
                        <div>
                            <label class="text-xs text-gray-500">Harga Jual (opsional)</label>
                            <input type="number" name="harga_jual"
                                   class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-primary outline-none"
                                   placeholder="Otomatis">
                        </div>
                    </div>
                    
                    <p class="text-xs text-gray-400">⚠️ Kosongkan harga untuk hitung otomatis (isi × harga ecer)</p>
                    
                    <button type="submit" class="w-full bg-primary text-white py-2 rounded-lg text-sm font-semibold hover:bg-indigo-700 transition">
                        <i class="fas fa-plus mr-1"></i> Tambah Satuan
                    </button>
                </form>

                <!-- List satuan -->
                <div class="space-y-2">
                    <?php if(mysqli_num_rows($satuan_list) > 0): ?>
                        <?php while($s = mysqli_fetch_assoc($satuan_list)): 
                            $harga_otomatis = $s['isi_satuan'] * $produk['harga_jual'];
                            $harga_display = $s['harga_jual'] ?? $harga_otomatis;
                        ?>
                        <div class="flex items-center justify-between p-3 border rounded-xl hover:bg-gray-50 transition group">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center text-blue-600 font-bold text-sm">
                                    <?= strtoupper(substr($s['nama_satuan'], 0, 2)) ?>
                                </div>
                                <div>
                                    <p class="font-semibold text-sm">1 <?= $s['nama_satuan'] ?></p>
                                    <p class="text-xs text-gray-400">= <?= $s['isi_satuan'] ?> <?= $produk['satuan_dasar'] ?></p>
                                </div>
                            </div>
                            <div class="text-right flex items-center gap-3">
                                <div>
                                    <p class="font-bold text-sm"><?= rupiah($harga_display) ?></p>
                                    <p class="text-xs text-gray-400">/ <?= $s['nama_satuan'] ?></p>
                                    <?php if($s['harga_jual']): ?>
                                    <p class="text-xs text-green-500">Harga custom</p>
                                    <?php else: ?>
                                    <p class="text-xs text-gray-400">Auto: <?= rupiah($produk['harga_jual']) ?> × <?= $s['isi_satuan'] ?></p>
                                    <?php endif; ?>
                                </div>
                                <a href="proses/satuan_hapus.php?id=<?= $s['id'] ?>&produk_id=<?= $produk_id ?>" 
                                   class="text-red-400 hover:text-red-600 opacity-0 group-hover:opacity-100 transition"
                                   onclick="return confirm('Hapus satuan ini?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-center py-8 text-gray-400">
                            <i class="fas fa-boxes text-3xl mb-2"></i>
                            <p class="text-sm">Belum ada satuan paket</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- ==================== PANEL GROSIR ==================== -->
        <div class="bg-white rounded-2xl shadow-sm">
            <div class="p-6 border-b">
                <h3 class="font-bold text-lg flex items-center gap-2">
                    <i class="fas fa-tags text-green-500"></i> Harga Grosir
                </h3>
                <p class="text-xs text-gray-400 mt-1">Diskon berdasarkan jumlah pembelian</p>
            </div>
            
            <div class="p-6">
                <!-- Form tambah grosir -->
                <form action="proses/grosir_simpan.php" method="post" class="bg-gray-50 rounded-xl p-4 mb-4 space-y-3">
                    <input type="hidden" name="produk_id" value="<?= $produk_id ?>">
                    <p class="text-sm font-semibold text-gray-700">+ Tambah Aturan Grosir</p>
                    
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="text-xs text-gray-500">Minimal Qty</label>
                            <input type="number" name="min_qty" required min="1"
                                   class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-primary outline-none"
                                   placeholder="50">
                        </div>
                        <div>
                            <label class="text-xs text-gray-500">Tipe Diskon</label>
                            <select name="tipe_diskon" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-primary outline-none">
                                <option value="persen">Persen (%)</option>
                                <option value="nominal">Nominal (Rp)</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs text-gray-500">Nilai Diskon</label>
                            <input type="number" name="nilai_diskon" required min="1"
                                   class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-primary outline-none"
                                   placeholder="5">
                        </div>
                    </div>
                    
                    <button type="submit" class="w-full bg-green-500 text-white py-2 rounded-lg text-sm font-semibold hover:bg-green-600 transition">
                        <i class="fas fa-plus mr-1"></i> Tambah Grosir
                    </button>
                </form>

                <!-- List grosir -->
                <div class="space-y-2">
                    <?php if(mysqli_num_rows($grosir_list) > 0): ?>
                        <?php while($g = mysqli_fetch_assoc($grosir_list)): ?>
                        <div class="flex items-center justify-between p-3 border rounded-xl hover:bg-gray-50 transition group">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center text-green-600">
                                    <i class="fas fa-tag"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-sm">Min beli <?= $g['min_qty'] ?> <?= $produk['satuan_dasar'] ?></p>
                                    <p class="text-xs text-gray-500">
                                        <?php if($g['tipe_diskon'] == 'persen'): ?>
                                            Diskon <?= $g['nilai_diskon'] ?>%
                                        <?php else: ?>
                                            Potong Rp <?= number_format($g['nilai_diskon']) ?>/item
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                            <a href="proses/grosir_hapus.php?id=<?= $g['id'] ?>&produk_id=<?= $produk_id ?>" 
                               class="text-red-400 hover:text-red-600 opacity-0 group-hover:opacity-100 transition"
                               onclick="return confirm('Hapus aturan grosir ini?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-center py-8 text-gray-400">
                            <i class="fas fa-tags text-3xl mb-2"></i>
                            <p class="text-sm">Belum ada aturan grosir</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
    
    <!-- Tombol kembali -->
    <div class="text-center">
        <a href="?page=produk" class="inline-flex items-center gap-2 text-gray-500 hover:text-primary transition">
            <i class="fas fa-arrow-left"></i> Kembali ke Produk
        </a>
    </div>
</div>