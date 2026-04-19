<?php
declare(strict_types=1);
if (!defined('ADMIN_KIT_RUNNING')) { http_response_code(404); exit; }

function ak_media_index_path(): string { return SITE_DATA . '/media.json'; }

function ak_media_load(): array {
    $p = ak_media_index_path();
    if (!is_file($p)) return [];
    $d = @json_decode(@file_get_contents($p) ?: '[]', true);
    return is_array($d) ? $d : [];
}

function ak_media_save(array $index): bool {
    return ak_atomic_write(ak_media_index_path(), $index);
}

function ak_media_add(string $filename, int $size, ?array $dims, string $mime, string $alt = ''): array {
    $index = ak_media_load();
    $id = uniqid('m', true);
    $item = [
        'id'       => $id,
        'filename' => $filename,
        'url'      => MEDIA_URL . '/' . $filename,
        'size'     => $size,
        'width'    => $dims['w'] ?? null,
        'height'   => $dims['h'] ?? null,
        'mime'     => $mime,
        'alt'      => $alt,
        'uploaded' => time(),
    ];
    array_unshift($index, $item);
    ak_media_save($index);
    return $item;
}

function ak_media_delete(string $id): bool {
    $index = ak_media_load();
    $found = null;
    $filtered = [];
    foreach ($index as $m) {
        if (($m['id'] ?? '') === $id) { $found = $m; continue; }
        $filtered[] = $m;
    }
    if (!$found) return false;
    @unlink(MEDIA_DIR . '/' . $found['filename']);
    ak_media_save($filtered);
    return true;
}

function ak_media_set_alt(string $id, string $alt): bool {
    $index = ak_media_load();
    $changed = false;
    foreach ($index as &$m) {
        if (($m['id'] ?? '') === $id) { $m['alt'] = $alt; $changed = true; break; }
    }
    unset($m);
    if ($changed) ak_media_save($index);
    return $changed;
}

/**
 * Slugify a filename while preserving extension. Cyrillic → latin transliteration.
 */
function ak_media_slug(string $name): string {
    $name = trim($name);
    $ext = '';
    if (preg_match('/(.+)\.([a-z0-9]+)$/i', $name, $m)) {
        [$_, $name, $ext] = $m;
    }
    $map = ['а'=>'a','б'=>'b','в'=>'v','г'=>'h','ґ'=>'g','д'=>'d','е'=>'e','є'=>'ie','ж'=>'zh','з'=>'z','и'=>'y','і'=>'i','ї'=>'i','й'=>'i','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'kh','ц'=>'ts','ч'=>'ch','ш'=>'sh','щ'=>'shch','ь'=>'','ю'=>'iu','я'=>'ia','ы'=>'y','э'=>'e','ё'=>'e','ъ'=>''];
    $name = mb_strtolower($name, 'UTF-8');
    $name = strtr($name, $map);
    $name = preg_replace('/[^a-z0-9]+/', '-', $name);
    $name = trim($name, '-');
    if ($name === '') $name = 'img';
    return $name . ($ext ? '.' . strtolower($ext) : '');
}

function ak_media_upload(array $file, string $alt = ''): ?array {
    if (!is_array($file) || !isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) return null;
    $size = (int)($file['size'] ?? 0);
    if ($size <= 0 || $size > 12 * 1024 * 1024) return null;
    $name = (string)($file['name'] ?? 'upload');
    $mime = (string)($file['type'] ?? '');
    $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/svg+xml'];
    if (!in_array($mime, $allowed, true)) return null;
    if (!is_dir(MEDIA_DIR)) @mkdir(MEDIA_DIR, 0755, true);

    // Decide target filename
    $slug = ak_media_slug($name);
    $target = MEDIA_DIR . '/' . $slug;
    $i = 1;
    while (is_file($target)) {
        $target = MEDIA_DIR . '/' . preg_replace('/(\.[^.]+)$/', '-' . $i . '$1', $slug, 1);
        $i++;
    }
    $slug = basename($target);

    if (!move_uploaded_file($file['tmp_name'], $target)) return null;
    @chmod($target, 0644);

    $dims = null;
    if (in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'image/gif'], true)) {
        $info = @getimagesize($target);
        if ($info) $dims = ['w' => $info[0], 'h' => $info[1]];
    }

    return ak_media_add($slug, $size, $dims, $mime, $alt);
}
