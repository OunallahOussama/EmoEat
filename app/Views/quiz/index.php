<?php $pageTitle = __('quiz_title'); ?>

<div class="max-w-3xl mx-auto">
    <h1 class="text-2xl font-bold mb-2"><?= __('quiz_heading') ?></h1>
    <p class="text-base-content/60 mb-6"><?= __('quiz_intro') ?></p>

    <!-- Generate Quiz Form -->
    <div class="card card-border bg-base-100 mb-8">
        <div class="card-body">
            <h2 class="card-title"><?= __('quiz_generate') ?></h2>
            <form method="POST" action="/quiz/generate" class="flex flex-col sm:flex-row gap-4 items-end">
                <input type="hidden" name="_csrf" value="<?= \App\Core\Session::csrfToken() ?>">

                <div class="flex-1">
                    <label for="mood_context" class="label-text font-semibold mb-1"><?= __('quiz_mood_context') ?></label>
                    <select id="mood_context" name="mood_context" class="select w-full">
                        <option value="general"><?= __('quiz_mood_general') ?></option>
                        <option value="happy" <?= ($lastMood && $lastMood->emotion_code === 'happy') ? 'selected' : '' ?>>Happy 😊</option>
                        <option value="sad" <?= ($lastMood && $lastMood->emotion_code === 'sad') ? 'selected' : '' ?>>Sad 😢</option>
                        <option value="stressed" <?= ($lastMood && $lastMood->emotion_code === 'stressed') ? 'selected' : '' ?>>Stressed 😫</option>
                        <option value="anxious" <?= ($lastMood && $lastMood->emotion_code === 'anxious') ? 'selected' : '' ?>>Anxious 😰</option>
                        <option value="tired" <?= ($lastMood && $lastMood->emotion_code === 'tired') ? 'selected' : '' ?>>Tired 😴</option>
                        <option value="angry" <?= ($lastMood && $lastMood->emotion_code === 'angry') ? 'selected' : '' ?>>Angry 😠</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary whitespace-nowrap"><?= __('quiz_submit_generate') ?></button>
            </form>
        </div>
    </div>

    <!-- Previous Quizzes -->
    <?php if (!empty($quizzes)): ?>
    <div>
        <h2 class="text-lg font-bold mb-4"><?= __('quiz_previous') ?></h2>
        <div class="space-y-3">
            <?php foreach ($quizzes as $quiz): ?>
            <div class="card card-border bg-base-100">
                <div class="card-body flex-row items-center justify-between p-4">
                    <div>
                        <p class="font-semibold text-sm"><?= htmlspecialchars($quiz->title) ?></p>
                        <p class="text-xs text-base-content/50"><?= htmlspecialchars($quiz->mood_context ?? 'general') ?> • <?= htmlspecialchars($quiz->created_at ?? '') ?></p>
                    </div>
                    <a href="/quiz/take/<?= (int)$quiz->id ?>" class="btn btn-sm btn-outline btn-primary"><?= __('quiz_retake') ?></a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
