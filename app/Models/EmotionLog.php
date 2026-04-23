<?php

namespace App\Models;

use App\Core\Model;

class EmotionLog extends Model
{
    protected static string $table = 'emotion_logs';
    protected static array $fillable = ['user_id', 'emotion_id', 'intensity', 'context'];

    public static function lastByUser(int $userId): ?self
    {
        $rows = self::query(
            "SELECT el.*, e.code AS emotion_code, e.label AS emotion_label, e.emoji
             FROM emotion_logs el
             JOIN emotions e ON e.id = el.emotion_id
             WHERE el.user_id = :uid
             ORDER BY el.created_at DESC LIMIT 1",
            [':uid' => $userId]
        );

        if (empty($rows)) {
            return null;
        }

        $m = new self($rows[0]);
        $m->exists = true;
        return $m;
    }

    public static function recentByUser(int $userId, int $limit = 10): array
    {
        return self::query(
            "SELECT el.*, e.code AS emotion_code, e.label AS emotion_label, e.emoji
             FROM emotion_logs el
             JOIN emotions e ON e.id = el.emotion_id
             WHERE el.user_id = :uid
             ORDER BY el.created_at DESC LIMIT {$limit}",
            [':uid' => $userId]
        );
    }

    public static function uniqueEmotionCount(int $userId): int
    {
        $rows = self::query(
            "SELECT COUNT(DISTINCT emotion_id) AS cnt FROM emotion_logs WHERE user_id = :uid",
            [':uid' => $userId]
        );
        return (int)($rows[0]['cnt'] ?? 0);
    }
}
