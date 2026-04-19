<?php
declare(strict_types=1);
if (!defined('ADMIN_KIT_RUNNING')) { http_response_code(404); exit; }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { ak_json_response(['ok'=>false], 405); }
if (!ak_check_csrf($_POST['csrf'] ?? null)) { ak_json_response(['ok'=>false,'err'=>'csrf'], 403); }
$id  = (string)($_POST['id']  ?? '');
$alt = (string)($_POST['alt'] ?? '');
if ($id === '') ak_json_response(['ok'=>false,'err'=>'no-id'], 400);
ak_media_set_alt($id, $alt);
ak_json_response(['ok'=>true]);
