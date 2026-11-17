-- Database: marketplace (import via phpMyAdmin)
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','penjual') NOT NULL DEFAULT 'penjual',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE kategori (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama_kategori VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE produk (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  kategori_id INT NOT NULL,
  nama_produk VARCHAR(150) NOT NULL,
  deskripsi TEXT,
  harga DECIMAL(12,2) NOT NULL,
  stok INT NOT NULL DEFAULT 0,
  gambar VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (kategori_id) REFERENCES kategori(id)
) ENGINE=InnoDB;

CREATE TABLE keranjang (
  id INT AUTO_INCREMENT PRIMARY KEY,
  session_id VARCHAR(64) NOT NULL,
  produk_id INT NOT NULL,
  jumlah INT NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (produk_id) REFERENCES produk(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE pesanan (
  id INT AUTO_INCREMENT PRIMARY KEY,
  kode_pesanan VARCHAR(20) NOT NULL UNIQUE,
  nama_pembeli VARCHAR(100) NOT NULL,
  telepon VARCHAR(20) NOT NULL,
  alamat TEXT NOT NULL,
  total DECIMAL(12,2) NOT NULL,
  status ENUM('baru','diproses','selesai','batal') NOT NULL DEFAULT 'baru',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE detail_pesanan (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pesanan_id INT NOT NULL,
  produk_id INT NOT NULL,
  harga DECIMAL(12,2) NOT NULL,
  jumlah INT NOT NULL,
  subtotal DECIMAL(12,2) NOT NULL,
  FOREIGN KEY (pesanan_id) REFERENCES pesanan(id) ON DELETE CASCADE,
  FOREIGN KEY (produk_id) REFERENCES produk(id)
) ENGINE=InnoDB;

-- Data awal. Login admin: admin@pasar.com / admin123 (WAJIB ganti setelah deploy)
INSERT INTO users (nama,email,password,role) VALUES ('Admin Pasar','admin@pasar.com','$2y$10$aeC8ttNpspilT3oBVHQ0oOTfRT0FV1W0fvZjL4zkto40PeysZxWsG','admin');
INSERT INTO kategori (nama_kategori) VALUES ('Sayur & Buah'),('Makanan & Jajanan'),('Kerajinan'),('Pakaian');
INSERT INTO produk (user_id,kategori_id,nama_produk,deskripsi,harga,stok,gambar) VALUES
(1,1,'Cabai Merah 1 Kg','Cabai merah segar langsung dari petani lokal dengan kualitas pilihan, pedas alami, dan baru dipetik.',45000,50,'cabai.svg'),
(1,2,'Keripik Singkong 250 gr','Keripik singkong renyah bumbu tradisional, diolah secara higienis tanpa bahan pengawet.',15000,100,'keripik.svg'),
(1,3,'Tas Anyaman Pandan','Tas anyaman tangan khas pengrajin daerah, kuat, ramah lingkungan, dan bernilai seni tinggi.',85000,20,'tas-pandan.svg'),
(1,4,'Kain Batik Tulis 2 m','Batik tulis motif klasik Nusantara dari bahan katun primisima yang halus dan nyaman dipakai.',250000,10,'batik.svg');
