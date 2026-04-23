<?php $pageTitle = __('profile_title'); ?>

<div class="max-w-xl mx-auto">
    <h1 class="text-2xl font-bold mb-6"><?= __('profile_title') ?></h1>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error alert-soft text-sm mb-4"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success alert-soft text-sm mb-4"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <div class="card card-border bg-base-100">
        <div class="card-body">
            <div class="flex items-center gap-4 mb-6">
                <?php if ($user->avatar_url): ?>
                    <img src="<?= htmlspecialchars($user->avatar_url) ?>" alt="Avatar" class="w-16 h-16 rounded-full object-cover">
                <?php else: ?>
                    <div class="w-16 h-16 bg-primary text-primary-content flex items-center justify-center rounded-full text-xl font-bold">
                        <?= strtoupper(substr($user->username, 0, 1)) ?>
                    </div>
                <?php endif; ?>
                <div>
                    <h2 class="text-lg font-bold"><?= htmlspecialchars($user->username) ?></h2>
                    <p class="text-sm text-base-content/50"><?= htmlspecialchars($user->email) ?></p>
                    <div class="flex gap-3 mt-1 text-sm text-base-content/50">
                        <span>⭐ <?= __('profile_xp', (int)$user->xp) ?></span>
                        <span>📊 <?= __('profile_level', (int)$user->level) ?></span>
                        <span>🔥 <?= __('profile_streak', (int)$user->streak_days) ?></span>
                    </div>
                </div>
            </div>

            <form method="POST" action="/profile">
                <input type="hidden" name="_csrf" value="<?= \App\Core\Session::csrfToken() ?>">

                <div class="mb-4">
                    <label for="username" class="label-text font-semibold mb-1"><?= __('profile_username') ?></label>
                    <input type="text" id="username" name="username"
                           value="<?= htmlspecialchars($user->username) ?>"
                           required minlength="3" maxlength="50"
                           class="input w-full">
                </div>

                <div class="mb-4">
                    <label for="bio" class="label-text font-semibold mb-1"><?= __('profile_bio') ?></label>
                    <textarea id="bio" name="bio" rows="3"
                              placeholder="<?= __('profile_bio_placeholder') ?>"
                              class="textarea textarea-bordered w-full"><?= htmlspecialchars($user->bio ?? '') ?></textarea>
                </div>

                <div class="mb-4">
                    <label for="avatar_url" class="label-text font-semibold mb-1"><?= __('profile_avatar') ?></label>
                    <input type="url" id="avatar_url" name="avatar_url"
                           value="<?= htmlspecialchars($user->avatar_url ?? '') ?>"
                           placeholder="https://example.com/avatar.jpg"
                           class="input w-full">
                </div>

                <div class="divider"></div>
                <h3 class="font-bold mb-4"><?= __('profile_change_pw') ?></h3>

                <div class="mb-6">
                    <label for="new_password" class="label-text font-semibold mb-1"><?= __('profile_new_pw') ?></label>
                    <input type="password" id="new_password" name="new_password"
                           placeholder="<?= __('profile_new_pw_hint') ?>" minlength="6"
                           class="input w-full">
                </div>

                <button type="submit" class="btn btn-primary"><?= __('profile_save') ?></button>
            </form>
        </div>
    </div>
</div>
