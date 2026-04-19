<?php
declare(strict_types=1);
if (!defined('ADMIN_KIT_RUNNING')) { http_response_code(404); exit; }

$content = ak_load_working();
$ov = $content['fullHtmlOverride'] ?? [];
$csrf = ak_csrf_token();

// Pre-populate the textarea with CURRENT live page HTML so the editor seeds its canvas
// synchronously (no async fetch needed). Uses ob_start+include with preview=raw to bypass
// the fullHtmlOverride check.
function ak_capture_raw_page(): string {
    $prev = $_GET['preview'] ?? null;
    $_GET['preview'] = 'raw';
    ob_start();
    try {
        include SITE_ROOT . '/index.php';
    } catch (Throwable $e) {
        ob_end_clean();
        return '';
    }
    $html = ob_get_clean();
    if ($prev === null) unset($_GET['preview']); else $_GET['preview'] = $prev;
    return is_string($html) ? $html : '';
}

$starterHtml = (string)($ov['html'] ?? '');
if (trim($starterHtml) === '') {
    $starterHtml = ak_capture_raw_page();
}

$__extraHead = <<<HTML
<link rel="stylesheet" href="https://unpkg.com/grapesjs@0.21.11/dist/css/grapes.min.css">
<style>
  .gjs-one-bg      { background-color: #01221f !important; }
  .gjs-two-color   { color: #FEFEFE !important; }
  .gjs-three-bg    { background-color: #D8FE01 !important; color: #023936 !important; }
  .gjs-four-color, .gjs-four-color-h:hover { color: #D8FE01 !important; }
  #gjs             { border: 1px solid var(--ak-line-2); border-radius: var(--ak-radius); overflow: hidden; }
  .editor-help     { color: var(--ak-muted); font-size: 12px; margin: 6px 0 12px; }
</style>
HTML;

$__extraFoot = <<<HTML
<script src="https://unpkg.com/grapesjs@0.21.11/dist/grapes.min.js"></script>
<script src="https://unpkg.com/grapesjs-blocks-basic@1.0.2/dist/index.js"></script>
<script src="https://unpkg.com/grapesjs-preset-webpage@1.0.3/dist/index.js"></script>
<script src="/admin/admin-kit/assets/editor.js?v=1" defer></script>
HTML;

ob_start(); ?>
<form method="post" action="/admin/?action=save" id="editForm">
  <input type="hidden" name="csrf" value="<?= ak_e($csrf) ?>">
  <input type="hidden" name="scope" value="html">

  <div class="ak-panel">
    <label class="ak-checkbox">
      <input type="checkbox" name="fullHtmlOverride[enabled]" value="1" <?= !empty($ov['enabled']) ? 'checked' : '' ?>>
      <strong>Використовувати візуальний редактор замість шаблону</strong>
    </label>
    <p class="editor-help">Зняти прапорець — повертається шаблон (керування через вкладки «Налаштування», «Табори» тощо). Поставити — сайт показує те, що в редакторі нижче.</p>
  </div>

  <div class="ak-edit-toolbar">
    <button type="button" class="ak-btn ak-btn--ghost" id="revertDefault"><?= ak_icon('ak-undo') ?> Скинути до шаблону</button>
    <a class="ak-btn ak-btn--ghost" href="/?preview=draft" target="_blank"><?= ak_icon('ak-eye') ?> Превʼю draft</a>
    <a class="ak-btn ak-btn--ghost" href="/?preview=raw" target="_blank"><?= ak_icon('ak-external') ?> Відкрити шаблон</a>
  </div>

  <div id="gjs" style="height: 70vh"></div>
  <textarea name="fullHtmlOverride[html]" id="htmlArea" style="display:none"><?= ak_e($starterHtml) ?></textarea>
  <textarea id="htmlStarter" style="display:none"><?= ak_e($starterHtml) ?></textarea>

  <details class="ak-mt-4" id="rawDetails">
    <summary style="cursor:pointer; color:var(--ak-muted); font-weight:600">Raw HTML (для експертів)</summary>
    <textarea id="htmlRaw" readonly style="font-family:ui-monospace,Menlo,monospace; font-size:11px; min-height:240px; background:var(--ak-bg); margin-top:10px; white-space:pre"></textarea>
  </details>

  <div class="ak-form-footer">
    <button type="submit" class="ak-btn ak-btn--primary"><?= ak_icon('ak-save') ?> Зберегти</button>
  </div>
</form>
<?php
$__content = ob_get_clean();
$__page = 'html';
$__title = 'Візуальний редактор';
require ADMIN_KIT_PATH . '/templates/layout.php';
