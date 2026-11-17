<?php
$title = 'Pesanan Berhasil'; require 'includes/header.php';
$kode = $_GET['kode'] ?? '';
$st = $db->prepare('SELECT * FROM pesanan WHERE kode_pesanan=?'); $st->bind_param('s', $kode); $st->execute();
$o = $st->get_result()->fetch_assoc();
if (!$o) { echo '<div class="alert alert-danger">Pesanan tidak ditemukan.</div>'; require 'includes/footer.php'; exit; }
$d = $db->prepare('SELECT d.*,p.nama_produk FROM detail_pesanan d JOIN produk p ON p.id=d.produk_id WHERE d.pesanan_id=?');
$d->bind_param('i', $o['id']); $d->execute(); $rows = $d->get_result();
?>
<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="surface-card p-4 p-md-5">
      <div class="text-center mb-4">
        <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success-subtle text-success mb-3" style="width:72px;height:72px">
          <i class="bi bi-patch-check-fill fs-1"></i>
        </div>
        <h3 class="fw-bold mb-1">Terima Kasih! Pesanan Berhasil Dibuat</h3>
        <p class="text-muted mb-2">Simpan kode pesanan berikut untuk konfirmasi pengiriman:</p>
        <span class="badge rounded-pill bg-success fs-6 px-4 py-2"><?= e($o['kode_pesanan']) ?></span>
      </div>

      <div class="row g-3 p-3 rounded-4 bg-light border mb-4 small">
        <div class="col-md-6">
          <div class="text-muted">Nama Pembeli</div>
          <div class="fw-bold text-dark"><?= e($o['nama_pembeli']) ?> (<?= e($o['telepon']) ?>)</div>
        </div>
        <div class="col-md-6">
          <div class="text-muted">Waktu Transaksi</div>
          <div class="fw-bold text-dark"><?= e($o['created_at']) ?></div>
        </div>
        <div class="col-12">
          <div class="text-muted">Alamat Pengiriman</div>
          <div class="fw-bold text-dark"><?= e($o['alamat']) ?></div>
        </div>
      </div>

      <h6 class="fw-bold mb-3">Rincian Barang</h6>
      <div class="table-responsive mb-4">
        <table class="table align-middle">
          <thead class="table-light">
            <tr><th>Produk</th><th>Harga</th><th class="text-center">Qty</th><th class="text-end">Subtotal</th></tr>
          </thead>
          <tbody>
            <?php foreach ($rows as $r): ?>
              <tr>
                <td class="fw-medium"><?= e($r['nama_produk']) ?></td>
                <td><?= rupiah($r['harga']) ?></td>
                <td class="text-center"><?= $r['jumlah'] ?></td>
                <td class="text-end fw-semibold"><?= rupiah($r['subtotal']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr class="fw-bold fs-5">
              <td colspan="3" class="text-end">Total Pembayaran</td>
              <td class="text-end text-success"><?= rupiah($o['total']) ?></td>
            </tr>
          </tfoot>
        </table>
      </div>

      <div class="d-flex justify-content-between align-items-center">
        <button onclick="window.print()" class="btn btn-outline-pasar"><i class="bi bi-printer me-1"></i>Cetak Bukti</button>
        <a href="index.php" class="btn btn-pasar px-4"><i class="bi bi-bag-check me-2"></i>Kembali Belanja</a>
      </div>
    </div>
  </div>
</div>
<?php require 'includes/footer.php'; ?>
