<?php
/**
 * EmoEat MVC — Front Controller
 * All requests routed through here via .htaccess
 */

define('BASE_PATH', dirname(__DIR__));

// Autoloader (PSR-4 style)
spl_autoload_register(function (string $class) {
    $prefix = 'App\\';
    $baseDir = BASE_PATH . '/app/';

    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Load config
$config = require BASE_PATH . '/app/Config/config.php';

// Boot application
use App\Core\App;

$app = App::getInstance($config);
$app->run();
