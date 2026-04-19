<?php
declare(strict_types=1);
if (!defined('ADMIN_KIT_RUNNING')) { http_response_code(404); exit; }

// Tiny endpoint used by inline-edit.js on the live site:
// returns whether the current visitor is an admin.
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
echo json_encode(['admin' => ak_is_admin()]);
exit;
