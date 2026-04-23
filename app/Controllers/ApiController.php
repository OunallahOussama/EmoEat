<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Emotion;
use App\Models\User;
use App\Services\QuizGeneratorService;
use App\Services\RecommendationService;
use App\Services\MCPService;

/**
 * ApiController — JSON REST endpoints
 */
class ApiController extends Controller
{
    private function requireApiAuth(): int
    {
        if (!Session::isLoggedIn()) {
            $this->json(['error' => 'Unauthorized'], 401);
            exit;
        }
        return Session::userId();
    }

    public function emotions(): void
    {
        $emotions = Emotion::allSorted();
        $this->json(array_map(fn($e) => $e->toArray(), $emotions));
    }

    public function profile(): void
    {
        $userId = $this->requireApiAuth();
        $user = User::find($userId);
        $data = $user->toArray();
        unset($data['password_hash']);
        $this->json($data);
    }

    public function generateQuiz(): void
    {
        $userId = $this->requireApiAuth();

        $input = json_decode(file_get_contents('php://input'), true) ?: [];
        $mood = $input['mood'] ?? 'general';
        $provider = $input['provider'] ?? $this->config['llm']['provider'];

        $generator = new QuizGeneratorService($this->config);
        $quizData = $generator->generate($mood, $provider);

        $this->json($quizData);
    }

    public function recommend(): void
    {
        $userId = $this->requireApiAuth();

        $input = json_decode(file_get_contents('php://input'), true) ?: [];
        $mood = $input['mood'] ?? 'happy';
        $intensity = (int)($input['intensity'] ?? 5);

        $service = new RecommendationService($this->config);
        $recs = $service->getAIRecommendations($mood, $intensity);

        $this->json($recs);
    }

    /**
     * MCP Invoke — Model Context Protocol endpoint
     * Allows binding to different LLM providers (Ollama, OpenAI)
     */
    public function mcpInvoke(): void
    {
        $userId = $this->requireApiAuth();

        $input = json_decode(file_get_contents('php://input'), true) ?: [];
        $tool = $input['tool'] ?? '';
        $params = $input['params'] ?? [];
        $provider = $input['provider'] ?? $this->config['llm']['provider'];

        $mcp = new MCPService($this->config);
        $result = $mcp->invoke($tool, $params, $provider);

        $this->json($result);
    }
}
