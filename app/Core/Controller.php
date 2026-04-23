<?php

namespace App\Core;

/**
 * Base Controller — shared helpers for all controllers
 * Design Pattern: Template Method
 */
abstract class Controller
{
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    protected function view(string $viewName, array $data = []): void
    {
        // Make data available to view
        extract($data);

        $viewFile = BASE_PATH . '/app/Views/' . str_replace('.', '/', $viewName) . '.php';

        if (!file_exists($viewFile)) {
            http_response_code(500);
            echo "View not found: {$viewName}";
            return;
        }

        // Start output buffering for layout
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        // Render within layout
        require BASE_PATH . '/app/Views/layouts/main.php';
    }

    protected function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    protected function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    protected function requireAuth(): int
    {
        if (!Session::isLoggedIn()) {
            $this->redirect('/login');
        }
        return Session::userId();
    }

    protected function db(): \PDO
    {
        return Database::getInstance($this->config['db'])->getPdo();
    }

    protected function post(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $default;
    }

    protected function get(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }
}
