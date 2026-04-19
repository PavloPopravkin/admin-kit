<?php
declare(strict_types=1);
if (!defined('ADMIN_KIT_RUNNING')) { http_response_code(404); exit; }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { ak_json_response(['ok'=>false], 405); }
if (!ak_check_csrf($_POST['csrf'] ?? null)) { ak_json_response(['ok'=>false,'err'=>'csrf'], 403); }
$id = (string)($_POST['id'] ?? '');
if ($id === '') ak_json_response(['ok'=>false,'err'=>'no-id'], 400);
if (ak_media_delete($id)) { ak_log_activity('media-delete'); ak_json_response(['ok'=>true]); }
ak_json_response(['ok'=>false,'err'=>'not-found'], 404);
