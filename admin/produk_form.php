<?php
require '../includes/init.php'; require_admin();
$id = (int)($_GET['id'] ?? 0); $p = ['nama_produk'=>'','kategori_id'=>0,'deskripsi'=>'','harga'=>'','stok'=>'','gambar'=>null]; $err = '';
if ($id) {
    $st = $db->prepare('SELECT * FROM produk WHERE id=?'); $st->bind_param('i', $id); $st->execute();
    $p = $st->get_result()->fetch_assoc() ?: exit('Produk tidak ditemukan.');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $nama = trim($_POST['nama_produk']); $kid = (int)$_POST['kategori_id']; $desk = trim($_POST['deskripsi']);
    $harga = (float)$_POST['harga']; $stok = (int)$_POST['stok']; $gambar = $p['gambar'];
    if ($nama === '' || $kid <= 0 || $harga <= 0 || $stok < 0) $err = 'Data tidak valid.';
    if (!$err && !empty($_FILES['gambar']['name'])) {
        $ext = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
        $ok = in_array($ext, ['jpg','jpeg','png','webp']) && $_FILES['gambar']['size'] <= 2*1024*1024 && @getimagesize($_FILES['gambar']['tmp_name']);
        if (!$ok) $err = 'Gambar harus JPG/PNG/WEBP, maks 2 MB.';
        else {
            $new = bin2hex(random_bytes(8)) . '.' . $ext;
            move_uploaded_file($_FILES['gambar']['tmp_name'], "../uploads/$new");
            if ($gambar && !in_array($gambar, ['cabai.svg','keripik.svg','tas-pandan.svg','batik.svg']) && is_file("../uploads/$gambar")) unlink("../uploads/$gambar");
            $gambar = $new;
        }
    }
    if (!$err) {
        if ($id) {
            $s = $db->prepare('UPDATE produk SET kategori_id=?,nama_produk=?,deskripsi=?,harga=?,stok=?,gambar=? WHERE id=?');
            $s->bind_param('issdisi', $kid, $nama, $desk, $harga, $stok, $gambar, $id);
        } else {
            $uid = $_SESSION['user']['id'];
            $s = $db->prepare('INSERT INTO produk (user_id,kategori_id,nama_produk,deskripsi,harga,stok,gambar) VALUES (?,?,?,?,?,?,?)');
            $s->bind_param('iissdis', $uid, $kid, $nama, $desk, $harga, $stok, $gambar);
        }
        $s->execute(); flash('Produk berhasil disimpan.'); header('Location: produk.php'); exit;
    }
    $p = array_merge($p, ['nama_produk'=>$nama,'kategori_id'=>$kid,'deskripsi'=>$desk,'harga'=>$harga,'stok'=>$stok]);
}
$kategori = $db->query('SELECT * FROM kategori');
$title = $id ? 'Edit Produk' : 'Tambah Produk'; $base = '../'; require '../includes/header.php';
?>
<div class="row justify-content-center">
  <div class="col-lg-7">
    <div class="d-flex align-items-center justify-content-between mb-3">
      <h4 class="fw-bold mb-0"><?= e($title) ?></h4>
      <a href="produk.php" class="btn btn-outline-pasar btn-sm"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
    </div>
    <?php if ($err) echo '<div class="alert alert-danger rounded-3">' . e($err) . '</div>'; ?>
    <form method="post" enctype="multipart/form-data" class="surface-card p-4">
      <input type="hidden" name="csrf" value="<?= csrf() ?>">
      <div class="mb-3">
        <label class="form-label fw-semibold small">Nama Produk</label>
        <input class="form-control rounded-3" name="nama_produk" value="<?= e($p['nama_produk']) ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label fw-semibold small">Kategori</label>
        <select class="form-select rounded-3" name="kategori_id" required>
          <option value="">-- Pilih Kategori --</option>
          <?php foreach ($kategori as $k): ?>
            <option value="<?= $k['id'] ?>" <?= $p['kategori_id'] == $k['id'] ? 'selected' : '' ?>><?= e($k['nama_kategori']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label fw-semibold small">Deskripsi Produk</label>
        <textarea class="form-control rounded-3" name="deskripsi" rows="3"><?= e($p['deskripsi']) ?></textarea>
      </div>
      <div class="row g-3 mb-3">
        <div class="col-sm-6">
          <label class="form-label fw-semibold small">Harga (Rp)</label>
          <input type="number" min="1" step="any" class="form-control rounded-3" name="harga" value="<?= e($p['harga']) ?>" required>
        </div>
        <div class="col-sm-6">
          <label class="form-label fw-semibold small">Stok Tersedia</label>
          <input type="number" min="0" class="form-control rounded-3" name="stok" value="<?= e($p['stok']) ?>" required>
        </div>
      </div>
      <div class="mb-4">
        <label class="form-label fw-semibold small">Foto Produk (Opsional — JPG/PNG/WEBP, maks 2 MB)</label>
        <input type="file" class="form-control rounded-3" name="gambar" accept="image/*">
      </div>
      <div class="d-flex justify-content-end gap-2">
        <a href="produk.php" class="btn btn-outline-pasar px-4">Batal</a>
        <button class="btn btn-pasar px-4"><i class="bi bi-check2-circle me-1"></i>Simpan Produk</button>
      </div>
    </form>
  </div>
</div>
<?php require '../includes/footer.php'; ?>
