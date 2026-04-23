<?php

namespace App\Services;

use App\Models\User;
use App\Models\Badge;
use App\Models\UserBadge;
use App\Models\EmotionLog;
use App\Models\QuizResult;

/**
 * GamificationService — XP, levels, badges, streaks
 * Design Pattern: Observer-like (checks and awards badges after actions)
 */
class GamificationService
{
    /**
     * Check all badge criteria for a user and award any newly earned badges
     */
    public function checkBadges(int $userId): array
    {
        $user = User::find($userId);
        if (!$user) {
            return [];
        }

        $awarded = [];
        $allBadges = Badge::all();

        foreach ($allBadges as $badge) {
            $criteria = $badge->criteria ?? '';
            if (empty($criteria)) {
                continue;
            }

            if ($this->evaluateCriteria($criteria, $user, $userId)) {
                if (UserBadge::award($userId, (int)$badge->id)) {
                    $user->addXp((int)$badge->xp_reward);
                    $awarded[] = $badge->toArray();
                }
            }
        }

        return $awarded;
    }

    /**
     * Evaluate a criteria string like "quizzes_completed>=5"
     */
    private function evaluateCriteria(string $criteria, User $user, int $userId): bool
    {
        // Parse: "metric>=value" or "metric>value"
        if (!preg_match('/^(\w+)(>=|>|=)(\d+)$/', $criteria, $m)) {
            return false;
        }

        $metric = $m[1];
        $operator = $m[2];
        $threshold = (int)$m[3];

        $actual = $this->getMetricValue($metric, $user, $userId);

        return match ($operator) {
            '>=' => $actual >= $threshold,
            '>'  => $actual > $threshold,
            '='  => $actual === $threshold,
            default => false,
        };
    }

    private function getMetricValue(string $metric, User $user, int $userId): int
    {
        return match ($metric) {
            'login_count'              => 1, // If checking, they've logged in at least once
            'checkins'                 => EmotionLog::count('user_id', $userId),
            'quizzes_completed'        => QuizResult::completedCount($userId),
            'streak_days'              => (int)$user->streak_days,
            'level'                    => (int)$user->level,
            'unique_emotions'          => EmotionLog::uniqueEmotionCount($userId),
            'recommendations_followed' => 0, // Placeholder for future tracking
            default                    => 0,
        };
    }

    /**
     * Get user's gamification summary
     */
    public function getSummary(int $userId): array
    {
        $user = User::find($userId);
        $earned = Badge::earnedByUser($userId);
        $locked = Badge::notEarnedByUser($userId);

        $config = \App\Core\App::getInstance()->getConfig();
        $xpPerLevel = $config['gamification']['xp_per_level'];
        $currentLevelXp = ($user->xp ?? 0) % $xpPerLevel;

        return [
            'xp'             => (int)($user->xp ?? 0),
            'level'          => (int)($user->level ?? 1),
            'streak_days'    => (int)($user->streak_days ?? 0),
            'xp_to_next'     => $xpPerLevel - $currentLevelXp,
            'xp_progress_pct'=> round(($currentLevelXp / $xpPerLevel) * 100),
            'badges_earned'  => $earned,
            'badges_locked'  => $locked,
        ];
    }
}
