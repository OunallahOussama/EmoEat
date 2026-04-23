<?php $pageTitle = __('admin_llm_title'); ?>

<div class="max-w-2xl mx-auto space-y-6">

    <?php if (!empty($error)): ?>
    <div class="alert alert-error alert-soft text-sm"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
    <div class="alert alert-success alert-soft text-sm"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <!-- Config Form -->
    <div class="card card-border bg-base-100">
        <div class="card-body">
            <h3 class="card-title"><?= __('admin_llm_config') ?></h3>
            <form method="POST" action="/admin/llm">
                <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::csrfToken() ?>">
                <div class="space-y-4">
                    <!-- Provider -->
                    <div>
                        <label class="label-text font-medium mb-1"><?= __('admin_llm_provider') ?></label>
                        <select name="provider" class="select w-full">
                            <option value="openai" <?= ($config['provider'] ?? '') === 'openai' ? 'selected' : '' ?>>OpenAI</option>
                            <option value="ollama" <?= ($config['provider'] ?? '') === 'ollama' ? 'selected' : '' ?>>Ollama (local)</option>
                        </select>
                    </div>

                    <!-- OpenAI section -->
                    <div class="border-l-4 border-info pl-4 space-y-3">
                        <p class="text-xs font-semibold text-info uppercase">OpenAI</p>
                        <div>
                            <label class="label-text font-medium mb-1">API Key</label>
                            <input type="password" name="openai_key" value="<?= htmlspecialchars($config['openai_key'] ?? '') ?>"
                                   class="input w-full font-mono text-sm">
                        </div>
                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="label-text font-medium mb-1">Model</label>
                                <input type="text" name="openai_model" value="<?= htmlspecialchars($config['openai_model'] ?? 'gpt-3.5-turbo') ?>"
                                       class="input w-full text-sm">
                            </div>
                            <div>
                                <label class="label-text font-medium mb-1">Max Tokens</label>
                                <input type="number" name="openai_max_tokens" value="<?= htmlspecialchars($config['openai_max_tokens'] ?? '1500') ?>"
                                       class="input w-full text-sm">
                            </div>
                            <div>
                                <label class="label-text font-medium mb-1">Temperature</label>
                                <input type="number" step="0.1" min="0" max="2" name="openai_temperature" value="<?= htmlspecialchars($config['openai_temperature'] ?? '0.7') ?>"
                                       class="input w-full text-sm">
                            </div>
                        </div>
                    </div>

                    <!-- Ollama section -->
                    <div class="border-l-4 border-success pl-4 space-y-3">
                        <p class="text-xs font-semibold text-success uppercase">Ollama</p>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="label-text font-medium mb-1">Host</label>
                                <input type="text" name="ollama_host" value="<?= htmlspecialchars($config['ollama_host'] ?? '') ?>"
                                       class="input w-full text-sm">
                            </div>
                            <div>
                                <label class="label-text font-medium mb-1">Model</label>
                                <input type="text" name="ollama_model" value="<?= htmlspecialchars($config['ollama_model'] ?? 'tinyllama') ?>"
                                       class="input w-full text-sm">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-base-300 flex justify-end">
                    <button type="submit" class="btn btn-primary">
                        <?= __('admin_save') ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Test LLM -->
    <div class="card card-border bg-base-100">
        <div class="card-body">
            <h3 class="card-title"><?= __('admin_llm_test') ?></h3>
            <form method="POST" action="/admin/llm/test" class="flex gap-3">
                <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::csrfToken() ?>">
                <button type="submit" class="btn btn-warning">
                    🔬 <?= __('admin_test_now') ?>
                </button>
            </form>
            <?php if (!empty($testResult)):
                $tr = json_decode($testResult, true);
            ?>
            <div class="mt-4 bg-base-200 rounded-lg p-4 text-sm">
                <div class="flex justify-between mb-2">
                    <span class="text-base-content/50">Provider: <strong><?= htmlspecialchars($tr['provider'] ?? '') ?></strong></span>
                    <span class="text-base-content/50"><?= $tr['duration_ms'] ?? '?' ?>ms</span>
                </div>
                <div class="bg-base-100 border border-base-300 rounded p-3 whitespace-pre-wrap"><?= htmlspecialchars($tr['response'] ?? 'No response') ?></div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
