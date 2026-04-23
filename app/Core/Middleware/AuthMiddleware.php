<?php

namespace App\Core\Middleware;

use App\Core\Session;

/**
 * AuthMiddleware — ensures user is authenticated
 */
class AuthMiddleware
{
    public static function check(): bool
    {
        return Session::isLoggedIn();
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            header('Location: /login');
            exit;
        }
    }
}
