# Web Marketplace Tradisional (PHP Native + MySQLi + Bootstrap 5)

Fitur: daftar produk (cari & filter kategori), detail produk, CRUD produk (login penjual), keranjang belanja, checkout yang menyimpan transaksi ke database (dengan database transaction), serta manajemen status pesanan.

## Struktur
```
index.php, produk.php, keranjang.php, checkout.php, sukses.php
admin/   login, logout, produk (list+hapus), produk_form (tambah/edit), pesanan
includes/ init.php (koneksi & helper), header.php, footer.php
uploads/  gambar produk
database.sql, config.example.php, docs/PERANCANGAN.md
```

## Instalasi lokal (XAMPP)
1. Buat database `marketplace`, lalu import `database.sql`.
2. Salin `config.example.php` menjadi `config.php`, isi dengan `localhost`, `root`, password kosong, `marketplace`.
3. Letakkan folder di `htdocs`, buka `http://localhost/marketplace`.

Login penjual: `admin@pasar.com` / `admin123` (ganti setelah deploy).

## Deploy ke InfinityFree
1. Daftar di infinityfree.com, buat akun hosting (subdomain gratis).
2. Di Control Panel, buat MySQL Database. Catat hostname (sqlXXX.infinityfree.com), username, nama database, dan password.
3. Buka phpMyAdmin, pilih database, import `database.sql`.
4. Salin `config.example.php` jadi `config.php`, isi kredensial InfinityFree.
5. Upload semua file ke folder `htdocs` via File Manager atau FTP (FileZilla).
6. Buka subdomain kamu dan uji aplikasi.

## Upload ke GitHub
```
git init
git add .
git commit -m "Marketplace tradisional"
git branch -M main
git remote add origin https://github.com/USERNAME/marketplace.git
git push -u origin main
```
`config.php` sudah masuk `.gitignore`, jadi password tidak ikut ter-upload. Cek dengan `git status` sebelum commit.
