
# 🛒 Aplikasi Kasir - Point of Sale

#FULL DIBUAT DENGAN AI DEEPSEEK

Aplikasi kasir lengkap untuk toko kelontong, warung sembako, dan agen PPOB. Dibangun dengan **PHP Native + MySQL + Tailwind CSS**.

---

## 🚀 Fitur Utama

### 💰 Kasir (Desktop & Mobile)
- Live search produk dengan gambar
- Scan barcode (USB scanner & kamera HP)
- Multi satuan: ecer, pak, dus, slop, grosir
- Edit harga (override) dengan alert HPP
- Produk custom (manual input)
- PPOB: pulsa, token, transfer, tarik tunai, tagihan
- Metode bayar: Cash & Piutang
- SweetAlert2 untuk semua notifikasi
- Cetak struk thermal 58mm + Download PDF
- Keyboard shortcut (F2, F8, Esc)

### 📦 Produk & Inventori
- CRUD produk + upload gambar (auto resize & kompresi)
- Multi-level packaging (paket/grosir)
- Restok dengan live search + auto kalkulasi harga
- Stock opname (rusak/kadaluarsa/hilang)
- Tracking mutasi stok

### 👥 Pelanggan & Piutang
- CRUD pelanggan
- Piutang dengan saldo berjalan
- Bayar piutang (full/cicilan)
- Pinjaman manual
- Kwitansi 58mm

### 📊 Laporan Lengkap
- Penjualan (filter tanggal, top produk)
- Laba Rugi (Pendapatan - HPP - Biaya)
- Arus Kas (masuk vs keluar)
- Stok (nilai inventori, mutasi)
- Keuangan (harian & bulanan + grafik)
- Pelanggan (aktif, utang, terbaik, paling untung)
- Pembelian (per produk, per supplier, tren harga)
- Analitik Bisnis (jam sibuk, hari ramai, tren 6 bulan)
- Program THR Loyalitas (5 level)

### ⚙️ Sistem
- Multi-user (Admin & Kasir)
- Void transaksi (stok balik, piutang batal)
- Log aktivitas (audit trail)
- Backup database (1-klik)
- Manajemen user

---

## 🛠️ Teknologi

| Layer | Teknologi |
|-------|-----------|
| Backend | PHP 7.4+ |
| Database | MySQL / MariaDB |
| Frontend | HTML5, Tailwind CSS, JavaScript |
| UI | SweetAlert2, Font Awesome 6 |
| Barcode | html5-qrcode (mobile), USB scanner (desktop) |
| Printer | Thermal 58mm (CSS @page) |

---

## 📦 Instalasi

### 1. Clone repository
```bash
git clone https://github.com/username/aplikasi-kasir.git
