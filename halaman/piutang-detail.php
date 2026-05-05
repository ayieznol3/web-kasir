<?php
$page = 'piutang';
$id = $_GET['id'] ?? 0;

// Ambil data pelanggan
$pl = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM pelanggan WHERE id = $id"));

if (!$pl) {
    echo "<script>alert('Pelanggan tidak ditemukan!'); window.location='?page=piutang';</script>";
    exit;
}

// Hitung ringkasan
$ringkasan = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT 
        COALESCE(SUM(CASE WHEN tipe = 'pembayaran' THEN jumlah ELSE 0 END), 0) as total_bayar,
        COALESCE(SUM(CASE WHEN tipe != 'pembayaran' THEN jumlah ELSE 0 END), 0) as total_hutang
    FROM piutang 
    WHERE pelanggan_id = $id
"));

// History piutang
$histori = mysqli_query($conn, "
    SELECT p.*, t.no_invoice, t.total as transaksi_total, u.nama as nama_user
    FROM piutang p
    LEFT JOIN transaksi t ON p.transaksi_id = t.id
    JOIN users u ON p.user_id = u.id
    WHERE p.pelanggan_id = $id
    ORDER BY p.created_at DESC
");
?>

<div class="space-y-6 max-w-5xl mx-auto">
    
    <!-- ==================== BACK BUTTON ==================== -->
    <a href="?page=piutang" class="inline-flex items-center gap-2 text-gray-400 hover:text-primary transition text-sm">
        <i class="fas fa-arrow-left"></i> Kembali ke Daftar Piutang
    </a>

    <!-- ==================== HEADER PELANGGAN ==================== -->
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="p-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                
                <!-- Info Pelanggan -->
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center text-2xl font-bold
                        <?= $pl['saldo_piutang'] > 500000 ? 'bg-red-100 text-red-600' : ($pl['saldo_piutang'] > 0 ? 'bg-yellow-100 text-yellow-600' : 'bg-green-100 text-green-600') ?>">
                        <?= strtoupper(substr($pl['nama'], 0, 1)) ?>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-dark"><?= htmlspecialchars($pl['nama']) ?></h1>
                        <div class="flex flex-wrap gap-4 mt-2 text-sm text-gray-500">
                            <?php if($pl['no_hp']): ?>
                            <span><i class="fas fa-phone mr-1"></i> <?= htmlspecialchars($pl['no_hp']) ?></span>
                            <?php endif; ?>
                            <?php if($pl['alamat']): ?>
                            <span><i class="fas fa-map-marker-alt mr-1"></i> <?= htmlspecialchars($pl['alamat']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Saldo -->
                <div class="text-right">
                    <p class="text-sm text-gray-500">Sisa Piutang</p>
                    <p class="text-3xl font-bold <?= $pl['saldo_piutang'] > 0 ? 'text-red-500' : 'text-green-500' ?>">
                        <?= rupiah($pl['saldo_piutang']) ?>
                    </p>
                  <div class="flex gap-2">
    <button onclick="showModalPinjaman()" 
            class="mt-3 inline-flex items-center gap-2 bg-yellow-500 text-white px-5 py-2.5 rounded-xl font-semibold hover:bg-yellow-600 transition shadow-lg shadow-yellow-200">
        <i class="fas fa-hand-holding-usd"></i> Pinjaman Manual
    </button>
    <?php if($pl['saldo_piutang'] > 0): ?>
    <button onclick="showModalBayar()" 
            class="mt-3 inline-flex items-center gap-2 bg-green-500 text-white px-5 py-2.5 rounded-xl font-semibold hover:bg-green-600 transition shadow-lg shadow-green-200">
        <i class="fas fa-money-bill"></i> Bayar Piutang
    </button>
    <?php endif; ?>
</div>
                </div>
            </div>
        </div>
        
        <!-- Ringkasan Cards -->
        <div class="grid grid-cols-3 border-t">
            <div class="p-4 text-center border-r">
                <p class="text-xs text-gray-500 uppercase">Total Hutang</p>
                <p class="text-xl font-bold text-red-600 mt-1"><?= rupiah($ringkasan['total_hutang']) ?></p>
            </div>
            <div class="p-4 text-center border-r">
                <p class="text-xs text-gray-500 uppercase">Total Dibayar</p>
                <p class="text-xl font-bold text-green-600 mt-1"><?= rupiah($ringkasan['total_bayar']) ?></p>
            </div>
            <div class="p-4 text-center">
                <p class="text-xs text-gray-500 uppercase">Sisa</p>
                <p class="text-xl font-bold <?= $pl['saldo_piutang'] > 0 ? 'text-red-600' : 'text-green-600' ?> mt-1">
                    <?= rupiah($pl['saldo_piutang']) ?>
                </p>
            </div>
        </div>
    </div>

    <!-- ==================== RIWAYAT PIUTANG ==================== -->
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="p-4 border-b flex items-center justify-between">
            <h3 class="font-bold text-dark">
                <i class="fas fa-history text-primary mr-2"></i>Riwayat Piutang
            </h3>
            <span class="text-sm text-gray-400"><?= mysqli_num_rows($histori) ?> catatan</span>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Tanggal</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Tipe</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Ref / Invoice</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Keterangan</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Jumlah</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Saldo</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php while($h = mysqli_fetch_assoc($histori)): ?>
                    <tr class="hover:bg-gray-50 transition text-sm">
                        
                        <!-- Tanggal -->
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="font-medium"><?= date('d/m/Y', strtotime($h['created_at'])) ?></div>
                            <div class="text-xs text-gray-400"><?= date('H:i', strtotime($h['created_at'])) ?></div>
                        </td>
                        
                        <!-- Tipe -->
                        <td class="px-4 py-3">
                            <?php if($h['tipe'] == 'transaksi'): ?>
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs rounded-full font-medium bg-blue-100 text-blue-700">
                                <i class="fas fa-shopping-cart"></i> Transaksi
                            </span>
                            <?php elseif($h['tipe'] == 'pinjaman'): ?>
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs rounded-full font-medium bg-yellow-100 text-yellow-700">
                                <i class="fas fa-hand-holding-usd"></i> Pinjaman
                            </span>
                            <?php else: ?>
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs rounded-full font-medium bg-green-100 text-green-700">
                                <i class="fas fa-check-circle"></i> Pembayaran
                            </span>
                            <?php endif; ?>
                        </td>
                        
                        <!-- Referensi -->
                        <td class="px-4 py-3">
                            <?php if($h['no_invoice']): ?>
                            <span class="font-mono text-xs font-medium text-primary">
                                <?= $h['no_invoice'] ?>
                            </span>
                            <?php elseif($h['no_referensi']): ?>
                            <span class="font-mono text-xs font-medium">
                                <?= $h['no_referensi'] ?>
                            </span>
                            <?php else: ?>
                            <span class="text-gray-400">-</span>
                            <?php endif; ?>
                        </td>
                        
                        <!-- Keterangan -->
                        <td class="px-4 py-3">
                            <p class="text-gray-600 max-w-xs truncate" title="<?= htmlspecialchars($h['keterangan']) ?>">
                                <?= htmlspecialchars($h['keterangan']) ?>
                            </p>
                            <p class="text-xs text-gray-400">oleh <?= $h['nama_user'] ?></p>
                        </td>
                        
                        <!-- Jumlah -->
                        <td class="px-4 py-3 text-right">
                            <span class="font-bold <?= $h['tipe'] == 'pembayaran' ? 'text-green-600' : 'text-red-600' ?>">
                                <?= $h['tipe'] == 'pembayaran' ? '-' : '+' ?>
                                <?= rupiah($h['jumlah']) ?>
                            </span>
                        </td>
                        
                        <!-- Saldo -->
                        <td class="px-4 py-3 text-right">
                            <span class="font-bold <?= $h['saldo_sesudah'] > 0 ? 'text-red-500' : 'text-green-500' ?>">
                                <?= rupiah($h['saldo_sesudah']) ?>
                            </span>
                        </td>
                        
                        <!-- Aksi -->
                        <!-- Aksi -->
<td class="px-4 py-3 text-center">
    <div class="flex justify-center gap-1">
        <?php if($h['transaksi_id']): ?>
        <button onclick="lihatDetailTransaksi(<?= $h['transaksi_id'] ?>)" 
                class="px-2 py-1 text-xs border border-gray-200 rounded-lg hover:bg-blue-50 hover:text-blue-600 transition"
                title="Lihat Detail Transaksi">
            <i class="fas fa-eye"></i> Detail
        </button>
        <button onclick="cetakStruk(<?= $h['transaksi_id'] ?>)" 
                class="px-2 py-1 text-xs border border-gray-200 rounded-lg hover:bg-green-50 hover:text-green-600 transition"
                title="Cetak Struk">
            <i class="fas fa-print"></i>
        </button>
        <?php else: ?>
        <!-- Tombol Kwitansi untuk pinjaman manual & pembayaran -->
        <button onclick="cetakKwitansi(<?= $h['id'] ?>)" 
                class="px-2 py-1 text-xs border border-green-200 text-green-600 rounded-lg hover:bg-green-50 transition"
                title="Cetak Kwitansi">
            <i class="fas fa-file-invoice"></i> Kwitansi
        </button>
        <?php endif; ?>
    </div>
</td>
                    </tr>
                    <?php endwhile; ?>
                    
                    <?php if(mysqli_num_rows($histori) == 0): ?>
                    <tr>
                        <td colspan="7">
                            <div class="text-center py-12 text-gray-400">
                                <i class="fas fa-history text-3xl mb-2"></i>
                                <p>Belum ada riwayat piutang</p>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- ==================== MODAL BAYAR PIUTANG ==================== -->
<div id="modal-bayar" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl">
        <div class="p-6 border-b flex items-center justify-between">
            <h3 class="text-lg font-bold">
                <i class="fas fa-money-bill text-green-500 mr-2"></i>Bayar Piutang
            </h3>
            <button onclick="closeModalBayar()" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
        </div>
        
        <form action="proses/piutang_bayar.php" method="post" class="p-6 space-y-4">
            <input type="hidden" name="pelanggan_id" value="<?= $id ?>">
            
            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                <p class="text-sm text-yellow-700">
                    <strong>Sisa Piutang:</strong> 
                    <span class="text-xl font-bold text-red-500"><?= rupiah($pl['saldo_piutang']) ?></span>
                </p>
                <p class="text-xs text-yellow-600 mt-1">Pelanggan: <?= htmlspecialchars($pl['nama']) ?></p>
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Jumlah Pembayaran *</label>
                <div class="relative">
                    <span class="absolute left-4 top-2.5 text-gray-400 font-medium">Rp</span>
                    <input type="number" name="jumlah" id="jumlah-bayar" required 
                           min="1" max="<?= $pl['saldo_piutang'] ?>"
                           class="w-full pl-12 pr-4 py-2.5 border border-gray-200 rounded-xl text-lg font-bold focus:ring-2 focus:ring-green-500 outline-none transition"
                           placeholder="0"
                           onkeyup="hitungSisaBayar()">
                </div>
                <p class="text-xs text-gray-400 mt-1">Maksimal: <?= rupiah($pl['saldo_piutang']) ?></p>
            </div>
            
            <div id="preview-sisa" class="hidden bg-gray-50 rounded-xl p-3">
                <div class="flex justify-between text-sm">
                    <span>Sisa setelah bayar:</span>
                    <span id="sisa-after" class="font-bold">-</span>
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Keterangan</label>
                <input type="text" name="keterangan" value="Pembayaran piutang" 
                       class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 outline-none transition">
            </div>
            
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeModalBayar()" 
                        class="flex-1 py-2.5 border border-gray-200 rounded-xl font-medium hover:bg-gray-50 transition">
                    Batal
                </button>
                <button type="submit" class="flex-1 py-2.5 bg-green-500 text-white rounded-xl font-semibold hover:bg-green-600 transition">
                    <i class="fas fa-check mr-1"></i> Simpan Pembayaran
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ==================== MODAL PINJAMAN MANUAL ==================== -->
<div id="modal-pinjaman" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl">
        <div class="p-6 border-b flex items-center justify-between">
            <h3 class="text-lg font-bold">
                <i class="fas fa-hand-holding-usd text-yellow-500 mr-2"></i>Pinjaman Manual
            </h3>
            <button onclick="closeModalPinjaman()" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
        </div>
        
        <form action="proses/piutang_tambah.php" method="post" class="p-6 space-y-4">
            <input type="hidden" name="pelanggan_id" value="<?= $id ?>">
            
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                <p class="text-sm text-blue-700">
                    <strong>Pelanggan:</strong> <?= htmlspecialchars($pl['nama']) ?>
                </p>
                <p class="text-xs text-blue-600 mt-1">
                    Saldo piutang saat ini: 
                    <span class="font-bold <?= $pl['saldo_piutang'] > 0 ? 'text-red-500' : 'text-green-500' ?>">
                        <?= rupiah($pl['saldo_piutang']) ?>
                    </span>
                </p>
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    Jumlah Pinjaman <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <span class="absolute left-4 top-2.5 text-gray-400 font-medium">Rp</span>
                    <input type="number" name="jumlah" id="jumlah-pinjaman" required min="1000"
                           class="w-full pl-12 pr-4 py-2.5 border border-gray-200 rounded-xl text-lg font-bold focus:ring-2 focus:ring-yellow-500 outline-none transition"
                           placeholder="0"
                           onkeyup="hitungPreviewPinjaman()">
                </div>
                <p class="text-xs text-gray-400 mt-1">Minimal Rp 1.000</p>
            </div>
            
            <div id="preview-pinjaman" class="hidden bg-yellow-50 border border-yellow-200 rounded-xl p-3">
                <div class="flex justify-between text-sm">
                    <span>Saldo setelah pinjaman:</span>
                    <span id="saldo-after-pinjaman" class="font-bold text-red-600">-</span>
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    Keterangan <span class="text-red-500">*</span>
                </label>
                <textarea name="keterangan" rows="2" required
                          class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-yellow-500 outline-none transition resize-none"
                          placeholder="Alasan pinjaman..."></textarea>
            </div>
            
            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-3 text-sm text-yellow-700">
                <i class="fas fa-info-circle mr-1"></i>
                Pinjaman ini akan menambah saldo piutang pelanggan.
            </div>
            
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeModalPinjaman()" 
                        class="flex-1 py-2.5 border border-gray-200 rounded-xl font-medium text-gray-600 hover:bg-gray-50 transition">
                    Batal
                </button>
                <button type="submit" class="flex-1 py-2.5 bg-yellow-500 text-white rounded-xl font-semibold hover:bg-yellow-600 transition">
                    <i class="fas fa-save mr-1"></i> Simpan Pinjaman
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ==================== MODAL DETAIL TRANSAKSI ==================== -->
<div id="modal-detail-transaksi" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-2xl max-h-[85vh] overflow-y-auto shadow-2xl">
        <div id="modal-detail-content">
            <div class="flex items-center justify-center py-20">
                <i class="fas fa-spinner fa-spin text-3xl text-primary"></i>
            </div>
        </div>
    </div>
</div>
<script>

function cetakKwitansi(piutang_id) {
    window.open('halaman/kwitansi.php?id=' + piutang_id, '_blank', 'width=500,height=600');
}

// ==================== MODAL BAYAR ====================
function showModalBayar() {
    document.getElementById('modal-bayar').classList.remove('hidden');
    document.getElementById('jumlah-bayar').focus();
}

function closeModalBayar() {
    document.getElementById('modal-bayar').classList.add('hidden');
}

function hitungSisaBayar() {
    const bayar = parseInt(document.getElementById('jumlah-bayar').value) || 0;
    const sisa = <?= $pl['saldo_piutang'] ?>;
    const after = sisa - bayar;
    
    const preview = document.getElementById('preview-sisa');
    if (bayar > 0) {
        preview.classList.remove('hidden');
        const sisaEl = document.getElementById('sisa-after');
        sisaEl.textContent = 'Rp ' + after.toLocaleString();
        sisaEl.className = after <= 0 ? 'font-bold text-green-600' : 'font-bold text-red-600';
    } else {
        preview.classList.add('hidden');
    }
}

// ==================== MODAL PINJAMAN ====================
function showModalPinjaman() {
    document.getElementById('modal-pinjaman').classList.remove('hidden');
    document.getElementById('jumlah-pinjaman').focus();
}

function closeModalPinjaman() {
    document.getElementById('modal-pinjaman').classList.add('hidden');
}

function hitungPreviewPinjaman() {
    const jumlah = parseInt(document.getElementById('jumlah-pinjaman').value) || 0;
    const saldoSekarang = <?= $pl['saldo_piutang'] ?>;
    const after = saldoSekarang + jumlah;
    
    const preview = document.getElementById('preview-pinjaman');
    if (jumlah > 0) {
        preview.classList.remove('hidden');
        document.getElementById('saldo-after-pinjaman').textContent = 'Rp ' + after.toLocaleString();
    } else {
        preview.classList.add('hidden');
    }
}

// ==================== MODAL DETAIL TRANSAKSI ====================
function lihatDetailTransaksi(transaksi_id) {
    const modal = document.getElementById('modal-detail-transaksi');
    const content = document.getElementById('modal-detail-content');
    
    modal.classList.remove('hidden');
    content.innerHTML = '<div class="flex items-center justify-center py-20"><i class="fas fa-spinner fa-spin text-3xl text-primary"></i></div>';
    
    fetch('ajax/get_transaksi_detail.php?id=' + transaksi_id)
        .then(res => res.text())
        .then(html => { content.innerHTML = html; })
        .catch(err => {
            content.innerHTML = '<div class="text-center py-10 text-red-500">Gagal memuat detail</div>';
        });
}

function cetakStruk(transaksi_id) {
    window.open('halaman/struk.php?id=' + transaksi_id, '_blank', 'width=400,height=600');
}

// ==================== TUTUP MODAL DENGAN KLIK DI LUAR ====================
document.getElementById('modal-bayar').addEventListener('click', function(e) {
    if (e.target === this) closeModalBayar();
});

document.getElementById('modal-pinjaman').addEventListener('click', function(e) {
    if (e.target === this) closeModalPinjaman();
});

document.getElementById('modal-detail-transaksi').addEventListener('click', function(e) {
    if (e.target === this) this.classList.add('hidden');
});

// ==================== TUTUP SEMUA MODAL DENGAN ESCAPE ====================
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.getElementById('modal-bayar').classList.add('hidden');
        document.getElementById('modal-pinjaman').classList.add('hidden');
        document.getElementById('modal-detail-transaksi').classList.add('hidden');
    }
});
</script>