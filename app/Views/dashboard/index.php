<?php $pageTitle = __('dash_title'); ?>

<div>
    <!-- Header & Stats -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold mb-4"><?= __('dash_welcome', htmlspecialchars($user->username)) ?></h1>
        <div class="stats stats-border w-full grid grid-cols-2 sm:grid-cols-4">
            <div class="stat text-center">
                <div class="stat-figure text-2xl">⭐</div>
                <div class="stat-value text-xl"><?= (int)$user->xp ?></div>
                <div class="stat-desc"><?= __('dash_xp') ?></div>
            </div>
            <div class="stat text-center">
                <div class="stat-figure text-2xl">📊</div>
                <div class="stat-value text-xl"><?= __('dash_level_progress', (int)$user->level, '') ?></div>
                <div class="stat-desc"><?= __('dash_level') ?></div>
            </div>
            <div class="stat text-center">
                <div class="stat-figure text-2xl">🔥</div>
                <div class="stat-value text-xl"><?= (int)$user->streak_days ?></div>
                <div class="stat-desc"><?= __('dash_streak') ?></div>
            </div>
            <div class="stat text-center">
                <div class="stat-figure text-2xl">📝</div>
                <div class="stat-value text-xl"><?= $quizCount ?></div>
                <div class="stat-desc"><?= __('dash_quizzes') ?></div>
            </div>
        </div>
    </div>

    <!-- XP Progress Bar -->
    <?php
        $xpPerLevel = 100;
        $currentLevelXp = ($user->xp ?? 0) % $xpPerLevel;
        $progressPct = round(($currentLevelXp / $xpPerLevel) * 100);
    ?>
    <div class="mb-8">
        <p class="text-sm font-semibold mb-1"><?= __('dash_level_progress', (int)$user->level, (int)$user->level + 1) ?></p>
        <progress class="progress progress-primary w-full" value="<?= $progressPct ?>" max="100"></progress>
        <p class="text-xs text-base-content/50 mt-1"><?= $currentLevelXp ?> / <?= $xpPerLevel ?> XP</p>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <a href="/checkin" class="card card-border bg-base-100 hover:shadow-md hover:-translate-y-0.5 transition">
            <div class="card-body items-center text-center p-5">
                <span class="text-3xl mb-2">😊</span>
                <span class="font-bold text-sm"><?= __('dash_checkin') ?></span>
                <span class="text-xs text-base-content/50"><?= __('dash_checkin_desc') ?></span>
            </div>
        </a>
        <a href="/quiz" class="card card-border bg-base-100 hover:shadow-md hover:-translate-y-0.5 transition">
            <div class="card-body items-center text-center p-5">
                <span class="text-3xl mb-2">🧠</span>
                <span class="font-bold text-sm"><?= __('dash_quiz') ?></span>
                <span class="text-xs text-base-content/50"><?= __('dash_quiz_desc') ?></span>
            </div>
        </a>
        <a href="/recommendations" class="card card-border bg-base-100 hover:shadow-md hover:-translate-y-0.5 transition">
            <div class="card-body items-center text-center p-5">
                <span class="text-3xl mb-2">🍽️</span>
                <span class="font-bold text-sm"><?= __('dash_reco') ?></span>
                <span class="text-xs text-base-content/50"><?= __('dash_reco_desc') ?></span>
            </div>
        </a>
        <a href="/badges" class="card card-border bg-base-100 hover:shadow-md hover:-translate-y-0.5 transition">
            <div class="card-body items-center text-center p-5">
                <span class="text-3xl mb-2">🏅</span>
                <span class="font-bold text-sm"><?= __('dash_badges') ?></span>
                <span class="text-xs text-base-content/50"><?= __('dash_badges_desc', count($badges)) ?></span>
            </div>
        </a>
    </div>

    <!-- Last Mood -->
    <?php if ($lastMood): ?>
    <div class="mb-8">
        <h2 class="text-lg font-bold mb-3"><?= __('dash_latest_mood') ?></h2>
        <div class="card card-border bg-base-100">
            <div class="card-body flex-row items-center gap-4 p-4">
                <span class="text-4xl"><?= htmlspecialchars($lastMood->emoji ?? '😐') ?></span>
                <div>
                    <p class="font-bold"><?= htmlspecialchars($lastMood->emotion_label ?? '') ?></p>
                    <p class="text-sm text-base-content/60"><?= __('dash_intensity', (int)$lastMood->intensity) ?></p>
                    <p class="text-xs text-base-content/40"><?= htmlspecialchars($lastMood->created_at ?? '') ?></p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Recent Moods -->
    <?php if (!empty($recentMoods)): ?>
    <div class="mb-8">
        <h2 class="text-lg font-bold mb-3"><?= __('dash_recent_moods') ?></h2>
        <div class="flex gap-2 flex-wrap">
            <?php foreach ($recentMoods as $mood): ?>
            <span class="badge badge-soft badge-lg gap-2">
                <?= htmlspecialchars($mood['emoji'] ?? '😐') ?>
                <?= htmlspecialchars($mood['emotion_label']) ?>
            </span>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Badges Preview -->
    <?php if (!empty($badges)): ?>
    <div>
        <h2 class="text-lg font-bold mb-3"><?= __('dash_recent_badges') ?></h2>
        <div class="grid grid-cols-3 sm:grid-cols-6 gap-3">
            <?php foreach (array_slice($badges, 0, 6) as $badge): ?>
            <div class="card card-border bg-base-100 border-warning">
                <div class="card-body items-center text-center p-3">
                    <span class="text-2xl"><?= htmlspecialchars($badge['icon']) ?></span>
                    <p class="text-xs font-bold mt-1"><?= htmlspecialchars($badge['name']) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
