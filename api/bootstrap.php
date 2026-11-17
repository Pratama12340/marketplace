<?php
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept');

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(200);
    exit;
}

function json_response(int $httpCode, bool $success, string $message, $data = null): void {
    http_response_code($httpCode);
    $payload = [
        'status'  => $success ? 'success' : 'error',
        'code'    => $httpCode,
        'message' => $message,
    ];
    if ($data !== null) {
        $payload['data'] = $data;
    }
    echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

set_exception_handler(function (Throwable $e) {
    json_response(500, false, 'Kesalahan server internal: ' . $e->getMessage());
});

require_once __DIR__ . '/../config.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$db->set_charset('utf8mb4');

function json_input(): array {
    $raw = file_get_contents('php://input');
    if ($raw !== false && trim($raw) !== '') {
        $decoded = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            json_response(400, false, 'Format body JSON tidak valid.');
        }
        return $decoded;
    }
    if (!empty($_POST)) {
        return $_POST;
    }
    return [];
}

function base_url(): string {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/marketplace/api/produk.php')), '/');
    $rootDir = preg_replace('#/api$#', '', $scriptDir);
    return $scheme . '://' . $host . $rootDir;
}
