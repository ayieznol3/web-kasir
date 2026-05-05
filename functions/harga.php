<?php
function hitungHarga($produk_id, $qty, $satuan_input = 'dasar') {
    global $conn;
    
    // 1. Konversi ke satuan dasar
    $qty_dasar = $qty;
    $nama_satuan_input = 'Ecer';
    
    if ($satuan_input != 'dasar') {
        $s = mysqli_query($conn, "SELECT * FROM satuan WHERE produk_id = $produk_id AND nama_satuan = '$satuan_input'");
        if ($satuan_row = mysqli_fetch_assoc($s)) {
            $qty_dasar = $qty * $satuan_row['isi_satuan'];
            $nama_satuan_input = $satuan_row['nama_satuan'];
        }
    }
    
    // 2. Ambil data produk
    $p = mysqli_query($conn, "SELECT * FROM produk WHERE id = $produk_id");
    $produk = mysqli_fetch_assoc($p);
    $harga_ecer = $produk['harga_jual'];
    
    // 3. Ambil SEMUA satuan (dari terbesar)
    $satuan_list = [];
    $q_satuan = mysqli_query($conn, "SELECT * FROM satuan WHERE produk_id = $produk_id ORDER BY isi_satuan DESC");
    while($row = mysqli_fetch_assoc($q_satuan)) {
        $satuan_list[] = $row;
    }
    
    $sisa = $qty_dasar;
    $total = 0;
    $detail = [];
    
    // 4. Pecah pakai satuan terbesar dulu
    foreach ($satuan_list as $satuan) {
        $jumlah_satuan = floor($sisa / $satuan['isi_satuan']);
        
        if ($jumlah_satuan > 0) {
            // Harga satuan ini (custom atau otomatis)
            $harga_per_satuan = $satuan['harga_jual'] ?? ($satuan['isi_satuan'] * $harga_ecer);
            $subtotal = $jumlah_satuan * $harga_per_satuan;
            $total += $subtotal;
            
            $detail[] = [
                'satuan'    => $satuan['nama_satuan'],
                'jumlah'    => $jumlah_satuan,
                'isi'       => $satuan['isi_satuan'],
                'qty'       => $jumlah_satuan * $satuan['isi_satuan'],
                'harga_per_satuan' => $harga_per_satuan,
                'harga_per_item'   => round($harga_per_satuan / $satuan['isi_satuan']),
                'subtotal'  => $subtotal
            ];
            
            $sisa -= $jumlah_satuan * $satuan['isi_satuan'];
        }
    }
    
    // 5. Sisa pakai harga ecer
    if ($sisa > 0) {
        $subtotal_ecer = $sisa * $harga_ecer;
        $total += $subtotal_ecer;
        $detail[] = [
            'satuan'    => 'Ecer',
            'jumlah'    => $sisa,
            'isi'       => 1,
            'qty'       => $sisa,
            'harga_per_satuan' => $harga_ecer,
            'harga_per_item'   => $harga_ecer,
            'subtotal'  => $subtotal_ecer
        ];
    }
    
    // 6. Cek diskon grosir
    $diskon = 0;
    $g = mysqli_query($conn, "SELECT * FROM grosir WHERE produk_id = $produk_id AND min_qty <= $qty_dasar ORDER BY min_qty DESC LIMIT 1");
    if ($grosir = mysqli_fetch_assoc($g)) {
        if ($grosir['tipe_diskon'] == 'persen') {
            $diskon = round($total * ($grosir['nilai_diskon'] / 100));
        } else {
            $diskon = $qty_dasar * $grosir['nilai_diskon'];
        }
    }
    
    $total_final = $total - $diskon;
    
    return [
        'total'         => $total_final,
        'total_kotor'   => $total,
        'qty_dasar'     => $qty_dasar,
        'qty_input'     => $qty,
        'satuan_input'  => $nama_satuan_input,
        'detail'        => $detail,
        'diskon'        => $diskon,
        'ada_diskon'    => $diskon > 0,
        'produk'        => $produk
    ];
}
?>