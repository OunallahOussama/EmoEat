<?php $pageTitle = __('reco_title'); ?>

<div>
    <h1 class="text-2xl font-bold mb-6"><?= __('reco_heading') ?></h1>

    <?php if ($lastMood): ?>
    <div class="card card-border bg-base-100 mb-6">
        <div class="card-body flex-row items-center gap-4 p-4">
            <span class="text-3xl"><?= htmlspecialchars($lastMood->emoji ?? '😐') ?></span>
            <div>
                <p class="font-semibold"><?= __('reco_current_mood') ?> <?= htmlspecialchars($lastMood->emotion_label ?? 'Unknown') ?></p>
                <p class="text-sm text-base-content/60"><?= __('reco_intensity', (int)$lastMood->intensity) ?> — <?= htmlspecialchars($lastMood->created_at ?? '') ?></p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($recommendations)): ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        <?php foreach ($recommendations as $rec): ?>
        <div class="card card-border bg-base-100">
            <div class="card-body p-5">
                <div class="flex justify-between items-start mb-3">
                    <h3 class="card-title text-base"><?= htmlspecialchars($rec['title']) ?></h3>
                    <span class="badge badge-primary"><?= (int)$rec['score'] ?>%</span>
                </div>
                <p class="text-sm text-base-content/60 mb-3"><?= htmlspecialchars($rec['description'] ?? '') ?></p>
                <div class="flex gap-3 text-sm text-base-content/50 mb-2">
                    <span>⏱️ <?= (int)$rec['prep_time'] ?> min</span>
                    <span>🔥 <?= (int)$rec['calories'] ?> cal</span>
                </div>
                <?php if (!empty($rec['ingredients'])): ?>
                <p class="text-sm mb-2"><span class="font-semibold"><?= __('reco_ingredients') ?></span> <?= htmlspecialchars($rec['ingredients']) ?></p>
                <?php endif; ?>
                <?php if (!empty($rec['tags'])): ?>
                <div class="flex gap-1 flex-wrap mb-2">
                    <?php foreach (explode(',', $rec['tags']) as $tag): ?>
                        <span class="badge badge-soft badge-primary badge-sm"><?= htmlspecialchars(trim($tag)) ?></span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <p class="text-sm text-base-content/40 italic"><?= htmlspecialchars($rec['justification'] ?? '') ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="text-center py-12 text-base-content/40">
        <p><?= __('reco_empty') ?> <a href="/checkin" class="link link-primary"><?= __('reco_empty_cta') ?></a> <?= __('reco_empty_text') ?></p>
    </div>
    <?php endif; ?>
</div>
