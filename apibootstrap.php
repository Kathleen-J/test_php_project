<?php

use Illuminate\Database\Capsule\Manager as Capsule;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$config = [
  'driver' => getenv('DB_DRIVER') ?: ($_ENV['DB_DRIVER'] ?? $_SERVER['DB_DRIVER']),
  'host' => getenv('DB_HOST')  ?: ($_ENV['DB_HOST'] ?? $_SERVER['DB_HOST']),
  'database' => getenv('DB_NAME')  ?: ($_ENV['DB_NAME'] ?? $_SERVER['DB_NAME']),
  'username' => getenv('DB_USER')  ?: ($_ENV['DB_USER'] ?? $_SERVER['DB_USER']),
  'password' => getenv('DB_PASSWORD')  ?: ($_ENV['DB_PASSWORD'] ?? $_SERVER['DB_PASSWORD']),
  'port' => getenv('DB_PORT')  ?: ($_ENV['DB_PORT'] ?? $_SERVER['DB_PORT'])
];

$capsule = new Capsule();
$capsule->addConnection($config, 'service');
$capsule->setAsGlobal();
$capsule->bootEloquent();