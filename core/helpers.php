<?php
declare(strict_types=1);
if (!defined('ADMIN_KIT_RUNNING')) { http_response_code(404); exit; }

function ak_e($s): string {
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function ak_icon(string $id, ?string $class = null, ?int $size = null): string {
    $cls = $class ? ' class="' . ak_e($class) . '"' : '';
    $sz  = $size ? ' width="' . (int)$size . '" height="' . (int)$size . '"' : '';
    return '<svg' . $cls . $sz . ' aria-hidden="true"><use href="' . ADMIN_KIT_URL . '/assets/icons.svg#' . ak_e($id) . '"/></svg>';
}

function ak_phone_href(string $raw): string {
    $digits = preg_replace('/[^0-9+]/', '', $raw);
    if (!$digits) return '';
    if (str_starts_with($digits, '+')) return 'tel:' . $digits;
    if (str_starts_with($digits, '380')) return 'tel:+' . $digits;
    if (str_starts_with($digits, '0')) return 'tel:+38' . $digits;
    return 'tel:+' . $digits;
}

function ak_json_response($data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function ak_redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

function ak_flash_set(string $type, string $msg): void {
    ak_session_start();
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function ak_flash_pop(): ?array {
    ak_session_start();
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

function ak_human_time(int $ts): string {
    $diff = time() - $ts;
    if ($diff < 60) return 'щойно';
    if ($diff < 3600) return (int)($diff/60) . ' хв тому';
    if ($diff < 86400) return (int)($diff/3600) . ' год тому';
    if ($diff < 604800) return (int)($diff/86400) . ' дн тому';
    return date('d.m.Y', $ts);
}
