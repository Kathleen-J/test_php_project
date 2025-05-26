<?php

// проброс конфига в кастомный класс миграций
global $dbconn;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$dbconn = [
    'driver' => getenv('DB_DRIVER') ?: ($_ENV['DB_DRIVER'] ?? $_SERVER['DB_DRIVER']),
    'host' => getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? $_SERVER['DB_HOST']),
    'database' => getenv('DB_NAME') ?: ($_ENV['DB_NAME'] ?? $_SERVER['DB_NAME']),
    'username' => getenv('DB_USER') ?: ($_ENV['DB_USER'] ?? $_SERVER['DB_USER']),
    'password' => getenv('DB_PASSWORD') ?: ($_ENV['DB_PASSWORD'] ?? $_SERVER['DB_PASSWORD']),
    'port' => getenv('DB_PORT') ?: ($_ENV['DB_PORT'] ?? $_SERVER['DB_PORT'])
];

$environmentParams = [
    'adapter' => getenv('DB_DRIVER') ?: ($_ENV['DB_DRIVER'] ?? $_SERVER['DB_DRIVER']),
    'host' => getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? $_SERVER['DB_HOST']),
    'name' => getenv('DB_NAME') ?: ($_ENV['DB_NAME'] ?? $_SERVER['DB_NAME']),
    'user' => getenv('DB_USER') ?: ($_ENV['DB_USER'] ?? $_SERVER['DB_USER']),
    'pass' => getenv('DB_PASSWORD') ?: ($_ENV['DB_PASSWORD'] ?? $_SERVER['DB_PASSWORD']),
    'port' => getenv('DB_PORT') ?: ($_ENV['DB_PORT'] ?? $_SERVER['DB_PORT'])
];

$env = getenv('ENVIRONMENT') ?: ($_ENV['ENVIRONMENT'] ?? $_SERVER['ENVIRONMENT']);

return
[
    'paths' => [
        'migrations' => '%%PHINX_CONFIG_DIR%%/db/migrations',
        'seeds' => '%%PHINX_CONFIG_DIR%%/db/seeds'
    ],
    'migration_base_class' => 'db\Migration',
    'environments' => [
        'default_migration_table' => 'migrations',
        'default_environment' => $env,
        $env => $environmentParams
    ],
    'version_order' => 'creation'
];
