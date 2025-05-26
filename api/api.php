<?php

define('ROOT_PATH', __DIR__ . '/..');

if (!defined('VENDOR_PATH')) {
  define('VENDOR_PATH', ROOT_PATH . '/vendor');
}
require VENDOR_PATH . '/autoload.php';
require ROOT_PATH . '/apibootstrap.php';

use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Routing\Contracts\CallableDispatcher;
use Illuminate\Routing\CallableDispatcher as DefaultDispatcher;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Exception;

$apiVersion = 1;
$authPrefix = 'auth/api';
$usersPrefix = 'users/api';
$postsPrefix = 'posts/api';
$commentsPrefix = 'comments/api';

$container = new Container;

$request = Request::capture();
$container->instance('Illuminate\Http\Request', $request);

$container->bind(CallableDispatcher::class, function () use ($container) {
    return new DefaultDispatcher($container);
});

$events = new Dispatcher($container);
$router = new Router($events, $container);

require_once "v$apiVersion" . '/routes.php';

error_reporting(E_ALL);
ini_set('display_errors', '1');

try {
  $response = $router->dispatch($request);

  $response->send();
} catch (NotFoundHttpException $e) {
  echo '404 not found!';
  echo $e->getMessage();

  return header('HTTP/1.1 404');
} catch (RuntimeException $e) {
  echo $e->getMessage();
  $code = $e->getCode();

  header("HTTP/1.1 $code");
} catch (Error $e) {
  echo $e->getMessage();
  $code = $e->getCode();

  header("HTTP/1.1 $code");
} catch (Exception $e) {
  echo $e->getMessage();
  $code = $e->getCode();

  header("HTTP/1.1 $code");
}