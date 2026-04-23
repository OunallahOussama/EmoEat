<?php

namespace App\Services;

/**
 * MCPService — Model Context Protocol
 * Provides a unified interface to invoke LLM tools (Ollama / OpenAI)
 * MCP Pattern: tool-based invocation with provider switching
 */
class MCPService
{
    private array $config;
    private LLMService $llm;

    /** Registered MCP tools */
    private array $tools = [
        'generate_quiz'      => 'toolGenerateQuiz',
        'recommend_food'     => 'toolRecommendFood',
        'mood_analysis'      => 'toolMoodAnalysis',
        'nutrition_advice'   => 'toolNutritionAdvice',
    ];

    public function __construct(array $appConfig)
    {
        $this->config = $appConfig;
        $this->llm = new LLMService($appConfig);
    }

    /**
     * List available MCP tools
     */
    public function listTools(): array
    {
        return [
            [
                'name'        => 'generate_quiz',
                'description' => 'Generate an emotional eating quiz based on mood context',
                'parameters'  => ['mood' => 'string', 'num_questions' => 'int'],
            ],
            [
                'name'        => 'recommend_food',
                'description' => 'Get food recommendations based on current mood and intensity',
                'parameters'  => ['mood' => 'string', 'intensity' => 'int'],
            ],
            [
                'name'        => 'mood_analysis',
                'description' => 'Analyze mood patterns and provide insights',
                'parameters'  => ['moods' => 'array'],
            ],
            [
                'name'        => 'nutrition_advice',
                'description' => 'Get nutrition advice for emotional wellbeing',
                'parameters'  => ['topic' => 'string'],
            ],
        ];
    }

    /**
     * Invoke an MCP tool
     */
    public function invoke(string $tool, array $params, string $provider = 'ollama'): array
    {
        if (!isset($this->tools[$tool])) {
            return ['error' => "Unknown tool: {$tool}", 'available_tools' => array_keys($this->tools)];
        }

        $method = $this->tools[$tool];
        return $this->$method($params, $provider);
    }

    private function toolGenerateQuiz(array $params, string $provider): array
    {
        $mood = $params['mood'] ?? 'general';
        $numQ = (int)($params['num_questions'] ?? 5);

        $generator = new QuizGeneratorService($this->config);
        return $generator->generate($mood, $provider, $numQ);
    }

    private function toolRecommendFood(array $params, string $provider): array
    {
        $mood = $params['mood'] ?? 'happy';
        $intensity = (int)($params['intensity'] ?? 5);

        $prompt = "You are an emotional eating wellness expert. A user is feeling {$mood} with intensity {$intensity}/10.
Suggest 5 food recommendations that can help with their mood. Return a JSON array with objects containing: name, description, why_it_helps.
Return ONLY the JSON array, no extra text.";

        $response = $this->llm->complete($prompt, $provider);
        $decoded = json_decode($response, true);

        return [
            'mood'            => $mood,
            'intensity'       => $intensity,
            'provider'        => $provider,
            'recommendations' => is_array($decoded) ? $decoded : [['name' => 'Healthy Snack', 'description' => $response, 'why_it_helps' => 'Balanced nutrition']],
        ];
    }

    private function toolMoodAnalysis(array $params, string $provider): array
    {
        $moods = $params['moods'] ?? [];
        $moodStr = implode(', ', $moods);

        $prompt = "Analyze this sequence of mood check-ins: {$moodStr}. 
Provide a brief JSON object with: pattern (string), insight (string), suggestion (string).
Return ONLY valid JSON.";

        $response = $this->llm->complete($prompt, $provider);
        $decoded = json_decode($response, true);

        return [
            'provider' => $provider,
            'analysis' => $decoded ?: ['pattern' => 'varied', 'insight' => $response, 'suggestion' => 'Keep tracking your moods'],
        ];
    }

    private function toolNutritionAdvice(array $params, string $provider): array
    {
        $topic = $params['topic'] ?? 'emotional eating';

        $prompt = "Give a concise JSON nutrition advice about: {$topic}. 
Return a JSON object with: title (string), advice (string), foods (array of strings), tip (string).
Return ONLY valid JSON.";

        $response = $this->llm->complete($prompt, $provider);
        $decoded = json_decode($response, true);

        return [
            'provider' => $provider,
            'advice'   => $decoded ?: ['title' => $topic, 'advice' => $response, 'foods' => [], 'tip' => 'Eat mindfully'],
        ];
    }
}
