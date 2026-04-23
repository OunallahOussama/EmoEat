<?php

namespace App\Models;

use App\Core\Model;

class Recommendation extends Model
{
    protected static string $table = 'recommendations';
    protected static array $fillable = ['emotion_log_id', 'recipe_id', 'score', 'justification'];

    public static function byEmotionLog(int $emotionLogId): array
    {
        return self::query(
            "SELECT rec.*, r.title, r.description, r.ingredients, r.prep_time, r.calories, r.tags
             FROM recommendations rec
             JOIN recipes r ON r.id = rec.recipe_id
             WHERE rec.emotion_log_id = :elid
             ORDER BY rec.score DESC",
            [':elid' => $emotionLogId]
        );
    }
}
