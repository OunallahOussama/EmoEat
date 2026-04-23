<?php

namespace App\Core;

/**
 * App — Singleton Application Container & Router
 * Design Pattern: Singleton + Front Controller
 */
class App
{
    private static ?App $instance = null;
    private array $config;
    private Router $router;

    private function __construct(array $config)
    {
        $this->config = $config;
        $this->router = new Router();
        $this->registerRoutes();
    }

    public static function getInstance(array $config = []): self
    {
        if (self::$instance === null) {
            self::$instance = new self($config);
        }
        return self::$instance;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    private function registerRoutes(): void
    {
        // Auth
        $this->router->add('GET',  '/',             'Auth',           'loginForm');
        $this->router->add('GET',  '/login',        'Auth',           'loginForm');
        $this->router->add('POST', '/login',        'Auth',           'doLogin');
        $this->router->add('GET',  '/register',     'Auth',           'registerForm');
        $this->router->add('POST', '/register',     'Auth',           'doRegister');
        $this->router->add('GET',  '/logout',       'Auth',           'logout');

        // Dashboard
        $this->router->add('GET',  '/dashboard',    'Dashboard',      'index');

        // Profile
        $this->router->add('GET',  '/profile',      'Profile',        'index');
        $this->router->add('POST', '/profile',      'Profile',        'update');

        // Emotion check-in
        $this->router->add('GET',  '/checkin',      'Emotion',        'form');
        $this->router->add('POST', '/checkin',      'Emotion',        'submit');

        // Recommendations
        $this->router->add('GET',  '/recommendations', 'Recommendation', 'index');

        // Quiz
        $this->router->add('GET',  '/quiz',         'Quiz',           'index');
        $this->router->add('POST', '/quiz/generate','Quiz',           'generate');
        $this->router->add('GET',  '/quiz/take/{id}', 'Quiz',        'take');
        $this->router->add('POST', '/quiz/submit',  'Quiz',           'submit');
        $this->router->add('GET',  '/quiz/result/{id}', 'Quiz',      'result');

        // Gamification
        $this->router->add('GET',  '/badges',       'Gamification',   'badges');
        $this->router->add('GET',  '/leaderboard',  'Gamification',   'leaderboard');

        // Language switch
        $this->router->add('GET',  '/lang/{locale}', 'Language',     'switchLang');

        // API (JSON endpoints)
        $this->router->add('POST', '/api/quiz/generate', 'Api',      'generateQuiz');
        $this->router->add('POST', '/api/recommend',     'Api',      'recommend');
        $this->router->add('GET',  '/api/emotions',      'Api',      'emotions');
        $this->router->add('GET',  '/api/profile',       'Api',      'profile');
        $this->router->add('POST', '/api/mcp/invoke',    'Api',      'mcpInvoke');

        // Admin panel
        $this->router->add('GET',  '/admin',                'Admin', 'index');
        $this->router->add('GET',  '/admin/users',          'Admin', 'users');
        $this->router->add('GET',  '/admin/users/edit/{id}','Admin', 'userEdit');
        $this->router->add('POST', '/admin/users/edit/{id}','Admin', 'userUpdate');
        $this->router->add('POST', '/admin/users/delete/{id}','Admin','userDelete');
        $this->router->add('GET',  '/admin/llm',            'Admin', 'llmConfig');
        $this->router->add('POST', '/admin/llm',            'Admin', 'llmConfigSave');
        $this->router->add('POST', '/admin/llm/test',       'Admin', 'llmTest');
        $this->router->add('GET',  '/admin/mcp',            'Admin', 'mcpConfig');
        $this->router->add('POST', '/admin/mcp/test',       'Admin', 'mcpTest');
    }

    public function run(): void
    {
        Session::start();
        I18n::getInstance(); // boot translations from session locale

        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        $match = $this->router->match($method, $uri);

        if ($match === null) {
            http_response_code(404);
            echo '404 — Page not found';
            return;
        }

        $controllerName = 'App\\Controllers\\' . $match['controller'] . 'Controller';
        $action = $match['action'];
        $params = $match['params'];

        if (!class_exists($controllerName)) {
            http_response_code(500);
            echo "Controller not found: {$match['controller']}";
            return;
        }

        $controller = new $controllerName($this->config);
        $controller->$action(...$params);
    }
}
