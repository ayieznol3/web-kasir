<?php
// ============================================
// FUNGSI PIUTANG
// ============================================

function getHistoriPiutang($pelanggan_id) {
    global $conn;
    return mysqli_query($conn, "
        SELECT p.*, t.no_invoice, u.nama as nama_user
        FROM piutang p
        LEFT JOIN transaksi t ON p.transaksi_id = t.id
        JOIN users u ON p.user_id = u.id
        WHERE p.pelanggan_id = $pelanggan_id
        ORDER BY p.created_at DESC
    ");
}
?>