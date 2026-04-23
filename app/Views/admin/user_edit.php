<?php $pageTitle = __('admin_edit_user'); ?>

<div class="max-w-xl mx-auto">
    <?php if (!empty($error)): ?>
    <div class="alert alert-error alert-soft text-sm mb-4"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
    <div class="alert alert-success alert-soft text-sm mb-4"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <div class="card card-border bg-base-100">
        <div class="card-body">
            <form method="POST" action="/admin/users/edit/<?= (int)$user->id ?>">
                <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::csrfToken() ?>">

                <div class="space-y-4">
                    <div>
                        <label class="label-text font-medium mb-1">Username</label>
                        <input type="text" name="username" value="<?= htmlspecialchars($user->username) ?>"
                               class="input w-full">
                    </div>
                    <div>
                        <label class="label-text font-medium mb-1">Email</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($user->email) ?>"
                               class="input w-full">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="label-text font-medium mb-1">XP</label>
                            <input type="number" name="xp" value="<?= (int)$user->xp ?>"
                                   class="input w-full">
                        </div>
                        <div>
                            <label class="label-text font-medium mb-1">Level</label>
                            <input type="number" name="level" value="<?= (int)$user->level ?>"
                                   class="input w-full">
                        </div>
                    </div>
                    <div>
                        <label class="label-text font-medium mb-1">New Password <span class="text-base-content/40">(leave blank to keep)</span></label>
                        <input type="password" name="new_password" autocomplete="new-password"
                               class="input w-full">
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_admin" value="1" id="is_admin" <?= $user->is_admin ? 'checked' : '' ?>
                               class="checkbox checkbox-primary checkbox-sm">
                        <label for="is_admin" class="label-text font-medium"><?= __('admin_is_admin') ?></label>
                    </div>
                </div>

                <div class="flex justify-between items-center mt-6 pt-4 border-t border-base-300">
                    <a href="/admin/users" class="btn btn-sm btn-ghost">← <?= __('admin_back') ?></a>
                    <button type="submit" class="btn btn-primary">
                        <?= __('admin_save') ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
