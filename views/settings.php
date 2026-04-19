<?php
declare(strict_types=1);
if (!defined('ADMIN_KIT_RUNNING')) { http_response_code(404); exit; }

$content = ak_load_working();
$site = $content['site'] ?? [];
$hero = $content['hero'] ?? [];
$about = $content['about'] ?? [];
$contacts = $content['contacts'] ?? [];
$submit = $content['submitCta'] ?? [];
$archive = $content['archive'] ?? [];
$csrf = ak_csrf_token();

$tab = $_GET['s'] ?? 'general';
$tabs = ['general' => 'Бренд', 'hero' => 'Hero', 'about' => 'Про', 'contacts' => 'Контакти', 'submit' => 'Submit CTA', 'archive' => 'Архів'];

ob_start(); ?>
<div class="ak-settings-tabs">
  <?php foreach ($tabs as $k => $l): ?>
    <a href="?action=settings&s=<?= ak_e($k) ?>" class="<?= $tab === $k ? 'is-active' : '' ?>"><?= ak_e($l) ?></a>
  <?php endforeach; ?>
</div>

<form method="post" action="/admin/?action=save" id="editForm">
  <input type="hidden" name="csrf" value="<?= ak_e($csrf) ?>">
  <input type="hidden" name="scope" value="settings:<?= ak_e($tab) ?>">

  <?php if ($tab === 'general'): ?>
    <div class="ak-panel">
      <h3>Бренд</h3>
      <div class="ak-grid">
        <label class="ak-field col-6"><span class="ak-field__label">Назва</span><input type="text" name="site[brand]" value="<?= ak_e($site['brand'] ?? '') ?>"></label>
        <label class="ak-field col-6"><span class="ak-field__label">Слоган</span><input type="text" name="site[tagline]" value="<?= ak_e($site['tagline'] ?? '') ?>"></label>
        <label class="ak-field col-3"><span class="ak-field__label">Мова</span><input type="text" name="site[lang]" value="<?= ak_e($site['lang'] ?? 'uk') ?>"></label>
        <label class="ak-field col-3"><span class="ak-field__label">Рік</span><input type="text" name="site[year]" value="<?= ak_e($site['year'] ?? '2026') ?>"></label>
      </div>
    </div>
    <div class="ak-panel">
      <h3>Кольори</h3>
      <div class="ak-grid">
        <label class="ak-field col-6"><span class="ak-field__label">Основний (темно-зелений)</span><div class="color-picker"><input type="color" value="<?= ak_e($site['primaryColor'] ?? '#023936') ?>" oninput="this.nextElementSibling.value=this.value"><input type="text" name="site[primaryColor]" value="<?= ak_e($site['primaryColor'] ?? '#023936') ?>"></div></label>
        <label class="ak-field col-6"><span class="ak-field__label">Акцент Orange</span><div class="color-picker"><input type="color" value="<?= ak_e($site['accentOrange'] ?? '#E68620') ?>" oninput="this.nextElementSibling.value=this.value"><input type="text" name="site[accentOrange]" value="<?= ak_e($site['accentOrange'] ?? '#E68620') ?>"></div></label>
        <label class="ak-field col-6"><span class="ak-field__label">Акцент Lime</span><div class="color-picker"><input type="color" value="<?= ak_e($site['accentLime'] ?? '#D8FE01') ?>" oninput="this.nextElementSibling.value=this.value"><input type="text" name="site[accentLime]" value="<?= ak_e($site['accentLime'] ?? '#D8FE01') ?>"></div></label>
        <label class="ak-field col-6"><span class="ak-field__label">Колір тексту</span><div class="color-picker"><input type="color" value="<?= ak_e($site['textColor'] ?? '#FEFEFE') ?>" oninput="this.nextElementSibling.value=this.value"><input type="text" name="site[textColor]" value="<?= ak_e($site['textColor'] ?? '#FEFEFE') ?>"></div></label>
      </div>
    </div>

  <?php elseif ($tab === 'hero'): ?>
    <div class="ak-panel">
      <h3>Hero-секція</h3>
      <div class="ak-grid">
        <label class="ak-field col-12"><span class="ak-field__label">Eyebrow (маленький напис зверху)</span><input type="text" name="hero[eyebrow]" value="<?= ak_e($hero['eyebrow'] ?? '') ?>"></label>
        <label class="ak-field col-4"><span class="ak-field__label">Заголовок — рядок 1</span><input type="text" name="hero[heading]" value="<?= ak_e($hero['heading'] ?? '') ?>"></label>
        <label class="ak-field col-4"><span class="ak-field__label">Заголовок — рядок 2</span><input type="text" name="hero[headingLine2]" value="<?= ak_e($hero['headingLine2'] ?? '') ?>"></label>
        <label class="ak-field col-4"><span class="ak-field__label">Рік (акцент)</span><input type="text" name="hero[headingYear]" value="<?= ak_e($hero['headingYear'] ?? '') ?>"></label>
        <label class="ak-field col-12"><span class="ak-field__label">Підпис</span><textarea name="hero[sub]"><?= ak_e($hero['sub'] ?? '') ?></textarea></label>
        <label class="ak-field col-6"><span class="ak-field__label">Кнопка 1 — текст</span><input type="text" name="hero[ctaPrimary]" value="<?= ak_e($hero['ctaPrimary'] ?? '') ?>"></label>
        <label class="ak-field col-6"><span class="ak-field__label">Кнопка 1 — посилання</span><input type="text" name="hero[ctaPrimaryHref]" value="<?= ak_e($hero['ctaPrimaryHref'] ?? '') ?>"></label>
        <label class="ak-field col-6"><span class="ak-field__label">Кнопка 2 — текст</span><input type="text" name="hero[ctaSecondary]" value="<?= ak_e($hero['ctaSecondary'] ?? '') ?>"></label>
        <label class="ak-field col-6"><span class="ak-field__label">Кнопка 2 — посилання</span><input type="text" name="hero[ctaSecondaryHref]" value="<?= ak_e($hero['ctaSecondaryHref'] ?? '') ?>"></label>
      </div>
    </div>

  <?php elseif ($tab === 'about'): ?>
    <div class="ak-panel">
      <h3>Про бренд</h3>
      <label class="ak-checkbox ak-mt-2"><input type="checkbox" name="about[visible]" value="1" <?= !empty($about['visible']) ? 'checked' : '' ?>> Показувати секцію</label>
      <div class="ak-grid ak-mt-4">
        <label class="ak-field col-4"><span class="ak-field__label">Eyebrow</span><input type="text" name="about[eyebrow]" value="<?= ak_e($about['eyebrow'] ?? '') ?>"></label>
        <label class="ak-field col-8"><span class="ak-field__label">Заголовок</span><input type="text" name="about[heading]" value="<?= ak_e($about['heading'] ?? '') ?>"></label>
        <label class="ak-field col-12"><span class="ak-field__label">Текст</span><textarea name="about[body]" rows="5"><?= ak_e($about['body'] ?? '') ?></textarea></label>
      </div>
    </div>
    <div class="ak-panel">
      <h3>Статистики (3 блока)</h3>
      <div class="ak-grid">
        <?php $stats = $about['stats'] ?? []; for ($i = 0; $i < 3; $i++): $s = $stats[$i] ?? ['value'=>'','label'=>'']; ?>
          <label class="ak-field col-6"><span class="ak-field__label">Значення <?= $i+1 ?></span><input type="text" name="about[stats][<?= $i ?>][value]" value="<?= ak_e($s['value']) ?>"></label>
          <label class="ak-field col-6"><span class="ak-field__label">Підпис <?= $i+1 ?></span><input type="text" name="about[stats][<?= $i ?>][label]" value="<?= ak_e($s['label']) ?>"></label>
        <?php endfor; ?>
      </div>
    </div>

  <?php elseif ($tab === 'contacts'): ?>
    <div class="ak-panel">
      <h3>Куратор</h3>
      <div class="ak-grid">
        <label class="ak-field col-6"><span class="ak-field__label">Імʼя</span><input type="text" name="contacts[curatorName]" value="<?= ak_e($contacts['curatorName'] ?? '') ?>"></label>
        <label class="ak-field col-6"><span class="ak-field__label">Роль</span><input type="text" name="contacts[curatorRole]" value="<?= ak_e($contacts['curatorRole'] ?? '') ?>"></label>
        <label class="ak-field col-6"><span class="ak-field__label">Email</span><input type="email" name="contacts[curatorEmail]" value="<?= ak_e($contacts['curatorEmail'] ?? '') ?>"></label>
        <label class="ak-field col-6"><span class="ak-field__label">Телефон</span><input type="text" name="contacts[curatorPhone]" value="<?= ak_e($contacts['curatorPhone'] ?? '') ?>"></label>
        <label class="ak-field col-12"><span class="ak-field__label">Заголовок секції</span><input type="text" name="contacts[heading]" value="<?= ak_e($contacts['heading'] ?? '') ?>"></label>
      </div>
    </div>
    <div class="ak-panel">
      <h3>Youth promo</h3>
      <div class="ak-grid">
        <label class="ak-field col-4"><span class="ak-field__label">URL</span><input type="text" name="contacts[youthUrl]" value="<?= ak_e($contacts['youthUrl'] ?? '') ?>"></label>
        <label class="ak-field col-4"><span class="ak-field__label">Label</span><input type="text" name="contacts[youthLabel]" value="<?= ak_e($contacts['youthLabel'] ?? '') ?>"></label>
        <label class="ak-field col-4"><span class="ak-field__label">Назва</span><input type="text" name="contacts[youthLabel2]" value="<?= ak_e($contacts['youthLabel2'] ?? '') ?>"></label>
      </div>
    </div>

  <?php elseif ($tab === 'submit'): ?>
    <div class="ak-panel">
      <h3>Submit CTA банер</h3>
      <label class="ak-checkbox ak-mt-2"><input type="checkbox" name="submitCta[visible]" value="1" <?= !empty($submit['visible']) ? 'checked' : '' ?>> Показувати</label>
      <div class="ak-grid ak-mt-4">
        <label class="ak-field col-12"><span class="ak-field__label">Заголовок</span><input type="text" name="submitCta[heading]" value="<?= ak_e($submit['heading'] ?? '') ?>"></label>
        <label class="ak-field col-12"><span class="ak-field__label">Текст</span><textarea name="submitCta[body]"><?= ak_e($submit['body'] ?? '') ?></textarea></label>
        <label class="ak-field col-6"><span class="ak-field__label">Кнопка</span><input type="text" name="submitCta[ctaText]" value="<?= ak_e($submit['ctaText'] ?? '') ?>"></label>
        <label class="ak-field col-6"><span class="ak-field__label">Посилання</span><input type="text" name="submitCta[ctaHref]" value="<?= ak_e($submit['ctaHref'] ?? '') ?>"></label>
      </div>
    </div>

  <?php elseif ($tab === 'archive'): ?>
    <div class="ak-panel">
      <h3>Архів</h3>
      <label class="ak-checkbox ak-mt-2"><input type="checkbox" name="archive[visible]" value="1" <?= !empty($archive['visible']) ? 'checked' : '' ?>> Показувати секцію</label>
      <div class="ak-grid ak-mt-4">
        <label class="ak-field col-4"><span class="ak-field__label">Eyebrow</span><input type="text" name="archive[eyebrow]" value="<?= ak_e($archive['eyebrow'] ?? '') ?>"></label>
        <label class="ak-field col-8"><span class="ak-field__label">Заголовок</span><input type="text" name="archive[heading]" value="<?= ak_e($archive['heading'] ?? '') ?>"></label>
        <label class="ak-field col-12"><span class="ak-field__label">Текст</span><textarea name="archive[body]"><?= ak_e($archive['body'] ?? '') ?></textarea></label>
      </div>
    </div>
    <div class="ak-panel">
      <h3>Записи архіву</h3>
      <div class="ak-grid">
        <?php $items = $archive['items'] ?? []; foreach ($items as $i => $it): ?>
          <label class="ak-field col-3"><span class="ak-field__label">Рік</span><input type="text" name="archive[items][<?= $i ?>][year]" value="<?= ak_e($it['year'] ?? '') ?>"></label>
          <label class="ak-field col-5"><span class="ak-field__label">Назва</span><input type="text" name="archive[items][<?= $i ?>][title]" value="<?= ak_e($it['title'] ?? '') ?>"></label>
          <label class="ak-field col-4"><span class="ak-field__label">URL</span><input type="text" name="archive[items][<?= $i ?>][url]" value="<?= ak_e($it['url'] ?? '') ?>"></label>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>

  <div class="ak-form-footer">
    <button type="submit" class="ak-btn ak-btn--primary"><?= ak_icon('ak-save') ?> Зберегти</button>
  </div>
</form>
<?php
$__content = ob_get_clean();
$__page = 'settings';
$__title = 'Налаштування';
require ADMIN_KIT_PATH . '/templates/layout.php';
