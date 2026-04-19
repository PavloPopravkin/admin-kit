<?php
declare(strict_types=1);
if (!defined('ADMIN_KIT_RUNNING')) { http_response_code(404); exit; }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Method not allowed'); }
if (!ak_check_csrf($_POST['csrf'] ?? null)) { http_response_code(403); exit('CSRF'); }

if (ak_discard_draft()) {
    ak_log_activity('discard');
    ak_flash_set('success', 'Чорновик скинуто.');
} else {
    ak_flash_set('error', 'Не вдалося скинути.');
}
ak_redirect($_SERVER['HTTP_REFERER'] ?? '/admin/');
