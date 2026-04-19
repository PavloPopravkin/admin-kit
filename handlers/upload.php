<?php
declare(strict_types=1);
if (!defined('ADMIN_KIT_RUNNING')) { http_response_code(404); exit; }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { ak_json_response(['ok'=>false,'err'=>'method'], 405); }
if (!ak_check_csrf($_POST['csrf'] ?? $_SERVER['HTTP_X_CSRF'] ?? null)) { ak_json_response(['ok'=>false,'err'=>'csrf'], 403); }

$results = [];
foreach (($_FILES['files']['name'] ?? []) as $i => $name) {
    $item = [
        'name'     => $_FILES['files']['name'][$i]     ?? '',
        'type'     => $_FILES['files']['type'][$i]     ?? '',
        'tmp_name' => $_FILES['files']['tmp_name'][$i] ?? '',
        'error'    => $_FILES['files']['error'][$i]    ?? 0,
        'size'     => $_FILES['files']['size'][$i]     ?? 0,
    ];
    if ($item['error'] !== UPLOAD_ERR_OK) { $results[] = ['ok'=>false,'name'=>$item['name'],'err'=>'upload_err_'.$item['error']]; continue; }
    $uploaded = ak_media_upload($item);
    if ($uploaded) {
        ak_log_activity('upload', ['summary' => $uploaded['filename']]);
        $results[] = ['ok'=>true, 'item'=>$uploaded];
    } else {
        $results[] = ['ok'=>false, 'name'=>$item['name'], 'err'=>'rejected'];
    }
}
ak_json_response(['ok' => true, 'results' => $results]);
