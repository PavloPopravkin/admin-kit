<?php
declare(strict_types=1);
if (!defined('ADMIN_KIT_RUNNING')) { http_response_code(404); exit; }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { ak_json_response(['ok'=>false, 'err'=>'method'], 405); }
if (!ak_check_csrf($_POST['csrf'] ?? null)) { ak_json_response(['ok'=>false, 'err'=>'csrf'], 403); }

// Autosave is essentially a save with _ajax=1
$_POST['_ajax'] = '1';
require __DIR__ . '/save.php';
