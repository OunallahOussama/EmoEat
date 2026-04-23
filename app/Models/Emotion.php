<?php

namespace App\Models;

use App\Core\Model;

class Emotion extends Model
{
    protected static string $table = 'emotions';
    protected static array $fillable = ['code', 'label', 'emoji'];

    public static function allSorted(): array
    {
        return self::all('label ASC');
    }
}
