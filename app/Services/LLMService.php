<?php

namespace App\Services;

/**
 * LLMService — Adapter for Ollama (local) and OpenAI
 * Design Pattern: Strategy (provider swappable at runtime)
 */
class LLMService
{
    private array $config;

    public function __construct(array $appConfig)
    {
        $this->config = $appConfig['llm'];
    }

    /**
     * Send a prompt to the configured LLM and return the text response.
     */
    public function complete(string $prompt, ?string $provider = null): string
    {
        $provider = $provider ?? $this->config['provider'];

        return match ($provider) {
            'openai' => $this->callOpenAI($prompt),
            default  => $this->callOllama($prompt),
        };
    }

    // ---- Ollama (local TinyLlama etc.) ----

    private function callOllama(string $prompt): string
    {
        $url = rtrim($this->config['ollama_host'], '/') . '/api/generate';

        $payload = json_encode([
            'model'  => $this->config['ollama_model'],
            'prompt' => $prompt,
            'stream' => false,
        ]);

        $response = $this->httpPost($url, $payload, [
            'Content-Type: application/json',
        ]);

        if ($response === false) {
            return $this->fallbackResponse($prompt);
        }

        $data = json_decode($response, true);
        return $data['response'] ?? $this->fallbackResponse($prompt);
    }

    // ---- OpenAI GPT ----

    private function callOpenAI(string $prompt): string
    {
        $apiKey = $this->config['openai_key'];

        if (empty($apiKey)) {
            return $this->fallbackResponse($prompt);
        }

        $url = 'https://api.openai.com/v1/chat/completions';

        $payload = json_encode([
            'model'    => $this->config['openai_model'],
            'messages' => [
                ['role' => 'system', 'content' => 'You are EmoEat, an emotional eating wellness assistant. Respond with valid JSON when asked for structured data.'],
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => 0.7,
            'max_tokens'  => 1500,
        ]);

        $response = $this->httpPost($url, $payload, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
        ]);

        if ($response === false) {
            return $this->fallbackResponse($prompt);
        }

        $data = json_decode($response, true);
        return $data['choices'][0]['message']['content'] ?? $this->fallbackResponse($prompt);
    }

    // ---- HTTP helper ----

    private function httpPost(string $url, string $body, array $headers): string|false
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            return $result;
        }

        error_log("LLM API error [{$httpCode}]: {$result}");
        return false;
    }

    /**
     * Fallback when LLM is unavailable — returns a static quiz/response
     */
    private function fallbackResponse(string $prompt): string
    {
        // Return a default quiz structure if the LLM is down
        if (str_contains($prompt, 'quiz') || str_contains($prompt, 'questions')) {
            return json_encode([
                'title' => 'EmoEat Wellness Quiz',
                'questions' => [
                    [
                        'question' => 'Which food is rich in omega-3 fatty acids that can help improve mood?',
                        'options'  => ['Salmon', 'White bread', 'Candy', 'Soda'],
                        'correct'  => 'Salmon',
                        'explanation' => 'Salmon is rich in omega-3 fatty acids which are linked to reduced depression and better mood.',
                    ],
                    [
                        'question' => 'What vitamin is known as the "sunshine vitamin" and can affect mood?',
                        'options'  => ['Vitamin D', 'Vitamin C', 'Vitamin B1', 'Vitamin K'],
                        'correct'  => 'Vitamin D',
                        'explanation' => 'Vitamin D deficiency is associated with mood disorders. Sunlight exposure helps your body produce it.',
                    ],
                    [
                        'question' => 'Which of these is a healthy way to cope with stress?',
                        'options'  => ['Mindful eating', 'Binge eating', 'Skipping meals', 'Energy drinks'],
                        'correct'  => 'Mindful eating',
                        'explanation' => 'Mindful eating helps you pay attention to hunger cues and enjoy food without emotional overeating.',
                    ],
                    [
                        'question' => 'Dark chocolate can boost mood because it contains:',
                        'options'  => ['Magnesium & phenylethylamine', 'Sodium', 'Trans fats', 'Artificial sweeteners'],
                        'correct'  => 'Magnesium & phenylethylamine',
                        'explanation' => 'Dark chocolate contains magnesium and phenylethylamine, which can trigger endorphin release.',
                    ],
                    [
                        'question' => 'How does dehydration affect your mood?',
                        'options'  => ['Increases fatigue and irritability', 'Improves focus', 'No effect', 'Makes you happier'],
                        'correct'  => 'Increases fatigue and irritability',
                        'explanation' => 'Even mild dehydration can cause fatigue, anxiety, and difficulty concentrating.',
                    ],
                ],
            ]);
        }

        return json_encode(['response' => 'Service temporarily unavailable. Please try again later.']);
    }
}
