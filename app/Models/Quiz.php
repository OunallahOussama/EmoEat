<?php

namespace App\Models;

use App\Core\Model;

class Quiz extends Model
{
    protected static string $table = 'quizzes';
    protected static array $fillable = ['user_id', 'title', 'mood_context', 'llm_provider', 'questions_json'];

    public function getQuestions(): array
    {
        return json_decode($this->questions_json, true) ?: [];
    }

    public static function byUser(int $userId): array
    {
        return self::where('user_id', $userId, 'created_at DESC');
    }
}
