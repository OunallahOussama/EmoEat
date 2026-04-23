<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Models\Badge;
use App\Models\UserBadge;

class GamificationController extends Controller
{
    public function badges(): void
    {
        $userId = $this->requireAuth();
        $user = User::find($userId);

        $earned = Badge::earnedByUser($userId);
        $locked = Badge::notEarnedByUser($userId);

        $this->view('gamification.badges', compact('user', 'earned', 'locked'));
    }

    public function leaderboard(): void
    {
        $userId = $this->requireAuth();

        $leaders = User::query(
            "SELECT id, username, xp, level, streak_days
             FROM users
             ORDER BY xp DESC, level DESC
             LIMIT 20"
        );

        $this->view('gamification.leaderboard', compact('leaders', 'userId'));
    }
}
