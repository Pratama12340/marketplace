<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$db->set_charset('utf8mb4');

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function rupiah($n) { return 'Rp ' . number_format((float)$n, 0, ',', '.'); }

function flash($msg = null, $type = 'success') {
    if ($msg !== null) { $_SESSION['flash'] = [$msg, $type]; return; }
    if (!empty($_SESSION['flash'])) {
        [$m, $t] = $_SESSION['flash']; unset($_SESSION['flash']);
        $icon = match($t) {
            'danger'  => 'bi-exclamation-octagon-fill',
            'warning' => 'bi-exclamation-triangle-fill',
            'info'    => 'bi-info-circle-fill',
            default   => 'bi-check-circle-fill',
        };
        return '<div class="alert alert-' . e($t) . ' alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm border-0 rounded-3 mb-4" role="alert">'
             . '<i class="bi ' . $icon . ' fs-5"></i>'
             . '<div>' . e($m) . '</div>'
             . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>'
             . '</div>';
    }
    return '';
}

function csrf() {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}

function csrf_check() {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) { http_response_code(400); exit('Token tidak valid.'); }
}

function require_admin() {
    if (empty($_SESSION['user'])) { header('Location: login.php'); exit; }
}

function cart_count($db) {
    $sid = session_id();
    $st = $db->prepare('SELECT COALESCE(SUM(jumlah),0) FROM keranjang WHERE session_id=?');
    $st->bind_param('s', $sid); $st->execute();
    return (int)$st->get_result()->fetch_row()[0];
}

function kategori_icon($nama) {
    $n = strtolower((string)$nama);
    if (str_contains($n, 'sayur') || str_contains($n, 'buah')) return 'bi-basket2-fill';
    if (str_contains($n, 'makanan') || str_contains($n, 'jajanan')) return 'bi-cup-hot-fill';
    if (str_contains($n, 'kerajinan')) return 'bi-palette-fill';
    if (str_contains($n, 'pakaian') || str_contains($n, 'batik')) return 'bi-handbag-fill';
    return 'bi-tag-fill';
}
