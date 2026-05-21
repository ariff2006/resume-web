<?php
// _auth.php — Auth helper. Include at top of admin pages.

session_start();

// Password is read from env var ADMIN_PASSWORD (set via Docker -e).
// Falls back to "admin123" if not set (DEV ONLY — change in production!)
// Try multiple sources because Apache+mod_php sometimes doesn't expose env vars via getenv().
function admin_password() {
    $candidates = [
        getenv('ADMIN_PASSWORD'),
        $_SERVER['ADMIN_PASSWORD'] ?? null,
        $_ENV['ADMIN_PASSWORD'] ?? null,
        apache_getenv('ADMIN_PASSWORD') ?? null,
    ];
    foreach ($candidates as $p) {
        if ($p !== false && $p !== null && $p !== '') return $p;
    }
    return 'admin123';
}

// Polyfill in case apache_getenv doesn't exist (CLI / non-apache builds)
if (!function_exists('apache_getenv')) {
    function apache_getenv($k) { return null; }
}

function is_logged_in() {
    return !empty($_SESSION['admin_authed']);
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

function data_file_path() {
    // resume-data.json sits one level up from /admin
    return __DIR__ . '/../resume-data.json';
}

function certs_dir() {
    $d = __DIR__ . '/../certs';
    if (!is_dir($d)) @mkdir($d, 0775, true);
    return $d;
}

function photos_dir() {
    $d = __DIR__ . '/../photos';
    if (!is_dir($d)) @mkdir($d, 0775, true);
    return $d;
}

function load_data() {
    $path = data_file_path();
    if (!file_exists($path)) {
        return null;
    }
    $json = file_get_contents($path);
    return json_decode($json, true);
}

function save_data($data) {
    $path = data_file_path();
    $data['meta']['lastUpdated'] = date('Y-m-d H:i:s');
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $bytes = @file_put_contents($path, $json);
    if ($bytes === false) {
        // Could not write — likely a permission issue (file owned by root)
        json_response([
            'error' => 'เขียน resume-data.json ไม่สำเร็จ (Permission denied) — รัน: docker exec resume chown www-data:www-data /var/www/html/resume-data.json'
        ], 500);
    }
    return true;
}

function json_response($data, $status = 200) {
    // Discard any buffered output (PHP warnings/notices that may have leaked)
    while (ob_get_level() > 0) { @ob_end_clean(); }
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function new_id($prefix) {
    return $prefix . '-' . substr(md5(uniqid('', true)), 0, 8);
}
