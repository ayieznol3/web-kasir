<?php

date_default_timezone_set('Asia/Jakarta');

session_start();
require_once 'config/database.php';
require_once 'functions/helper.php';
require_once 'functions/harga.php';
require_once 'functions/piutang.php';
require_once 'functions/upload.php';

// Ambil halaman yang diminta
$page = $_GET['page'] ?? 'dashboard';

// Halaman yang bisa diakses tanpa login
$public_pages = ['login'];

// Cek login
if (!isset($_SESSION['user_id']) && !in_array($page, $public_pages)) {
    header('Location: index.php?page=login');
    exit;
}

// Jika sudah login dan mengakses login, redirect
if (isset($_SESSION['user_id']) && $page == 'login') {
    if (isAdmin()) {
        header('Location: index.php?page=dashboard');
    } else {
        header('Location: index.php?page=kasir');
    }
    exit;
}

// Routing halaman
switch ($page) {
    
    // ==================== PUBLIC ====================
    case 'login':
        include 'halaman/login.php';
        break;
    
    // ==================== DASHBOARD ====================
    case 'dashboard':
        if (isKasir()) {
            header('Location: index.php?page=kasir');
            exit;
        }
        include 'includes/header.php';
        include 'halaman/dashboard.php';
        include 'includes/footer.php';
        break;
    
    // ==================== KASIR ====================
    case 'kasir':
        include 'includes/header.php';
        include 'halaman/kasir.php';
        include 'includes/footer.php';
        break;
    
    // ==================== TRANSAKSI ====================
    case 'transaksi':
        include 'includes/header.php';
        include 'halaman/transaksi.php';
        include 'includes/footer.php';
        break;
    
    // ==================== PRODUK ====================
    case 'produk':
        include 'includes/header.php';
        include 'halaman/produk.php';
        include 'includes/footer.php';
        break;
    
    case 'produk-tambah':
        include 'includes/header.php';
        include 'halaman/produk-tambah.php';
        include 'includes/footer.php';
        break;
    
    case 'produk-edit':
        include 'includes/header.php';
        include 'halaman/produk-edit.php';
        include 'includes/footer.php';
        break;
    
    case 'satuan':
        include 'includes/header.php';
        include 'halaman/satuan.php';
        include 'includes/footer.php';
        break;
    
    // ==================== RESTOK ====================
    case 'restok':
        include 'includes/header.php';
        include 'halaman/restok.php';
        include 'includes/footer.php';
        break;
    
    // ==================== STOCK OPNAME ====================
    case 'stock-opname':
        include 'includes/header.php';
        include 'halaman/stock-opname.php';
        include 'includes/footer.php';
        break;
    
    // ==================== PELANGGAN ====================
    case 'pelanggan':
        include 'includes/header.php';
        include 'halaman/pelanggan.php';
        include 'includes/footer.php';
        break;
    
    // ==================== PROGRAM THR ====================
    case 'program-thr':
        include 'includes/header.php';
        include 'halaman/program-thr.php';
        include 'includes/footer.php';
        break;
    
    // ==================== PIUTANG ====================
    case 'piutang':
        include 'includes/header.php';
        include 'halaman/piutang.php';
        include 'includes/footer.php';
        break;
    
    case 'piutang-detail':
        include 'includes/header.php';
        include 'halaman/piutang-detail.php';
        include 'includes/footer.php';
        break;
    
    // ==================== USERS ====================
    case 'users':
        include 'includes/header.php';
        include 'halaman/users.php';
        include 'includes/footer.php';
        break;
    
    // ==================== PENGELUARAN ====================
    case 'pengeluaran':
        include 'includes/header.php';
        include 'halaman/pengeluaran.php';
        include 'includes/footer.php';
        break;
        
        case 'backup':
    include 'includes/header.php';
    include 'halaman/backup.php';
    include 'includes/footer.php';
    break;
    
    case 'input-produk-mobile':
    include 'halaman/input-produk-mobile.php';
    break;
    
    case 'kasir-mobile':
    include 'halaman/kasir-mobile.php';
    break;
        
        case 'laporan-keuangan':
    include 'includes/header.php';
    include 'halaman/laporan-keuangan.php';
    include 'includes/footer.php';
    break;
    
    // ==================== LAPORAN ====================
    case 'laporan':
        $jenis = $_GET['jenis'] ?? 'penjualan';
        include 'includes/header.php';
        switch ($jenis) {
            case 'labarugi':
                include 'halaman/laporan-labarugi.php';
                break;
            case 'aruskas':
                include 'halaman/laporan-aruskas.php';
                break;
            case 'stok':
                include 'halaman/laporan-stok.php';
                break;
            case 'pelanggan':
                include 'halaman/laporan-pelanggan.php';
                break;
            case 'pembelian':
                include 'halaman/laporan-pembelian.php';
                break;
            default:
                include 'halaman/laporan-penjualan.php';
        }
        include 'includes/footer.php';
        break;
        
        case 'analitik':
    include 'includes/header.php';
    include 'halaman/analitik.php';
    include 'includes/footer.php';
    break;
    
    // ==================== LOG ====================
    case 'log':
        include 'includes/header.php';
        include 'halaman/log.php';
        include 'includes/footer.php';
        break;
    
    // ==================== 404 ====================
    default:
        $file = "halaman/$page.php";
        if (file_exists($file)) {
            include 'includes/header.php';
            include $file;
            include 'includes/footer.php';
        } else {
            include 'includes/header.php';
            echo "
            <div class='text-center py-20'>
                <i class='fas fa-exclamation-circle text-6xl text-gray-300 mb-4'></i>
                <h2 class='text-2xl font-bold text-gray-400'>Halaman Tidak Ditemukan</h2>
                <p class='text-gray-400 mt-2'>Halaman yang Anda cari tidak tersedia</p>
                <a href='?page=dashboard' class='inline-block mt-4 text-primary hover:underline'>Kembali ke Dashboard</a>
            </div>";
            include 'includes/footer.php';
        }
        break;
}
?>