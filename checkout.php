<?php
require 'includes/init.php';
$sid = session_id(); $err = '';
$st = $db->prepare('SELECT k.produk_id,k.jumlah,p.nama_produk,p.harga,p.stok FROM keranjang k JOIN produk p ON p.id=k.produk_id WHERE k.session_id=?');
$st->bind_param('s', $sid); $st->execute(); $items = $st->get_result()->fetch_all(MYSQLI_ASSOC);
if (!$items) { flash('Keranjang kosong.', 'warning'); header('Location: keranjang.php'); exit; }
$total = array_sum(array_map(fn($i) => $i['harga'] * $i['jumlah'], $items));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $nama = trim($_POST['nama'] ?? ''); $tel = trim($_POST['telepon'] ?? ''); $alamat = trim($_POST['alamat'] ?? '');
    if ($nama === '' || $tel === '' || $alamat === '') $err = 'Semua data wajib diisi.';
    elseif (!preg_match('/^[0-9+\-\s]{8,20}$/', $tel)) $err = 'Nomor telepon tidak valid.';
    else {
        try {
            $db->begin_transaction();
            $kode = 'INV' . date('ymdHis') . random_int(10, 99);
            $p = $db->prepare('INSERT INTO pesanan (kode_pesanan,nama_pembeli,telepon,alamat,total) VALUES (?,?,?,?,?)');
            $p->bind_param('ssssd', $kode, $nama, $tel, $alamat, $total); $p->execute();
            $oid = $db->insert_id;
            $d = $db->prepare('INSERT INTO detail_pesanan (pesanan_id,produk_id,harga,jumlah,subtotal) VALUES (?,?,?,?,?)');
            $s = $db->prepare('UPDATE produk SET stok=stok-? WHERE id=? AND stok>=?');
            foreach ($items as $it) {
                $sub = $it['harga'] * $it['jumlah'];
                $d->bind_param('iidid', $oid, $it['produk_id'], $it['harga'], $it['jumlah'], $sub); $d->execute();
                $s->bind_param('iii', $it['jumlah'], $it['produk_id'], $it['jumlah']); $s->execute();
                if ($s->affected_rows === 0) throw new Exception('Stok ' . $it['nama_produk'] . ' tidak mencukupi.');
            }
            $c = $db->prepare('DELETE FROM keranjang WHERE session_id=?'); $c->bind_param('s', $sid); $c->execute();
            $db->commit();
            header('Location: sukses.php?kode=' . urlencode($kode)); exit;
        } catch (Throwable $ex) { $db->rollback(); $err = 'Gagal membuat pesanan: ' . $ex->getMessage(); }
    }
}
$title = 'Checkout Pengiriman'; require 'includes/header.php';
?>
<div class="mb-4">
  <h4 class="fw-bold mb-1"><i class="bi bi-truck text-success me-2"></i>Checkout &amp; Pengiriman</h4>
  <p class="text-muted small mb-0">Lengkapi alamat pengiriman untuk menyelesaikan pesanan Anda.</p>
</div>

<?php if ($err) echo '<div class="alert alert-danger rounded-3 border-0 shadow-sm"><i class="bi bi-exclamation-triangle-fill me-2"></i>' . e($err) . '</div>'; ?>

<div class="row g-4">
  <div class="col-lg-7">
    <form method="post" class="surface-card p-4">
      <h6 class="fw-bold mb-3 pb-2 border-bottom"><i class="bi bi-geo-alt-fill text-success me-2"></i>Informasi Penerima</h6>
      <input type="hidden" name="csrf" value="<?= csrf() ?>">
      <div class="mb-3">
        <label class="form-label fw-semibold small">Nama Lengkap Penerima</label>
        <input class="form-control rounded-3" name="nama" placeholder="Contoh: Budi Santoso" value="<?= e($_POST['nama'] ?? '') ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label fw-semibold small">Nomor WhatsApp / Telepon Aktif</label>
        <input class="form-control rounded-3" name="telepon" placeholder="Contoh: 081234567890" value="<?= e($_POST['telepon'] ?? '') ?>" required>
      </div>
      <div class="mb-4">
        <label class="form-label fw-semibold small">Alamat Lengkap Pengiriman</label>
        <textarea class="form-control rounded-3" name="alamat" rows="3" placeholder="Nama jalan, nomor rumah, RT/RW, kelurahan, kecamatan..." required><?= e($_POST['alamat'] ?? '') ?></textarea>
      </div>
      <div class="d-flex justify-content-between align-items-center">
        <a href="keranjang.php" class="btn btn-outline-pasar px-3"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
        <button class="btn btn-pasar px-4 py-2"><i class="bi bi-check2-circle me-2"></i>Konfirmasi &amp; Buat Pesanan</button>
      </div>
    </form>
  </div>
  <div class="col-lg-5">
    <div class="surface-card p-4">
      <h6 class="fw-bold mb-3 pb-2 border-bottom"><i class="bi bi-receipt text-success me-2"></i>Ringkasan Pesanan</h6>
      <?php foreach ($items as $it): ?>
        <div class="d-flex justify-content-between align-items-center py-2 border-bottom border-light">
          <div>
            <div class="fw-semibold small"><?= e($it['nama_produk']) ?></div>
            <div class="text-muted" style="font-size:0.75rem"><?= $it['jumlah'] ?> x <?= rupiah($it['harga']) ?></div>
          </div>
          <span class="fw-semibold small"><?= rupiah($it['harga'] * $it['jumlah']) ?></span>
        </div>
      <?php endforeach; ?>
      <div class="d-flex justify-content-between align-items-center pt-3 mt-2">
        <span class="fw-bold">Total Tagihan</span>
        <span class="fs-4 fw-extrabold text-success" style="font-weight:800"><?= rupiah($total) ?></span>
      </div>
    </div>
  </div>
</div>
<?php require 'includes/footer.php'; ?>
