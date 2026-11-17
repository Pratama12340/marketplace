# Pasar Nusantara — Web Marketplace Tradisional & REST API (PHP Native + MySQLi)

Aplikasi Web Marketplace Tradisional dilengkapi layanan **REST API (JSON Web Service)** menggunakan PHP Native, MySQLi (Prepared Statements & Database Transaction), dan Bootstrap 5.

## Fitur Utama
1. **Web Marketplace (Frontend & Portal Penjual)**
   - Katalog produk dengan pencarian & filter kategori, detail produk, keranjang belanja berbasis sesi, dan checkout dengan **MySQLi Database Transaction** (`BEGIN`, `COMMIT`, `ROLLBACK`).
   - Portal Penjual (`admin/`): Login autentikasi password hash, CRUD katalog produk + upload gambar, dan manajemen status pesanan.
2. **Layanan Web Service / REST API (`api/`)**
   - `GET /api/produk.php` — Menampilkan seluruh daftar produk (mendukung filter `?kategori=` & `?q=`).
   - `GET /api/produk.php?id={id}` — Menampilkan detail satu produk berdasarkan ID (`404 Not Found` bila tidak ditemukan).
   - `GET /api/kategori.php` — Menampilkan daftar seluruh kategori beserta jumlah produknya.
   - `POST /api/pesanan.php` — Membuat pesanan baru melalui payload JSON dengan validasi stok & **Database Transaction**.
   - `GET /api/pesanan.php` (atau `?kode=INV...`) — Menampilkan daftar atau rincian pesanan.

## Struktur Direktori
```
├── api/
│   ├── bootstrap.php      # Koneksi DB, helper JSON response, CORS, & error handler
│   ├── produk.php         # Endpoint GET daftar produk & GET detail produk (?id=)
│   ├── kategori.php       # Endpoint GET daftar kategori
│   └── pesanan.php        # Endpoint POST buat pesanan baru & GET riwayat pesanan
├── admin/                 # Portal penjual (login, logout, kelola produk, pesanan)
├── includes/              # Komponen utama (init.php, header.php, footer.php)
├── uploads/               # Gambar & ilustrasi produk
├── docs/                  # Dokumen Perancangan, ERD, Laporan REST API (.doc & screenshot)
├── index.php, produk.php, keranjang.php, checkout.php, sukses.php
├── database.sql           # Skema tabel & data awal MySQL
└── config.example.php     # Contoh konfigurasi koneksi database
```

## Instalasi Lokal (XAMPP / Laragon)
1. Buat database bernama `marketplace` di phpMyAdmin, lalu import file `database.sql`.
2. Salin `config.example.php` menjadi `config.php`, sesuaikan kredensial (`localhost`, `root`, password kosong, `marketplace`).
3. Akses aplikasi web di `http://localhost/marketplace` dan endpoint REST API di `http://localhost/marketplace/api/produk.php`.
   - **Akun Penjual Default:** `admin@pasar.com` / `admin123`

## Contoh Penggunaan REST API

### 1. Menampilkan Seluruh Produk
- **Request:** `GET http://localhost/marketplace/api/produk.php`
- **Response (`200 OK`):**
```json
{
    "status": "success",
    "code": 200,
    "message": "Daftar produk berhasil diambil.",
    "data": {
        "total": 4,
        "produk": [
            {
                "id": 1,
                "nama_produk": "Cabai Merah 1 Kg",
                "harga": 45000,
                "harga_format": "Rp 45.000",
                "stok": 48,
                "gambar": "cabai.svg",
                "kategori": { "id": 1, "nama_kategori": "Sayur & Buah" },
                "penjual": { "id": 1, "nama": "Admin Pasar" }
            }
        ]
    }
}
```

### 2. Menampilkan Detail Produk
- **Request:** `GET http://localhost/marketplace/api/produk.php?id=1`

### 3. Membuat Pesanan Baru
- **Request:** `POST http://localhost/marketplace/api/pesanan.php`
- **Headers:** `Content-Type: application/json`
- **Body (raw JSON):**
```json
{
    "nama_pembeli": "Rina Kartika",
    "telepon": "081298765432",
    "alamat": "Jl. Sudirman No. 45, Bandung",
    "items": [
        { "produk_id": 1, "jumlah": 2 },
        { "produk_id": 2, "jumlah": 1 }
    ]
}
```
