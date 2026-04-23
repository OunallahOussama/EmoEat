<?php $pageTitle = __('checkin_title'); ?>

<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold mb-6"><?= __('checkin_heading') ?></h1>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success alert-soft text-sm mb-4"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST" action="/checkin">
        <input type="hidden" name="_csrf" value="<?= \App\Core\Session::csrfToken() ?>">

        <div class="mb-6">
            <label class="label-text font-semibold mb-3 block"><?= __('checkin_select') ?></label>
            <div class="grid grid-cols-5 gap-3">
                <?php foreach ($emotions as $emotion): ?>
                <label class="cursor-pointer">
                    <input type="radio" name="emotion_id" value="<?= (int)$emotion->id ?>" required class="hidden peer">
                    <div class="custom-option flex flex-col items-center gap-1 p-3 border-2 border-base-300 rounded-xl text-center peer-checked:border-primary peer-checked:bg-primary/10 transition">
                        <span class="text-2xl"><?= htmlspecialchars($emotion->emoji) ?></span>
                        <span class="text-xs font-semibold"><?= htmlspecialchars($emotion->label) ?></span>
                    </div>
                </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="mb-6">
            <label for="intensity" class="label-text font-semibold mb-1 block"><?= __('checkin_intensity') ?> <span id="intensity-value" class="text-primary">5</span>/10</label>
            <input type="range" id="intensity" name="intensity" min="1" max="10" value="5"
                   oninput="document.getElementById('intensity-value').textContent=this.value"
                   class="range range-primary w-full mt-1">
            <div class="flex justify-between text-xs text-base-content/40 mt-1">
                <span><?= __('checkin_mild') ?></span>
                <span><?= __('checkin_moderate') ?></span>
                <span><?= __('checkin_intense') ?></span>
            </div>
        </div>

        <div class="mb-6">
            <label for="context" class="label-text font-semibold mb-1 block"><?= __('checkin_context') ?></label>
            <textarea id="context" name="context" rows="3"
                      placeholder="<?= __('checkin_context_hint') ?>"
                      class="textarea textarea-bordered w-full"></textarea>
        </div>

        <button type="submit" class="btn btn-primary btn-block"><?= __('checkin_submit') ?></button>
    </form>
</div>
