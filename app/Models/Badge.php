<?php

namespace App\Models;

use App\Core\Model;

class Badge extends Model
{
    protected static string $table = 'badges';
    protected static array $fillable = ['code', 'name', 'description', 'icon', 'xp_reward', 'criteria'];

    public static function earnedByUser(int $userId): array
    {
        return self::query(
            "SELECT b.*, ub.earned_at
             FROM badges b
             JOIN user_badges ub ON ub.badge_id = b.id
             WHERE ub.user_id = :uid
             ORDER BY ub.earned_at DESC",
            [':uid' => $userId]
        );
    }

    public static function notEarnedByUser(int $userId): array
    {
        return self::query(
            "SELECT b.*
             FROM badges b
             WHERE b.id NOT IN (SELECT badge_id FROM user_badges WHERE user_id = :uid)
             ORDER BY b.name",
            [':uid' => $userId]
        );
    }
}
