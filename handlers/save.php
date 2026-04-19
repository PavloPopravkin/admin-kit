<?php
declare(strict_types=1);
if (!defined('ADMIN_KIT_RUNNING')) { http_response_code(404); exit; }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Method not allowed'); }
if (!ak_check_csrf($_POST['csrf'] ?? null)) { http_response_code(403); exit('CSRF'); }

$scope = (string)($_POST['scope'] ?? '');
$isJson = !empty($_POST['_ajax']);

$content = ak_load_working();
$summary = '';

if (strpos($scope, 'type:') === 0) {
    $typeKey = substr($scope, 5);
    $type = ak_type($typeKey);
    if (!$type) { http_response_code(400); exit('Unknown type'); }
    $fields = $type['fields'] ?? [];
    $records = [];
    foreach (($_POST['records'] ?? []) as $row) {
        if (!empty($row['_deleted'])) continue;
        $rec = [];
        foreach ($fields as $fk => $field) {
            $v = $row[$fk] ?? '';
            if (is_array($v)) $v = implode(',', array_map('strval', $v));
            $rec[$fk] = trim((string)$v);
        }
        // Require name/title field
        $titleKey = $type['display'][0] ?? (array_keys($fields)[0] ?? '');
        if ($titleKey && trim((string)($rec[$titleKey] ?? '')) === '') continue;
        $records[] = $rec;
    }
    if (!empty($type['sort'])) $records = ak_sort_records($records, $type['sort']);
    $content = ak_type_set_records($typeKey, $content, $records);

    // Derived fields: for camps → rebuild conferences list, etc.
    if ($typeKey === 'camps' && !empty($type['derive_conferences'])) {
        $confs = [];
        foreach ($records as $r) { if (!empty($r['conference'])) $confs[$r['conference']] = true; }
        $content['conferences'] = array_values(array_keys($confs));
        sort($content['conferences']);
    }
    $summary = sprintf('%s · %d записів', $type['label'] ?? $typeKey, count($records));
} elseif (strpos($scope, 'settings:') === 0) {
    $sec = substr($scope, 9);
    $map = ['general' => 'site', 'hero' => 'hero', 'about' => 'about', 'contacts' => 'contacts', 'submit' => 'submitCta', 'archive' => 'archive'];
    $target = $map[$sec] ?? null;
    if (!$target) { http_response_code(400); exit('Unknown settings section'); }
    $incoming = $_POST[$target] ?? ($target === 'submit' ? $_POST['submitCta'] ?? [] : []);
    // checkboxes → bool
    if (in_array($target, ['about', 'submitCta', 'archive'], true)) {
        $incoming['visible'] = !empty($incoming['visible']);
    }
    // Merge into existing (so untouched subkeys survive)
    $content[$target] = is_array($content[$target] ?? null) ? array_replace($content[$target], $incoming) : $incoming;
    $summary = $sec;
} elseif ($scope === 'html') {
    $ov = $_POST['fullHtmlOverride'] ?? [];
    $content['fullHtmlOverride'] = [
        'enabled' => !empty($ov['enabled']),
        'html'    => (string)($ov['html'] ?? ''),
    ];
    $summary = 'HTML override';
} else {
    http_response_code(400); exit('Unknown scope');
}

if (!ak_save_draft($content)) { http_response_code(500); exit('Save failed'); }
ak_log_activity('save', ['summary' => $summary, 'scope' => $scope]);
ak_flash_set('success', 'Збережено в чорновик. Натисни «Опублікувати» щоб викласти на сайт.');

if ($isJson) ak_json_response(['ok' => true]);
$back = (string)($_SERVER['HTTP_REFERER'] ?? '/admin/');
ak_redirect($back);
