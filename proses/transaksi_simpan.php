<?php
session_start();
require_once '../config/database.php';
require_once '../functions/helper.php';
require_once '../functions/harga.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Silakan login dulu']);
    exit;
}

$user_id = $_SESSION['user_id'];
$total = (int)$_POST['total'];
$bayar = (int)$_POST['bayar'];
$pelanggan_id = $_POST['pelanggan_id'] ? (int)$_POST['pelanggan_id'] : null;
$cart = json_decode($_POST['cart'], true);

if (empty($cart)) {
    echo json_encode(['success' => false, 'message' => 'Keranjang kosong!']);
    exit;
}

$kembalian = $bayar >= $total ? $bayar - $total : 0;
$kurang = $bayar < $total ? $total - $bayar : 0;
$status = $bayar >= $total ? 'lunas' : 'piutang';
$no_invoice = generateInvoice();

mysqli_begin_transaction($conn);

try {
    // ==================== 1. INSERT TRANSAKSI ====================
    $sql = "INSERT INTO transaksi (no_invoice, user_id, pelanggan_id, total, bayar, kembalian, kurang, status) 
            VALUES ('$no_invoice', $user_id, " . ($pelanggan_id ?? 'NULL') . ", $total, $bayar, $kembalian, $kurang, '$status')";
    mysqli_query($conn, $sql);
    $transaksi_id = mysqli_insert_id($conn);

    // ==================== 2. INSERT DETAIL & UPDATE STOK ====================
    foreach ($cart as $item) {
        $produk_id = (int)$item['produkId'];
        $qty = (int)$item['qty'];
        $subtotal = $item['subtotal'];
        
        // Ambil data produk (kalau bukan custom)
        $produk = null;
        if ($produk_id > 0 && empty($item['isCustom'])) {
            $produk = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM produk WHERE id = $produk_id"));
        }
        
        if (!empty($item['isPPOB']) && $item['isPPOB'] === true) {
            // ============ ITEM PPOB ============
            $nominal = (int)$item['nominal'];
            $admin = (int)$item['admin'];
            $subtotal = $nominal + $admin;
            $satuan = 'transaksi';
            $tipe_harga = 'PPOB';
            $harga_satuan = $subtotal;
            
        } elseif (!empty($item['isCustom']) && $item['isCustom'] === true) {
            // ============ ITEM CUSTOM ============
            $subtotal = $item['harga'] * $qty;
            $satuan = 'pcs';
            $tipe_harga = 'custom';
            $harga_satuan = $item['harga'];
            
            $nama_custom = mysqli_real_escape_string($conn, $item['nama']);
            $harga_jual_custom = (int)$item['harga'];
            $kode_custom = 'CUST-' . date('Ymd') . '-' . rand(100, 999);
            
            $cek = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM produk WHERE nama = '$nama_custom'"));
            
            if (!$cek) {
                mysqli_query($conn, "
                    INSERT INTO produk (kode, nama, satuan_dasar, harga_beli, harga_jual, stok_dasar) 
                    VALUES ('$kode_custom', '$nama_custom', 'pcs', 0, $harga_jual_custom, 0)
                ");
                $produk_id = mysqli_insert_id($conn);
            } else {
                $produk_id = $cek['id'];
            }
            
        } else {
            // ============ ITEM BARANG FISIK ============
            $harga_aktual = $item['harga'];
            $subtotal = $harga_aktual * $qty;
            $satuan = ($item['satuanType'] != 'dasar') ? $item['satuanType'] : ($produk['satuan_dasar'] ?? 'pcs');
            $tipe_harga = ($item['satuanType'] != 'dasar') ? 'Paket' : (($item['hargaOverride'] ?? false) ? 'override' : 'Ecer');
            $harga_satuan = $harga_aktual;
            
            // ============ KURANGI STOK ============
            if ($produk && $produk['stok_dasar'] > 0) {
                // Hitung qty dalam satuan dasar
                $qty_dasar = $qty;
                
                // Jika pakai satuan paket (bukan 'dasar', bukan PPOB, bukan custom)
                if (!empty($item['satuanType']) && $item['satuanType'] != 'dasar' && empty($item['isPPOB']) && empty($item['isCustom'])) {
                    $satuan_query = mysqli_fetch_assoc(mysqli_query($conn, "
                        SELECT isi_satuan FROM satuan 
                        WHERE produk_id = $produk_id AND nama_satuan = '{$item['satuanType']}'
                    "));
                    if ($satuan_query) {
                        $qty_dasar = $qty * $satuan_query['isi_satuan'];
                    }
                }
                
                if ($produk['stok_dasar'] < $qty_dasar) {
                    throw new Exception("Stok {$produk['nama']} tidak cukup! Tersedia: {$produk['stok_dasar']} {$produk['satuan_dasar']}");
                }
                
                mysqli_query($conn, "UPDATE produk SET stok_dasar = stok_dasar - $qty_dasar WHERE id = $produk_id");
                
                mysqli_query($conn, "INSERT INTO mutasi_stok (produk_id, transaksi_id, qty_keluar, keterangan) 
                                     VALUES ($produk_id, $transaksi_id, $qty_dasar, 'Penjualan $no_invoice')");
            }
        }

        // Insert detail transaksi
        mysqli_query($conn, "
    INSERT INTO transaksi_detail (transaksi_id, produk_id, qty, qty_dasar, satuan, tipe_harga, harga_satuan, subtotal) 
    VALUES ($transaksi_id, $produk_id, $qty, $qty_dasar, '$satuan', '$tipe_harga', $harga_satuan, $subtotal)
");
    }

    // ==================== 3. PIUTANG JIKA KURANG ====================
    if ($status == 'piutang' && $pelanggan_id) {
        $pl = mysqli_fetch_assoc(mysqli_query($conn, "SELECT saldo_piutang, nama FROM pelanggan WHERE id = $pelanggan_id"));
        $saldo_sebelum = $pl['saldo_piutang'];
        $saldo_sesudah = $saldo_sebelum + $kurang;

        mysqli_query($conn, "
            INSERT INTO piutang (pelanggan_id, transaksi_id, no_referensi, tipe, jumlah, saldo_sebelum, saldo_sesudah, keterangan, user_id) 
            VALUES ($pelanggan_id, $transaksi_id, '$no_invoice', 'transaksi', $kurang, $saldo_sebelum, $saldo_sesudah, 'Kekurangan bayar transaksi', $user_id)
        ");
        
        mysqli_query($conn, "UPDATE pelanggan SET saldo_piutang = $saldo_sesudah WHERE id = $pelanggan_id");
    }

    // ==================== 4. LOG AKTIVITAS ====================
    $detail_log = "Transaksi $no_invoice: Total Rp $total, Bayar Rp $bayar";
    if ($pelanggan_id) {
        $pl_name = mysqli_fetch_assoc(mysqli_query($conn, "SELECT nama FROM pelanggan WHERE id = $pelanggan_id"))['nama'];
        $detail_log .= ", Pelanggan: $pl_name";
    }
    if ($status == 'piutang') $detail_log .= ", Piutang: Rp $kurang";
    
    $has_custom = false;
    foreach ($cart as $item) {
        if (!empty($item['isCustom']) && $item['isCustom'] === true) {
            $has_custom = true;
            break;
        }
    }
    if ($has_custom) $detail_log .= " (Termasuk produk custom)";
    
    mysqli_query($conn, "
        INSERT INTO log_aktivitas (user_id, aktivitas, detail) 
        VALUES ($user_id, 'Transaksi', '$detail_log')
    ");

    mysqli_commit($conn);

    echo json_encode([
        'success' => true,
        'message' => 'Transaksi berhasil',
        'transaksi_id' => $transaksi_id,
        'no_invoice' => $no_invoice
    ]);

} catch (Exception $e) {
    mysqli_rollback($conn);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>