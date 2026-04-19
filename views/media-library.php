<?php
declare(strict_types=1);
if (!defined('ADMIN_KIT_RUNNING')) { http_response_code(404); exit; }

$media = ak_media_load();
$csrf = ak_csrf_token();

ob_start(); ?>
<div class="ak-media-drop" id="mediaDrop">
  <?= ak_icon('ak-upload', null, 32) ?>
  <h3 class="ak-mt-2">Перетягни сюди файли або клікни</h3>
  <p>Підтримка JPG / PNG / WEBP / SVG / GIF · до 12 МБ · автозавантаження</p>
  <input type="file" id="mediaInput" accept="image/*" multiple style="display:none">
</div>

<div class="ak-media-grid" id="mediaGrid">
  <?php foreach ($media as $m): ?>
    <div class="ak-media-tile" data-id="<?= ak_e($m['id']) ?>">
      <div class="ak-media-tile__img">
        <?php if (!empty($m['url'])): ?>
          <img src="<?= ak_e($m['url']) ?>" alt="<?= ak_e($m['alt'] ?? '') ?>" loading="lazy">
        <?php endif; ?>
        <button type="button" class="copy-url" data-url="<?= ak_e($m['url']) ?>">Копіювати URL</button>
      </div>
      <div class="ak-media-tile__meta">
        <input type="text" placeholder="alt-текст" value="<?= ak_e($m['alt'] ?? '') ?>" data-alt="<?= ak_e($m['id']) ?>">
        <span class="name" title="<?= ak_e($m['filename']) ?>"><?= ak_e($m['filename']) ?></span>
        <?php if (!empty($m['width'])): ?><span class="name"><?= (int)$m['width'] ?>×<?= (int)$m['height'] ?></span><?php endif; ?>
        <button type="button" class="ak-media-tile__del" data-del="<?= ak_e($m['id']) ?>"><?= ak_icon('ak-trash') ?> Видалити</button>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<p class="ak-mt-4 ak-dim" style="font-size:12px">Файли зберігаються в <code>/assets/img/uploads/</code>, індекс — <code>/data/media.json</code>. URL можна вставляти в блоки візуального редактора або в поле «Картинка».</p>

<script>window.AK_CSRF = <?= json_encode($csrf) ?>;</script>
<?php
$__content = ob_get_clean();
$__page = 'media';
$__title = 'Медіа-бібліотека';
require ADMIN_KIT_PATH . '/templates/layout.php';
