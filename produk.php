<?php
$title = 'Detail Produk';
require 'includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$st = $db->prepare('SELECT p.*, k.nama_kategori, u.nama AS penjual FROM produk p
  JOIN kategori k ON k.id=p.kategori_id JOIN users u ON u.id=p.user_id WHERE p.id=?');
$st->bind_param('i', $id);
$st->execute();
$p = $st->get_result()->fetch_assoc();
if (!$p) {
    echo '<div class="surface-card p-5 text-center"><h5 class="fw-bold text-danger">Produk tidak ditemukan</h5><a href="index.php" class="btn btn-pasar mt-3">Kembali ke Katalog</a></div>';
    require 'includes/footer.php';
    exit;
}
?>
<nav aria-label="breadcrumb" class="mb-3">
  <ol class="breadcrumb small mb-0">
    <li class="breadcrumb-item"><a href="index.php" class="text-success text-decoration-none"><i class="bi bi-house-door me-1"></i>Beranda</a></li>
    <li class="breadcrumb-item"><a href="index.php?kategori=<?= $p['kategori_id'] ?>" class="text-success text-decoration-none"><?= e($p['nama_kategori']) ?></a></li>
    <li class="breadcrumb-item active" aria-current="page"><?= e($p['nama_produk']) ?></li>
  </ol>
</nav>

<div class="surface-card overflow-hidden">
  <div class="row g-0">
    <div class="col-lg-5 bg-light d-flex align-items-center justify-content-center p-4" style="background:linear-gradient(135deg,#ecfdf5 0%,#fef3c7 100%)">
      <?php if (!empty($p['gambar']) && is_file(__DIR__ . '/uploads/' . $p['gambar'])): ?>
        <img src="uploads/<?= e($p['gambar']) ?>" class="img-fluid rounded-4 shadow-sm w-100" style="max-height:360px;object-fit:cover" alt="<?= e($p['nama_produk']) ?>">
      <?php else: ?>
        <div class="text-center py-5 text-success">
          <i class="bi <?= kategori_icon($p['nama_kategori']) ?> display-1"></i>
          <div class="fw-semibold mt-2"><?= e($p['nama_kategori']) ?></div>
        </div>
      <?php endif; ?>
    </div>
    <div class="col-lg-7">
      <div class="p-4 p-md-5">
        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
          <span class="badge rounded-pill bg-success-subtle text-success px-3 py-2 fw-semibold">
            <i class="bi <?= kategori_icon($p['nama_kategori']) ?> me-1"></i><?= e($p['nama_kategori']) ?>
          </span>
          <?php if ((int)$p['stok'] > 0): ?>
            <span class="badge rounded-pill bg-primary-subtle text-primary px-3 py-2 fw-semibold">
              <i class="bi bi-check2-circle me-1"></i>Tersedia (<?= (int)$p['stok'] ?> stok)
            </span>
          <?php else: ?>
            <span class="badge rounded-pill bg-danger-subtle text-danger px-3 py-2 fw-semibold">Stok Habis</span>
          <?php endif; ?>
        </div>

        <h2 class="fw-bold mb-2"><?= e($p['nama_produk']) ?></h2>
        <div class="display-6 fw-extrabold text-success mb-3" style="font-weight:800"><?= rupiah($p['harga']) ?></div>

        <div class="d-flex align-items-center gap-2 p-3 rounded-3 bg-light border mb-4">
          <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success text-white" style="width:38px;height:38px">
            <i class="bi bi-shop"></i>
          </span>
          <div>
            <div class="small text-muted lh-1">Dijual oleh</div>
            <div class="fw-bold text-dark"><?= e($p['penjual']) ?> <i class="bi bi-patch-check-fill text-success small" title="Penjual Terverifikasi"></i></div>
          </div>
        </div>

        <h6 class="fw-bold text-secondary text-uppercase small mb-2">Deskripsi Produk</h6>
        <p class="text-secondary mb-4" style="line-height:1.7"><?= nl2br(e($p['deskripsi'] ?: 'Produk pilihan berkualitas langsung dari penjual lokal.')) ?></p>

        <?php if (!empty($_SESSION['user'])): ?>
          <div class="d-flex gap-2 pt-3 border-top">
            <a href="admin/produk_form.php?id=<?= $p['id'] ?>" class="btn btn-pasar px-4 py-2">
              <i class="bi bi-pencil-square me-2"></i>Edit Produk Ini
            </a>
            <a href="index.php" class="btn btn-outline-pasar px-3 py-2">
              <i class="bi bi-arrow-left me-1"></i>Kembali
            </a>
          </div>
        <?php elseif ($p['stok'] > 0): ?>
          <form method="post" action="keranjang.php" class="row g-2 align-items-center pt-3 border-top">
            <input type="hidden" name="csrf" value="<?= csrf() ?>">
            <input type="hidden" name="aksi" value="tambah">
            <input type="hidden" name="produk_id" value="<?= $p['id'] ?>">
            <div class="col-auto">
              <label class="form-label small text-muted mb-1">Jumlah</label>
              <input type="number" name="jumlah" value="1" min="1" max="<?= (int)$p['stok'] ?>" class="form-control rounded-3 text-center fw-bold" style="width:95px">
            </div>
            <div class="col-auto align-self-end">
              <button class="btn btn-pasar px-4 py-2">
                <i class="bi bi-cart-plus-fill me-2"></i>Tambah ke Keranjang
              </button>
            </div>
            <div class="col-auto align-self-end">
              <a href="index.php" class="btn btn-outline-pasar px-3 py-2">
                <i class="bi bi-arrow-left me-1"></i>Kembali
              </a>
            </div>
          </form>
        <?php else: ?>
          <div class="alert alert-danger d-inline-flex align-items-center gap-2 mb-3">
            <i class="bi bi-exclamation-circle-fill"></i> Maaf, stok produk ini sedang habis.
          </div>
          <div><a href="index.php" class="btn btn-outline-pasar"><i class="bi bi-arrow-left me-1"></i>Kembali ke Katalog</a></div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php require 'includes/footer.php'; ?>
