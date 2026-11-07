<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/helpers.php';

spl_autoload_register(function (string $class): void {
    $prefixes = [
        'Core\\' => __DIR__ . '/',
        'App\\Controllers\\' => __DIR__ . '/../app/controllers/',
        'App\\Models\\' => __DIR__ . '/../app/models/',
    ];
    foreach ($prefixes as $prefix => $dir) {
        if (str_starts_with($class, $prefix)) {
            $relative = substr($class, strlen($prefix));
            $file = $dir . str_replace('\\', '/', $relative) . '.php';
            if (is_file($file)) {
                require $file;
            }
            return;
        }
    }
});

$config = [
    'app' => require __DIR__ . '/../config/app.php',
    'database' => require __DIR__ . '/../config/database.php',
    'mail' => require __DIR__ . '/../config/mail.php',
];

date_default_timezone_set($config['app']['timezone']);

// A project-specific session cookie name: other local PHP apps on this
// machine may also run on localhost at different ports, and cookies ignore
// the port, so the default PHPSESSID could be shared (and clobbered) between
// this app and another one running side by side in the same browser.
session_name('talentfit_session');
session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
]);

Core\Config::init($config);
Core\Database::init($config['database']);
Core\Router::setBasePath($config['app']['base_path']);
