<?php
declare(strict_types=1);
if (!defined('ADMIN_KIT_RUNNING')) { http_response_code(404); exit; }

ak_session_start();
$error = '';
$ret = $_GET['ret'] ?? $_POST['ret'] ?? '/admin/';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim((string)($_POST['user'] ?? ''));
    $pass = (string)($_POST['pass'] ?? '');
    if (ak_verify_login($user, $pass)) {
        session_regenerate_id(true);
        $_SESSION['admin'] = true;
        $_SESSION['user'] = $user;
        ak_log_activity('login');
        ak_redirect($ret !== '' ? $ret : '/admin/');
    }
    $error = 'Невірний логін або пароль.';
    usleep(500000);
}
?><!doctype html>
<html lang="<?= ak_e(SITE_LANG) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Вхід · <?= ak_e(SITE_BRAND) ?></title>
<meta name="robots" content="noindex,nofollow">
<link rel="icon" type="image/svg+xml" href="/assets/img/icons/favicon.svg">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&family=Unbounded:wght@800;900&display=swap">
<link rel="stylesheet" href="<?= ADMIN_KIT_URL ?>/assets/admin.css?v=<?= ADMIN_KIT_VERSION ?>">
</head>
<body class="ak-body ak-body--login">
<main class="ak-login-card">
  <svg class="mark" viewBox="0 0 64 64" aria-hidden="true"><use href="/assets/img/icons/sprite.svg#ic-logo-mark"/></svg>
  <h1><?= ak_e(SITE_BRAND) ?></h1>
  <p class="muted">Увійди, щоб керувати сайтом.</p>
  <?php if ($error): ?><div class="ak-flash ak-flash--error" style="margin:0 0 16px"><?= ak_e($error) ?></div><?php endif; ?>
  <form method="post" autocomplete="off">
    <input type="hidden" name="ret" value="<?= ak_e($ret) ?>">
    <label>Логін <input type="text" name="user" required autofocus></label>
    <label>Пароль <input type="password" name="pass" required></label>
    <button class="ak-btn ak-btn--primary" type="submit">Увійти</button>
  </form>
</main>
</body>
</html>
