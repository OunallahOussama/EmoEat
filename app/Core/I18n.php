<?php

namespace App\Core {

/**
 * I18n — lightweight internationalisation helper.
 * Loads flat key→value PHP arrays from app/Lang/{locale}.php.
 * Language is stored in the session and defaults to 'en'.
 */
class I18n
{
    private static ?self $instance = null;
    private string $locale;
    private array  $messages = [];

    public const SUPPORTED = ['en', 'fr', 'ar'];
    public const RTL       = ['ar'];

    private function __construct(string $locale)
    {
        $this->locale = in_array($locale, self::SUPPORTED, true) ? $locale : 'en';
        $this->load();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            $locale = Session::get('locale', 'en');
            self::$instance = new self($locale);
        }
        return self::$instance;
    }

    public static function setLocale(string $locale): void
    {
        if (in_array($locale, self::SUPPORTED, true)) {
            Session::set('locale', $locale);
            self::$instance = null; // force reload on next access
        }
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function isRtl(): bool
    {
        return in_array($this->locale, self::RTL, true);
    }

    /**
     * Translate a key. Accepts optional sprintf placeholders.
     */
    public function t(string $key, mixed ...$args): string
    {
        $text = $this->messages[$key] ?? $key;
        if (!empty($args)) {
            $text = sprintf($text, ...$args);
        }
        return $text;
    }

    private function load(): void
    {
        $file = BASE_PATH . '/app/Lang/' . $this->locale . '.php';
        if (file_exists($file)) {
            $this->messages = require $file;
        }
    }
}

} // end namespace App\Core

namespace {
    /**
     * Global shortcut for translations.
     */
    function __(string $key, mixed ...$args): string
    {
        return \App\Core\I18n::getInstance()->t($key, ...$args);
    }
}
