<?php
if (!defined('ADMIN_KIT_RUNNING')) { http_response_code(404); exit; }
$__page    = $__page    ?? 'dashboard';
$__title   = $__title   ?? 'Admin';
$__content = $__content ?? '';
$__extraHead = $__extraHead ?? '';
$__extraFoot = $__extraFoot ?? '';
$hasDraft = function_exists('ak_has_draft') ? ak_has_draft() : false;
$flash = ak_flash_pop();

$nav = [
  'dashboard'  => ['label' => 'Огляд',        'icon' => 'home'],
];
foreach (ak_types() as $tk => $t) {
  $nav['edit:'.$tk] = ['label' => $t['label'] ?? $tk, 'icon' => $t['icon'] ?? 'list', 'type' => $tk];
}
$nav['html']     = ['label' => 'Візуальний',  'icon' => 'layout'];
$nav['media']    = ['label' => 'Медіа',       'icon' => 'image'];
$nav['settings'] = ['label' => 'Налаштування','icon' => 'settings'];
?><!doctype html>
<html lang="<?= ak_e(SITE_LANG) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= ak_e($__title) ?> · <?= ak_e(SITE_BRAND) ?> admin</title>
<meta name="robots" content="noindex,nofollow">
<link rel="icon" type="image/svg+xml" href="/assets/img/icons/favicon.svg">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Unbounded:wght@700;800;900&display=swap">
<link rel="stylesheet" href="<?= ADMIN_KIT_URL ?>/assets/admin.css?v=<?= ADMIN_KIT_VERSION ?>">
<?= $__extraHead ?>
</head>
<body class="ak-body" data-page="<?= ak_e($__page) ?>">
<?php require ADMIN_KIT_PATH . '/assets/icons.svg'; ?>

<aside class="ak-side">
  <a href="/admin/" class="ak-logo">
    <svg class="mark" aria-hidden="true"><use href="/assets/img/icons/sprite.svg#ic-logo-mark"/></svg>
    <span><?= ak_e(SITE_BRAND) ?></span>
  </a>
  <nav class="ak-nav">
    <?php foreach ($nav as $key => $item):
      $href = '/admin/';
      if ($key === 'dashboard') $href = '/admin/';
      elseif (strpos($key, 'edit:') === 0) $href = '/admin/?action=edit&type=' . urlencode($item['type']);
      else $href = '/admin/?action=' . $key;
      $pageMatches = ($key === $__page) || (strpos($key, 'edit:') === 0 && $__page === 'edit:' . ($_GET['type'] ?? ''));
    ?>
      <a href="<?= ak_e($href) ?>" class="<?= $pageMatches ? 'is-active' : '' ?>">
        <?= ak_icon('ak-' . $item['icon']) ?>
        <span><?= ak_e($item['label']) ?></span>
      </a>
    <?php endforeach; ?>
  </nav>
  <div class="ak-side__bottom">
    <a href="/" target="_blank" class="ak-side__view">
      <?= ak_icon('ak-external') ?>
      <span>Сайт</span>
    </a>
    <a href="/admin/?action=logout" class="ak-side__logout">
      <?= ak_icon('ak-logout') ?>
      <span>Вийти</span>
    </a>
  </div>
</aside>

<main class="ak-main">
  <header class="ak-topbar">
    <h1 class="ak-topbar__title"><?= ak_e($__title) ?></h1>
    <div class="ak-topbar__actions">
      <?php if ($hasDraft): ?>
        <span class="ak-draft-pill">
          <?= ak_icon('ak-dot') ?> Є незбережені зміни
        </span>
        <a href="/?preview=draft" target="_blank" class="ak-btn ak-btn--ghost"><?= ak_icon('ak-eye') ?> Попередній перегляд</a>
        <form action="/admin/?action=publish" method="post" style="display:inline">
          <input type="hidden" name="csrf" value="<?= ak_e(ak_csrf_token()) ?>">
          <button type="submit" class="ak-btn ak-btn--primary"><?= ak_icon('ak-upload') ?> Опублікувати</button>
        </form>
      <?php else: ?>
        <a href="/" target="_blank" class="ak-btn ak-btn--ghost"><?= ak_icon('ak-external') ?> Відкрити сайт</a>
      <?php endif; ?>
    </div>
  </header>

  <?php if ($flash): ?>
    <div class="ak-flash ak-flash--<?= ak_e($flash['type']) ?>"><?= ak_e($flash['msg']) ?></div>
  <?php endif; ?>

  <div class="ak-content">
    <?= $__content ?>
  </div>
</main>

<script src="<?= ADMIN_KIT_URL ?>/assets/admin.js?v=<?= ADMIN_KIT_VERSION ?>" defer></script>
<?= $__extraFoot ?>
</body>
</html>
