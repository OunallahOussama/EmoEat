<?php $pageTitle = __('quiz_take_title'); ?>

<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold mb-1"><?= htmlspecialchars($quiz->title) ?></h1>
    <p class="text-sm text-base-content/50 mb-6"><?= __('quiz_mood_label') ?> <?= htmlspecialchars($quiz->mood_context ?? 'general') ?></p>

    <form method="POST" action="/quiz/submit">
        <input type="hidden" name="_csrf" value="<?= \App\Core\Session::csrfToken() ?>">
        <input type="hidden" name="quiz_id" value="<?= (int)$quiz->id ?>">

        <?php foreach ($questions as $i => $q): ?>
        <div class="card card-border bg-base-100 mb-4">
            <div class="card-body p-5">
                <p class="text-xs text-base-content/40 font-semibold mb-2"><?= __('quiz_question_of', $i + 1, count($questions)) ?></p>
                <h3 class="font-bold mb-4"><?= htmlspecialchars($q['question'] ?? '') ?></h3>

                <div class="space-y-2">
                    <?php foreach (($q['options'] ?? []) as $j => $option): ?>
                    <label class="custom-option flex items-center gap-3 px-4 py-3 border-2 border-base-300 rounded-lg cursor-pointer hover:border-primary has-[:checked]:border-primary has-[:checked]:bg-primary/10 transition">
                        <input type="radio" name="answers[<?= $i ?>]"
                               value="<?= htmlspecialchars($option) ?>" required
                               class="radio radio-primary radio-sm">
                        <span class="text-sm"><?= htmlspecialchars($option) ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <button type="submit" class="btn btn-primary btn-block"><?= __('quiz_submit_answers') ?></button>
    </form>
</div>
