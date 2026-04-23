<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\User;
use App\Models\EmotionLog;
use App\Models\Quiz;
use App\Models\QuizResult;
use App\Models\LlmConfig;
use App\Models\ApiLog;
use App\Services\MCPService;
use App\Services\LLMService;

class AdminController extends Controller
{
    private function requireAdmin(): int
    {
        $userId = $this->requireAuth();
        if (!Session::get('is_admin')) {
            http_response_code(403);
            echo '403 — Forbidden';
            exit;
        }
        return $userId;
    }

    // ───── Dashboard / Metrics ─────

    public function index(): void
    {
        $this->requireAdmin();

        $totalUsers = User::count();
        $totalCheckins = EmotionLog::count();
        $totalQuizzes = QuizResult::count();
        $totalApiCalls = ApiLog::totalCount();

        $recentUsers = User::query(
            "SELECT id, username, email, xp, level, is_admin, created_at FROM users ORDER BY created_at DESC LIMIT 5"
        );

        $dailyStats = ApiLog::dailyStats(7);
        $providerStats = ApiLog::providerBreakdown();
        $recentLogs = ApiLog::recentLogs(10);

        // Emotion distribution
        $emotionDist = EmotionLog::query(
            "SELECT e.label, e.emoji, COUNT(*) AS cnt
             FROM emotion_logs el JOIN emotions e ON e.id = el.emotion_id
             GROUP BY el.emotion_id ORDER BY cnt DESC LIMIT 10"
        );

        $this->view('admin.dashboard', compact(
            'totalUsers', 'totalCheckins', 'totalQuizzes', 'totalApiCalls',
            'recentUsers', 'dailyStats', 'providerStats', 'recentLogs', 'emotionDist'
        ));
    }

    // ───── User Management ─────

    public function users(): void
    {
        $this->requireAdmin();
        $users = User::query(
            "SELECT id, username, email, xp, level, streak_days, is_admin, created_at, last_login FROM users ORDER BY created_at DESC"
        );
        $this->view('admin.users', compact('users'));
    }

    public function userEdit(string $id): void
    {
        $this->requireAdmin();
        $user = User::find((int)$id);
        if (!$user) {
            $this->redirect('/admin/users');
        }
        $error = Session::getFlash('error');
        $success = Session::getFlash('success');
        $this->view('admin.user_edit', compact('user', 'error', 'success'));
    }

    public function userUpdate(string $id): void
    {
        $this->requireAdmin();
        if (!Session::verifyCsrf()) {
            Session::flash('error', 'Invalid CSRF token.');
            $this->redirect("/admin/users/edit/{$id}");
        }

        $user = User::find((int)$id);
        if (!$user) {
            $this->redirect('/admin/users');
        }

        $user->username = trim($this->post('username', $user->username));
        $user->email = trim($this->post('email', $user->email));
        $user->is_admin = $this->post('is_admin') ? 1 : 0;
        $user->xp = (int)$this->post('xp', $user->xp);
        $user->level = (int)$this->post('level', $user->level);

        $newPw = $this->post('new_password');
        if (!empty($newPw) && strlen($newPw) >= 6) {
            $user->password_hash = password_hash($newPw, PASSWORD_BCRYPT);
        }

        $user->save();
        Session::flash('success', 'User updated successfully.');
        $this->redirect("/admin/users/edit/{$id}");
    }

    public function userDelete(string $id): void
    {
        $this->requireAdmin();
        $user = User::find((int)$id);
        if ($user && (int)$user->id !== Session::userId()) {
            $user->delete();
        }
        $this->redirect('/admin/users');
    }

    // ───── LLM / OpenAI Configuration ─────

    public function llmConfig(): void
    {
        $this->requireAdmin();
        $config = LlmConfig::allAsMap();
        $error = Session::getFlash('error');
        $success = Session::getFlash('success');
        $testResult = Session::getFlash('test_result');
        $this->view('admin.llm', compact('config', 'error', 'success', 'testResult'));
    }

    public function llmConfigSave(): void
    {
        $this->requireAdmin();
        if (!Session::verifyCsrf()) {
            Session::flash('error', 'Invalid CSRF token.');
            $this->redirect('/admin/llm');
        }

        $keys = ['provider', 'openai_model', 'openai_key', 'openai_max_tokens', 'openai_temperature', 'ollama_host', 'ollama_model'];
        foreach ($keys as $key) {
            $val = $this->post($key);
            if ($val !== null) {
                LlmConfig::setValue($key, trim($val));
            }
        }

        Session::flash('success', 'LLM configuration saved.');
        $this->redirect('/admin/llm');
    }

    public function llmTest(): void
    {
        $this->requireAdmin();

        $config = LlmConfig::allAsMap();
        $appConfig = $this->config;
        $appConfig['llm']['provider'] = $config['provider'] ?? 'openai';
        $appConfig['llm']['openai_key'] = $config['openai_key'] ?? '';
        $appConfig['llm']['openai_model'] = $config['openai_model'] ?? 'gpt-3.5-turbo';
        $appConfig['llm']['ollama_host'] = $config['ollama_host'] ?? 'http://host.docker.internal:11434';
        $appConfig['llm']['ollama_model'] = $config['ollama_model'] ?? 'tinyllama';

        $llm = new LLMService($appConfig);
        $start = microtime(true);
        $response = $llm->complete('Say "Hello from EmoEat!" in one short sentence.');
        $duration = round((microtime(true) - $start) * 1000);

        Session::flash('test_result', json_encode([
            'provider' => $appConfig['llm']['provider'],
            'response' => $response,
            'duration_ms' => $duration,
        ]));
        $this->redirect('/admin/llm');
    }

    // ───── MCP Configuration ─────

    public function mcpConfig(): void
    {
        $this->requireAdmin();
        $mcp = new MCPService($this->config);
        $tools = $mcp->listTools();
        $testResult = Session::getFlash('mcp_test_result');
        $this->view('admin.mcp', compact('tools', 'testResult'));
    }

    public function mcpTest(): void
    {
        $this->requireAdmin();

        $tool = $this->post('tool', 'nutrition_advice');
        $provider = $this->post('provider', $this->config['llm']['provider']);
        $params = ['topic' => 'emotional eating basics'];

        if ($tool === 'recommend_food') {
            $params = ['mood' => 'happy', 'intensity' => 5];
        } elseif ($tool === 'mood_analysis') {
            $params = ['moods' => ['happy', 'stressed', 'calm']];
        } elseif ($tool === 'generate_quiz') {
            $params = ['mood' => 'general', 'num_questions' => 2];
        }

        $mcp = new MCPService($this->config);
        $start = microtime(true);
        $result = $mcp->invoke($tool, $params, $provider);
        $duration = round((microtime(true) - $start) * 1000);

        Session::flash('mcp_test_result', json_encode([
            'tool' => $tool,
            'provider' => $provider,
            'result' => $result,
            'duration_ms' => $duration,
        ]));
        $this->redirect('/admin/mcp');
    }
}
