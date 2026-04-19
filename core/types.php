<?php
declare(strict_types=1);
if (!defined('ADMIN_KIT_RUNNING')) { http_response_code(404); exit; }

/**
 * Content types layer — each brief site declares its editable data
 * collections in site/admin/types.php. admin-kit auto-builds CRUD forms.
 *
 * Expected format of site/admin/types.php (returns an array):
 *
 *   return [
 *     'camps' => [
 *       'label'            => 'Табори',
 *       'label_singular'   => 'Табір',
 *       'icon'             => 'list',
 *       'source'           => 'camps2026',   // path inside content.json (flat key or dotted path)
 *       'display'          => ['dates', 'name', 'conference', 'age'],
 *       'search'           => ['name', 'place', 'responsible'],
 *       'sort'             => 'month,dates',
 *       'fields'           => [
 *          'dates'   => ['label' => 'Дати', 'type' => 'text', 'required' => true, 'col' => 6],
 *          'name'    => ['label' => 'Назва', 'type' => 'text', 'required' => true, 'col' => 6],
 *          'bucket'  => ['label' => 'Категорія', 'type' => 'select', 'options_from' => 'categories.*.key', 'col' => 4],
 *          // ...
 *       ],
 *     ],
 *   ];
 */

function ak_types(): array {
    static $cached = null;
    if ($cached !== null) return $cached;
    $p = SITE_ADMIN . '/types.php';
    $cached = is_file($p) ? (array)require $p : [];
    return $cached;
}

function ak_type(string $key): ?array {
    $all = ak_types();
    return $all[$key] ?? null;
}

function ak_type_records(string $key, array $content): array {
    $t = ak_type($key);
    if (!$t) return [];
    $src = $t['source'] ?? $key;
    $parts = explode('.', $src);
    $node = $content;
    foreach ($parts as $p) {
        if (!is_array($node) || !array_key_exists($p, $node)) return [];
        $node = $node[$p];
    }
    return is_array($node) ? $node : [];
}

function ak_type_set_records(string $key, array $content, array $records): array {
    $t = ak_type($key);
    if (!$t) return $content;
    $src = $t['source'] ?? $key;
    $parts = explode('.', $src);
    $last = array_pop($parts);
    $ref = &$content;
    foreach ($parts as $p) {
        if (!isset($ref[$p]) || !is_array($ref[$p])) $ref[$p] = [];
        $ref = &$ref[$p];
    }
    $ref[$last] = $records;
    return $content;
}

function ak_resolve_options($spec, array $content): array {
    if (is_array($spec)) {
        $out = [];
        foreach ($spec as $k => $v) {
            $out[] = is_int($k) ? ['value' => $v, 'label' => (string)$v] : ['value' => $k, 'label' => (string)$v];
        }
        return $out;
    }
    if (is_string($spec) && str_contains($spec, '.*.')) {
        // e.g. "categories.*.key" with optional label suffix after pipe
        [$path, $labelField] = array_pad(explode('|', $spec, 2), 2, null);
        [$collPath, $valField] = [substr($path, 0, strrpos($path, '.*.')), substr($path, strrpos($path, '.*.') + 3)];
        $parts = explode('.', $collPath);
        $node = $content;
        foreach ($parts as $p) {
            if (!is_array($node) || !array_key_exists($p, $node)) return [];
            $node = $node[$p];
        }
        if (!is_array($node)) return [];
        $out = [];
        foreach ($node as $row) {
            $val = is_array($row) ? ($row[$valField] ?? null) : null;
            $lab = $labelField && is_array($row) ? ($row[$labelField] ?? $val) : $val;
            if ($val !== null) $out[] = ['value' => (string)$val, 'label' => (string)$lab];
        }
        return $out;
    }
    return [];
}

function ak_sort_records(array $records, ?string $sort): array {
    if (!$sort) return $records;
    $keys = array_map('trim', explode(',', $sort));
    usort($records, function ($a, $b) use ($keys) {
        foreach ($keys as $k) {
            $av = is_array($a) ? ($a[$k] ?? '') : '';
            $bv = is_array($b) ? ($b[$k] ?? '') : '';
            if ($av === $bv) continue;
            return $av <=> $bv;
        }
        return 0;
    });
    return $records;
}
