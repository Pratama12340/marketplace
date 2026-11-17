<?php
require_once __DIR__ . '/bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method !== 'GET') {
    json_response(405, false, 'Method tidak diizinkan. Gunakan GET.');
}

// Endpoint 3: GET /api/kategori.php
$res = $db->query('
    SELECT k.id, k.nama_kategori, COUNT(p.id) AS jumlah_produk
    FROM kategori k
    LEFT JOIN produk p ON p.kategori_id = k.id
    GROUP BY k.id, k.nama_kategori
    ORDER BY k.id ASC
');

$kategori = [];
while ($row = $res->fetch_assoc()) {
    $kategori[] = [
        'id'            => (int)$row['id'],
        'nama_kategori' => $row['nama_kategori'],
        'jumlah_produk' => (int)$row['jumlah_produk'],
    ];
}

json_response(200, true, 'Daftar kategori berhasil diambil.', [
    'total'    => count($kategori),
    'kategori' => $kategori,
]);
