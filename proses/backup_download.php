<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php?page=login');
    exit;
}

$file = $_GET['file'] ?? '';
$file_path = BACKUP_PATH . basename($file);

if (file_exists($file_path)) {
    header('Content-Type: application/sql');
    header('Content-Disposition: attachment; filename="' . basename($file) . '"');
    header('Content-Length: ' . filesize($file_path));
    readfile($file_path);
    exit;
} else {
    echo "File tidak ditemukan!";
}
?>