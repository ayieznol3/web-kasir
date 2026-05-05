<?php
$page = 'pengaturan';

// Ambil semua pengaturan
$setting = [];
$result = mysqli_query($conn, "SELECT * FROM pengaturan");
while($row = mysqli_fetch_assoc($result)) {
    $setting[$row['kunci']] = $row['nilai'];
}
?>

<div class="space-y-6 max-w-3xl mx-auto">
    
    <div>
        <h1 class="text-2xl font-bold text-dark">
            <i class="fas fa-cog text-primary mr-2"></i>Pengaturan Toko
        </h1>
        <p class="text-sm text-gray-500 mt-1">Atur informasi toko, printer, dan backup</p>
    </div>

    <form action="proses/pengaturan_simpan.php" method="post" class="space-y-6">
        
        <!-- ==================== INFORMASI TOKO ==================== -->
        <div class="bg-white rounded-2xl shadow-sm">
            <div class="p-6 border-b flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-store text-blue-500 text-xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-lg">📝 Informasi Toko</h3>
                    <p class="text-sm text-gray-400">Tampil di struk dan header aplikasi</p>
                </div>
            </div>
            
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Toko</label>
                    <input type="text" name="toko_nama" value="<?= htmlspecialchars($setting['toko_nama'] ?? '') ?>" 
                           class="w-full px-4 py-2.5 border rounded-xl focus:ring-2 focus:ring-primary outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Alamat</label>
                    <textarea name="toko_alamat" rows="2" 
                              class="w-full px-4 py-2.5 border rounded-xl focus:ring-2 focus:ring-primary outline-none"><?= htmlspecialchars($setting['toko_alamat'] ?? '') ?></textarea>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Nomor Telepon</label>
                    <input type="text" name="toko_telp" value="<?= htmlspecialchars($setting['toko_telp'] ?? '') ?>" 
                           class="w-full px-4 py-2.5 border rounded-xl focus:ring-2 focus:ring-primary outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Footer Struk</label>
                    <textarea name="struk_footer" rows="3" 
                              class="w-full px-4 py-2.5 border rounded-xl focus:ring-2 focus:ring-primary outline-none"><?= htmlspecialchars($setting['struk_footer'] ?? '') ?></textarea>
                    <p class="text-xs text-gray-400 mt-1">Gunakan \n untuk baris baru</p>
                </div>
            </div>
        </div>

        <!-- ==================== PRINTER ==================== -->
        <div class="bg-white rounded-2xl shadow-sm">
            <div class="p-6 border-b flex items-center gap-4">
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-print text-green-500 text-xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-lg">🖨️ Printer</h3>
                    <p class="text-sm text-gray-400">Pengaturan cetak struk</p>
                </div>
            </div>
            
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Ukuran Kertas</label>
                    <select name="printer_ukuran" class="w-full px-4 py-2.5 border rounded-xl focus:ring-2 focus:ring-primary outline-none">
                        <option value="58mm" <?= ($setting['printer_ukuran'] ?? '') == '58mm' ? 'selected' : '' ?>>58mm (Thermal Mini)</option>
                        <option value="80mm" <?= ($setting['printer_ukuran'] ?? '') == '80mm' ? 'selected' : '' ?>>80mm (Thermal Standard)</option>
                    </select>
                </div>
                <div class="flex items-center gap-3">
                    <input type="checkbox" name="printer_auto" value="1" <?= ($setting['printer_auto'] ?? '0') == '1' ? 'checked' : '' ?> 
                           class="w-5 h-5 text-primary rounded focus:ring-primary">
                    <label class="text-sm text-gray-700">Auto print setelah transaksi</label>
                </div>
            </div>
        </div>

        <!-- ==================== BACKUP ==================== -->
        <div class="bg-white rounded-2xl shadow-sm">
            <div class="p-6 border-b flex items-center gap-4">
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-database text-purple-500 text-xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-lg">💾 Backup</h3>
                    <p class="text-sm text-gray-400">Pengaturan backup database</p>
                </div>
            </div>
            
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Folder Backup</label>
                    <input type="text" name="backup_path" value="<?= htmlspecialchars($setting['backup_path'] ?? 'backups/') ?>" 
                           class="w-full px-4 py-2.5 border rounded-xl focus:ring-2 focus:ring-primary outline-none">
                    <p class="text-xs text-gray-400 mt-1">Relatif dari folder aplikasi</p>
                </div>
            </div>
        </div>

        <!-- ==================== TOMBOL ==================== -->
        <div class="flex justify-end gap-3">
            <a href="?page=dashboard" class="px-6 py-2.5 border rounded-xl text-gray-600 hover:bg-gray-50 transition">Batal</a>
            <button type="submit" class="px-8 py-2.5 bg-primary text-white rounded-xl font-semibold hover:bg-indigo-700 transition">
                <i class="fas fa-save mr-2"></i> Simpan Pengaturan
            </button>
        </div>
        
    </form>
</div>