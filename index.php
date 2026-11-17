<?php
$title = 'Belanja Hasil Tani & Karya Pengrajin Lokal';
require 'includes/header.php';

$q = trim($_GET['q'] ?? '');
$kat = (int)($_GET['kategori'] ?? 0);
$like = "%$q%";

$st = $db->prepare('SELECT p.*, k.nama_kategori, u.nama AS nama_penjual FROM produk p
  JOIN kategori k ON k.id=p.kategori_id
  JOIN users u ON u.id=p.user_id
  WHERE p.nama_produk LIKE ? AND (? = 0 OR p.kategori_id = ?)
  ORDER BY p.id DESC');
$st->bind_param('sii', $like, $kat, $kat);
$st->execute();
$produk = $st->get_result();
$kategoriList = $db->query('SELECT * FROM kategori')->fetch_all(MYSQLI_ASSOC);
?>

<!-- Hero Banner -->
<section class="hero-pasar p-4 p-md-5 mb-4">
  <div class="row align-items-center gy-4 position-relative" style="z-index:1">
    <div class="col-lg-7">
      <div class="d-flex flex-wrap gap-2 mb-3">
        <span class="trust-pill"><i class="bi bi-patch-check-fill text-warning"></i> 100% Produk Asli Daerah</span>
        <span class="trust-pill"><i class="bi bi-truck text-warning"></i> Langsung dari Petani &amp; UMKM</span>
      </div>
      <h1 class="display-6 fw-extrabold mb-2" style="font-weight:800;letter-spacing:-0.02em">
        Belanja Segar &amp; Hemat di <span class="text-warning">Pasar Nusantara</span>
      </h1>
      <p class="text-white-50 mb-4 fs-6" style="max-width:560px">
        Temukan hasil panen segar, jajanan tradisional tanpa pengawet, hingga kerajinan tangan dan kain batik tulis karya pengrajin terbaik.
      </p>

      <!-- Search Bar -->
      <form class="search-glass" method="get">
        <div class="row g-2 align-items-center">
          <div class="col-md-6">
            <div class="input-group">
              <span class="input-group-text bg-transparent border-0 text-muted ps-3"><i class="bi bi-search"></i></span>
              <input class="form-control" name="q" placeholder="Cari cabai, keripik, batik, anyaman..." value="<?= e($q) ?>">
            </div>
          </div>
          <div class="col-md-4 border-start-md">
            <select class="form-select text-secondary" name="kategori">
              <option value="0">Semua Kategori</option>
              <?php foreach ($kategoriList as $k): ?>
                <option value="<?= $k['id'] ?>" <?= $kat == $k['id'] ? 'selected' : '' ?>><?= e($k['nama_kategori']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-2 d-grid">
            <button class="btn btn-pasar py-2 px-3 rounded-3">
              <i class="bi bi-funnel-fill me-1"></i>Cari
            </button>
          </div>
        </div>
      </form>
    </div>

    <div class="col-lg-5 d-none d-lg-block text-center">
      <div class="p-3 rounded-4 d-inline-block" style="background:rgba(255,255,255,0.1);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,0.18)">
        <div class="row g-2 text-start" style="max-width:320px">
          <div class="col-6">
            <div class="p-3 rounded-3 bg-white bg-opacity-10">
              <i class="bi bi-basket2-fill text-warning fs-3"></i>
              <div class="fw-bold small mt-1">Panen Segar</div>
              <div class="text-white-50" style="font-size:0.72rem">Langsung dari kebun</div>
            </div>
          </div>
          <div class="col-6">
            <div class="p-3 rounded-3 bg-white bg-opacity-10">
              <i class="bi bi-cup-hot-fill text-warning fs-3"></i>
              <div class="fw-bold small mt-1">Kuliner Asli</div>
              <div class="text-white-50" style="font-size:0.72rem">Resep warisan lokal</div>
            </div>
          </div>
          <div class="col-6">
            <div class="p-3 rounded-3 bg-white bg-opacity-10">
              <i class="bi bi-palette-fill text-warning fs-3"></i>
              <div class="fw-bold small mt-1">Kriya Lokal</div>
              <div class="text-white-50" style="font-size:0.72rem">Buatan tangan terampil</div>
            </div>
          </div>
          <div class="col-6">
            <div class="p-3 rounded-3 bg-white bg-opacity-10">
              <i class="bi bi-shield-check text-warning fs-3"></i>
              <div class="fw-bold small mt-1">Harga Pasar</div>
              <div class="text-white-50" style="font-size:0.72rem">Jujur &amp; bersahabat</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Category Pills & Counter -->
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
  <div class="d-flex flex-wrap gap-2">
    <a href="index.php<?= $q !== '' ? '?q=' . urlencode($q) : '' ?>" class="cat-pill <?= $kat === 0 ? 'active' : '' ?>">
      <i class="bi bi-grid-fill"></i> Semua Produk
    </a>
    <?php foreach ($kategoriList as $k):
      $params = ['kategori' => $k['id']];
      if ($q !== '') $params['q'] = $q;
    ?>
      <a href="index.php?<?= http_build_query($params) ?>" class="cat-pill <?= $kat === (int)$k['id'] ? 'active' : '' ?>">
        <i class="bi <?= kategori_icon($k['nama_kategori']) ?>"></i>
        <?= e($k['nama_kategori']) ?>
      </a>
    <?php endforeach; ?>
  </div>
  <div class="text-muted small fw-medium">
    Menampilkan <span class="fw-bold text-dark"><?= $produk->num_rows ?></span> produk pilihan
    <?php if ($q !== '' || $kat > 0): ?>
      &bull; <a href="index.php" class="text-success text-decoration-none fw-semibold">Reset filter</a>
    <?php endif; ?>
  </div>
</div>

<!-- Product Grid -->
<div class="row g-4">
<?php foreach ($produk as $p): ?>
  <div class="col-12 col-sm-6 col-md-4 col-lg-3">
    <div class="product-card h-100 d-flex flex-column">
      <a href="produk.php?id=<?= $p['id'] ?>" class="product-thumb-wrap d-block text-decoration-none">
        <?php if (!empty($p['gambar']) && is_file(__DIR__ . '/uploads/' . $p['gambar'])): ?>
          <img src="uploads/<?= e($p['gambar']) ?>" alt="<?= e($p['nama_produk']) ?>">
        <?php else: ?>
          <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center text-success p-3 text-center">
            <i class="bi <?= kategori_icon($p['nama_kategori']) ?> display-4 mb-1 opacity-75"></i>
            <span class="small fw-semibold text-secondary"><?= e($p['nama_kategori']) ?></span>
          </div>
        <?php endif; ?>
        <span class="badge-cat-float">
          <i class="bi <?= kategori_icon($p['nama_kategori']) ?> text-success me-1"></i><?= e($p['nama_kategori']) ?>
        </span>
        <?php if ((int)$p['stok'] > 0): ?>
          <span class="badge-stok-float"><i class="bi bi-box-seam me-1"></i>Stok <?= (int)$p['stok'] ?></span>
        <?php else: ?>
          <span class="badge-stok-float bg-danger">Habis</span>
        <?php endif; ?>
      </a>

      <div class="card-body p-3 d-flex flex-column flex-grow-1">
        <div class="text-muted mb-1" style="font-size:0.76rem">
          <i class="bi bi-shop me-1 text-success"></i><?= e($p['nama_penjual']) ?>
        </div>
        <h6 class="fw-bold mb-1">
          <a href="produk.php?id=<?= $p['id'] ?>" class="text-dark text-decoration-none stretched-link-title">
            <?= e($p['nama_produk']) ?>
          </a>
        </h6>
        <p class="text-muted small mb-3 flex-grow-1" style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden">
          <?= e($p['deskripsi'] ?: 'Produk pilihan berkualitas langsung dari penjual lokal.') ?>
        </p>

        <div class="d-flex align-items-center justify-content-between pt-2 border-top mt-auto">
          <div>
            <div class="text-muted" style="font-size:0.7rem">Harga Pasar</div>
            <div class="price-tag"><?= rupiah($p['harga']) ?></div>
          </div>
          <div class="d-flex gap-1">
            <a href="produk.php?id=<?= $p['id'] ?>" class="btn btn-outline-pasar btn-sm px-2 py-2" title="Lihat Detail">
              <i class="bi bi-eye"></i> Detail
            </a>
            <?php if (!empty($_SESSION['user'])): ?>
              <a href="admin/produk_form.php?id=<?= $p['id'] ?>" class="btn btn-pasar btn-sm px-2 py-2" title="Edit Produk">
                <i class="bi bi-pencil-square"></i>
              </a>
            <?php elseif ((int)$p['stok'] > 0): ?>
              <form method="post" action="keranjang.php" class="d-inline">
                <input type="hidden" name="csrf" value="<?= csrf() ?>">
                <input type="hidden" name="aksi" value="tambah">
                <input type="hidden" name="produk_id" value="<?= $p['id'] ?>">
                <input type="hidden" name="jumlah" value="1">
                <button class="btn btn-pasar btn-sm px-2 py-2" title="Tambah ke Keranjang">
                  <i class="bi bi-cart-plus-fill"></i>
                </button>
              </form>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
<?php endforeach; ?>

<?php if ($produk->num_rows === 0): ?>
  <div class="col-12">
    <div class="surface-card p-5 text-center">
      <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success-subtle text-success mb-3" style="width:68px;height:68px">
        <i class="bi bi-search fs-2"></i>
      </div>
      <h5 class="fw-bold">Produk Tidak Ditemukan</h5>
      <p class="text-muted mb-3">Maaf, kami tidak menemukan produk yang sesuai dengan pencarian Anda.</p>
      <a href="index.php" class="btn btn-pasar px-4"><i class="bi bi-arrow-counterclockwise me-1"></i>Tampilkan Semua Produk</a>
    </div>
  </div>
<?php endif; ?>
</div>

<?php require 'includes/footer.php'; ?>
