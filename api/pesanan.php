<?php
require_once __DIR__ . '/bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// GET /api/pesanan.php (atau ?kode=INV...)
if ($method === 'GET') {
    if (!empty($_GET['kode'])) {
        $kode = trim($_GET['kode']);
        $st = $db->prepare('SELECT * FROM pesanan WHERE kode_pesanan = ?');
        $st->bind_param('s', $kode);
        $st->execute();
        $o = $st->get_result()->fetch_assoc();
        if (!$o) {
            json_response(404, false, 'Pesanan dengan kode ' . $kode . ' tidak ditemukan.');
        }

        $d = $db->prepare('
            SELECT dp.produk_id, pr.nama_produk, dp.harga, dp.jumlah, dp.subtotal
            FROM detail_pesanan dp
            JOIN produk pr ON pr.id = dp.produk_id
            WHERE dp.pesanan_id = ?
        ');
        $d->bind_param('i', $o['id']);
        $d->execute();
        $details = [];
        foreach ($d->get_result() as $r) {
            $details[] = [
                'produk_id'   => (int)$r['produk_id'],
                'nama_produk' => $r['nama_produk'],
                'harga'       => (float)$r['harga'],
                'jumlah'      => (int)$r['jumlah'],
                'subtotal'    => (float)$r['subtotal'],
            ];
        }

        json_response(200, true, 'Detail pesanan berhasil diambil.', [
            'id'           => (int)$o['id'],
            'kode_pesanan' => $o['kode_pesanan'],
            'nama_pembeli' => $o['nama_pembeli'],
            'telepon'      => $o['telepon'],
            'alamat'       => $o['alamat'],
            'total'        => (float)$o['total'],
            'status'       => $o['status'],
            'created_at'   => $o['created_at'],
            'items'        => $details,
        ]);
    }

    $res = $db->query('SELECT * FROM pesanan ORDER BY id DESC');
    $orders = [];
    while ($o = $res->fetch_assoc()) {
        $orders[] = [
            'id'           => (int)$o['id'],
            'kode_pesanan' => $o['kode_pesanan'],
            'nama_pembeli' => $o['nama_pembeli'],
            'telepon'      => $o['telepon'],
            'alamat'       => $o['alamat'],
            'total'        => (float)$o['total'],
            'status'       => $o['status'],
            'created_at'   => $o['created_at'],
        ];
    }
    json_response(200, true, 'Daftar pesanan berhasil diambil.', [
        'total'   => count($orders),
        'pesanan' => $orders,
    ]);
}

// Endpoint 4: POST /api/pesanan.php (Membuat Pesanan Baru via REST API)
if ($method === 'POST') {
    $in = json_input();
    $nama    = trim((string)($in['nama_pembeli'] ?? $in['nama'] ?? ''));
    $telepon = trim((string)($in['telepon'] ?? ''));
    $alamat  = trim((string)($in['alamat'] ?? ''));
    $items   = $in['items'] ?? [];

    if ($nama === '' || $telepon === '' || $alamat === '') {
        json_response(400, false, 'Field nama_pembeli, telepon, dan alamat wajib diisi.');
    }
    if (!preg_match('/^[0-9+\-\s]{8,20}$/', $telepon)) {
        json_response(400, false, 'Format nomor telepon tidak valid.');
    }
    if (!is_array($items) || count($items) === 0) {
        json_response(400, false, 'Field items (daftar produk yang dipesan) tidak boleh kosong.');
    }

    try {
        $db->begin_transaction();

        $validatedItems = [];
        $total = 0.0;

        $stCheck = $db->prepare('SELECT id, nama_produk, harga, stok FROM produk WHERE id = ? FOR UPDATE');
        foreach ($items as $idx => $item) {
            $pid = (int)($item['produk_id'] ?? 0);
            $qty = (int)($item['jumlah'] ?? 0);
            if ($pid <= 0 || $qty <= 0) {
                $db->rollback();
                json_response(400, false, "Item ke-" . ($idx + 1) . " harus memiliki produk_id dan jumlah > 0.");
            }

            $stCheck->bind_param('i', $pid);
            $stCheck->execute();
            $prod = $stCheck->get_result()->fetch_assoc();

            if (!$prod) {
                $db->rollback();
                json_response(404, false, "Produk dengan ID $pid tidak ditemukan.");
            }
            if ((int)$prod['stok'] < $qty) {
                $db->rollback();
                json_response(400, false, "Stok produk '{$prod['nama_produk']}' tidak mencukupi (tersedia: {$prod['stok']}, diminta: $qty).");
            }

            $harga = (float)$prod['harga'];
            $sub   = $harga * $qty;
            $total += $sub;

            $validatedItems[] = [
                'produk_id'   => $pid,
                'nama_produk' => $prod['nama_produk'],
                'harga'       => $harga,
                'jumlah'      => $qty,
                'subtotal'    => $sub,
            ];
        }

        $kode = 'INV' . date('ymdHis') . random_int(10, 99);
        $stOrder = $db->prepare('INSERT INTO pesanan (kode_pesanan, nama_pembeli, telepon, alamat, total) VALUES (?, ?, ?, ?, ?)');
        $stOrder->bind_param('ssssd', $kode, $nama, $telepon, $alamat, $total);
        $stOrder->execute();
        $pesananId = (int)$db->insert_id;

        $stDetail = $db->prepare('INSERT INTO detail_pesanan (pesanan_id, produk_id, harga, jumlah, subtotal) VALUES (?, ?, ?, ?, ?)');
        $stStok   = $db->prepare('UPDATE produk SET stok = stok - ? WHERE id = ? AND stok >= ?');

        foreach ($validatedItems as $vi) {
            $stDetail->bind_param('iidid', $pesananId, $vi['produk_id'], $vi['harga'], $vi['jumlah'], $vi['subtotal']);
            $stDetail->execute();

            $stStok->bind_param('iii', $vi['jumlah'], $vi['produk_id'], $vi['jumlah']);
            $stStok->execute();
            if ($stStok->affected_rows === 0) {
                throw new RuntimeException("Gagal memperbarui stok untuk produk {$vi['nama_produk']}.");
            }
        }

        $db->commit();

        json_response(201, true, 'Pesanan baru berhasil dibuat.', [
            'id'           => $pesananId,
            'kode_pesanan' => $kode,
            'nama_pembeli' => $nama,
            'telepon'      => $telepon,
            'alamat'       => $alamat,
            'total'        => $total,
            'total_format' => 'Rp ' . number_format($total, 0, ',', '.'),
            'status'       => 'baru',
            'items'        => $validatedItems,
        ]);
    } catch (Throwable $ex) {
        $db->rollback();
        json_response(500, false, 'Gagal memproses pesanan: ' . $ex->getMessage());
    }
}

json_response(405, false, 'Method tidak diizinkan. Gunakan GET atau POST.');
