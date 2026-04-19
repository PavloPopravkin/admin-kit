<?php
declare(strict_types=1);
if (!defined('ADMIN_KIT_RUNNING')) { http_response_code(404); exit; }

function ak_session_start(): void {
    if (session_status() === PHP_SESSION_ACTIVE) return;
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
          || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    session_set_cookie_params([
        'lifetime' => 0, 'path' => '/', 'domain' => '',
        'secure' => $secure, 'httponly' => true, 'samesite' => 'Lax',
    ]);
    session_name('AKADMIN');
    session_start();
}

function ak_is_admin(): bool {
    ak_session_start();
    return !empty($_SESSION['admin']) && $_SESSION['admin'] === true;
}

function ak_require_admin(): void {
    if (!ak_is_admin()) {
        $ret = $_SERVER['REQUEST_URI'] ?? '/admin/';
        ak_redirect('/admin/?action=login&ret=' . urlencode($ret));
    }
}

function ak_csrf_token(): string {
    ak_session_start();
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}

function ak_check_csrf(?string $token): bool {
    ak_session_start();
    return !empty($token) && !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}

function ak_logout(): void {
    ak_session_start();
    $_SESSION = [];
    session_destroy();
    ak_redirect('/admin/?action=login');
}

function ak_creds_path(): string { return SITE_ADMIN . '/.credentials.php'; }
function ak_load_creds(): array {
    $p = ak_creds_path();
    if (!is_file($p)) return ['user' => '', 'hash' => ''];
    $c = require $p;
    return is_array($c) ? $c : ['user' => '', 'hash' => ''];
}

function ak_verify_login(string $user, string $pass): bool {
    $creds = ak_load_creds();
    if ($user === '' || empty($creds['user']) || empty($creds['hash'])) return false;
    return hash_equals($creds['user'], $user) && password_verify($pass, $creds['hash']);
}

function ak_log_activity(string $event, array $data = []): void {
    $log = SITE_DATA . '/activity.json';
    $arr = [];
    if (is_file($log)) {
        $raw = @file_get_contents($log);
        $dec = @json_decode($raw ?: '[]', true);
        if (is_array($dec)) $arr = $dec;
    }
    array_unshift($arr, [
        'ts'    => time(),
        'user'  => $_SESSION['user'] ?? 'admin',
        'event' => $event,
        'data'  => $data,
    ]);
    $arr = array_slice($arr, 0, 50);
    @file_put_contents($log, json_encode($arr, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT), LOCK_EX);
}
