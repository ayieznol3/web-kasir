<?php
$page = 'piutang';

// ==================== SEARCH & PAGINATION ====================
$search = $_GET['search'] ?? '';
$currentPage = isset($_GET['page_num']) ? max(1, (int)$_GET['page_num']) : 1;
$perPage = 15;

// Build query
$where = "HAVING (saldo_piutang > 0 OR total_hutang > 0)";
if ($search) {
    $search_esc = mysqli_real_escape_string($conn, $search);
    $where = "HAVING (saldo_piutang > 0 OR total_hutang > 0) AND (nama LIKE '%$search_esc%')";
}

// Query utama
$sql = "
    SELECT p.*,
           COALESCE((SELECT SUM(jumlah) FROM piutang WHERE pelanggan_id = p.id AND tipe = 'pembayaran'),0) as total_bayar,
           COALESCE((SELECT SUM(jumlah) FROM piutang WHERE pelanggan_id = p.id AND tipe != 'pembayaran'),0) as total_hutang
    FROM pelanggan p
    $where
    ORDER BY saldo_piutang DESC
";

// Hitung total
$countSql = "SELECT COUNT(*) as total FROM ($sql) as subquery";
$totalQuery = mysqli_query($conn, $countSql);
$totalRows = mysqli_fetch_assoc($totalQuery)['total'];
$totalPages = ceil($totalRows / $perPage);
$start = ($currentPage - 1) * $perPage;

// Ambil data
$list = mysqli_query($conn, "$sql LIMIT $start, $perPage");

// Total keseluruhan
$total_semua = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(saldo_piutang),0) as total FROM pelanggan"));
?>

<div class="space-y-6">
    
    <!-- ==================== HEADER ==================== -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-dark">
                <i class="fas fa-hand-holding-usd text-yellow-500 mr-2"></i>Piutang Pelanggan
            </h1>
            <p class="text-sm text-gray-500 mt-1">Pantau dan kelola piutang pelanggan</p>
        </div>
    </div>

    <!-- ==================== STAT CARDS ==================== -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl shadow-sm p-5 border-l-4 border-red-500 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Piutang</p>
                    <p class="text-2xl font-bold text-red-600 mt-1"><?= rupiah($total_semua['total']) ?></p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-money-bill-wave text-red-500 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm p-5 border-l-4 border-blue-500 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Pelanggan Berhutang</p>
                    <p class="text-2xl font-bold mt-1"><?= $totalRows ?> <span class="text-sm font-normal text-gray-400">orang</span></p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-users text-blue-500 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm p-5 border-l-4 border-green-500 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Rata-rata Piutang</p>
                    <p class="text-2xl font-bold text-green-600 mt-1">
                        <?= $totalRows > 0 ? rupiah(round($total_semua['total'] / $totalRows)) : rupiah(0) ?>
                    </p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-calculator text-green-500 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== SEARCH ==================== -->
    <div class="bg-white rounded-2xl shadow-sm p-4">
        <form method="get" class="flex flex-wrap gap-3 items-end">
            <input type="hidden" name="page" value="piutang">
            
            <div class="flex-1 min-w-[200px]">
                <label class="text-xs text-gray-500 font-medium block mb-1">Cari Pelanggan</label>
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400 text-sm"></i>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                           placeholder="Cari nama pelanggan..."
                           class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition">
                </div>
            </div>
            
            <button type="submit" class="bg-primary text-white px-5 py-2.5 rounded-xl text-sm font-medium hover:bg-indigo-700 transition">
                <i class="fas fa-search mr-1"></i> Cari
            </button>
            
            <?php if($search): ?>
            <a href="?page=piutang" class="px-4 py-2.5 border border-gray-200 rounded-xl text-sm text-gray-500 hover:bg-gray-50 transition">
                <i class="fas fa-times mr-1"></i> Reset
            </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- ==================== INFO ==================== -->
    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">
            <?php if($totalRows > 0): ?>
            Menampilkan <span class="font-medium"><?= $start + 1 ?>-<?= min($totalRows, $start + $perPage) ?></span> 
            dari <span class="font-medium"><?= $totalRows ?></span> pelanggan
            <?php else: ?>
            Tidak ada piutang 🎉
            <?php endif; ?>
        </p>
    </div>

    <!-- ==================== TABEL PIUTANG ==================== -->
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="text-left px-4 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Pelanggan</th>
                        <th class="text-right px-4 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Hutang</th>
                        <th class="text-right px-4 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Dibayar</th>
                        <th class="text-right px-4 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Sisa</th>
                        <th class="text-center px-4 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Progress</th>
                        <th class="text-center px-4 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php while($p = mysqli_fetch_assoc($list)): 
                        $progress = $p['total_hutang'] > 0 ? round(($p['total_bayar'] / $p['total_hutang']) * 100) : 0;
                        $sisa = $p['saldo_piutang'];
                    ?>
                    <tr class="hover:bg-gray-50 transition cursor-pointer" 
                        onclick="window.location='?page=piutang-detail&id=<?= $p['id'] ?>'">
                        
                        <!-- Pelanggan -->
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm
                                    <?= $sisa > 500000 ? 'bg-red-100 text-red-600' : ($sisa > 0 ? 'bg-yellow-100 text-yellow-600' : 'bg-green-100 text-green-600') ?>">
                                    <?= strtoupper(substr($p['nama'], 0, 1)) ?>
                                </div>
                                <div>
                                    <p class="font-medium text-dark"><?= htmlspecialchars($p['nama']) ?></p>
                                    <p class="text-xs text-gray-400">ID: <?= $p['id'] ?></p>
                                </div>
                            </div>
                        </td>
                        
                        <!-- Total Hutang -->
                        <td class="px-4 py-3 text-right">
                            <span class="text-red-600 font-bold text-sm"><?= rupiah($p['total_hutang']) ?></span>
                        </td>
                        
                        <!-- Dibayar -->
                        <td class="px-4 py-3 text-right">
                            <span class="text-green-600 font-bold text-sm"><?= rupiah($p['total_bayar']) ?></span>
                        </td>
                        
                        <!-- Sisa -->
                        <td class="px-4 py-3 text-right">
                            <span class="text-lg font-bold <?= $sisa > 0 ? 'text-red-600' : 'text-green-600' ?>">
                                <?= rupiah($sisa) ?>
                            </span>
                        </td>
                        
                        <!-- Progress Bar -->
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 bg-gray-200 rounded-full h-2">
                                    <div class="h-2 rounded-full <?= $progress >= 100 ? 'bg-green-500' : 'bg-yellow-500' ?>" 
                                         style="width: <?= $progress ?>%"></div>
                                </div>
                                <span class="text-xs text-gray-500 w-10"><?= $progress ?>%</span>
                            </div>
                        </td>
                        
                        <!-- Aksi -->
                        <td class="px-4 py-3" onclick="event.stopPropagation()">
                            <div class="flex justify-center gap-1">
                                <a href="?page=piutang-detail&id=<?= $p['id'] ?>" 
                                   class="px-3 py-1.5 text-xs border border-gray-200 rounded-lg hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 transition">
                                    <i class="fas fa-eye mr-1"></i> Detail
                                </a>
                                <?php if($sisa > 0): ?>
                                <a href="?page=piutang-detail&id=<?= $p['id'] ?>#bayar" 
                                   class="px-3 py-1.5 text-xs bg-green-500 text-white rounded-lg hover:bg-green-600 transition">
                                    <i class="fas fa-money-bill mr-1"></i> Bayar
                                </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    
                    <!-- Empty State -->
                    <?php if(mysqli_num_rows($list) == 0): ?>
                    <tr>
                        <td colspan="6">
                            <div class="text-center py-16">
                                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-check-circle text-3xl text-green-500"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-500 mb-2">Semua Lunas! 🎉</h3>
                                <p class="text-gray-400 text-sm">
                                    Tidak ada pelanggan dengan piutang
                                </p>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ==================== PAGINATION ==================== -->
    <?php if($totalPages > 1): ?>
    <div class="flex items-center justify-between pt-4 border-t border-gray-200">
        <p class="text-sm text-gray-500">
            Halaman <span class="font-medium"><?= $currentPage ?></span> dari <span class="font-medium"><?= $totalPages ?></span>
        </p>
        
        <div class="flex gap-1">
            <?php if($currentPage > 1): 
                $prevUrl = "?page=piutang&page_num=" . ($currentPage - 1);
                if($search) $prevUrl .= "&search=" . urlencode($search);
            ?>
            <a href="<?= $prevUrl ?>" class="px-3 py-2 border border-gray-200 rounded-lg text-sm hover:bg-gray-100 transition">
                <i class="fas fa-chevron-left text-xs"></i>
            </a>
            <?php else: ?>
            <span class="px-3 py-2 border border-gray-100 rounded-lg text-sm text-gray-300 cursor-not-allowed">
                <i class="fas fa-chevron-left text-xs"></i>
            </span>
            <?php endif; ?>
            
            <?php
            $startPage = max(1, $currentPage - 2);
            $endPage = min($totalPages, $currentPage + 2);
            
            if($startPage > 1): 
                $firstUrl = "?page=piutang&page_num=1";
                if($search) $firstUrl .= "&search=" . urlencode($search);
            ?>
            <a href="<?= $firstUrl ?>" class="px-3 py-2 border border-gray-200 rounded-lg text-sm hover:bg-gray-100 transition">1</a>
            <?php if($startPage > 2): ?>
            <span class="px-2 py-2 text-gray-400 text-sm">...</span>
            <?php endif; ?>
            <?php endif; ?>
            
            <?php for($i = $startPage; $i <= $endPage; $i++): 
                $pageUrl = "?page=piutang&page_num=$i";
                if($search) $pageUrl .= "&search=" . urlencode($search);
                $activeClass = $i == $currentPage ? 'bg-primary text-white border-primary shadow-lg shadow-indigo-200' : 'border-gray-200 hover:bg-gray-100';
            ?>
            <a href="<?= $pageUrl ?>" class="px-3 py-2 border rounded-lg text-sm <?= $activeClass ?> transition"><?= $i ?></a>
            <?php endfor; ?>
            
            <?php if($endPage < $totalPages): 
                if($endPage < $totalPages - 1): ?>
                <span class="px-2 py-2 text-gray-400 text-sm">...</span>
                <?php endif;
                $lastUrl = "?page=piutang&page_num=$totalPages";
                if($search) $lastUrl .= "&search=" . urlencode($search);
            ?>
            <a href="<?= $lastUrl ?>" class="px-3 py-2 border border-gray-200 rounded-lg text-sm hover:bg-gray-100 transition"><?= $totalPages ?></a>
            <?php endif; ?>
            
            <?php if($currentPage < $totalPages): 
                $nextUrl = "?page=piutang&page_num=" . ($currentPage + 1);
                if($search) $nextUrl .= "&search=" . urlencode($search);
            ?>
            <a href="<?= $nextUrl ?>" class="px-3 py-2 border border-gray-200 rounded-lg text-sm hover:bg-gray-100 transition">
                <i class="fas fa-chevron-right text-xs"></i>
            </a>
            <?php else: ?>
            <span class="px-3 py-2 border border-gray-100 rounded-lg text-sm text-gray-300 cursor-not-allowed">
                <i class="fas fa-chevron-right text-xs"></i>
            </span>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

</div>