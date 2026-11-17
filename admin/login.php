<?php
require '../includes/init.php'; $err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $st = $db->prepare('SELECT * FROM users WHERE email=?'); $st->bind_param('s', $_POST['email']); $st->execute();
    $u = $st->get_result()->fetch_assoc();
    if ($u && password_verify($_POST['password'] ?? '', $u['password'])) {
        session_regenerate_id(true); $_SESSION['user'] = ['id' => $u['id'], 'nama' => $u['nama']];
        header('Location: produk.php'); exit;
    }
    $err = 'Email atau password salah.';
}
$title = 'Login Penjual'; $base = '../'; require '../includes/header.php';
?>
<div class="row justify-content-center py-4">
  <div class="col-md-6 col-lg-5">
    <div class="surface-card p-4 p-md-5">
      <div class="text-center mb-4">
        <div class="d-inline-flex align-items-center justify-content-center rounded-4 bg-success-subtle text-success mb-3" style="width:60px;height:60px">
          <i class="bi bi-shop-window fs-2"></i>
        </div>
        <h4 class="fw-bold mb-1">Portal Penjual</h4>
        <p class="text-muted small mb-0">Masuk untuk mengelola katalog produk dan pesanan pelanggan</p>
      </div>

      <?php if ($err): ?>
        <div class="alert alert-danger d-flex align-items-center gap-2 py-2 small rounded-3">
          <i class="bi bi-exclamation-circle-fill"></i> <?= e($err) ?>
        </div>
      <?php endif; ?>

      <form method="post">
        <input type="hidden" name="csrf" value="<?= csrf() ?>">
        <div class="mb-3">
          <label class="form-label small fw-semibold">Alamat Email</label>
          <div class="input-group">
            <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
            <input class="form-control border-start-0" type="email" name="email" placeholder="admin@pasar.com" required>
          </div>
        </div>
        <div class="mb-4">
          <label class="form-label small fw-semibold">Kata Sandi</label>
          <div class="input-group">
            <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
            <input class="form-control border-start-0" type="password" name="password" placeholder="••••••••" required>
          </div>
        </div>
        <div class="d-grid">
          <button class="btn btn-pasar py-2"><i class="bi bi-box-arrow-in-right me-2"></i>Masuk ke Dashboard</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php require '../includes/footer.php'; ?>
