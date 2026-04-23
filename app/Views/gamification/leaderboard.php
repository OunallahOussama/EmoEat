<?php $pageTitle = __('leaderboard_title'); ?>

<div>
    <h1 class="text-2xl font-bold mb-6"><?= __('leaderboard_heading') ?></h1>

    <div class="card card-border bg-base-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table table-zebra w-full">
                <thead>
                    <tr>
                        <th><?= __('leaderboard_rank') ?></th>
                        <th><?= __('leaderboard_user') ?></th>
                        <th><?= __('leaderboard_level') ?></th>
                        <th><?= __('leaderboard_xp') ?></th>
                        <th><?= __('leaderboard_streak') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leaders as $i => $leader): ?>
                    <tr class="<?= (int)$leader['id'] === $userId ? 'bg-primary/10 font-semibold' : '' ?>">
                        <td>
                            <?php if ($i === 0): ?>🥇
                            <?php elseif ($i === 1): ?>🥈
                            <?php elseif ($i === 2): ?>🥉
                            <?php else: ?>#<?= $i + 1 ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= htmlspecialchars($leader['username']) ?>
                            <?php if ((int)$leader['id'] === $userId): ?>
                                <span class="badge badge-primary badge-sm ml-1"><?= __('leaderboard_you') ?></span>
                            <?php endif; ?>
                        </td>
                        <td>Level <?= (int)$leader['level'] ?></td>
                        <td><?= (int)$leader['xp'] ?> XP</td>
                        <td>🔥 <?= (int)$leader['streak_days'] ?>d</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
