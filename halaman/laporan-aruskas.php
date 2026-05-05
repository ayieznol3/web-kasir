<?php
$page = 'laporan';
$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');

// Kas Masuk (Penjualan + Bayar Piutang)
$kas_masuk_penjualan = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COALESCE(SUM(bayar - kembalian),0) as total 
    FROM transaksi 
    WHERE MONTH(created_at) = $bulan AND YEAR(created_at) = $tahun 
        AND status != 'void'
"));

$kas_masuk_piutang = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COALESCE(SUM(jumlah),0) as total 
    FROM piutang 
    WHERE tipe = 'pembayaran' 
    AND MONTH(created_at) = $bulan AND YEAR(created_at) = $tahun
"));

// Kas Keluar
$kas_keluar_restok = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COALESCE(SUM(total_harga),0) as total 
    FROM pembelian 
    WHERE MONTH(created_at) = $bulan AND YEAR(created_at) = $tahun
"));

$kas_keluar_biaya = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COALESCE(SUM(jumlah),0) as total 
    FROM pengeluaran 
    WHERE MONTH(created_at) = $bulan AND YEAR(created_at) = $tahun
"));

$total_masuk = $kas_masuk_penjualan['total'] + $kas_masuk_piutang['total'];
$total_keluar = $kas_keluar_restok['total'] + $kas_keluar_biaya['total'];
$saldo = $total_masuk - $total_keluar;

$bulan_nama = ['', 'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
?>

<div class="space-y-6">
    <h1 class="text-2xl font-bold text-dark">
        <i class="fas fa-wallet text-primary mr-2"></i>Laporan Arus Kas
    </h1>

    <div class="bg-white rounded-2xl shadow-sm p-4">
        <form method="get" class="flex flex-wrap gap-4 items-end">
            <input type="hidden" name="page" value="laporan">
            <input type="hidden" name="jenis" value="aruskas">
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
            <button type="submit" class="bg-primary text-white px-6 py-2 rounded-xl">Tampilkan</button>
        </form>
    </div>

    <!-- Saldo -->
    <div class="bg-white rounded-2xl shadow-sm p-6 text-center">
        <p class="text-gray-400">SALDO AKHIR <?= strtoupper($bulan_nama[(int)$bulan]) ?> <?= $tahun ?></p>
        <h2 class="text-4xl font-bold <?= $saldo >= 0 ? 'text-green-600' : 'text-red-600' ?> mt-2">
            <?= rupiah($saldo) ?>
        </h2>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Kas Masuk -->
        <div class="bg-white rounded-2xl shadow-sm">
            <div class="p-4 border-b bg-green-50 rounded-t-2xl">
                <h3 class="font-bold text-green-700"><i class="fas fa-arrow-down mr-2"></i>Kas Masuk</h3>
            </div>
            <div class="p-6 space-y-4">
                <div class="flex justify-between py-3 border-b">
    <span>🛒 Penjualan (setelah kembalian)</span>
    <span class="font-bold"><?= rupiah($kas_masuk_penjualan['total']) ?></span>
</div>
                <div class="flex justify-between py-3 border-b">
                    <span>💳 Pembayaran Piutang</span>
                    <span class="font-bold"><?= rupiah($kas_masuk_piutang['total']) ?></span>
                </div>
                <div class="flex justify-between py-3 text-lg font-bold bg-green-50 px-3 rounded-lg">
                    <span>Total Masuk</span>
                    <span class="text-green-600"><?= rupiah($total_masuk) ?></span>
                </div>
            </div>
        </div>

        <!-- Kas Keluar -->
        <div class="bg-white rounded-2xl shadow-sm">
            <div class="p-4 border-b bg-red-50 rounded-t-2xl">
                <h3 class="font-bold text-red-700"><i class="fas fa-arrow-up mr-2"></i>Kas Keluar</h3>
            </div>
            <div class="p-6 space-y-4">
                <div class="flex justify-between py-3 border-b">
                    <span>📦 Pembelian/Restok</span>
                    <span class="font-bold"><?= rupiah($kas_keluar_restok['total']) ?></span>
                </div>
                <div class="flex justify-between py-3 border-b">
                    <span>💸 Biaya Operasional</span>
                    <span class="font-bold"><?= rupiah($kas_keluar_biaya['total']) ?></span>
                </div>
                <div class="flex justify-between py-3 text-lg font-bold bg-red-50 px-3 rounded-lg">
                    <span>Total Keluar</span>
                    <span class="text-red-600"><?= rupiah($total_keluar) ?></span>
                </div>
            </div>
        </div>
    </div>
</div>