<?php $pageTitle = __('register_title'); ?>

<div class="min-h-[80vh] flex items-center justify-center">
    <div class="card card-border w-full max-w-md shadow-lg">
        <div class="card-body">
            <div class="text-center mb-4">
                <h1 class="text-3xl font-bold text-primary">🍽️ <?= __('app_name') ?></h1>
                <p class="text-base-content/60 mt-1"><?= __('register_heading') ?></p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error alert-soft text-sm mb-4"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="/register">
                <input type="hidden" name="_csrf" value="<?= \App\Core\Session::csrfToken() ?>">

                <div class="mb-4">
                    <label for="username" class="label-text font-semibold mb-1"><?= __('register_username') ?></label>
                    <input type="text" id="username" name="username" required autofocus
                           placeholder="Your username" minlength="3" maxlength="50"
                           class="input w-full">
                </div>

                <div class="mb-4">
                    <label for="email" class="label-text font-semibold mb-1"><?= __('register_email') ?></label>
                    <input type="email" id="email" name="email" required
                           placeholder="you@example.com"
                           class="input w-full">
                </div>

                <div class="mb-4">
                    <label for="password" class="label-text font-semibold mb-1"><?= __('register_password') ?></label>
                    <input type="password" id="password" name="password" required
                           placeholder="Min. 6 characters" minlength="6"
                           class="input w-full">
                </div>

                <div class="mb-6">
                    <label for="password_confirm" class="label-text font-semibold mb-1"><?= __('register_confirm') ?></label>
                    <input type="password" id="password_confirm" name="password_confirm" required
                           placeholder="Repeat password"
                           class="input w-full">
                </div>

                <button type="submit" class="btn btn-primary btn-block"><?= __('register_submit') ?></button>
            </form>

            <div class="text-center mt-5 text-sm text-base-content/60">
                <p><?= __('register_have_account') ?> <a href="/login" class="link link-primary font-medium"><?= __('register_login_link') ?></a></p>
            </div>
        </div>
    </div>
</div>
