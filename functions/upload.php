<?php
// ============================================
// FUNGSI UPLOAD GAMBAR
// ============================================

function uploadGambar($file, $nama_lama = null) {
    $target_dir = "../uploads/produk/";
    
    if (!isset($file) || $file['error'] == 4) {
        return ['success' => false, 'nama_file' => $nama_lama ?? 'default.png'];
    }
    
    if ($file['error'] != 0) {
        return ['success' => false, 'nama_file' => $nama_lama ?? 'default.png', 'error' => 'Error upload'];
    }
    
    if ($file['size'] > 5000000) { // 5MB max
        return ['success' => false, 'nama_file' => $nama_lama ?? 'default.png', 'error' => 'Ukuran terlalu besar (max 5MB)'];
    }
    
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($ext, $allowed)) {
        return ['success' => false, 'nama_file' => $nama_lama ?? 'default.png', 'error' => 'Format tidak diizinkan'];
    }
    
    $nama_baru = 'prd_' . time() . '_' . rand(100, 999) . '.jpg'; // Selalu jpg
    
    // ==================== KOMPRESI GAMBAR ====================
    $source = null;
    switch ($ext) {
        case 'jpeg':
        case 'jpg':
            $source = imagecreatefromjpeg($file['tmp_name']);
            break;
        case 'png':
            $source = imagecreatefrompng($file['tmp_name']);
            break;
        case 'gif':
            $source = imagecreatefromgif($file['tmp_name']);
            break;
        case 'webp':
            $source = imagecreatefromwebp($file['tmp_name']);
            break;
    }
    
    if ($source) {
        // Resize maksimal 500px
        $width = imagesx($source);
        $height = imagesy($source);
        $max_size = 500;
        
        if ($width > $max_size || $height > $max_size) {
            if ($width > $height) {
                $new_width = $max_size;
                $new_height = round($height * ($max_size / $width));
            } else {
                $new_height = $max_size;
                $new_width = round($width * ($max_size / $height));
            }
            
            $resized = imagecreatetruecolor($new_width, $new_height);
            
            // Pertahankan transparansi PNG
            if ($ext == 'png') {
                imagealphablending($resized, false);
                imagesavealpha($resized, true);
            }
            
            imagecopyresampled($resized, $source, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
            imagedestroy($source);
            $source = $resized;
        }
        
        // Simpan sebagai JPEG (kualitas 80%)
        imagejpeg($source, $target_dir . $nama_baru, 80);
        imagedestroy($source);
        
        // Hapus file lama
        if ($nama_lama && $nama_lama != 'default.png' && file_exists($target_dir . $nama_lama)) {
            unlink($target_dir . $nama_lama);
        }
        
        return ['success' => true, 'nama_file' => $nama_baru];
    }
    
    return ['success' => false, 'nama_file' => $nama_lama ?? 'default.png', 'error' => 'Gagal proses gambar'];
}

function getGambar($gambar = null) {
    if ($gambar && file_exists("uploads/produk/" . $gambar)) {
        return "uploads/produk/" . $gambar;
    }
    return "uploads/produk/default.png";
}

function hapusGambar($nama_file) {
    if ($nama_file && $nama_file != 'default.png') {
        $file = "../uploads/produk/" . $nama_file;
        if (file_exists($file)) {
            unlink($file);
        }
    }
}
?>