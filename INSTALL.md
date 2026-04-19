# Installing admin-kit into a new brief

Five small per-site files. Everything else comes from admin-kit/.

## 1. `site/admin/bootstrap.php`

```php
<?php
declare(strict_types=1);

// Brand tokens — used by admin UI chrome + emitted to editor.js via window.AK_CONFIG.
define('SITE_BRAND',    'My Brand');
define('SITE_LANG',     'uk');
define('SITE_TITLE',    'My Brand · Tagline');
define('SITE_THEME',    '#023936');
define('SITE_STYLES',   '/assets/css/styles.css');
define('SITE_FONTS',    'https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Unbounded:wght@700;800;900&display=swap');
define('SITE_SPRITE',   '/assets/img/icons/sprite.svg');
define('SITE_MAIN_JS',  '/assets/js/main.js');

require __DIR__ . '/admin-kit/bootstrap.php';
```

## 2. `site/admin/index.php`

One line:

```php
<?php require __DIR__ . '/bootstrap.php';
```

## 3. `site/admin/types.php`

Declare the editable collections. Each type key becomes an auto-built form at `/admin/?action=edit&type=<key>`.

```php
<?php
return [
    'camps' => [
        'label'           => 'Табори',
        'label_singular'  => 'табір',
        'icon'            => 'list',            // admin-kit icon id (see icons.svg)
        'source'          => 'camps2026',        // key in content.json (dotted path ok)
        'description'     => 'Список літніх таборів.',
        'display'         => ['name', 'dates'],  // columns in the list view
        'search'          => ['name', 'place'],
        'sort'            => 'month,dates',
        'derive_conferences' => true,             // optional post-save hook for this type
        'fields' => [
            'name'  => ['label' => 'Назва',  'type' => 'text', 'required' => true, 'col' => 6],
            'dates' => ['label' => 'Дати',   'type' => 'text', 'col' => 3],
            'month' => ['label' => 'Місяць', 'type' => 'text', 'pattern' => '[0-9]{2}', 'col' => 3],
            'bucket'=> ['label' => 'Категорія', 'type' => 'select',
                        'options_from' => 'categories.*.key|title', 'col' => 4],
            'notes' => ['label' => 'Примітки', 'type' => 'textarea', 'col' => 12],
        ],
    ],
];
```

Supported field types: `text`, `textarea`, `email`, `tel`, `url`, `color`, `image`, `select`, `datalist`. `options` can be literal or `options_from` path like `categories.*.key|title`.

## 4. `site/admin/.credentials.php`

Generate via:

```bash
PASS=$(python3 -c "import secrets,string; alph=string.ascii_letters+string.digits; print(''.join(secrets.choice(alph) for _ in range(14)))")
HASH=$(php -r "echo password_hash('$PASS', PASSWORD_BCRYPT);")
echo "pass: $PASS"
cat > site/admin/.credentials.php <<PHP
<?php
return [
    'user' => 'admin',
    'hash' => '$HASH',
];
PHP
```

## 5. `site/admin/.htaccess`

```
DirectoryIndex index.php

<Files ".credentials.php">
  Require all denied
</Files>
<Files "bootstrap.php">
  Require all denied
</Files>
<Files "types.php">
  Require all denied
</Files>

Options -Indexes
```

## Wire the live site

In `site/index.php`:

1. Near the top, support `?preview=draft` and the `fullHtmlOverride`:

   ```php
   require __DIR__ . '/includes/render.php';
   $content = site_load_content();  // your loader — check ?preview=draft session cookie AKADMIN

   $override = $content['fullHtmlOverride'] ?? [];
   $rawMode  = isset($_GET['preview']) && $_GET['preview'] === 'raw';
   if (!$rawMode && !empty($override['enabled']) && !empty($override['html'])) {
       header('Content-Type: text/html; charset=utf-8');
       $injected = '<script src="/admin/admin-kit/assets/inline-edit.js" defer></script></body>';
       echo str_replace('</body>', $injected, $override['html']);
       exit;
   }
   ```

2. Before `</body>`, include the inline-edit overlay:

   ```html
   <script src="/admin/admin-kit/assets/inline-edit.js" defer></script>
   ```

3. Your loader must support draft / live distinction:

   ```php
   function site_load_content(): array {
       // respect ?preview=draft if admin session present
       $cookieName = 'AKADMIN';
       if (session_status() === PHP_SESSION_NONE && !empty($_COOKIE[$cookieName])) {
           session_name($cookieName); @session_start();
       }
       $wantDraft = isset($_GET['preview']) && $_GET['preview'] === 'draft' && !empty($_SESSION['admin']);
       $root = dirname(__DIR__);
       $path = ($wantDraft && is_file($root . '/data/draft.json'))
           ? $root . '/data/draft.json'
           : $root . '/data/content.json';
       $d = json_decode((string)@file_get_contents($path), true);
       return is_array($d) ? $d : [];
   }
   ```

## Customize the GrapesJS editor for this brand

Edit `site/admin/admin-kit/assets/editor.js`:

- Change the `BRAND` object constants (fonts/styles/title/theme/lang/sprite/mainJs) at the top
- Replace `BRAND_BLOCKS` array with your site's drag-drop blocks

Everything else in editor.js is generic.

## Per-site settings tabs

`admin-kit/views/settings.php` ships with an A·CAMP-style tab set (general / hero / about / contacts / submit / archive). If your brief uses different sections, adapt that one file — it's the only settings UI file per site.

## Server-side infra (on Server 1 FastPanel, once per site)

See `fastpanel_php_site_setup.md` in Pavel's memory system. Summary: create dedicated PHP-FPM pool and rewrite the FastPanel nginx stub to add `index index.php` + `fastcgi_pass`.

## Tested with

- GrapesJS 0.21.11 + grapesjs-blocks-basic 1.0.2 + grapesjs-preset-webpage 1.0.3 (CDN-pinned)
- PHP 8.1 / 8.2
- Chrome, Safari 14+, Firefox
