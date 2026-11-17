<?php
require 'includes/init.php';
$sid = session_id();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $aksi = $_POST['aksi'] ?? ''; $pid = (int)($_POST['produk_id'] ?? 0); $jml = max(1, (int)($_POST['jumlah'] ?? 1));
    $st = $db->prepare('SELECT stok FROM produk WHERE id=?'); $st->bind_param('i', $pid); $st->execute();
    $stok = $st->get_result()->fetch_row()[0] ?? null;
    if ($aksi === 'tambah' && $stok !== null) {
        $st = $db->prepare('SELECT id,jumlah FROM keranjang WHERE session_id=? AND produk_id=?');
        $st->bind_param('si', $sid, $pid); $st->execute();
        if ($row = $st->get_result()->fetch_assoc()) {
            $baru = min($stok, $row['jumlah'] + $jml);
            $u = $db->prepare('UPDATE keranjang SET jumlah=? WHERE id=?'); $u->bind_param('ii', $baru, $row['id']); $u->execute();
        } else {
            $jml = min($stok, $jml);
            $i = $db->prepare('INSERT INTO keranjang (session_id,produk_id,jumlah) VALUES (?,?,?)'); $i->bind_param('sii', $sid, $pid, $jml); $i->execute();
        }
        flash('Produk berhasil ditambahkan ke keranjang belanja.');
    } elseif ($aksi === 'ubah' && $stok !== null) {
        $jml = min($stok, $jml);
        $u = $db->prepare('UPDATE keranjang SET jumlah=? WHERE session_id=? AND produk_id=?'); $u->bind_param('isi', $jml, $sid, $pid); $u->execute();
        flash('Jumlah produk diperbarui.');
    } elseif ($aksi === 'hapus') {
        $d = $db->prepare('DELETE FROM keranjang WHERE session_id=? AND produk_id=?'); $d->bind_param('si', $sid, $pid); $d->execute();
        flash('Produk dihapus dari keranjang.', 'warning');
    }
    header('Location: keranjang.php'); exit;
}
$title = 'Keranjang Belanja'; require 'includes/header.php';
$st = $db->prepare('SELECT k.produk_id,k.jumlah,p.nama_produk,p.harga,p.stok,p.gambar FROM keranjang k JOIN produk p ON p.id=k.produk_id WHERE k.session_id=?');
$st->bind_param('s', $sid); $st->execute(); $items = $st->get_result()->fetch_all(MYSQLI_ASSOC);
$total = 0;
?>
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="fw-bold mb-1"><i class="bi bi-basket2-fill text-success me-2"></i>Keranjang Belanja</h4>
    <p class="text-muted small mb-0">Periksa kembali barang belanjaan Anda sebelum melanjutkan ke pembayaran.</p>
  </div>
  <a href="index.php" class="btn btn-outline-pasar btn-sm px-3 py-2"><i class="bi bi-plus-lg me-1"></i>Tambah Produk Lain</a>
</div>

<?php if (!$items): ?>
  <div class="surface-card p-5 text-center">
    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success-subtle text-success mb-3" style="width:72px;height:72px">
      <i class="bi bi-cart-x fs-1"></i>
    </div>
    <h5 class="fw-bold">Keranjang Belanja Masih Kosong</h5>
    <p class="text-muted mb-4">Yuk, mulai belanja produk segar dan kerajinan lokal pilihan hari ini!</p>
    <a href="index.php" class="btn btn-pasar px-4 py-2"><i class="bi bi-bag-heart me-2"></i>Mulai Belanja</a>
  </div>
<?php else: ?>
  <div class="row g-4">
    <div class="col-lg-8">
      <div class="surface-card overflow-hidden">
        <div class="table-responsive">
          <table class="table align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th class="ps-4">Produk</th>
                <th>Harga</th>
                <th width="160">Jumlah</th>
                <th>Subtotal</th>
                <th class="pe-4 text-end">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($items as $it): $sub = $it['harga'] * $it['jumlah']; $total += $sub; ?>
              <tr>
                <td class="ps-4 py-3">
                  <div class="d-flex align-items-center gap-3">
                    <?php if (!empty($it['gambar']) && is_file(__DIR__ . '/uploads/' . $it['gambar'])): ?>
                      <img src="uploads/<?= e($it['gambar']) ?>" class="rounded-3 border" style="width:54px;height:54px;object-fit:cover" alt="">
                    <?php else: ?>
                      <div class="rounded-3 bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width:54px;height:54px">
                        <i class="bi bi-box-seam fs-5"></i>
                      </div>
                    <?php endif; ?>
                    <div>
                      <a href="produk.php?id=<?= $it['produk_id'] ?>" class="fw-bold text-dark text-decoration-none"><?= e($it['nama_produk']) ?></a>
                      <div class="text-muted small">Stok tersedia: <?= (int)$it['stok'] ?></div>
                    </div>
                  </div>
                </td>
                <td class="text-secondary fw-medium"><?= rupiah($it['harga']) ?></td>
                <td>
                  <form method="post" class="d-flex gap-1">
                    <input type="hidden" name="csrf" value="<?= csrf() ?>">
                    <input type="hidden" name="aksi" value="ubah">
                    <input type="hidden" name="produk_id" value="<?= $it['produk_id'] ?>">
                    <input type="number" name="jumlah" value="<?= $it['jumlah'] ?>" min="1" max="<?= $it['stok'] ?>" class="form-control form-control-sm text-center fw-semibold rounded-3">
                    <button class="btn btn-sm btn-outline-pasar" title="Simpan Jumlah"><i class="bi bi-check2"></i></button>
                  </form>
                </td>
                <td class="fw-bold text-success"><?= rupiah($sub) ?></td>
                <td class="pe-4 text-end">
                  <form method="post">
                    <input type="hidden" name="csrf" value="<?= csrf() ?>">
                    <input type="hidden" name="aksi" value="hapus">
                    <input type="hidden" name="produk_id" value="<?= $it['produk_id'] ?>">
                    <button class="btn btn-sm btn-outline-danger rounded-3" title="Hapus"><i class="bi bi-trash3"></i></button>
                  </form>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="surface-card p-4">
        <h6 class="fw-bold mb-3">Ringkasan Belanja</h6>
        <div class="d-flex justify-content-between text-muted small mb-2">
          <span>Total Jenis Barang</span>
          <span><?= count($items) ?> produk</span>
        </div>
        <hr>
        <div class="d-flex justify-content-between align-items-center mb-4">
          <span class="fw-bold">Total Pembayaran</span>
          <span class="fs-4 fw-extrabold text-success" style="font-weight:800"><?= rupiah($total) ?></span>
        </div>
        <div class="d-grid gap-2">
          <a href="checkout.php" class="btn btn-pasar py-2"><i class="bi bi-shield-check me-2"></i>Lanjut ke Checkout</a>
          <a href="index.php" class="btn btn-outline-pasar py-2">Lanjut Belanja</a>
        </div>
      </div>
    </div>
  </div>
<?php endif; require 'includes/footer.php'; ?>
