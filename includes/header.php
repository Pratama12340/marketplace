<?php require_once __DIR__ . '/init.php'; $base = $base ?? ''; $cartQty = cart_count($db); ?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title ?? 'Pasar Tradisional Online') ?> — Pasar Nusantara</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <style>
    :root {
      --pasar-primary: #0f6848;
      --pasar-primary-dark: #0a4731;
      --pasar-accent: #f59e0b;
      --pasar-bg: #f6f8f5;
      --pasar-card-border: rgba(15, 104, 72, 0.09);
    }
    body {
      font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
      background-color: var(--pasar-bg);
      color: #1e293b;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }
    /* Navbar */
    .navbar-pasar {
      background: linear-gradient(135deg, #0a4731 0%, #0f6848 60%, #15803d 100%);
      box-shadow: 0 4px 20px rgba(10, 71, 49, 0.15);
      padding-top: 0.85rem;
      padding-bottom: 0.85rem;
    }
    .brand-logo {
      width: 40px;
      height: 40px;
      border-radius: 12px;
      background: rgba(255, 255, 255, 0.16);
      backdrop-filter: blur(6px);
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 1.25rem;
      border: 1px solid rgba(255, 255, 255, 0.25);
    }
    /* Hero Banner */
    .hero-pasar {
      background: linear-gradient(125deg, #0a4731 0%, #0f6848 52%, #166534 100%);
      border-radius: 24px;
      position: relative;
      overflow: hidden;
      color: #fff;
      box-shadow: 0 16px 36px rgba(15, 104, 72, 0.16);
    }
    .hero-pasar::before {
      content: "";
      position: absolute;
      top: -80px;
      right: -80px;
      width: 300px;
      height: 300px;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(251, 191, 36, 0.28) 0%, rgba(251, 191, 36, 0) 70%);
      pointer-events: none;
    }
    .hero-pasar::after {
      content: "";
      position: absolute;
      bottom: -90px;
      left: 20%;
      width: 260px;
      height: 260px;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(52, 211, 153, 0.2) 0%, rgba(52, 211, 153, 0) 70%);
      pointer-events: none;
    }
    .search-glass {
      background: #ffffff;
      border-radius: 16px;
      padding: 0.45rem;
      box-shadow: 0 10px 28px rgba(0, 0, 0, 0.12);
    }
    .search-glass .form-control,
    .search-glass .form-select {
      border: 0;
      box-shadow: none !important;
      padding-top: 0.65rem;
      padding-bottom: 0.65rem;
    }
    /* Category Pills */
    .cat-pill {
      display: inline-flex;
      align-items: center;
      gap: 0.45rem;
      padding: 0.55rem 1.1rem;
      border-radius: 999px;
      font-weight: 600;
      font-size: 0.88rem;
      text-decoration: none;
      background: #ffffff;
      color: #334155;
      border: 1px solid #e2e8f0;
      transition: all 0.2s ease;
      box-shadow: 0 2px 6px rgba(15, 23, 42, 0.03);
    }
    .cat-pill:hover {
      border-color: var(--pasar-primary);
      color: var(--pasar-primary);
      transform: translateY(-2px);
    }
    .cat-pill.active {
      background: var(--pasar-primary);
      color: #ffffff;
      border-color: var(--pasar-primary);
      box-shadow: 0 6px 16px rgba(15, 104, 72, 0.22);
    }
    /* Product Cards */
    .product-card {
      border: 1px solid var(--pasar-card-border);
      border-radius: 20px;
      overflow: hidden;
      background: #ffffff;
      transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s ease;
      box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04);
    }
    .product-card:hover {
      transform: translateY(-6px);
      box-shadow: 0 18px 36px rgba(15, 104, 72, 0.12);
      border-color: rgba(15, 104, 72, 0.24);
    }
    .product-thumb-wrap {
      position: relative;
      height: 200px;
      overflow: hidden;
      background: linear-gradient(135deg, #ecfdf5 0%, #fef3c7 100%);
    }
    .product-thumb-wrap img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.4s ease;
    }
    .product-card:hover .product-thumb-wrap img {
      transform: scale(1.06);
    }
    .badge-cat-float {
      position: absolute;
      top: 12px;
      left: 12px;
      background: rgba(255, 255, 255, 0.92);
      backdrop-filter: blur(6px);
      color: var(--pasar-primary-dark);
      font-weight: 700;
      font-size: 0.75rem;
      padding: 0.38rem 0.75rem;
      border-radius: 999px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }
    .badge-stok-float {
      position: absolute;
      top: 12px;
      right: 12px;
      background: rgba(15, 23, 42, 0.72);
      backdrop-filter: blur(4px);
      color: #fff;
      font-size: 0.72rem;
      font-weight: 600;
      padding: 0.3rem 0.65rem;
      border-radius: 999px;
    }
    .price-tag {
      font-size: 1.18rem;
      font-weight: 800;
      color: var(--pasar-primary);
      letter-spacing: -0.02em;
    }
    .btn-pasar {
      background-color: var(--pasar-primary);
      border-color: var(--pasar-primary);
      color: #fff;
      font-weight: 600;
      border-radius: 12px;
      transition: all 0.2s ease;
    }
    .btn-pasar:hover, .btn-pasar:focus {
      background-color: var(--pasar-primary-dark);
      border-color: var(--pasar-primary-dark);
      color: #fff;
      transform: translateY(-1px);
    }
    .btn-outline-pasar {
      border: 1.5px solid rgba(15, 104, 72, 0.28);
      color: var(--pasar-primary);
      font-weight: 600;
      border-radius: 12px;
      background: #fff;
      transition: all 0.2s ease;
    }
    .btn-outline-pasar:hover {
      background-color: #ecfdf5;
      border-color: var(--pasar-primary);
      color: var(--pasar-primary-dark);
    }
    .surface-card {
      background: #fff;
      border: 1px solid var(--pasar-card-border);
      border-radius: 20px;
      box-shadow: 0 6px 24px rgba(15, 23, 42, 0.04);
    }
    .trust-pill {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      background: rgba(255, 255, 255, 0.14);
      border: 1px solid rgba(255, 255, 255, 0.2);
      padding: 0.4rem 0.85rem;
      border-radius: 999px;
      font-size: 0.82rem;
      font-weight: 500;
      color: #ecfdf5;
    }
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark navbar-pasar sticky-top mb-4">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center gap-2 fw-bold" href="<?= $base ?>index.php">
      <span class="brand-logo"><i class="bi bi-shop-window text-warning"></i></span>
      <div>
        <div class="lh-1">Pasar Nusantara</div>
        <small class="fw-normal text-white-50" style="font-size:0.72rem"> Segar &bull; Lokal &bull; Terpercaya</small>
      </div>
    </a>
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navPasar">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navPasar">
      <div class="ms-auto d-flex flex-wrap align-items-center gap-2 mt-3 mt-lg-0">
        <?php if (!empty($_SESSION['user'])): ?>
          <a class="btn btn-outline-light btn-sm px-3 py-2 rounded-pill fw-semibold" href="<?= $base ?>index.php">
            <i class="bi bi-shop me-1"></i>Lihat Katalog
          </a>
          <a class="btn btn-outline-light btn-sm px-3 py-2 rounded-pill fw-semibold" href="<?= $base ?>admin/produk.php">
            <i class="bi bi-box-seam me-1"></i>Kelola Produk
          </a>
          <a class="btn btn-outline-light btn-sm px-3 py-2 rounded-pill fw-semibold" href="<?= $base ?>admin/pesanan.php">
            <i class="bi bi-receipt-cutoff me-1"></i>Pesanan
          </a>
          <a class="btn btn-warning btn-sm px-3 py-2 rounded-pill fw-semibold text-dark" href="<?= $base ?>admin/logout.php">
            <i class="bi bi-box-arrow-right me-1"></i>Logout (<?= e($_SESSION['user']['nama']) ?>)
          </a>
        <?php else: ?>
          <a class="btn btn-light btn-sm px-3 py-2 rounded-pill fw-semibold d-inline-flex align-items-center gap-2 shadow-sm" href="<?= $base ?>keranjang.php">
            <i class="bi bi-basket2-fill text-success"></i>
            <span>Keranjang</span>
            <span class="badge rounded-pill bg-success"><?= $cartQty ?></span>
          </a>
          <a class="btn btn-outline-light btn-sm px-3 py-2 rounded-pill fw-semibold" href="<?= $base ?>admin/login.php">
            <i class="bi bi-person-badge me-1"></i>Login Penjual
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>
<main class="container flex-grow-1 pb-4">
<?= flash() ?>
