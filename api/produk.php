<?php
require_once __DIR__ . '/bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method !== 'GET') {
    json_response(405, false, 'Method tidak diizinkan. Gunakan GET.');
}

// Endpoint 2: GET /api/produk.php?id={id} (Detail Produk)
if (isset($_GET['id'])) {
    $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if ($id === false || $id <= 0) {
        json_response(400, false, 'Parameter id produk tidak valid.');
    }

    $st = $db->prepare('
        SELECT p.id, p.nama_produk, p.deskripsi, p.harga, p.stok, p.gambar, p.created_at,
               k.id AS kategori_id, k.nama_kategori,
               u.id AS penjual_id, u.nama AS nama_penjual
        FROM produk p
        JOIN kategori k ON k.id = p.kategori_id
        JOIN users u ON u.id = p.user_id
        WHERE p.id = ?
    ');
    $st->bind_param('i', $id);
    $st->execute();
    $row = $st->get_result()->fetch_assoc();

    if (!$row) {
        json_response(404, false, 'Produk dengan ID ' . $id . ' tidak ditemukan.');
    }

    $data = [
        'id'          => (int)$row['id'],
        'nama_produk' => $row['nama_produk'],
        'deskripsi'   => $row['deskripsi'],
        'harga'       => (float)$row['harga'],
        'harga_format'=> 'Rp ' . number_format((float)$row['harga'], 0, ',', '.'),
        'stok'        => (int)$row['stok'],
        'gambar'      => $row['gambar'],
        'gambar_url'  => $row['gambar'] ? base_url() . '/uploads/' . $row['gambar'] : null,
        'kategori'    => [
            'id'            => (int)$row['kategori_id'],
            'nama_kategori' => $row['nama_kategori'],
        ],
        'penjual'     => [
            'id'   => (int)$row['penjual_id'],
            'nama' => $row['nama_penjual'],
        ],
        'created_at'  => $row['created_at'],
    ];

    json_response(200, true, 'Detail produk berhasil diambil.', $data);
}

// Endpoint 1: GET /api/produk.php (Daftar Seluruh Produk + Filter Opsional)
$q = trim($_GET['q'] ?? '');
$kat = isset($_GET['kategori']) ? (int)$_GET['kategori'] : 0;
$like = "%$q%";

$st = $db->prepare('
    SELECT p.id, p.nama_produk, p.deskripsi, p.harga, p.stok, p.gambar, p.created_at,
           k.id AS kategori_id, k.nama_kategori,
           u.id AS penjual_id, u.nama AS nama_penjual
    FROM produk p
    JOIN kategori k ON k.id = p.kategori_id
    JOIN users u ON u.id = p.user_id
    WHERE p.nama_produk LIKE ? AND (? = 0 OR p.kategori_id = ?)
    ORDER BY p.id ASC
');
$st->bind_param('sii', $like, $kat, $kat);
$st->execute();
$res = $st->get_result();

$items = [];
while ($row = $res->fetch_assoc()) {
    $items[] = [
        'id'           => (int)$row['id'],
        'nama_produk'  => $row['nama_produk'],
        'deskripsi'    => $row['deskripsi'],
        'harga'        => (float)$row['harga'],
        'harga_format' => 'Rp ' . number_format((float)$row['harga'], 0, ',', '.'),
        'stok'         => (int)$row['stok'],
        'gambar'       => $row['gambar'],
        'gambar_url'   => $row['gambar'] ? base_url() . '/uploads/' . $row['gambar'] : null,
        'kategori'     => [
            'id'            => (int)$row['kategori_id'],
            'nama_kategori' => $row['nama_kategori'],
        ],
        'penjual'      => [
            'id'   => (int)$row['penjual_id'],
            'nama' => $row['nama_penjual'],
        ],
        'created_at'   => $row['created_at'],
    ];
}

json_response(200, true, 'Daftar produk berhasil diambil.', [
    'total'  => count($items),
    'produk' => $items,
]);
