<?php $pageTitle = __('admin_users_title'); ?>

<div class="card card-border bg-base-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="table table-zebra w-full text-sm">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Level</th>
                    <th>XP</th>
                    <th>Streak</th>
                    <th>Role</th>
                    <th>Last Login</th>
                    <th>Joined</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): $isSelf = ((int)$u['id'] === \App\Core\Session::userId()); ?>
                <tr class="<?= $isSelf ? 'bg-primary/10' : '' ?>">
                    <td class="font-mono text-base-content/40"><?= (int)$u['id'] ?></td>
                    <td class="font-semibold"><?= htmlspecialchars($u['username']) ?></td>
                    <td class="text-base-content/50"><?= htmlspecialchars($u['email']) ?></td>
                    <td><?= (int)$u['level'] ?></td>
                    <td><?= (int)$u['xp'] ?></td>
                    <td><?= (int)$u['streak_days'] ?>🔥</td>
                    <td>
                        <?= $u['is_admin']
                            ? '<span class="badge badge-warning badge-sm">Admin</span>'
                            : '<span class="badge badge-soft badge-sm">User</span>' ?>
                    </td>
                    <td class="text-xs text-base-content/40"><?= htmlspecialchars($u['last_login'] ?? '—') ?></td>
                    <td class="text-xs text-base-content/40"><?= htmlspecialchars($u['created_at']) ?></td>
                    <td class="text-right space-x-1">
                        <a href="/admin/users/edit/<?= (int)$u['id'] ?>"
                           class="btn btn-xs btn-soft btn-info">
                            ✏️ <?= __('admin_edit') ?>
                        </a>
                        <?php if (!$isSelf): ?>
                        <form method="POST" action="/admin/users/delete/<?= (int)$u['id'] ?>" class="inline"
                              onsubmit="return confirm('<?= __('admin_confirm_delete') ?>')">
                            <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::csrfToken() ?>">
                            <button class="btn btn-xs btn-soft btn-error">
                                🗑 <?= __('admin_delete') ?>
                            </button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
