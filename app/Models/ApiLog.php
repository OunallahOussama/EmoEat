<?php

namespace App\Models;

use App\Core\Model;

class ApiLog extends Model
{
    protected static string $table = 'api_logs';
    protected static array $fillable = ['user_id', 'endpoint', 'method', 'status_code', 'duration_ms', 'llm_provider', 'tokens_used'];

    public static function recentLogs(int $limit = 50): array
    {
        return self::query(
            "SELECT al.*, u.username
             FROM api_logs al
             LEFT JOIN users u ON u.id = al.user_id
             ORDER BY al.created_at DESC LIMIT {$limit}"
        );
    }

    public static function dailyStats(int $days = 7): array
    {
        return self::query(
            "SELECT DATE(created_at) AS day, COUNT(*) AS requests, AVG(duration_ms) AS avg_ms, SUM(tokens_used) AS total_tokens
             FROM api_logs
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL {$days} DAY)
             GROUP BY DATE(created_at)
             ORDER BY day ASC"
        );
    }

    public static function providerBreakdown(): array
    {
        return self::query(
            "SELECT llm_provider, COUNT(*) AS cnt, AVG(duration_ms) AS avg_ms, SUM(tokens_used) AS total_tokens
             FROM api_logs
             WHERE llm_provider IS NOT NULL
             GROUP BY llm_provider"
        );
    }

    public static function totalCount(): int
    {
        return self::count();
    }
}
