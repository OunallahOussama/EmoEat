<?php

namespace App\Models;

use App\Core\Model;

class LlmConfig extends Model
{
    protected static string $table = 'llm_config';
    protected static array $fillable = ['config_key', 'config_value'];

    public static function getValue(string $key, string $default = ''): string
    {
        $row = self::findBy('config_key', $key);
        return $row ? ($row->config_value ?? $default) : $default;
    }

    public static function setValue(string $key, string $value): void
    {
        $row = self::findBy('config_key', $key);
        if ($row) {
            $row->config_value = $value;
            $row->save();
        } else {
            $m = new self(['config_key' => $key, 'config_value' => $value]);
            $m->save();
        }
    }

    public static function allAsMap(): array
    {
        $rows = self::all();
        $map = [];
        foreach ($rows as $row) {
            $map[$row->config_key] = $row->config_value;
        }
        return $map;
    }
}
