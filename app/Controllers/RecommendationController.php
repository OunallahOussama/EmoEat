<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\EmotionLog;
use App\Models\Recommendation;

class RecommendationController extends Controller
{
    public function index(): void
    {
        $userId = $this->requireAuth();

        $lastMood = EmotionLog::lastByUser($userId);
        $recommendations = [];

        if ($lastMood) {
            $recommendations = Recommendation::byEmotionLog((int)$lastMood->id);
        }

        $this->view('recommendation.index', compact('lastMood', 'recommendations'));
    }
}
