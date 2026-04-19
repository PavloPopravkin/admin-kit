<?php
declare(strict_types=1);
if (!defined('ADMIN_KIT_RUNNING')) { http_response_code(404); exit; }

$content = ak_load_working();
$types = ak_types();

// Build counts
$counts = [];
foreach ($types as $tk => $t) {
    $counts[$tk] = count(ak_type_records($tk, $content));
}
$mediaCount = count(ak_media_load());

// Last edit
$lastEditTs = 0;
if (is_file(ak_draft_path())) $lastEditTs = (int)@filemtime(ak_draft_path());
if (!$lastEditTs && is_file(ak_live_path())) $lastEditTs = (int)@filemtime(ak_live_path());

// Activity
$activity = [];
$actPath = SITE_DATA . '/activity.json';
if (is_file($actPath)) {
    $dec = @json_decode(@file_get_contents($actPath) ?: '[]', true);
    if (is_array($dec)) $activity = array_slice($dec, 0, 6);
}

ob_start(); ?>
<section class="ak-kpi-row">
  <div class="ak-kpi">
    <span class="ak-kpi__label">Останнє редагування</span>
    <span class="ak-kpi__value" style="font-size:20px"><?= $lastEditTs ? ak_e(ak_human_time($lastEditTs)) : '—' ?></span>
    <span class="ak-kpi__sub">Статус: <?= ak_has_draft() ? '<strong style="color:var(--ak-orange)">є чорновик</strong>' : '<strong>опубліковано</strong>' ?></span>
  </div>
  <?php foreach ($types as $tk => $t): if (($t['hide_from_dashboard'] ?? false)) continue; ?>
    <div class="ak-kpi">
      <span class="ak-kpi__label"><?= ak_e($t['label'] ?? $tk) ?></span>
      <span class="ak-kpi__value"><?= (int)$counts[$tk] ?></span>
      <a class="ak-kpi__sub" href="/admin/?action=edit&type=<?= ak_e($tk) ?>">Керувати →</a>
    </div>
  <?php endforeach; ?>
  <div class="ak-kpi">
    <span class="ak-kpi__label">Медіа</span>
    <span class="ak-kpi__value"><?= (int)$mediaCount ?></span>
    <a class="ak-kpi__sub" href="/admin/?action=media">Галерея →</a>
  </div>
</section>

<section class="ak-dash-grid">
  <a class="ak-card" href="/admin/?action=html">
    <div class="ak-card__icon-box"><?= ak_icon('ak-brush') ?></div>
    <div class="ak-card__title">Візуальний редактор</div>
    <div class="ak-card__sub">Drag-and-drop редагування макета. Перетягуй блоки, клікай щоб міняти текст і стилі, обирай мобайл/десктоп превʼю.</div>
  </a>
  <?php foreach ($types as $tk => $t): ?>
    <a class="ak-card" href="/admin/?action=edit&type=<?= ak_e($tk) ?>">
      <div class="ak-card__icon-box"><?= ak_icon('ak-' . ($t['icon'] ?? 'list')) ?></div>
      <div class="ak-card__title"><?= ak_e($t['label'] ?? $tk) ?></div>
      <div class="ak-card__sub"><?= ak_e($t['description'] ?? 'Редагувати записи — додавати, видаляти, сортувати.') ?></div>
      <span class="ak-card__count"><?= (int)$counts[$tk] ?></span>
    </a>
  <?php endforeach; ?>
  <a class="ak-card" href="/admin/?action=media">
    <div class="ak-card__icon-box"><?= ak_icon('ak-image') ?></div>
    <div class="ak-card__title">Медіа-бібліотека</div>
    <div class="ak-card__sub">Drag-drop завантаження, галерея переглядів, alt-тексти. Картинки можна повторно використовувати в будь-якому блоці.</div>
    <span class="ak-card__count"><?= (int)$mediaCount ?></span>
  </a>
  <a class="ak-card" href="/admin/?action=settings">
    <div class="ak-card__icon-box"><?= ak_icon('ak-settings') ?></div>
    <div class="ak-card__title">Налаштування</div>
    <div class="ak-card__sub">Бренд, кольори, шрифти, контакти, hero-секція, about, архів, submit-CTA.</div>
  </a>
</section>

<?php if (!empty($activity)): ?>
<section class="ak-activity">
  <h3>Остання активність</h3>
  <?php foreach ($activity as $a):
    $ev = (string)($a['event'] ?? '');
    $map = ['login'=>'Вхід','publish'=>'Опубліковано','save'=>'Збережено','upload'=>'Завантажено файл','media-delete'=>'Видалено файл','discard'=>'Скасовано чорновик'];
    $label = $map[$ev] ?? $ev;
    $detail = $a['data']['summary'] ?? '';
  ?>
    <div class="ak-activity__item">
      <?= ak_icon('ak-edit') ?>
      <div><strong><?= ak_e($label) ?></strong><?php if ($detail): ?> · <?= ak_e($detail) ?><?php endif; ?></div>
      <span class="when"><?= ak_e(ak_human_time((int)($a['ts'] ?? time()))) ?></span>
    </div>
  <?php endforeach; ?>
</section>
<?php endif; ?>
<?php
$__content = ob_get_clean();
$__page = 'dashboard';
$__title = 'Огляд';
require ADMIN_KIT_PATH . '/templates/layout.php';
