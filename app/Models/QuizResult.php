<?php

namespace App\Models;

use App\Core\Model;

class QuizResult extends Model
{
    protected static string $table = 'quiz_results';
    protected static array $fillable = ['quiz_id', 'user_id', 'answers_json', 'score', 'max_score', 'xp_earned', 'feedback'];

    public static function byUser(int $userId): array
    {
        return self::where('user_id', $userId, 'created_at DESC');
    }

    public static function completedCount(int $userId): int
    {
        return self::count('user_id', $userId);
    }
}
