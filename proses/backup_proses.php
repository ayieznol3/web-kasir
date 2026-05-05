<?php
session_start();
require_once '../config/database.php';
require_once '../functions/helper.php';

if (!isset($_SESSION['user_id']) || !isAdmin()) {
    redirect('../index.php?page=login');
}

// Nama file backup
$nama_file = 'backup_' . date('Ymd_His') . '.sql';

// Buat folder kalau belum ada
if (!is_dir(BACKUP_PATH)) {
    mkdir(BACKUP_PATH, 0755, true);
}

$file_path = BACKUP_PATH . $nama_file;

// Buka file untuk ditulis
$handle = fopen($file_path, 'w');

// Header SQL
$sql_header = "-- ============================================\n";
$sql_header .= "-- BACKUP DATABASE APLIKASI KASIR\n";
$sql_header .= "-- Tanggal: " . date('d/m/Y H:i:s') . "\n";
$sql_header .= "-- Oleh: " . $_SESSION['nama'] . "\n";
$sql_header .= "-- ============================================\n\n";
fwrite($handle, $sql_header);

// Ambil semua tabel
$tables = mysqli_query($conn, "SHOW TABLES");
while ($table = mysqli_fetch_array($tables)) {
    $table_name = $table[0];
    
    // Drop & Create table
    $create = mysqli_fetch_array(mysqli_query($conn, "SHOW CREATE TABLE `$table_name`"));
    fwrite($handle, "\n-- --------------------------------------------\n");
    fwrite($handle, "-- Tabel: $table_name\n");
    fwrite($handle, "-- --------------------------------------------\n\n");
    fwrite($handle, "DROP TABLE IF EXISTS `$table_name`;\n");
    fwrite($handle, $create[1] . ";\n\n");
    
    // Data
    $rows = mysqli_query($conn, "SELECT * FROM `$table_name`");
    if (mysqli_num_rows($rows) > 0) {
        fwrite($handle, "-- Data untuk tabel $table_name\n");
        
        while ($row = mysqli_fetch_assoc($rows)) {
            $values = [];
            foreach ($row as $value) {
                if ($value === null) {
                    $values[] = 'NULL';
                } else {
                    $values[] = "'" . mysqli_real_escape_string($conn, $value) . "'";
                }
            }
            fwrite($handle, "INSERT INTO `$table_name` VALUES (" . implode(', ', $values) . ");\n");
        }
        fwrite($handle, "\n");
    }
}

fclose($handle);

// Cek apakah file berhasil dibuat
if (file_exists($file_path)) {
    $size = filesize($file_path);
    $size_readable = round($size / 1024, 1) . ' KB';
    
    // Log
    mysqli_query($conn, "INSERT INTO log_aktivitas (user_id, aktivitas, detail) 
                         VALUES ({$_SESSION['user_id']}, 'Backup', 'Backup database: $nama_file ($size_readable)')");
    
    $_SESSION['sukses'] = "Backup berhasil!<br>File: $nama_file<br>Ukuran: $size_readable<br>Lokasi: Google Drive/Backup Kasir/";
    $_SESSION['sukses_type'] = 'success';
} else {
    $_SESSION['error'] = 'Gagal membuat file backup!';
    $_SESSION['error_type'] = 'danger';
}

redirect('../index.php?page=backup');
?>