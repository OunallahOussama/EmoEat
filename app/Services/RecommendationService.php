<?php

namespace App\Services;

use App\Models\Emotion;
use App\Models\Recipe;
use App\Models\Recommendation;

/**
 * RecommendationService — generates food recommendations via cosine similarity
 * Builds tag-frequency vectors for the user mood/context and each recipe,
 * then ranks by cosine similarity, keeping only the top matches.
 */
class RecommendationService
{
    private array $config;

    /** Mood → weighted tag mapping (tag => weight) */
    private const MOOD_TAG_WEIGHTS = [
        'happy'    => ['energy' => 1.0, 'fresh' => 0.8, 'celebration' => 0.6, 'fun' => 0.5],
        'sad'      => ['comfort' => 1.0, 'warm' => 0.9, 'soothing' => 0.7, 'healthy' => 0.3],
        'angry'    => ['calming' => 1.0, 'fresh' => 0.7, 'light' => 0.6, 'balanced' => 0.4],
        'anxious'  => ['calming' => 1.0, 'warm' => 0.8, 'comfort' => 0.7, 'stress-relief' => 0.6],
        'tired'    => ['energy' => 1.0, 'protein' => 0.9, 'brain-food' => 0.8, 'balanced' => 0.4],
        'stressed' => ['stress-relief' => 1.0, 'calming' => 0.9, 'comfort' => 0.7, 'warm' => 0.5],
        'excited'  => ['energy' => 1.0, 'spicy' => 0.8, 'fun' => 0.7, 'fresh' => 0.4],
        'calm'     => ['balanced' => 1.0, 'fresh' => 0.8, 'healthy' => 0.7, 'light' => 0.5],
        'lonely'   => ['comfort' => 1.0, 'warm' => 0.9, 'sharing' => 0.7, 'soothing' => 0.5],
        'bored'    => ['spicy' => 1.0, 'fun' => 0.9, 'creative' => 0.7, 'energy' => 0.4],
    ];

    /** Every known tag in the system (the vocabulary for our vectors) */
    private array $vocabulary = [];

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    // ------------------------------------------------------------------
    //  Public entry point — called after a mood check-in
    // ------------------------------------------------------------------

    public function generate(int $emotionLogId, int $emotionId, int $intensity): void
    {
        $emotion  = Emotion::find($emotionId);
        $moodCode = $emotion ? $emotion->code : 'happy';

        $recipes = Recipe::all('title ASC');

        // 1. Build a single vocabulary from every tag that appears
        $this->buildVocabulary($recipes, $moodCode);

        // 2. Build the user's "query" vector from mood + intensity
        $queryVec = $this->buildMoodVector($moodCode, $intensity);

        // 3. Score each recipe by cosine similarity
        $scored = [];
        foreach ($recipes as $recipe) {
            $recipeVec  = $this->buildRecipeVector($recipe);
            $similarity = $this->cosineSimilarity($queryVec, $recipeVec);

            $scored[] = [
                'recipe_id' => (int)$recipe->id,
                'score'     => round($similarity * 100, 2),   // 0-100
                'title'     => $recipe->title,
            ];
        }

        // 4. Sort descending, keep only recipes with score > 0
        usort($scored, fn($a, $b) => $b['score'] <=> $a['score']);
        $scored = array_filter($scored, fn($s) => $s['score'] > 0);

        // 5. Take top 3
        $top = array_slice($scored, 0, 3);

        if (empty($top)) {
            // No matches — pick top 3 by random
            shuffle($recipes);
            foreach (array_slice($recipes, 0, 3) as $r) {
                $top[] = ['recipe_id' => (int)$r->id, 'score' => 10, 'title' => $r->title];
            }
        }

        // 6. Ask GPT for personalised justifications
        $justifications = $this->generateAIJustifications($moodCode, $intensity, $top);

        foreach ($top as $i => $item) {
            $justification = $justifications[$i]
                ?? "Recommended for {$moodCode} mood (intensity {$intensity}/10). Similarity: {$item['score']}%";

            $rec = new Recommendation([
                'emotion_log_id' => $emotionLogId,
                'recipe_id'      => $item['recipe_id'],
                'score'          => $item['score'],
                'justification'  => $justification,
            ]);
            $rec->save();
        }
    }

    // ------------------------------------------------------------------
    //  Cosine similarity engine
    // ------------------------------------------------------------------

    /**
     * Collect every unique tag that appears in recipes + mood mappings.
     */
    private function buildVocabulary(array $recipes, string $moodCode): void
    {
        $tags = [];
        foreach ($recipes as $r) {
            foreach (array_map('trim', explode(',', $r->tags ?? '')) as $t) {
                if ($t !== '') $tags[$t] = true;
            }
        }
        foreach (self::MOOD_TAG_WEIGHTS as $weights) {
            foreach (array_keys($weights) as $t) {
                $tags[$t] = true;
            }
        }
        $this->vocabulary = array_keys($tags);
    }

    /**
     * Build a weighted vector for the user's mood.
     * Intensity scales the weights (higher intensity = stronger signal).
     */
    private function buildMoodVector(string $moodCode, int $intensity): array
    {
        $weights = self::MOOD_TAG_WEIGHTS[$moodCode] ?? ['balanced' => 1.0, 'healthy' => 0.8];
        $scale   = $intensity / 10;          // 0.1 … 1.0

        $vec = [];
        foreach ($this->vocabulary as $tag) {
            $vec[] = isset($weights[$tag]) ? $weights[$tag] * $scale : 0.0;
        }
        return $vec;
    }

    /**
     * Build a binary (0/1) vector for a recipe based on its tags.
     */
    private function buildRecipeVector(object $recipe): array
    {
        $recipeTags = array_map('trim', explode(',', $recipe->tags ?? ''));
        $tagSet = array_flip($recipeTags);

        $vec = [];
        foreach ($this->vocabulary as $tag) {
            $vec[] = isset($tagSet[$tag]) ? 1.0 : 0.0;
        }
        return $vec;
    }

    /**
     * Cosine similarity between two vectors.
     * Returns 0.0 – 1.0.
     */
    private function cosineSimilarity(array $a, array $b): float
    {
        $dot = 0.0;
        $magA = 0.0;
        $magB = 0.0;

        for ($i = 0, $n = count($a); $i < $n; $i++) {
            $dot  += $a[$i] * $b[$i];
            $magA += $a[$i] * $a[$i];
            $magB += $b[$i] * $b[$i];
        }

        $denom = sqrt($magA) * sqrt($magB);
        return $denom > 0 ? $dot / $denom : 0.0;
    }

    // ------------------------------------------------------------------
    //  LLM helpers
    // ------------------------------------------------------------------

    /**
     * Ask GPT for personalised justifications for the top picks.
     */
    private function generateAIJustifications(string $mood, int $intensity, array $top): array
    {
        $llm = new LLMService($this->config);

        $foodList = implode(', ', array_map(fn($t) => $t['title'], $top));

        $prompt = "A user feels {$mood} (intensity {$intensity}/10). "
            . "We recommend these foods: {$foodList}. "
            . "For EACH food give a 1-sentence personalised reason explaining why it helps this mood. "
            . "Return ONLY a JSON array of strings, one per food, in the same order. Example: [\"reason1\",\"reason2\"]";

        $response = $llm->complete($prompt);

        // Strip code fences the LLM may wrap around the JSON
        $response = preg_replace('/^```(?:json)?\s*/i', '', trim($response));
        $response = preg_replace('/\s*```$/', '', $response);

        $decoded = json_decode($response, true);

        if (is_array($decoded) && count($decoded) === count($top)) {
            return array_map(fn($v) => is_string($v) ? $v : '', $decoded);
        }

        return [];
    }

    /**
     * Get AI-enhanced recommendations (for API use)
     */
    public function getAIRecommendations(string $mood, int $intensity): array
    {
        $llm = new LLMService($this->config);

        $prompt = "You are a nutrition expert specializing in emotional eating. 
A user is feeling {$mood} with intensity {$intensity}/10.
Suggest 5 foods/recipes with reasons. Return JSON array: [{\"name\": \"...\", \"reason\": \"...\", \"benefit\": \"...\"}]
Return ONLY the JSON array.";

        $response = $llm->complete($prompt);
        $data = json_decode($response, true);

        return [
            'mood'       => $mood,
            'intensity'  => $intensity,
            'suggestions' => is_array($data) ? $data : [],
        ];
    }
}
