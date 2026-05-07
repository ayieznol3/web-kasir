<?php
// Cek login
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login');
    exit;
}

$current_page = $_GET['page'] ?? 'dashboard';
$user = userLogin();
?>
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aplikasi Kasir</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
    
    <style>
        .sidebar-link {
            transition: all 0.2s ease;
        }
        .sidebar-link:hover {
            background-color: #f1f5f9;
            padding-left: 1.5rem;
        }
        .sidebar-link.active {
            background-color: #6366f1;
            color: white;
        }
        .submenu-icon { transition: transform 0.3s ease; }
        .submenu-icon.open { transform: rotate(180deg); }
        .submenu { animation: slideDown 0.2s ease; }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        .badge-piutang { animation: pulse 2s infinite; }
        
    </style>
</head>
<body class="h-full bg-gray-50">

<!-- ==================== NAVBAR TOP ==================== -->
<nav class="bg-dark text-white shadow-lg sticky top-0 z-50">
    <div class="max-w-full mx-auto px-6">
        <div class="flex justify-between h-16 items-center">
            
            <div class="flex items-center gap-3">
                <button onclick="toggleSidebar()" class="lg:hidden text-white hover:text-gray-300">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-primary rounded-xl flex items-center justify-center">
                        <i class="fas fa-store text-white"></i>
                    </div>
                    <div>
                        <h1 class="font-bold text-lg"><?= APP_NAME ?></h1>
                        <p class="text-xs text-gray-400">Point of Sale</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <span class="text-sm text-gray-400 hidden md:block" id="jam"></span>
                
                <div class="relative group">
                    <button class="flex items-center gap-2 hover:text-gray-300 transition">
                        <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center text-sm font-bold">
                            <?= strtoupper(substr($user['nama'], 0, 1)) ?>
                        </div>
                        <span class="hidden md:block text-sm"><?= $user['nama'] ?></span>
                        <span class="hidden md:block px-2 py-0.5 rounded-full text-xs <?= $user['role'] == 'admin' ? 'bg-purple-500' : 'bg-blue-500' ?>">
                            <?= ucfirst($user['role']) ?>
                        </span>
                    </button>
                    
                    <div class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                        <div class="p-2">
                            <div class="px-3 py-2 text-sm text-gray-500 border-b">
                                <p class="font-medium text-dark"><?= $user['nama'] ?></p>
                                <p class="text-xs"><?= ucfirst($user['role']) ?></p>
                            </div>
                            <a href="proses/logout.php" class="flex items-center gap-2 px-3 py-2 text-sm text-red-600 hover:bg-red-50 rounded-lg mt-1 transition">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>

<!-- ==================== CONTAINER UTAMA ==================== -->
<div class="flex h-[calc(100vh-64px)]">
    
    <aside id="sidebar" class="w-64 bg-white shadow-md overflow-y-auto flex-shrink-0 hidden lg:block transition-transform duration-300">
        <nav class="p-4 space-y-1">
            
            <!-- ==================== MENU UTAMA ==================== -->
            <div class="px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                📌 Menu Utama
            </div>

            <a href="?page=dashboard" 
               class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition
                      <?= ($current_page == 'dashboard') ? 'bg-primary text-white shadow-lg shadow-indigo-200' : 'text-gray-700 hover:bg-gray-100' ?>">
                <i class="fas fa-th-large w-5 text-center"></i> Dashboard
            </a>

            <a href="?page=kasir" 
               class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition
                      <?= ($current_page == 'kasir') ? 'bg-primary text-white shadow-lg shadow-indigo-200' : 'text-gray-700 hover:bg-gray-100' ?>">
                <i class="fas fa-cash-register w-5 text-center"></i> Kasir
            </a>
            <a href="?page=kasir-mobile" 
   class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition
          <?= ($current_page == 'kasir-mobile') ? 'bg-primary text-white shadow-lg shadow-indigo-200' : 'text-gray-700 hover:bg-gray-100' ?>">
    <i class="fas fa-mobile-alt w-5 text-center"></i> Kasir Mobile
</a>
            
            <a href="?page=transaksi" 
               class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition
                      <?= ($current_page == 'transaksi') ? 'bg-primary text-white shadow-lg shadow-indigo-200' : 'text-gray-700 hover:bg-gray-100' ?>">
                <i class="fas fa-history w-5 text-center"></i> Riwayat Transaksi
            </a>

            <?php if (isAdmin()): ?>
            
            <!-- ==================== MANAJEMEN (DROPDOWN) ==================== -->
            <div class="border-t pt-4 mt-4">
                <button onclick="toggleSubmenu('submenu-manajemen')" 
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-100 transition">
                    <i class="fas fa-boxes w-5 text-center"></i>
                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">📦 Manajemen</span>
                    <i id="icon-manajemen" class="submenu-icon fas fa-chevron-down ml-auto text-xs text-gray-400 <?= in_array($current_page, ['produk', 'produk-tambah', 'produk-edit', 'satuan', 'restok', 'stock-opname', 'pelanggan', 'program-thr']) ? 'open' : '' ?>"></i>
                </button>
                
                <div id="submenu-manajemen" class="ml-8 mt-1 space-y-1 submenu <?= in_array($current_page, ['produk', 'produk-tambah', 'produk-edit', 'satuan', 'restok', 'stock-opname', 'pelanggan', 'program-thr']) ? '' : 'hidden' ?>">
                    <a href="?page=produk" 
                       class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition
                              <?= (in_array($current_page, ['produk', 'produk-tambah', 'produk-edit', 'satuan'])) ? 'bg-indigo-50 text-primary font-medium' : 'text-gray-600 hover:bg-gray-100' ?>">
                        <i class="fas fa-box w-4 text-center text-xs"></i> Produk
                    </a>
                    <a href="?page=restok" 
                       class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition
                              <?= ($current_page == 'restok') ? 'bg-indigo-50 text-primary font-medium' : 'text-gray-600 hover:bg-gray-100' ?>">
                        <i class="fas fa-truck-loading w-4 text-center text-xs"></i> Restok
                    </a>
                    <a href="?page=stock-opname" 
                       class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition
                              <?= ($current_page == 'stock-opname') ? 'bg-indigo-50 text-primary font-medium' : 'text-gray-600 hover:bg-gray-100' ?>">
                        <i class="fas fa-clipboard-check w-4 text-center text-xs"></i> Stock Opname
                    </a>
                    <a href="?page=pelanggan" 
                       class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition
                              <?= ($current_page == 'pelanggan') ? 'bg-indigo-50 text-primary font-medium' : 'text-gray-600 hover:bg-gray-100' ?>">
                        <i class="fas fa-users w-4 text-center text-xs"></i> Pelanggan
                    </a>
                    <a href="?page=program-thr" 
                       class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition
                              <?= ($current_page == 'program-thr') ? 'bg-indigo-50 text-primary font-medium' : 'text-gray-600 hover:bg-gray-100' ?>">
                        <i class="fas fa-gift w-4 text-center text-xs"></i> Program THR
                    </a>
                </div>
            </div>

            <!-- ==================== KEUANGAN (DROPDOWN) ==================== -->
            <div class="border-t pt-4 mt-4">
                <button onclick="toggleSubmenu('submenu-keuangan')" 
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-100 transition">
                    <i class="fas fa-money-bill-wave w-5 text-center"></i>
                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">💰 Keuangan</span>
                    <i id="icon-keuangan" class="submenu-icon fas fa-chevron-down ml-auto text-xs text-gray-400 <?= in_array($current_page, ['piutang', 'piutang-detail', 'pengeluaran']) ? 'open' : '' ?>"></i>
                </button>
                
                <div id="submenu-keuangan" class="ml-8 mt-1 space-y-1 submenu <?= in_array($current_page, ['piutang', 'piutang-detail', 'pengeluaran']) ? '' : 'hidden' ?>">
                    <a href="?page=piutang" 
                       class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition
                              <?= (in_array($current_page, ['piutang', 'piutang-detail'])) ? 'bg-indigo-50 text-primary font-medium' : 'text-gray-600 hover:bg-gray-100' ?>">
                        <i class="fas fa-hand-holding-usd w-4 text-center text-xs"></i> Piutang
                        <?php 
                        $jml_piutang = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pelanggan WHERE saldo_piutang > 0"));
                        if($jml_piutang['total'] > 0): 
                        ?>
                        <span class="ml-auto bg-red-500 text-white text-xs px-2 py-0.5 rounded-full badge-piutang"><?= $jml_piutang['total'] ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="?page=pengeluaran" 
                       class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition
                              <?= ($current_page == 'pengeluaran') ? 'bg-indigo-50 text-primary font-medium' : 'text-gray-600 hover:bg-gray-100' ?>">
                        <i class="fas fa-receipt w-4 text-center text-xs"></i> Pengeluaran
                    </a>
                </div>
            </div>

            <!-- ==================== LAPORAN (DROPDOWN) ==================== -->
            <div class="border-t pt-4 mt-4">
                <button onclick="toggleSubmenu('submenu-laporan')" 
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-100 transition">
                    <i class="fas fa-chart-bar w-5 text-center"></i>
                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">📊 Laporan</span>
                    <i id="icon-laporan" class="submenu-icon fas fa-chevron-down ml-auto text-xs text-gray-400 <?= in_array($current_page, ['laporan', 'laporan-keuangan', 'analitik']) ? 'open' : '' ?>"></i>
                </button>
                
                <div id="submenu-laporan" class="ml-8 mt-1 space-y-1 submenu <?= in_array($current_page, ['laporan', 'laporan-keuangan', 'analitik']) ? '' : 'hidden' ?>">
                    <a href="?page=laporan&jenis=penjualan" 
                       class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition
                              <?= (isset($_GET['jenis']) && $_GET['jenis'] == 'penjualan') ? 'bg-indigo-50 text-primary font-medium' : 'text-gray-600 hover:bg-gray-100' ?>">
                        <i class="fas fa-shopping-cart w-4 text-center text-xs"></i> Penjualan
                    </a>
                    <a href="?page=laporan&jenis=labarugi" 
                       class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition
                              <?= (isset($_GET['jenis']) && $_GET['jenis'] == 'labarugi') ? 'bg-indigo-50 text-primary font-medium' : 'text-gray-600 hover:bg-gray-100' ?>">
                        <i class="fas fa-balance-scale w-4 text-center text-xs"></i> Laba Rugi
                    </a>
                    <a href="?page=laporan&jenis=aruskas" 
                       class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition
                              <?= (isset($_GET['jenis']) && $_GET['jenis'] == 'aruskas') ? 'bg-indigo-50 text-primary font-medium' : 'text-gray-600 hover:bg-gray-100' ?>">
                        <i class="fas fa-wallet w-4 text-center text-xs"></i> Arus Kas
                    </a>
                    <a href="?page=laporan&jenis=stok" 
                       class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition
                              <?= (isset($_GET['jenis']) && $_GET['jenis'] == 'stok') ? 'bg-indigo-50 text-primary font-medium' : 'text-gray-600 hover:bg-gray-100' ?>">
                        <i class="fas fa-boxes w-4 text-center text-xs"></i> Stok
                    </a>
                    <a href="?page=laporan-keuangan" 
                       class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition
                              <?= ($current_page == 'laporan-keuangan') ? 'bg-indigo-50 text-primary font-medium' : 'text-gray-600 hover:bg-gray-100' ?>">
                        <i class="fas fa-chart-line w-4 text-center text-xs"></i> Laporan Keuangan
                    </a>
                    <a href="?page=laporan&jenis=pelanggan" 
                       class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition
                              <?= (isset($_GET['jenis']) && $_GET['jenis'] == 'pelanggan') ? 'bg-indigo-50 text-primary font-medium' : 'text-gray-600 hover:bg-gray-100' ?>">
                         <i class="fas fa-users w-4 text-center text-xs"></i> Pelanggan
                    </a>
                    <a href="?page=laporan&jenis=pembelian" 
                       class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition
                              <?= (isset($_GET['jenis']) && $_GET['jenis'] == 'pembelian') ? 'bg-indigo-50 text-primary font-medium' : 'text-gray-600 hover:bg-gray-100' ?>">
                        <i class="fas fa-truck w-4 text-center text-xs"></i> Pembelian
                    </a>
                    <a href="?page=analitik" 
                       class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition
                              <?= ($current_page == 'analitik') ? 'bg-indigo-50 text-primary font-medium' : 'text-gray-600 hover:bg-gray-100' ?>">
                        <i class="fas fa-chart-pie w-4 text-center text-xs"></i> Analitik Bisnis
                    </a>
                </div>
            </div>

            <!-- ==================== SISTEM (DROPDOWN) ==================== -->
            <div class="border-t pt-4 mt-4">
                <button onclick="toggleSubmenu('submenu-sistem')" 
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-100 transition">
                    <i class="fas fa-cog w-5 text-center"></i>
                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">⚙️ Sistem</span>
                    <i id="icon-sistem" class="submenu-icon fas fa-chevron-down ml-auto text-xs text-gray-400 <?= in_array($current_page, ['users', 'log', 'backup']) ? 'open' : '' ?>"></i>
                </button>
                
                <div id="submenu-sistem" class="ml-8 mt-1 space-y-1 submenu <?= in_array($current_page, ['users', 'log', 'backup']) ? '' : 'hidden' ?>">
                    <a href="?page=users" 
                       class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition
                              <?= ($current_page == 'users') ? 'bg-indigo-50 text-primary font-medium' : 'text-gray-600 hover:bg-gray-100' ?>">
                        <i class="fas fa-users-cog w-4 text-center text-xs"></i> Manajemen User
                    </a>
                    <a href="?page=log" 
                       class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition
                              <?= ($current_page == 'log') ? 'bg-indigo-50 text-primary font-medium' : 'text-gray-600 hover:bg-gray-100' ?>">
                        <i class="fas fa-history w-4 text-center text-xs"></i> Log Aktivitas
                    </a>
                    <a href="?page=pengaturan" 
                        class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition
                             <?= ($current_page == 'pengaturan') ? 'bg-indigo-50 text-primary font-medium' : 'text-gray-600 hover:bg-gray-100' ?>">
                        <i class="fas fa-cog w-5 text-center"></i>
                        Pengaturan
                    </a>
                    <a href="?page=backup" 
                       class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition
                              <?= ($current_page == 'backup') ? 'bg-indigo-50 text-primary font-medium' : 'text-gray-600 hover:bg-gray-100' ?>">
                        <i class="fas fa-database w-4 text-center text-xs"></i> Backup Database
                    </a>
                </div>
            </div>

            <?php endif; ?>

            <!-- ==================== USER BOTTOM ==================== -->
            <div class="border-t pt-4 mt-4">
                <div class="px-3 py-2">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-gray-200 flex items-center justify-center">
                            <span class="text-sm font-bold text-gray-600"><?= strtoupper(substr($user['nama'], 0, 1)) ?></span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium truncate"><?= $user['nama'] ?></p>
                            <p class="text-xs text-gray-400"><?= ucfirst($user['role']) ?></p>
                        </div>
                    </div>
                </div>

                <a href="proses/logout.php" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-red-500 hover:bg-red-50 transition mt-1">
                    <i class="fas fa-sign-out-alt w-5 text-center"></i> Logout
                </a>
            </div>

        </nav>
    </aside>

    <div id="overlay" class="hidden fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden" onclick="toggleSidebar()"></div>

    <main class="flex-1 overflow-y-auto p-6">
        <?php flash('sukses'); ?>
        <?php flash('error'); ?>
<!-- ==================== END OF HEADER.PHP ==================== -->