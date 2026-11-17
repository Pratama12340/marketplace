<?php
require '../includes/init.php'; require_admin();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'hapus') {
    csrf_check(); $id = (int)$_POST['id'];
    try {
        $g = $db->prepare('SELECT gambar FROM produk WHERE id=?'); $g->bind_param('i', $id); $g->execute();
        $img = $g->get_result()->fetch_row()[0] ?? null;
        $d = $db->prepare('DELETE FROM produk WHERE id=?'); $d->bind_param('i', $id); $d->execute();
        if ($img && !in_array($img, ['cabai.svg','keripik.svg','tas-pandan.svg','batik.svg']) && is_file("../uploads/$img")) unlink("../uploads/$img");
        flash('Produk dihapus.', 'warning');
    } catch (mysqli_sql_exception $ex) { flash('Produk tidak bisa dihapus karena sudah ada di pesanan.', 'danger'); }
    header('Location: produk.php'); exit;
}
$title = 'Kelola Produk'; $base = '../'; require '../includes/header.php';
$rows = $db->query('SELECT p.*,k.nama_kategori FROM produk p JOIN kategori k ON k.id=p.kategori_id ORDER BY p.id DESC');
?>
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
  <div>
    <h4 class="fw-bold mb-1"><i class="bi bi-box-seam-fill text-success me-2"></i>Kelola Katalog Produk</h4>
    <p class="text-muted small mb-0">Tambah, ubah, atau kelola ketersediaan stok barang dagangan Anda.</p>
  </div>
  <a href="produk_form.php" class="btn btn-pasar px-4 py-2"><i class="bi bi-plus-lg me-1"></i>Tambah Produk Baru</a>
</div>

<div class="surface-card overflow-hidden">
  <div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th class="ps-4">#</th>
          <th>Produk</th>
          <th>Kategori</th>
          <th>Harga</th>
          <th>Stok</th>
          <th width="170" class="pe-4 text-end">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
        <tr>
          <td class="ps-4 text-muted fw-semibold"><?= $r['id'] ?></td>
          <td class="py-3">
            <div class="d-flex align-items-center gap-3">
              <?php if (!empty($r['gambar']) && is_file(__DIR__ . '/../uploads/' . $r['gambar'])): ?>
                <img src="../uploads/<?= e($r['gambar']) ?>" class="rounded-3 border" style="width:48px;height:48px;object-fit:cover" alt="">
              <?php else: ?>
                <div class="rounded-3 bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width:48px;height:48px">
                  <i class="bi <?= kategori_icon($r['nama_kategori']) ?>"></i>
                </div>
              <?php endif; ?>
              <div class="fw-bold text-dark"><?= e($r['nama_produk']) ?></div>
            </div>
          </td>
          <td><span class="badge rounded-pill bg-success-subtle text-success px-3 py-2"><?= e($r['nama_kategori']) ?></span></td>
          <td class="fw-bold text-success"><?= rupiah($r['harga']) ?></td>
          <td>
            <?php if ((int)$r['stok'] > 5): ?>
              <span class="badge bg-light text-dark border"><?= (int)$r['stok'] ?> unit</span>
            <?php elseif ((int)$r['stok'] > 0): ?>
              <span class="badge bg-warning-subtle text-warning-emphasis"><?= (int)$r['stok'] ?> tersisa</span>
            <?php else: ?>
              <span class="badge bg-danger-subtle text-danger">Habis</span>
            <?php endif; ?>
          </td>
          <td class="pe-4 text-end">
            <div class="d-inline-flex gap-1">
              <a class="btn btn-sm btn-outline-pasar" href="produk_form.php?id=<?= $r['id'] ?>"><i class="bi bi-pencil-square me-1"></i>Edit</a>
              <form method="post" onsubmit="return confirm('Hapus produk ini?')">
                <input type="hidden" name="csrf" value="<?= csrf() ?>">
                <input type="hidden" name="aksi" value="hapus">
                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                <button class="btn btn-sm btn-outline-danger rounded-3"><i class="bi bi-trash3"></i></button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require '../includes/footer.php'; ?>
