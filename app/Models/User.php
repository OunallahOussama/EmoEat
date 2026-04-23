<?php

namespace App\Models;

use App\Core\Model;

class User extends Model
{
    protected static string $table = 'users';
    protected static array $fillable = [
        'username', 'email', 'password_hash', 'avatar_url', 'bio',
        'xp', 'level', 'streak_days', 'last_login', 'is_admin',
    ];

    public static function findByEmail(string $email): ?self
    {
        return self::findBy('email', $email);
    }

    public static function findByUsername(string $username): ?self
    {
        return self::findBy('username', $username);
    }

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password_hash);
    }

    public function addXp(int $amount): void
    {
        $this->xp += $amount;
        $config = \App\Core\App::getInstance()->getConfig();
        $xpPerLevel = $config['gamification']['xp_per_level'];
        $this->level = (int)floor($this->xp / $xpPerLevel) + 1;
        $this->save();
    }

    public function updateStreak(): void
    {
        $lastLogin = $this->last_login ? strtotime($this->last_login) : 0;
        $today = strtotime('today');
        $yesterday = strtotime('yesterday');

        if ($lastLogin >= $yesterday && $lastLogin < $today) {
            $this->streak_days++;
        } elseif ($lastLogin < $yesterday) {
            $this->streak_days = 1;
        }

        $this->last_login = date('Y-m-d H:i:s');
        $this->save();
    }
}
