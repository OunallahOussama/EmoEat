<?php $pageTitle = __('quiz_result_title'); ?>

<div class="max-w-2xl mx-auto">
    <div class="text-center mb-8">
        <h1 class="text-2xl font-bold mb-4"><?= __('quiz_result_heading') ?></h1>
        <?php $pct = $result->max_score > 0 ? round(($result->score / $result->max_score) * 100) : 0; ?>

        <div class="inline-flex flex-col items-center justify-center w-28 h-28 rounded-full font-bold mx-auto
            <?= $pct >= 80 ? 'bg-success/20 text-success' : ($pct >= 50 ? 'bg-warning/20 text-warning' : 'bg-error/20 text-error') ?>">
            <span class="text-2xl"><?= (int)$result->score ?>/<?= (int)$result->max_score ?></span>
            <span class="text-sm"><?= $pct ?>%</span>
        </div>

        <p class="text-lg font-semibold text-warning mt-3"><?= __('quiz_xp_earned', (int)$result->xp_earned) ?></p>
    </div>

    <?php if (!empty($result->feedback)): ?>
    <div class="alert alert-success mb-6">
        <div>
            <h3 class="font-bold"><?= __('quiz_feedback') ?></h3>
            <p class="text-sm"><?= htmlspecialchars($result->feedback) ?></p>
        </div>
    </div>
    <?php endif; ?>

    <!-- Review Answers -->
    <div class="mb-8">
        <h2 class="text-lg font-bold mb-4"><?= __('quiz_review') ?></h2>
        <?php foreach ($questions as $i => $q): ?>
        <?php
            $userAnswer = $answers[$i] ?? '';
            $correct = $q['correct'] ?? '';
            $isCorrect = strtolower(trim($userAnswer)) === strtolower(trim($correct));
        ?>
        <div class="alert <?= $isCorrect ? 'alert-success' : 'alert-error' ?> alert-soft mb-3">
            <span class="text-xl"><?= $isCorrect ? '✅' : '❌' ?></span>
            <div>
                <p class="font-semibold text-sm">Q<?= $i + 1 ?>: <?= htmlspecialchars($q['question'] ?? '') ?></p>
                <p class="text-sm mt-1">
                    <strong><?= __('quiz_your_answer') ?></strong> <?= htmlspecialchars($userAnswer) ?>
                    <?php if (!$isCorrect): ?>
                        <br><strong><?= __('quiz_correct_answer') ?></strong> <?= htmlspecialchars($correct) ?>
                    <?php endif; ?>
                </p>
                <?php if (!empty($q['explanation'])): ?>
                <p class="text-sm opacity-70 mt-1 italic">💡 <?= htmlspecialchars($q['explanation']) ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="flex gap-4 justify-center">
        <a href="/quiz" class="btn btn-primary"><?= __('quiz_take_another') ?></a>
        <a href="/dashboard" class="btn btn-outline btn-primary"><?= __('quiz_back_dash') ?></a>
    </div>
</div>
