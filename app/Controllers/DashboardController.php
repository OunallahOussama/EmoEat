<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\User;
use App\Models\EmotionLog;
use App\Models\QuizResult;
use App\Models\Badge;

class DashboardController extends Controller
{
    public function index(): void
    {
        $userId = $this->requireAuth();
        $user = User::find($userId);

        $recentMoods   = EmotionLog::recentByUser($userId, 5);
        $quizCount     = QuizResult::completedCount($userId);
        $badges        = Badge::earnedByUser($userId);
        $lastMood      = EmotionLog::lastByUser($userId);

        $this->view('dashboard.index', compact(
            'user', 'recentMoods', 'quizCount', 'badges', 'lastMood'
        ));
    }
}
