<?php $pageTitle = __('admin_mcp_title'); ?>

<div class="max-w-3xl mx-auto space-y-6">

    <!-- MCP Tools List -->
    <div class="card card-border bg-base-100">
        <div class="card-body">
            <h3 class="card-title"><?= __('admin_mcp_tools') ?></h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <?php foreach ($tools as $tool): ?>
                <div class="card card-border card-sm hover:border-primary transition">
                    <div class="card-body flex-row items-start gap-3">
                        <div class="w-8 h-8 bg-primary/10 text-primary rounded-lg flex items-center justify-center text-sm font-bold">🔧</div>
                        <div class="flex-1">
                            <p class="font-semibold"><?= htmlspecialchars($tool['name']) ?></p>
                            <p class="text-xs text-base-content/50 mt-1"><?= htmlspecialchars($tool['description'] ?? '') ?></p>
                            <?php if (!empty($tool['inputSchema']['properties'])): ?>
                            <div class="mt-2 flex flex-wrap gap-1">
                                <?php foreach (array_keys($tool['inputSchema']['properties']) as $param): ?>
                                <span class="badge badge-soft badge-sm font-mono"><?= htmlspecialchars($param) ?></span>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Test MCP Tool -->
    <div class="card card-border bg-base-100">
        <div class="card-body">
            <h3 class="card-title"><?= __('admin_mcp_test') ?></h3>
            <form method="POST" action="/admin/mcp/test" class="flex flex-wrap gap-3 items-end">
                <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::csrfToken() ?>">
                <div>
                    <label class="label-text text-xs font-medium mb-1">Tool</label>
                    <select name="tool" class="select select-sm">
                        <?php foreach ($tools as $tool): ?>
                        <option value="<?= htmlspecialchars($tool['name']) ?>"><?= htmlspecialchars($tool['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="label-text text-xs font-medium mb-1">Provider</label>
                    <select name="provider" class="select select-sm">
                        <option value="openai">OpenAI</option>
                        <option value="ollama">Ollama</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-warning btn-sm">
                    🧪 <?= __('admin_test_now') ?>
                </button>
            </form>

            <?php if (!empty($testResult)):
                $tr = json_decode($testResult, true);
            ?>
            <div class="mt-4 bg-base-200 rounded-lg p-4 text-sm space-y-2">
                <div class="flex flex-wrap gap-4 text-base-content/50">
                    <span>Tool: <strong><?= htmlspecialchars($tr['tool'] ?? '') ?></strong></span>
                    <span>Provider: <strong><?= htmlspecialchars($tr['provider'] ?? '') ?></strong></span>
                    <span><?= $tr['duration_ms'] ?? '?' ?>ms</span>
                </div>
                <pre class="bg-base-100 border border-base-300 rounded p-3 overflow-x-auto max-h-80 text-xs"><?= htmlspecialchars(json_encode($tr['result'] ?? null, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
