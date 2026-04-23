<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\I18n;

class LanguageController extends Controller
{
    public function switchLang(string $locale): void
    {
        I18n::setLocale($locale);

        // Redirect back to referrer or dashboard
        $back = $_SERVER['HTTP_REFERER'] ?? '/dashboard';
        $this->redirect($back);
    }
}
