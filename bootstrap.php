<?php
/**
 * admin-kit — drop-in CMS package for brief.croscor.com auto-built PHP sites.
 * Do not edit files inside admin-kit/ per-site — customize via:
 *   - site/admin/types.php       (content types)
 *   - site/admin/bootstrap.php   (site constants)
 *   - site/admin/blocks.php      (GrapesJS blocks, optional)
 *   - site/admin/.credentials.php
 *
 * Entry: site/admin/index.php → does `require 'admin-kit/bootstrap.php'`.
 */
declare(strict_types=1);

if (!defined('ADMIN_KIT_RUNNING')) define('ADMIN_KIT_RUNNING', true);
if (!defined('ADMIN_KIT_VERSION')) define('ADMIN_KIT_VERSION', '1.0.0');
if (!defined('ADMIN_KIT_PATH'))    define('ADMIN_KIT_PATH', __DIR__);
if (!defined('SITE_ROOT'))         define('SITE_ROOT', dirname(__DIR__, 2));
if (!defined('SITE_DATA'))         define('SITE_DATA', SITE_ROOT . '/data');
if (!defined('SITE_ADMIN'))        define('SITE_ADMIN', SITE_ROOT . '/admin');
if (!defined('MEDIA_DIR'))         define('MEDIA_DIR', SITE_ROOT . '/assets/img/uploads');
if (!defined('MEDIA_URL'))         define('MEDIA_URL', '/assets/img/uploads');
if (!defined('ADMIN_KIT_URL'))     define('ADMIN_KIT_URL', '/admin/admin-kit');
if (!defined('SITE_BRAND'))        define('SITE_BRAND', 'A·CAMP');
if (!defined('SITE_LANG'))         define('SITE_LANG', 'uk');

require __DIR__ . '/core/helpers.php';
require __DIR__ . '/core/session.php';
require __DIR__ . '/core/content.php';
require __DIR__ . '/core/types.php';
require __DIR__ . '/core/media.php';

$action = (string)($_GET['action'] ?? 'dashboard');

$publicActions = ['login', 'inline-check'];
if (!in_array($action, $publicActions, true)) {
    ak_require_admin();
}

switch ($action) {
    case 'login':         require __DIR__ . '/views/login.php'; break;
    case 'logout':        ak_logout(); break;
    case 'dashboard':     require __DIR__ . '/views/dashboard.php'; break;
    case 'edit':          require __DIR__ . '/views/edit.php'; break;
    case 'html':          require __DIR__ . '/views/html-editor.php'; break;
    case 'media':         require __DIR__ . '/views/media-library.php'; break;
    case 'settings':      require __DIR__ . '/views/settings.php'; break;
    case 'save':          require __DIR__ . '/handlers/save.php'; break;
    case 'autosave':      require __DIR__ . '/handlers/autosave.php'; break;
    case 'upload':        require __DIR__ . '/handlers/upload.php'; break;
    case 'media-delete':  require __DIR__ . '/handlers/media-delete.php'; break;
    case 'media-alt':     require __DIR__ . '/handlers/media-alt.php'; break;
    case 'publish':       require __DIR__ . '/handlers/publish.php'; break;
    case 'discard':       require __DIR__ . '/handlers/discard.php'; break;
    case 'inline-check':  require __DIR__ . '/handlers/inline-check.php'; break;
    default:
        http_response_code(404);
        echo 'Unknown action: ' . htmlspecialchars($action);
        exit;
}
