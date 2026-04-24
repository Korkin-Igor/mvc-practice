<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= htmlspecialchars(($pageTitle ?? $appName) . ' | ' . $appName) ?></title>
    <link rel="stylesheet" href="<?= app()->route->getUrl('/public/assets/app.css') ?>">
</head>
<body class="<?= htmlspecialchars($pageClass ?? '') ?>">
<div class="page-shell">
    <header class="site-header <?= empty($navItems) ? 'site-header--minimal' : '' ?>">
        <a class="brand" href="<?= app()->route->getUrl('/') ?>">
            <span class="brand-mark" aria-hidden="true">
                <span></span>
                <span></span>
                <span></span>
                <span></span>
            </span>
            <span class="brand-name"><?= htmlspecialchars($appName) ?></span>
        </a>

        <?php if (!empty($navItems)): ?>
            <nav class="site-nav">
                <?php foreach ($navItems as $item): ?>
                    <a
                        class="site-nav__link <?= ($activeNav ?? '') === $item['id'] ? 'is-active' : '' ?>"
                        href="<?= app()->route->getUrl($item['url']) ?>"
                    >
                        <?= htmlspecialchars($item['label']) ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <div class="site-user">
                <div class="site-user__avatar"><?= mb_substr($currentUser->name ?? 'U', 0, 1, 'UTF-8') ?></div>
                <div class="site-user__meta">
                    <strong><?= htmlspecialchars($currentUser->name ?? '') ?></strong>
                    <span><?= ($currentUser && $currentUser->isLibrarian()) ? 'Библиотекарь' : 'Читатель' ?></span>
                </div>
                <a class="button button--ghost button--small" href="<?= app()->route->getUrl('/logout') ?>">Выйти</a>
            </div>
        <?php else: ?>
            <nav class="site-nav site-nav--guest">
                <a class="site-nav__link" href="<?= app()->route->getUrl('/login') ?>">Вход</a>
                <a class="button button--small" href="<?= app()->route->getUrl('/signup') ?>">Регистрация</a>
            </nav>
        <?php endif; ?>
    </header>

    <?php if (!empty($flash)): ?>
        <div class="flash flash--<?= htmlspecialchars($flash['type']) ?>">
            <?= htmlspecialchars($flash['message']) ?>
        </div>
    <?php endif; ?>

    <main class="page-content">
        <?= $content ?? '' ?>
    </main>
</div>
</body>
</html>
