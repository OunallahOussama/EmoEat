<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Emotion;
use App\Models\EmotionLog;
use App\Models\User;
use App\Services\RecommendationService;
use App\Services\GamificationService;

class EmotionController extends Controller
{
    public function form(): void
    {
        $userId = $this->requireAuth();
        $emotions = Emotion::allSorted();
        $success = Session::getFlash('success');

        $this->view('emotion.form', compact('emotions', 'success'));
    }

    public function submit(): void
    {
        $userId = $this->requireAuth();

        if (!Session::verifyCsrf()) {
            Session::flash('error', 'Invalid form submission.');
            $this->redirect('/checkin');
        }

        $emotionId = (int)$this->post('emotion_id', 0);
        $intensity = (int)$this->post('intensity', 5);
        $context   = trim($this->post('context', ''));

        if ($emotionId <= 0 || $intensity < 1 || $intensity > 10) {
            Session::flash('error', 'Invalid input.');
            $this->redirect('/checkin');
        }

        // Create emotion log
        $log = new EmotionLog([
            'user_id'    => $userId,
            'emotion_id' => $emotionId,
            'intensity'  => $intensity,
            'context'    => $context,
        ]);
        $log->save();

        // Generate recommendations
        $recoService = new RecommendationService($this->config);
        $recoService->generate((int)$log->id, $emotionId, $intensity);

        // Gamification: XP + badge check
        $user = User::find($userId);
        $user->addXp($this->config['gamification']['xp_per_checkin']);

        $gamification = new GamificationService();
        $gamification->checkBadges($userId);

        Session::flash('success', 'Mood logged! Check your recommendations.');
        $this->redirect('/recommendations');
    }
}
