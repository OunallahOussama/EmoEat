<?php $pageTitle = __('admin_dashboard_title'); ?>

<!-- Summary Cards -->
<div class="stats stats-border w-full grid grid-cols-2 lg:grid-cols-4 mb-8">
    <div class="stat">
        <div class="stat-figure text-info text-lg">👥</div>
        <div class="stat-title"><?= __('admin_total_users') ?></div>
        <div class="stat-value"><?= $totalUsers ?></div>
    </div>
    <div class="stat">
        <div class="stat-figure text-success text-lg">😊</div>
        <div class="stat-title"><?= __('admin_total_checkins') ?></div>
        <div class="stat-value"><?= $totalCheckins ?></div>
    </div>
    <div class="stat">
        <div class="stat-figure text-secondary text-lg">🧠</div>
        <div class="stat-title"><?= __('admin_total_quizzes') ?></div>
        <div class="stat-value"><?= $totalQuizzes ?></div>
    </div>
    <div class="stat">
        <div class="stat-figure text-warning text-lg">📡</div>
        <div class="stat-title"><?= __('admin_total_api') ?></div>
        <div class="stat-value"><?= $totalApiCalls ?></div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Emotion Distribution -->
    <div class="card card-border bg-base-100">
        <div class="card-body">
            <h3 class="card-title"><?= __('admin_emotion_dist') ?></h3>
            <?php if (!empty($emotionDist)):
                $maxCnt = max(array_column($emotionDist, 'cnt'));
            ?>
            <div class="space-y-2">
                <?php foreach ($emotionDist as $ed): ?>
                <div class="flex items-center gap-2 text-sm">
                    <span class="w-6"><?= htmlspecialchars($ed['emoji']) ?></span>
                    <span class="w-20 truncate"><?= htmlspecialchars($ed['label']) ?></span>
                    <progress class="progress progress-primary flex-1" value="<?= $ed['cnt'] ?>" max="<?= $maxCnt ?>"></progress>
                    <span class="text-base-content/50 w-8 text-right"><?= $ed['cnt'] ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <p class="text-sm text-base-content/40"><?= __('admin_no_data') ?></p>
            <?php endif; ?>
        </div>
    </div>

    <!-- LLM Provider Stats -->
    <div class="card card-border bg-base-100">
        <div class="card-body">
            <h3 class="card-title"><?= __('admin_provider_stats') ?></h3>
            <?php if (!empty($providerStats)): ?>
            <div class="space-y-3">
                <?php foreach ($providerStats as $ps): ?>
                <div class="flex items-center justify-between p-3 bg-base-200 rounded-lg">
                    <div>
                        <p class="font-semibold text-sm"><?= htmlspecialchars($ps['llm_provider'] ?? 'unknown') ?></p>
                        <p class="text-xs text-base-content/50"><?= $ps['cnt'] ?> <?= __('admin_requests') ?></p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-mono"><?= round($ps['avg_ms']) ?>ms avg</p>
                        <p class="text-xs text-base-content/50"><?= number_format($ps['total_tokens'] ?? 0) ?> tokens</p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <p class="text-sm text-base-content/40"><?= __('admin_no_data') ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Daily API Stats Chart -->
<?php if (!empty($dailyStats)): ?>
<div class="card card-border bg-base-100 mb-8">
    <div class="card-body">
        <h3 class="card-title"><?= __('admin_daily_api') ?></h3>
        <div class="grid grid-cols-7 gap-2">
            <?php
            $maxReq = max(array_column($dailyStats, 'requests'));
            foreach ($dailyStats as $ds):
                $pct = $maxReq > 0 ? round(($ds['requests'] / $maxReq) * 100) : 0;
            ?>
            <div class="text-center">
                <div class="h-24 flex items-end justify-center">
                    <div class="w-8 bg-primary rounded-t" style="height: <?= max($pct, 5) ?>%"></div>
                </div>
                <p class="text-xs text-base-content/50 mt-1"><?= date('D', strtotime($ds['day'])) ?></p>
                <p class="text-xs font-mono"><?= $ds['requests'] ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Recent Users -->
<div class="card card-border bg-base-100 mb-8">
    <div class="card-body">
        <div class="flex justify-between items-center mb-2">
            <h3 class="card-title"><?= __('admin_recent_users') ?></h3>
            <a href="/admin/users" class="btn btn-sm btn-ghost btn-primary"><?= __('admin_view_all') ?> →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="table table-zebra w-full text-sm">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Level</th>
                        <th>XP</th>
                        <th>Role</th>
                        <th>Joined</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentUsers as $u): ?>
                    <tr>
                        <td class="font-medium"><?= htmlspecialchars($u['username']) ?></td>
                        <td class="text-base-content/50"><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= (int)$u['level'] ?></td>
                        <td><?= (int)$u['xp'] ?></td>
                        <td><?= $u['is_admin'] ? '<span class="badge badge-warning badge-sm">Admin</span>' : '<span class="badge badge-soft badge-sm">User</span>' ?></td>
                        <td class="text-base-content/50"><?= htmlspecialchars($u['created_at']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Recent API Logs -->
<?php if (!empty($recentLogs)): ?>
<div class="card card-border bg-base-100">
    <div class="card-body">
        <h3 class="card-title"><?= __('admin_recent_api') ?></h3>
        <div class="overflow-x-auto">
            <table class="table table-zebra w-full text-sm">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>User</th>
                        <th>Endpoint</th>
                        <th>Status</th>
                        <th>Duration</th>
                        <th>Provider</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentLogs as $log): ?>
                    <tr>
                        <td class="text-base-content/50 text-xs"><?= htmlspecialchars($log['created_at']) ?></td>
                        <td><?= htmlspecialchars($log['username'] ?? '—') ?></td>
                        <td class="font-mono text-xs"><?= htmlspecialchars($log['endpoint']) ?></td>
                        <td>
                            <span class="badge badge-sm <?= $log['status_code'] < 400 ? 'badge-success' : 'badge-error' ?>"><?= $log['status_code'] ?></span>
                        </td>
                        <td class="font-mono text-xs"><?= $log['duration_ms'] ? $log['duration_ms'] . 'ms' : '—' ?></td>
                        <td class="text-xs"><?= htmlspecialchars($log['llm_provider'] ?? '—') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>
