<?php
declare(strict_types=1);
if (!defined('ADMIN_KIT_RUNNING')) { http_response_code(404); exit; }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Method not allowed'); }
if (!ak_check_csrf($_POST['csrf'] ?? null)) { http_response_code(403); exit('CSRF'); }

if (!ak_has_draft()) {
    ak_flash_set('info', 'Немає незбережених змін.');
    ak_redirect($_SERVER['HTTP_REFERER'] ?? '/admin/');
}
if (!ak_publish_draft()) {
    ak_flash_set('error', 'Не вдалося опублікувати.');
} else {
    ak_log_activity('publish');
    ak_flash_set('success', 'Опубліковано! Сайт оновлено.');
}
ak_redirect($_SERVER['HTTP_REFERER'] ?? '/admin/');
