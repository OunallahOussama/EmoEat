<?php

namespace App\Models;

use App\Core\Model;

class UserBadge extends Model
{
    protected static string $table = 'user_badges';
    protected static array $fillable = ['user_id', 'badge_id'];

    public static function award(int $userId, int $badgeId): bool
    {
        // Check if already awarded
        $existing = self::query(
            "SELECT id FROM user_badges WHERE user_id = :uid AND badge_id = :bid",
            [':uid' => $userId, ':bid' => $badgeId]
        );

        if (!empty($existing)) {
            return false;
        }

        $ub = new self(['user_id' => $userId, 'badge_id' => $badgeId]);
        return $ub->save();
    }
}
