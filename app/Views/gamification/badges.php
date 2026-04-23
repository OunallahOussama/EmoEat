<?php $pageTitle = __('badges_title'); ?>

<div>
    <h1 class="text-2xl font-bold mb-6"><?= __('badges_heading') ?></h1>

    <div class="stats stats-border mb-8">
        <div class="stat">
            <div class="stat-figure text-2xl">⭐</div>
            <div class="stat-value"><?= (int)$user->xp ?></div>
            <div class="stat-desc">XP</div>
        </div>
        <div class="stat">
            <div class="stat-figure text-2xl">📊</div>
            <div class="stat-value">Level <?= (int)$user->level ?></div>
        </div>
        <div class="stat">
            <div class="stat-figure text-2xl">🔥</div>
            <div class="stat-value"><?= (int)$user->streak_days ?>d</div>
            <div class="stat-desc">streak</div>
        </div>
    </div>

    <?php if (!empty($earned)): ?>
    <div class="mb-8">
        <h2 class="text-lg font-bold mb-4"><?= __('badges_earned', count($earned)) ?></h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4">
            <?php foreach ($earned as $badge): ?>
            <div class="card card-border bg-base-100 border-warning">
                <div class="card-body items-center text-center p-4">
                    <span class="text-3xl"><?= htmlspecialchars($badge['icon']) ?></span>
                    <p class="font-bold text-sm mt-2"><?= htmlspecialchars($badge['name']) ?></p>
                    <p class="text-xs text-base-content/50 mt-1"><?= htmlspecialchars($badge['description'] ?? '') ?></p>
                    <p class="text-xs text-base-content/40 mt-1"><?= __('badges_earned_date') ?> <?= htmlspecialchars($badge['earned_at'] ?? '') ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($locked)): ?>
    <div>
        <h2 class="text-lg font-bold mb-4"><?= __('badges_locked', count($locked)) ?></h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4">
            <?php foreach ($locked as $badge): ?>
            <div class="card card-border bg-base-100 opacity-60">
                <div class="card-body items-center text-center p-4">
                    <span class="text-3xl">🔒</span>
                    <p class="font-bold text-sm mt-2"><?= htmlspecialchars($badge['name']) ?></p>
                    <p class="text-xs text-base-content/50 mt-1"><?= htmlspecialchars($badge['description'] ?? '') ?></p>
                    <p class="text-xs text-warning font-semibold mt-1">+<?= (int)$badge['xp_reward'] ?> XP</p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
