<?php
require '../includes/init.php'; require_admin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check(); $stt = $_POST['status'] ?? '';
    if (in_array($stt, ['baru','diproses','selesai','batal'])) {
        $s = $db->prepare('UPDATE pesanan SET status=? WHERE id=?'); $oid = (int)$_POST['id']; $s->bind_param('si', $stt, $oid); $s->execute();
        flash('Status pesanan berhasil diperbarui.');
    }
    header('Location: pesanan.php'); exit;
}
$title = 'Data Pesanan'; $base = '../'; require '../includes/header.php';
$rows = $db->query('SELECT p.*, (SELECT GROUP_CONCAT(CONCAT(pr.nama_produk," x",d.jumlah) SEPARATOR ", ") FROM detail_pesanan d JOIN produk pr ON pr.id=d.produk_id WHERE d.pesanan_id=p.id) AS item FROM pesanan p ORDER BY p.id DESC');
?>
<div class="mb-4">
  <h4 class="fw-bold mb-1"><i class="bi bi-receipt-cutoff text-success me-2"></i>Daftar Pesanan Masuk</h4>
  <p class="text-muted small mb-0">Pantau dan perbarui status pengiriman pesanan pelanggan.</p>
</div>

<div class="surface-card overflow-hidden">
  <div class="table-responsive">
    <table class="table align-middle mb-0 small">
      <thead class="table-light">
        <tr>
          <th class="ps-4">Kode</th>
          <th>Pembeli &amp; Alamat</th>
          <th>Rincian Item</th>
          <th>Total</th>
          <th>Tanggal</th>
          <th class="pe-4">Status Pesanan</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
        <tr>
          <td class="ps-4 fw-bold text-success"><?= e($r['kode_pesanan']) ?></td>
          <td class="py-3">
            <div class="fw-bold text-dark"><?= e($r['nama_pembeli']) ?></div>
            <div class="text-muted"><?= e($r['telepon']) ?> &bull; <?= e($r['alamat']) ?></div>
          </td>
          <td><?= e($r['item']) ?></td>
          <td class="fw-bold"><?= rupiah($r['total']) ?></td>
          <td class="text-muted"><?= e($r['created_at']) ?></td>
          <td class="pe-4">
            <form method="post" class="d-flex gap-1">
              <input type="hidden" name="csrf" value="<?= csrf() ?>">
              <input type="hidden" name="id" value="<?= $r['id'] ?>">
              <select name="status" class="form-select form-select-sm rounded-3" style="width:120px">
                <?php foreach (['baru','diproses','selesai','batal'] as $s): ?>
                  <option value="<?= $s ?>" <?= $r['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                <?php endforeach; ?>
              </select>
              <button class="btn btn-sm btn-pasar">Simpan</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if ($rows->num_rows === 0): ?>
          <tr><td colspan="6" class="text-center text-muted py-5">Belum ada pesanan yang masuk.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require '../includes/footer.php'; ?>
