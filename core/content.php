<?php
declare(strict_types=1);
if (!defined('ADMIN_KIT_RUNNING')) { http_response_code(404); exit; }

/**
 * Content storage with draft/live separation.
 *
 *   data/content.json   — live, served to the public
 *   data/draft.json     — draft, served to admins on /?preview=draft and inside admin editor
 *
 * Admin edits always write to draft.json. Publishing copies draft → live.
 */

function ak_live_path(): string  { return SITE_DATA . '/content.json'; }
function ak_draft_path(): string { return SITE_DATA . '/draft.json'; }

function ak_load_live(): array {
    $raw = @file_get_contents(ak_live_path());
    $d = @json_decode($raw ?: '{}', true);
    return is_array($d) ? $d : [];
}

function ak_load_draft(): array {
    $p = ak_draft_path();
    if (!is_file($p)) return ak_load_live();
    $raw = @file_get_contents($p);
    $d = @json_decode($raw ?: '{}', true);
    return is_array($d) ? $d : ak_load_live();
}

/** Admin-facing: current editable state. Draft if exists, else live. */
function ak_load_working(): array {
    return is_file(ak_draft_path()) ? ak_load_draft() : ak_load_live();
}

function ak_has_draft(): bool {
    if (!is_file(ak_draft_path())) return false;
    $live  = @json_encode(ak_load_live(),  JSON_UNESCAPED_UNICODE);
    $draft = @json_encode(ak_load_draft(), JSON_UNESCAPED_UNICODE);
    return $live !== $draft;
}

function ak_save_draft(array $data): bool {
    return ak_atomic_write(ak_draft_path(), $data);
}

function ak_publish_draft(): bool {
    if (!is_file(ak_draft_path())) return false;
    $draft = ak_load_draft();
    ak_snapshot_before_publish();
    if (!ak_atomic_write(ak_live_path(), $draft)) return false;
    @unlink(ak_draft_path());
    return true;
}

function ak_discard_draft(): bool {
    if (!is_file(ak_draft_path())) return true;
    return @unlink(ak_draft_path());
}

function ak_snapshot_before_publish(): void {
    $dir = SITE_DATA . '/snapshots';
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
    $now = date('Y-m-d_His');
    @copy(ak_live_path(), $dir . "/content-{$now}.json");
    // keep last 10
    $files = glob($dir . '/content-*.json') ?: [];
    sort($files);
    while (count($files) > 10) {
        @unlink(array_shift($files));
    }
}

function ak_atomic_write(string $path, array $data): bool {
    $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    if ($json === false) return false;
    $tmp = $path . '.tmp';
    if (file_put_contents($tmp, $json, LOCK_EX) === false) return false;
    if (!rename($tmp, $path)) { @unlink($tmp); return false; }
    return true;
}

/**
 * Update a top-level key of the working content with an array of values (deep merge optional).
 */
function ak_update_working(string $key, $value, bool $merge = false): bool {
    $content = ak_load_working();
    if ($merge && isset($content[$key]) && is_array($content[$key]) && is_array($value)) {
        $content[$key] = array_merge($content[$key], $value);
    } else {
        $content[$key] = $value;
    }
    return ak_save_draft($content);
}
