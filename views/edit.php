<?php
declare(strict_types=1);
if (!defined('ADMIN_KIT_RUNNING')) { http_response_code(404); exit; }

$typeKey = (string)($_GET['type'] ?? '');
$type    = ak_type($typeKey);
if (!$type) { http_response_code(404); echo 'Unknown content type'; exit; }

$content = ak_load_working();
$records = ak_type_records($typeKey, $content);
$csrf    = ak_csrf_token();

/** Render one field input */
function ak_render_field(string $fieldKey, array $field, $value, int $idx, array $content, string $recordKey = 'records'): string {
    $name = "{$recordKey}[{$idx}][{$fieldKey}]";
    $id   = "f_{$recordKey}_{$idx}_{$fieldKey}";
    $type = $field['type'] ?? 'text';
    $col  = isset($field['col']) ? 'col-' . (int)$field['col'] : 'col-12';
    $required = !empty($field['required']) ? 'required' : '';
    $req = !empty($field['required']) ? ' <span class="req">*</span>' : '';
    $labelText = ak_e($field['label'] ?? $fieldKey);
    $help = !empty($field['help']) ? '<span class="ak-field__help">' . ak_e($field['help']) . '</span>' : '';
    $valAttr = ak_e((string)$value);

    $input = '';
    switch ($type) {
        case 'textarea':
            $input = "<textarea id=\"{$id}\" name=\"{$name}\" {$required} rows=\"3\">{$valAttr}</textarea>";
            break;
        case 'select':
            $options = ak_resolve_options($field['options'] ?? ($field['options_from'] ?? []), $content);
            $opts = '';
            $has = false;
            foreach ($options as $o) {
                $sel = ((string)$o['value'] === (string)$value) ? ' selected' : '';
                if ($sel) $has = true;
                $opts .= '<option value="' . ak_e($o['value']) . '"' . $sel . '>' . ak_e($o['label']) . '</option>';
            }
            if (!empty($field['allow_empty'])) $opts = '<option value=""></option>' . $opts;
            $input = "<select id=\"{$id}\" name=\"{$name}\" {$required}>{$opts}</select>";
            break;
        case 'datalist':
            $options = ak_resolve_options($field['options'] ?? ($field['options_from'] ?? []), $content);
            $listId = "dl_{$fieldKey}_{$idx}";
            $opts = '';
            foreach ($options as $o) $opts .= '<option value="' . ak_e($o['value']) . '">';
            $input = "<input type=\"text\" id=\"{$id}\" name=\"{$name}\" value=\"{$valAttr}\" list=\"{$listId}\" {$required}><datalist id=\"{$listId}\">{$opts}</datalist>";
            break;
        case 'email': case 'tel': case 'url':
            $input = "<input type=\"{$type}\" id=\"{$id}\" name=\"{$name}\" value=\"{$valAttr}\" {$required}>";
            break;
        case 'color':
            $input = "<div class=\"color-picker\"><input type=\"color\" value=\"{$valAttr}\" oninput=\"this.nextElementSibling.value=this.value\"><input type=\"text\" id=\"{$id}\" name=\"{$name}\" value=\"{$valAttr}\"></div>";
            break;
        case 'image':
            $input = "<input type=\"text\" id=\"{$id}\" name=\"{$name}\" value=\"{$valAttr}\" placeholder=\"/assets/img/…\" data-media-picker=\"1\">";
            break;
        case 'text':
        default:
            $pattern = !empty($field['pattern']) ? ' pattern="' . ak_e($field['pattern']) . '"' : '';
            $input = "<input type=\"text\" id=\"{$id}\" name=\"{$name}\" value=\"{$valAttr}\"{$pattern} {$required}>";
    }

    return <<<HTML
<label class="ak-field {$col}" data-field="{$fieldKey}">
  <span class="ak-field__label">{$labelText}{$req}</span>
  {$input}
  {$help}
  <span class="ak-field__error">Перевір поле</span>
</label>
HTML;
}

/** Render one record card */
function ak_render_record(int $i, array $rec, array $type, array $content, array $fields): string {
    $displayFields = $type['display'] ?? array_slice(array_keys($fields), 0, 3);
    $title = '';
    $meta = [];
    foreach ($displayFields as $j => $df) {
        $val = $rec[$df] ?? '';
        if (is_array($val)) $val = json_encode($val, JSON_UNESCAPED_UNICODE);
        $val = (string)$val;
        if ($j === 0) $title = $val;
        elseif (trim($val) !== '') $meta[] = $val;
    }
    if ($title === '') $title = '(без назви)';
    $titleEsc = ak_e($title);
    $metaEsc = ak_e(implode(' · ', $meta));

    $inputs = '';
    foreach ($fields as $fk => $field) {
        $inputs .= ak_render_field($fk, $field, $rec[$fk] ?? '', $i, $content, 'records');
    }
    $deletedAttr = '';
    return <<<HTML
<article class="ak-record" data-idx="{$i}">
  <header class="ak-record__head" data-toggle>
    <span class="ak-record__drag" title="Перетягни для сортування">{$dragIcon}</span>
    <div>
      <div class="ak-record__title">{$titleEsc}</div>
      <div class="ak-record__meta">{$metaEsc}</div>
    </div>
    <div class="ak-record__actions">
      <button type="button" class="ak-btn ak-btn--ghost ak-btn--sm" data-toggle-btn>Редагувати</button>
      <button type="button" class="ak-btn ak-btn--danger ak-btn--sm" data-delete>Видалити</button>
      <input type="hidden" name="records[{$i}][_deleted]" value="" data-deleted>
    </div>
  </header>
  <div class="ak-record__body"><div class="ak-grid">{$inputs}</div></div>
</article>
HTML;
}

ob_start();
$fields = $type['fields'] ?? [];
$dragIcon = ak_icon('ak-drag');
$plusIcon = ak_icon('ak-plus');
$searchIcon = ak_icon('ak-search');
$saveIcon = ak_icon('ak-save');
?>
<form method="post" action="/admin/?action=save" id="editForm" data-type="<?= ak_e($typeKey) ?>">
  <input type="hidden" name="csrf" value="<?= ak_e($csrf) ?>">
  <input type="hidden" name="scope" value="type:<?= ak_e($typeKey) ?>">
  <input type="hidden" name="type" value="<?= ak_e($typeKey) ?>">

  <div class="ak-edit-toolbar">
    <div class="ak-search">
      <?= $searchIcon ?>
      <input type="text" id="recordSearch" placeholder="Пошук по списку…">
    </div>
    <span class="ak-save-indicator" id="saveIndicator"><?= ak_icon('ak-check') ?> <span>Збережено</span></span>
  </div>

  <div class="ak-records" id="records" data-sortable="1">
    <?php foreach ($records as $i => $rec):
      $displayFields = $type['display'] ?? array_slice(array_keys($fields), 0, 3);
      $title = '';
      $meta  = [];
      foreach ($displayFields as $j => $df) {
        $val = $rec[$df] ?? '';
        if (is_array($val)) $val = implode(', ', array_filter(array_map('strval', $val)));
        $val = (string)$val;
        if ($j === 0) $title = $val;
        elseif (trim($val) !== '') $meta[] = $val;
      }
      if ($title === '') $title = '(без назви)';
    ?>
      <article class="ak-record" data-idx="<?= $i ?>">
        <header class="ak-record__head" data-toggle>
          <span class="ak-record__drag" title="Перетягни для сортування"><?= $dragIcon ?></span>
          <div>
            <div class="ak-record__title"><?= ak_e($title) ?></div>
            <div class="ak-record__meta"><?= ak_e(implode(' · ', $meta)) ?></div>
          </div>
          <div class="ak-record__actions">
            <button type="button" class="ak-btn ak-btn--ghost ak-btn--sm" data-toggle-btn>Редагувати</button>
            <button type="button" class="ak-btn ak-btn--danger ak-btn--sm" data-delete>Видалити</button>
            <input type="hidden" name="records[<?= $i ?>][_deleted]" value="" data-deleted>
          </div>
        </header>
        <div class="ak-record__body">
          <div class="ak-grid">
            <?php foreach ($fields as $fk => $field): ?>
              <?= ak_render_field($fk, $field, $rec[$fk] ?? '', $i, $content) ?>
            <?php endforeach; ?>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
  </div>

  <button type="button" class="ak-add-row ak-mt-4" id="addRecord"><?= $plusIcon ?> Додати <?= ak_e(mb_strtolower($type['label_singular'] ?? 'запис')) ?></button>

  <div class="ak-form-footer">
    <button type="submit" class="ak-btn ak-btn--primary"><?= $saveIcon ?> Зберегти</button>
  </div>
</form>

<template id="recordTemplate">
  <article class="ak-record is-new is-open" data-idx="__I__">
    <header class="ak-record__head" data-toggle>
      <span class="ak-record__drag"><?= $dragIcon ?></span>
      <div><div class="ak-record__title">Новий запис</div><div class="ak-record__meta"></div></div>
      <div class="ak-record__actions">
        <button type="button" class="ak-btn ak-btn--ghost ak-btn--sm" data-toggle-btn>Згорнути</button>
        <button type="button" class="ak-btn ak-btn--danger ak-btn--sm" data-delete>Видалити</button>
        <input type="hidden" name="records[__I__][_deleted]" value="" data-deleted>
      </div>
    </header>
    <div class="ak-record__body">
      <div class="ak-grid">
        <?php foreach ($fields as $fk => $field): ?>
          <?= ak_render_field($fk, $field, $field['default'] ?? '', 0, $content) ?>
        <?php endforeach; ?>
      </div>
    </div>
  </article>
</template>
<?php
$__content = ob_get_clean();
$__page = 'edit:' . $typeKey;
$__title = ($type['label'] ?? $typeKey);
require ADMIN_KIT_PATH . '/templates/layout.php';
