<?php
// ============================================
// FUNGSI HELPER
// ============================================

// Format rupiah
function rupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

// Format tanggal Indonesia
function tgl_indo($tanggal) {
    $bulan = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 
              'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $t = explode('-', $tanggal);
    return $t[2] . ' ' . $bulan[(int)$t[1]] . ' ' . $t[0];
}

// Format tanggal jam
function tgl_jam($datetime) {
    return date('d/m/Y H:i', strtotime($datetime));
}

// Cek apakah user admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Cek apakah user kasir
function isKasir() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'kasir';
}

// Redirect halaman
function redirect($url) {
    header("Location: $url");
    exit;
}

// Escape string untuk keamanan
function esc($string) {
    global $conn;
    return mysqli_real_escape_string($conn, $string);
}

// Tampilkan pesan sukses/error
function flash($nama = 'sukses') {
    if (isset($_SESSION[$nama])) {
        $type = $_SESSION[$nama . '_type'] ?? 'success';
        $bg = $type == 'danger' ? 'bg-red-100 text-red-700 border-red-300' : 'bg-green-100 text-green-700 border-green-300';
        echo "<div class='mb-4 p-4 border rounded-lg $bg'>";
        echo $_SESSION[$nama];
        echo "</div>";
        unset($_SESSION[$nama], $_SESSION[$nama . '_type']);
    }
}

// Generate nomor invoice
function generateInvoice() {
    return 'INV-' . date('Ymd') . '-' . sprintf('%03d', rand(1, 999));
}

// Cek stok menipis
function stokTipis($stok) {
    return $stok <= 10 ? 'text-red-600 font-bold' : '';
}

// Ambil data user yang login
function userLogin() {
    return [
        'id' => $_SESSION['user_id'] ?? 0,
        'nama' => $_SESSION['nama'] ?? '',
        'role' => $_SESSION['role'] ?? ''
    ];
}

// Debug (untuk development)
function dd($data) {
    echo '<pre>';
    print_r($data);
    echo '</pre>';
    die;
}

// ============================================
// FUNGSI PAGINATION
// ============================================

/**
 * Membuat query LIMIT untuk pagination
 * @param int $page Halaman saat ini
 * @param int $perPage Jumlah data per halaman
 * @return string SQL LIMIT clause
 */
function getLimit($page = 1, $perPage = 20) {
    $page = max(1, (int)$page);
    $start = ($page - 1) * $perPage;
    return "LIMIT $start, $perPage";
}

/**
 * Menghitung total halaman
 * @param int $totalRows Total data
 * @param int $perPage Data per halaman
 * @return int Total halaman
 */
function getTotalPages($totalRows, $perPage = 20) {
    return ceil($totalRows / $perPage);
}

/**
 * Generate HTML pagination dengan Tailwind
 * @param int $currentPage Halaman aktif
 * @param int $totalPages Total halaman
 * @param string $baseUrl URL dasar
 * @param array $params Parameter tambahan
 * @return string HTML pagination
 */
function pagination($currentPage, $totalPages, $baseUrl = '?', $params = []) {
    if ($totalPages <= 1) return '';
    
    // Build query string
    $queryParams = $params;
    unset($queryParams['page']); // page akan ditambahkan manual
    
    $html = '<div class="flex items-center justify-between mt-6 pt-4 border-t">';
    
    // Info
    $html .= '<p class="text-sm text-gray-500">Halaman ' . $currentPage . ' dari ' . $totalPages . '</p>';
    
    // Links
    $html .= '<div class="flex gap-1">';
    
    // Previous
    if ($currentPage > 1) {
        $prevUrl = buildUrl($baseUrl, array_merge($queryParams, ['page' => $currentPage - 1]));
        $html .= '<a href="' . $prevUrl . '" class="px-3 py-2 border rounded-lg text-sm hover:bg-gray-100 transition">&laquo; Prev</a>';
    }
    
    // Page numbers
    $startPage = max(1, $currentPage - 2);
    $endPage = min($totalPages, $currentPage + 2);
    
    if ($startPage > 1) {
        $html .= '<a href="' . buildUrl($baseUrl, array_merge($queryParams, ['page' => 1])) . '" class="px-3 py-2 border rounded-lg text-sm hover:bg-gray-100 transition">1</a>';
        if ($startPage > 2) $html .= '<span class="px-2 py-2 text-gray-400">...</span>';
    }
    
    for ($i = $startPage; $i <= $endPage; $i++) {
        $activeClass = $i == $currentPage ? 'bg-primary text-white border-primary' : 'hover:bg-gray-100';
        $html .= '<a href="' . buildUrl($baseUrl, array_merge($queryParams, ['page' => $i])) . '" class="px-3 py-2 border rounded-lg text-sm ' . $activeClass . ' transition">' . $i . '</a>';
    }
    
    if ($endPage < $totalPages) {
        if ($endPage < $totalPages - 1) $html .= '<span class="px-2 py-2 text-gray-400">...</span>';
        $html .= '<a href="' . buildUrl($baseUrl, array_merge($queryParams, ['page' => $totalPages])) . '" class="px-3 py-2 border rounded-lg text-sm hover:bg-gray-100 transition">' . $totalPages . '</a>';
    }
    
    // Next
    if ($currentPage < $totalPages) {
        $nextUrl = buildUrl($baseUrl, array_merge($queryParams, ['page' => $currentPage + 1]));
        $html .= '<a href="' . $nextUrl . '" class="px-3 py-2 border rounded-lg text-sm hover:bg-gray-100 transition">Next &raquo;</a>';
    }
    
    $html .= '</div></div>';
    
    return $html;
}

/**
 * Build URL dengan parameter
 */
function buildUrl($baseUrl, $params) {
    $query = http_build_query(array_filter($params, function($v) { return $v !== '' && $v !== null; }));
    return $baseUrl . ($query ? '&' . $query : '');
}

/**
 * Get current page from URL
 */
function getCurrentPage() {
    return isset($_GET['page_num']) ? max(1, (int)$_GET['page_num']) : 1;
}


function terbilang($angka) {
    $angka = abs($angka);
    $bilangan = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];
    
    if ($angka < 12) {
        return $bilangan[$angka];
    } elseif ($angka < 20) {
        return terbilang($angka - 10) . ' Belas';
    } elseif ($angka < 100) {
        return terbilang(floor($angka / 10)) . ' Puluh ' . terbilang($angka % 10);
    } elseif ($angka < 200) {
        return 'Seratus ' . terbilang($angka - 100);
    } elseif ($angka < 1000) {
        return terbilang(floor($angka / 100)) . ' Ratus ' . terbilang($angka % 100);
    } elseif ($angka < 2000) {
        return 'Seribu ' . terbilang($angka - 1000);
    } elseif ($angka < 1000000) {
        return terbilang(floor($angka / 1000)) . ' Ribu ' . terbilang($angka % 1000);
    } elseif ($angka < 1000000000) {
        return terbilang(floor($angka / 1000000)) . ' Juta ' . terbilang($angka % 1000000);
    }
    return '';
}

// ============================================
// FUNGSI PENGATURAN
// ============================================

function getPengaturan($kunci, $default = '') {
    global $conn;
    $result = mysqli_fetch_assoc(mysqli_query($conn, "SELECT nilai FROM pengaturan WHERE kunci = '$kunci'"));
    return $result ? $result['nilai'] : $default;
}
?>