<?php
$page = 'laporan';

$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');

// Pendapatan
$pendapatan = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT 
        COALESCE(SUM(total),0) as total_pendapatan,
        COUNT(*) as total_transaksi
    FROM transaksi 
    WHERE MONTH(created_at) = $bulan AND YEAR(created_at) = $tahun 
        AND status != 'void'
"));

// HPP
$hpp = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COALESCE(SUM(td.qty_dasar * p.harga_beli),0) as total_hpp
    FROM transaksi_detail td
    JOIN transaksi t ON td.transaksi_id = t.id
    JOIN produk p ON td.produk_id = p.id
    WHERE MONTH(t.created_at) = $bulan AND YEAR(t.created_at) = $tahun 
        AND t.status != 'void'
"));

// Biaya Operasional
$biaya = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT 
        COALESCE(SUM(jumlah),0) as total_biaya,
        COUNT(*) as total_item
    FROM pengeluaran 
    WHERE MONTH(created_at) = $bulan AND YEAR(created_at) = $tahun
"));

// Total Restok (bukan biaya, tapi info tambahan)
$restok = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COALESCE(SUM(total_harga),0) as total_restok
    FROM pembelian 
    WHERE MONTH(created_at) = $bulan AND YEAR(created_at) = $tahun
"));

// Hitung laba rugi
$laba_kotor = $pendapatan['total_pendapatan'] - $hpp['total_hpp'];
$laba_bersih = $laba_kotor - $biaya['total_biaya'];

$bulan_nama = ['', 'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
?>

<div class="space-y-6">
    <h1 class="text-2xl font-bold text-dark">
        <i class="fas fa-balance-scale text-primary mr-2"></i>Laporan Laba Rugi
    </h1>

    <!-- Filter Bulan -->
    <div class="bg-white rounded-2xl shadow-sm p-4">
        <form method="get" class="flex flex-wrap gap-4 items-end">
            <input type="hidden" name="page" value="laporan">
            <input type="hidden" name="jenis" value="labarugi">
            
            <div>
                <label class="text-xs text-gray-500 block mb-1">Bulan</label>
                <select name="bulan" class="px-4 py-2 border rounded-xl">
                    <?php for($i=1; $i<=12; $i++): ?>
                    <option value="<?= $i ?>" <?= $bulan == $i ? 'selected' : '' ?>><?= $bulan_nama[$i] ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div>
                <label class="text-xs text-gray-500 block mb-1">Tahun</label>
                <input type="number" name="tahun" value="<?= $tahun ?>" class="px-4 py-2 border rounded-xl w-24">
            </div>
            <button type="submit" class="bg-primary text-white px-6 py-2 rounded-xl">
                <i class="fas fa-filter mr-1"></i> Tampilkan
            </button>
        </form>
    </div>

    <!-- Header Info -->
    <div class="bg-white rounded-2xl shadow-sm p-6 text-center">
        <p class="text-gray-400 text-sm">LAPORAN LABA RUGI</p>
        <h2 class="text-2xl font-bold mt-1"><?= $bulan_nama[(int)$bulan] ?> <?= $tahun ?></h2>
    </div>

    <!-- Ringkasan -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl shadow-sm p-6 border-l-4 border-blue-500">
            <p class="text-sm text-gray-500">Pendapatan</p>
            <p class="text-3xl font-bold text-blue-600"><?= rupiah($pendapatan['total_pendapatan']) ?></p>
            <p class="text-xs text-gray-400 mt-1"><?= $pendapatan['total_transaksi'] ?> transaksi</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm p-6 border-l-4 <?= $laba_bersih >= 0 ? 'border-green-500' : 'border-red-500' ?>">
            <p class="text-sm text-gray-500">Laba Bersih</p>
            <p class="text-3xl font-bold <?= $laba_bersih >= 0 ? 'text-green-600' : 'text-red-600' ?>">
                <?= rupiah($laba_bersih) ?>
            </p>
            <p class="text-xs text-gray-400 mt-1">
                Margin: <?= $pendapatan['total_pendapatan'] > 0 ? round(($laba_bersih/$pendapatan['total_pendapatan'])*100, 1) : 0 ?>%
            </p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm p-6 border-l-4 border-purple-500">
            <p class="text-sm text-gray-500">Total Biaya</p>
            <p class="text-3xl font-bold text-purple-600"><?= rupiah($biaya['total_biaya']) ?></p>
            <p class="text-xs text-gray-400 mt-1">Operasional</p>
        </div>
    </div>

    <!-- Detail -->
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="p-6">
            <h3 class="font-bold text-lg mb-4">Detail Perhitungan</h3>
            
            <div class="space-y-4 max-w-lg">
                <!-- Pendapatan -->
                <div class="flex justify-between py-3 border-b">
                    <span class="text-gray-600">📈 Pendapatan Penjualan</span>
                    <span class="font-bold text-blue-600"><?= rupiah($pendapatan['total_pendapatan']) ?></span>
                </div>
                
                <!-- HPP -->
                <div class="flex justify-between py-3 border-b">
                    <span class="text-gray-600">📦 Harga Pokok Penjualan (HPP)</span>
                    <span class="font-bold text-red-600">- <?= rupiah($hpp['total_hpp']) ?></span>
                </div>
                
                <!-- Laba Kotor -->
                <div class="flex justify-between py-3 border-b bg-gray-50 px-3 rounded-lg">
                    <span class="font-semibold">Laba Kotor</span>
                    <span class="font-bold <?= $laba_kotor >= 0 ? 'text-green-600' : 'text-red-600' ?>">
                        <?= rupiah($laba_kotor) ?>
                    </span>
                </div>
                
                <!-- Biaya Operasional -->
                <div class="flex justify-between py-3 border-b">
                    <span class="text-gray-600">💸 Biaya Operasional</span>
                    <span class="font-bold text-red-600">- <?= rupiah($biaya['total_biaya']) ?></span>
                </div>
                
                <!-- Laba Bersih -->
                <div class="flex justify-between py-3 text-lg bg-gray-50 px-3 rounded-lg">
                    <span class="font-bold">Laba Bersih</span>
                    <span class="font-bold <?= $laba_bersih >= 0 ? 'text-green-600' : 'text-red-600' ?>">
                        <?= rupiah($laba_bersih) ?>
                    </span>
                </div>
            </div>
            
            <div class="mt-6 p-4 bg-gray-50 rounded-xl text-sm text-gray-500">
                <p class="font-semibold mb-2">ℹ️ Catatan:</p>
                <ul class="list-disc list-inside space-y-1">
                    <li>HPP dihitung dari harga beli rata-rata per produk</li>
                    <li>Biaya operasional termasuk: listrik, gaji, transport, dll</li>
                    <li>Restok bulan ini: <?= rupiah($restok['total_restok']) ?> (tidak masuk biaya)</li>
                </ul>
            </div>
        </div>
    </div>
</div>