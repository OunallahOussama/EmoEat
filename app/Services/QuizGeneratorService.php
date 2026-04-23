<?php

namespace App\Services;

/**
 * QuizGeneratorService — Generates quizzes via LLM (Ollama / OpenAI)
 * Design Pattern: Strategy (provider selection at runtime)
 */
class QuizGeneratorService
{
    private LLMService $llm;
    private array $config;

    public function __construct(array $appConfig)
    {
        $this->config = $appConfig;
        $this->llm = new LLMService($appConfig);
    }

    /**
     * Generate a quiz about emotional eating based on mood context
     *
     * @return array{title: string, questions: array}
     */
    public function generate(string $moodContext, int $numQuestions = 5): array
    {
        $prompt = $this->buildQuizPrompt($moodContext, $numQuestions);
        $response = $this->llm->complete($prompt);

        // Try to parse JSON from response
        $data = $this->parseJsonResponse($response);

        if (isset($data['questions']) && is_array($data['questions'])) {
            return [
                'title'     => $data['title'] ?? "EmoEat Quiz — {$moodContext}",
                'questions' => $data['questions'],
            ];
        }

        // If structured JSON not returned, try to extract questions
        if (is_array($data) && !empty($data)) {
            return [
                'title'     => "EmoEat Quiz — {$moodContext}",
                'questions' => $data,
            ];
        }

        // Fallback
        return [
            'title'     => "EmoEat Quiz — {$moodContext}",
            'questions' => $this->fallbackQuestions($moodContext),
        ];
    }

    /**
     * Generate feedback for completed quiz
     */
    public function generateFeedback(array $questions, array $answers, int $score, int $maxScore): string
    {
        $pct = $maxScore > 0 ? round(($score / $maxScore) * 100) : 0;

        $prompt = "A user scored {$score}/{$maxScore} ({$pct}%) on an emotional eating quiz. 
Give brief, encouraging feedback (2-3 sentences) about their score and one actionable tip for emotional eating awareness.
Keep it positive and supportive.";

        $response = $this->llm->complete($prompt);

        if (empty(trim($response)) || str_contains($response, 'unavailable')) {
            if ($pct >= 80) {
                return "Excellent score! You have great knowledge about emotional eating. Keep applying these insights to your daily habits!";
            } elseif ($pct >= 50) {
                return "Good effort! You're on the right track. Try keeping a mood-food journal to deepen your awareness of emotional eating patterns.";
            } else {
                return "Every quiz is a learning opportunity! Focus on recognizing how your emotions influence food choices — awareness is the first step.";
            }
        }

        // LLM may return JSON-wrapped feedback — extract plain text
        $decoded = json_decode($response, true);
        if (is_array($decoded)) {
            // Try common keys the LLM might wrap feedback in
            $response = $decoded['feedback'] ?? $decoded['message'] ?? $decoded['text'] ?? $decoded['response'] ?? $decoded['content'] ?? '';
            if (empty($response)) {
                // Grab the first string value from the JSON
                foreach ($decoded as $val) {
                    if (is_string($val) && strlen($val) > 10) {
                        $response = $val;
                        break;
                    }
                }
            }
        }

        // Strip any leftover markdown code-fence wrappers
        $response = preg_replace('/^```(?:json)?\s*/i', '', trim($response));
        $response = preg_replace('/\s*```$/', '', $response);

        return trim($response);
    }

    private function buildQuizPrompt(string $moodContext, int $numQuestions): string
    {
        return "You are EmoEat, an emotional eating and nutrition expert.
The user's current mood is: \"{$moodContext}\".

Generate exactly {$numQuestions} multiple-choice questions that help determine the best food/recipe for this user RIGHT NOW.
Questions should explore:
- Current cravings (sweet, savory, spicy, light, hearty)
- Desired comfort level and texture (warm, cold, crunchy, creamy)
- Nutritional needs tied to this mood (omega-3, magnesium, tryptophan, antioxidants)
- Time/effort willingness (quick snack vs cooking)
- Dietary preferences relevant to mood improvement

Each answer option should map to a food or recipe category so we can recommend specific dishes afterwards.

Return ONLY valid JSON with this exact structure:
{
  \"title\": \"Quiz title here\",
  \"questions\": [
    {
      \"question\": \"The question text\",
      \"options\": [\"Option A\", \"Option B\", \"Option C\", \"Option D\"],
      \"correct\": \"The best option for this mood (must match one option exactly)\",
      \"explanation\": \"Brief explanation of why this choice helps with the mood\",
      \"tags\": [\"food-tag1\", \"food-tag2\"]
    }
  ]
}

The \"tags\" field should contain recipe categories the correct answer maps to (e.g. comfort, energy, calming, protein, fresh, spicy, warm, brain-food, stress-relief, balanced).
Return ONLY the JSON, no additional text or markdown.";
    }

    private function parseJsonResponse(string $response): array
    {
        // Direct parse
        $data = json_decode($response, true);
        if (is_array($data)) {
            return $data;
        }

        // Try to extract JSON from markdown code blocks
        if (preg_match('/```(?:json)?\s*(\{[\s\S]*?\})\s*```/', $response, $matches)) {
            $data = json_decode($matches[1], true);
            if (is_array($data)) {
                return $data;
            }
        }

        // Try to find JSON object in response
        if (preg_match('/\{[\s\S]*"questions"[\s\S]*\}/', $response, $matches)) {
            $data = json_decode($matches[0], true);
            if (is_array($data)) {
                return $data;
            }
        }

        return [];
    }

    private function fallbackQuestions(string $mood): array
    {
        $questions = [
            [
                'question'    => 'What is emotional eating?',
                'options'     => ['Eating in response to feelings rather than hunger', 'Eating very quickly', 'Only eating healthy food', 'Skipping meals'],
                'correct'     => 'Eating in response to feelings rather than hunger',
                'explanation' => 'Emotional eating is using food to cope with emotions rather than to satisfy physical hunger.',
            ],
            [
                'question'    => 'Which nutrient is known to help produce serotonin (the "happy chemical")?',
                'options'     => ['Tryptophan', 'Sodium', 'Cholesterol', 'Caffeine'],
                'correct'     => 'Tryptophan',
                'explanation' => 'Tryptophan is an amino acid that serves as a precursor to serotonin production in the brain.',
            ],
            [
                'question'    => 'What is a common trigger for emotional eating?',
                'options'     => ['Stress', 'Physical exercise', 'Getting enough sleep', 'Drinking water'],
                'correct'     => 'Stress',
                'explanation' => 'Stress is one of the most common triggers for emotional eating, as cortisol can increase cravings.',
            ],
            [
                'question'    => 'Which practice helps distinguish emotional hunger from physical hunger?',
                'options'     => ['Mindful eating', 'Eating faster', 'Watching TV while eating', 'Counting calories obsessively'],
                'correct'     => 'Mindful eating',
                'explanation' => 'Mindful eating involves paying full attention to the experience of eating, helping distinguish emotional from physical hunger.',
            ],
            [
                'question'    => 'Which food group is most associated with mood-boosting benefits?',
                'options'     => ['Fruits and vegetables', 'Fried foods', 'Sugary snacks', 'Processed meats'],
                'correct'     => 'Fruits and vegetables',
                'explanation' => 'Fruits and vegetables contain vitamins, minerals, and antioxidants that support brain health and mood regulation.',
            ],
        ];

        return $questions;
    }
}
