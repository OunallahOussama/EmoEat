<?php
    $i18n = \App\Core\I18n::getInstance();
    $locale = $i18n->getLocale();
    $dir = $i18n->isRtl() ? 'rtl' : 'ltr';
    $isAdmin = \App\Core\Session::get('is_admin', false);
    $currentUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $isAdminPage = str_starts_with($currentUri, '/admin');
?>
<!DOCTYPE html>
<html lang="<?= $locale ?>" dir="<?= $dir ?>" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'EmoEat') ?> — EmoEat</title>
    <link rel="stylesheet" href="/assets/css/flyonui.css">
    <script>document.documentElement.setAttribute('data-theme', localStorage.getItem('emoeat-theme') || 'light');</script>
</head>
<body class="bg-base-200 min-h-screen flex flex-col">

<?php if (\App\Core\Session::isLoggedIn()): ?>
    <?php if ($isAdminPage): ?>
    <!-- ═══════════ ADMIN LAYOUT ═══════════ -->
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-neutral text-neutral-content flex flex-col flex-shrink-0">
            <div class="px-6 py-5 border-b border-neutral-focus">
                <a href="/admin" class="text-xl font-bold text-white flex items-center gap-2">🍽️ EmoEat <span class="badge badge-primary badge-xs">Admin</span></a>
            </div>
            <ul class="menu menu-sm flex-1 px-2 py-4">
                <li>
                    <a href="/admin" class="<?= $currentUri === '/admin' ? 'menu-active' : '' ?>">
                        <span>📊</span> <?= __('admin_nav_dashboard') ?>
                    </a>
                </li>
                <li>
                    <a href="/admin/users" class="<?= str_starts_with($currentUri, '/admin/users') ? 'menu-active' : '' ?>">
                        <span>👥</span> <?= __('admin_nav_users') ?>
                    </a>
                </li>
                <li>
                    <a href="/admin/llm" class="<?= str_starts_with($currentUri, '/admin/llm') ? 'menu-active' : '' ?>">
                        <span>🤖</span> <?= __('admin_nav_llm') ?>
                    </a>
                </li>
                <li>
                    <a href="/admin/mcp" class="<?= str_starts_with($currentUri, '/admin/mcp') ? 'menu-active' : '' ?>">
                        <span>🔧</span> <?= __('admin_nav_mcp') ?>
                    </a>
                </li>
                <li class="border-t border-neutral-focus mt-3 pt-3">
                    <a href="/dashboard" class="text-xs opacity-70">← <?= __('admin_back_app') ?></a>
                </li>
            </ul>
            <div class="px-4 py-4 border-t border-neutral-focus">
                <div class="flex items-center gap-2">
                    <div class="flex gap-1">
                        <a href="/lang/en" class="btn btn-xs <?= $locale === 'en' ? 'btn-primary' : 'btn-ghost' ?>">EN</a>
                        <a href="/lang/fr" class="btn btn-xs <?= $locale === 'fr' ? 'btn-primary' : 'btn-ghost' ?>">FR</a>
                        <a href="/lang/ar" class="btn btn-xs <?= $locale === 'ar' ? 'btn-primary' : 'btn-ghost' ?>">AR</a>
                    </div>
                    <a href="/logout" class="btn btn-xs btn-ghost btn-error ml-auto"><?= __('nav_logout') ?></a>
                </div>
            </div>
        </aside>
        <!-- Main content -->
        <div class="flex-1 flex flex-col">
            <header class="navbar bg-base-100 border-b px-6">
                <div class="navbar-start">
                    <h1 class="text-lg font-semibold"><?= htmlspecialchars($pageTitle ?? 'Admin') ?></h1>
                </div>
                <div class="navbar-end gap-2">
                    <button onclick="toggleTheme()" class="btn btn-sm btn-ghost btn-circle" title="Toggle theme">
                        <span id="admin-theme-icon">🌙</span>
                    </button>
                    <span class="text-sm text-base-content/60">⭐ <?= htmlspecialchars(\App\Core\Session::get('user_name', 'Admin')) ?></span>
                </div>
            </header>
            <main class="flex-1 p-6 overflow-auto">
                <?= $content ?>
            </main>
        </div>
    </div>
    <?php else: ?>
    <!-- ═══════════ APP LAYOUT ═══════════ -->
    <nav class="navbar bg-base-100 shadow-sm sticky top-0 z-50">
        <div class="navbar-start">
            <a href="/dashboard" class="btn btn-ghost text-xl font-bold text-primary">🍽️ EmoEat</a>
        </div>
        <div class="navbar-center hidden md:flex">
            <ul class="menu menu-horizontal menu-sm gap-1">
                <li><a href="/dashboard" class="<?= $currentUri === '/dashboard' ? 'menu-active' : '' ?>"><?= __('nav_dashboard') ?></a></li>
                <li><a href="/checkin" class="<?= $currentUri === '/checkin' ? 'menu-active' : '' ?>"><?= __('nav_checkin') ?></a></li>
                <li><a href="/recommendations" class="<?= str_starts_with($currentUri, '/recommendations') ? 'menu-active' : '' ?>"><?= __('nav_recipes') ?></a></li>
                <li><a href="/quiz" class="<?= str_starts_with($currentUri, '/quiz') ? 'menu-active' : '' ?>"><?= __('nav_quiz') ?></a></li>
                <li><a href="/badges" class="<?= $currentUri === '/badges' ? 'menu-active' : '' ?>"><?= __('nav_badges') ?></a></li>
                <li><a href="/leaderboard" class="<?= $currentUri === '/leaderboard' ? 'menu-active' : '' ?>"><?= __('nav_leaderboard') ?></a></li>
                <li><a href="/profile" class="<?= $currentUri === '/profile' ? 'menu-active' : '' ?>"><?= __('nav_profile') ?></a></li>
                <?php if ($isAdmin): ?>
                <li><a href="/admin" class="text-warning font-semibold">⚙️ Admin</a></li>
                <?php endif; ?>
            </ul>
        </div>
        <div class="navbar-end gap-2">
            <button onclick="toggleTheme()" class="btn btn-sm btn-ghost btn-circle" title="Toggle theme">
                <span id="app-theme-icon">🌙</span>
            </button>
            <div class="flex gap-1">
                <a href="/lang/en" class="btn btn-xs <?= $locale === 'en' ? 'btn-primary' : 'btn-outline btn-ghost' ?>">EN</a>
                <a href="/lang/fr" class="btn btn-xs <?= $locale === 'fr' ? 'btn-primary' : 'btn-outline btn-ghost' ?>">FR</a>
                <a href="/lang/ar" class="btn btn-xs <?= $locale === 'ar' ? 'btn-primary' : 'btn-outline btn-ghost' ?>">AR</a>
            </div>
            <span class="text-sm font-semibold text-warning">⭐ <?= htmlspecialchars(\App\Core\Session::get('user_name', 'User')) ?></span>
            <a href="/logout" class="btn btn-sm btn-outline btn-primary"><?= __('nav_logout') ?></a>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-1">
        <?= $content ?>
    </main>

    <footer class="text-center py-6 text-base-content/40 text-sm">
        <p><?= __('footer_text', date('Y')) ?></p>
    </footer>
    <?php endif; ?>

<?php else: ?>
    <!-- ═══════════ GUEST ═══════════ -->
    <div class="flex justify-end items-center gap-2 px-4 py-2 bg-base-100 border-b">
        <button onclick="toggleTheme()" class="btn btn-xs btn-ghost btn-circle" title="Toggle theme">
            <span id="guest-theme-icon">🌙</span>
        </button>
        <a href="/lang/en" class="btn btn-xs <?= $locale === 'en' ? 'btn-primary' : 'btn-outline btn-ghost' ?>">EN</a>
        <a href="/lang/fr" class="btn btn-xs <?= $locale === 'fr' ? 'btn-primary' : 'btn-outline btn-ghost' ?>">FR</a>
        <a href="/lang/ar" class="btn btn-xs <?= $locale === 'ar' ? 'btn-primary' : 'btn-outline btn-ghost' ?>">AR</a>
    </div>

    <main class="flex-1">
        <?= $content ?>
    </main>

    <footer class="text-center py-6 text-base-content/40 text-sm">
        <p><?= __('footer_text', date('Y')) ?></p>
    </footer>
<?php endif; ?>

    <script src="/assets/js/flyonui.js"></script>
    <script>
    (function() {
        var saved = localStorage.getItem('emoeat-theme') || 'light';
        document.documentElement.setAttribute('data-theme', saved);
        updateIcons(saved);
    })();
    function toggleTheme() {
        var current = document.documentElement.getAttribute('data-theme');
        var next = current === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', next);
        localStorage.setItem('emoeat-theme', next);
        updateIcons(next);
    }
    function updateIcons(theme) {
        var icon = theme === 'dark' ? '☀️' : '🌙';
        ['admin-theme-icon','app-theme-icon','guest-theme-icon'].forEach(function(id) {
            var el = document.getElementById(id);
            if (el) el.textContent = icon;
        });
    }
    </script>
    <script src="/assets/js/app.js"></script>
</body>
</html>
