<?php
$page = 'backup';

// Cek folder backup
$folder_ada = is_dir(BACKUP_PATH);

// Ambil daftar file backup
$file_list = [];
if ($folder_ada) {
    $files = glob(BACKUP_PATH . 'backup_*.sql');
    rsort($files); // Urut terbaru
    
    foreach ($files as $file) {
        $file_list[] = [
            'nama' => basename($file),
            'size' => round(filesize($file) / 1024, 1) . ' KB',
            'tanggal' => date('d/m/Y H:i:s', filemtime($file)),
            'path' => $file
        ];
    }
}
?>

<div class="space-y-6 max-w-4xl mx-auto">
    
    <!-- Header -->
    <div>
        <h1 class="text-2xl font-bold text-dark">
            <i class="fas fa-database text-primary mr-2"></i>Backup Database
        </h1>
        <p class="text-sm text-gray-500 mt-1">Simpan data ke Google Drive</p>
    </div>

    <!-- Status Folder -->
    <div class="bg-white rounded-2xl shadow-sm p-6">
        <div class="flex items-center gap-4 mb-6">
            <div class="w-12 h-12 <?= $folder_ada ? 'bg-green-100' : 'bg-red-100' ?> rounded-xl flex items-center justify-center">
                <i class="fas fa-folder <?= $folder_ada ? 'text-green-500' : 'text-red-500' ?> text-xl"></i>
            </div>
            <div>
                <h3 class="font-bold">Folder Backup</h3>
                <p class="text-sm <?= $folder_ada ? 'text-green-600' : 'text-red-600' ?>">
                    <?php if($folder_ada): ?>
                    ✅ Terhubung: <?= BACKUP_PATH ?>
                    <?php else: ?>
                    ❌ Folder tidak ditemukan: <?= BACKUP_PATH ?>
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <!-- Tombol Backup -->
        <form action="proses/backup_proses.php" method="post" class="mb-6">
            <button type="submit" name="backup" 
                    class="w-full bg-primary text-white py-4 rounded-xl font-bold text-lg hover:bg-indigo-700 transition shadow-lg shadow-indigo-200">
                <i class="fas fa-download mr-2"></i> Backup Database Sekarang
            </button>
        </form>

        <!-- Info -->
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-sm text-blue-700">
            <h4 class="font-bold mb-2"><i class="fas fa-info-circle mr-2"></i>Informasi</h4>
            <ul class="list-disc list-inside space-y-1 text-xs">
                <li>File backup disimpan di folder <strong>Google Drive/Backup Kasir/</strong></li>
                <li>File akan otomatis sync ke cloud Google Drive</li>
                <li>Format nama file: <strong>backup_YYYYMMDD_HHMMSS.sql</strong></li>
                <li>Backup bisa di-restore via phpMyAdmin (Import)</li>
                <li>Disarankan backup setiap hari setelah tutup toko</li>
            </ul>
        </div>
    </div>

    <!-- Daftar File Backup -->
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="p-4 border-b">
            <h3 class="font-bold">
                <i class="fas fa-history mr-2"></i>Riwayat Backup 
                <span class="text-sm text-gray-400">(<?= count($file_list) ?> file)</span>
            </h3>
        </div>
        
        <?php if(count($file_list) > 0): ?>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs uppercase">Nama File</th>
                        <th class="px-4 py-3 text-center text-xs uppercase">Ukuran</th>
                        <th class="px-4 py-3 text-left text-xs uppercase">Tanggal</th>
                        <th class="px-4 py-3 text-center text-xs uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php foreach($file_list as $file): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-xs"><?= $file['nama'] ?></td>
                        <td class="px-4 py-3 text-center"><?= $file['size'] ?></td>
                        <td class="px-4 py-3 text-xs text-gray-500"><?= $file['tanggal'] ?></td>
                        <td class="px-4 py-3 text-center">
                            <a href="proses/backup_download.php?file=<?= urlencode($file['nama']) ?>" 
                               class="px-3 py-1 text-xs bg-green-500 text-white rounded-lg hover:bg-green-600 transition">
                                <i class="fas fa-download mr-1"></i> Download
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="text-center py-12 text-gray-400">
            <i class="fas fa-archive text-4xl mb-3"></i>
            <p>Belum ada file backup</p>
            <p class="text-sm mt-1">Klik tombol backup di atas</p>
        </div>
        <?php endif; ?>
    </div>

    <!-- Cara Restore -->
    <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-5 text-sm text-yellow-700">
        <h4 class="font-bold mb-2"><i class="fas fa-undo mr-2"></i>Cara Restore (Mengembalikan Data)</h4>
        <ol class="list-decimal list-inside space-y-1 text-xs">
            <li>Buka <strong>phpMyAdmin</strong></li>
            <li>Pilih database <strong>aplikasi_kasir</strong></li>
            <li>Klik tab <strong>Import</strong></li>
            <li>Pilih file backup <strong>.sql</strong></li>
            <li>Klik <strong>Go</strong> untuk memulai restore</li>
            <li>Tunggu sampai selesai</li>
        </ol>
    </div>
</div>