<?php $pageTitle = __('login_title'); ?>

<div class="min-h-[80vh] flex items-center justify-center">
    <div class="card card-border w-full max-w-md shadow-lg">
        <div class="card-body">
            <div class="text-center mb-4">
                <h1 class="text-3xl font-bold text-primary">🍽️ <?= __('app_name') ?></h1>
                <p class="text-base-content/60 mt-1"><?= __('app_tagline') ?></p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error alert-soft text-sm mb-4"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success alert-soft text-sm mb-4"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form method="POST" action="/login">
                <input type="hidden" name="_csrf" value="<?= \App\Core\Session::csrfToken() ?>">

                <div class="mb-4">
                    <label for="email" class="label-text font-semibold mb-1"><?= __('login_email') ?></label>
                    <input type="email" id="email" name="email" required autofocus
                           placeholder="you@example.com"
                           class="input w-full">
                </div>

                <div class="mb-6">
                    <label for="password" class="label-text font-semibold mb-1"><?= __('login_password') ?></label>
                    <input type="password" id="password" name="password" required
                           placeholder="••••••••"
                           class="input w-full">
                </div>

                <button type="submit" class="btn btn-primary btn-block"><?= __('login_submit') ?></button>
            </form>

            <div class="text-center mt-5 text-sm text-base-content/60">
                <p><?= __('login_no_account') ?> <a href="/register" class="link link-primary font-medium"><?= __('login_register_link') ?></a></p>
            </div>
        </div>
    </div>
</div>
