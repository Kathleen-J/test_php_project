<?php

use Illuminate\Routing\Router;
use Illuminate\Http\Request;

use api\v1\controllers\Auth;
use api\v1\controllers\Users;
use api\v1\controllers\Post;
use api\v1\controllers\Comment;

use api\v1\middlewares\RequestCasting;

/** @var Router $router */

$version = 'v' . $apiVersion;

//auth
$router->group(
  ['namespace' => "api\\$version\controllers", 'prefix' => "$authPrefix/$version"],
  function (Router $router) {
    $router->post('/authorize', function (Request $request) {
      return (new RequestCasting(new Auth))->authorize($request);
    });
    $router->post('/logout', function () {
      return (new Auth())->logout();
    });
  }
);

//users
$router->group(
  ['namespace' => "api\\$version\controllers", 'prefix' => "$usersPrefix/$version"],
  function (Router $router) {
    $router->get('/', function () {
      (new Auth())->isAdmin();

      return (new Users())->getAll();
    });
    $router->get('/{id}', function (int $id) {
      (new Auth())->isAdmin();

      return (new Users())->getById($id);
    });

    $router->post('/', function (Request $request) {
      (new Auth())->isUserAuthorized();

      return (new RequestCasting(new Users))->create($request);
    });
    $router->post('/admin', function () {
      return (new Users())->createAdmin();
    });
    $router->post('/external', function () {
      (new Auth())->isAdmin();

      return (new Users())->saveExternalUsers();
    });

    $router->put('/', function (Request $request) {
      (new Auth())->isAdmin();

      return (new RequestCasting(new Users))->update($request);
    });
    $router->put('/{id}', function (int $id, Request $request) {
      (new Auth())->isUserAuthorized();

      return (new RequestCasting(new Users))->updateByUser($id, $request);
    });
    $router->put('/password', function (Request $request) {
      (new Auth())->isUserAuthorized();

      return (new RequestCasting(new Users))->updatePassword($request);
    });

    $router->delete('/', function (int $id) {
      (new Auth())->isAdmin();

      return (new Users())->deleteEntity($id);
    });
  }
);

//posts
$router->group(
  ['namespace' => "api\\$version\controllers", 'prefix' => "$postsPrefix/$version"],
  function (Router $router) {
    $router->get('/', function () {
      (new Auth())->isUserAuthorized();

      return (new Post())->getWithComments();
    });
    $router->get('/{id}', function (int $id) {
      (new Auth())->isUserAuthorized();

      return (new Post())->readEntity($id);
    });

    $router->post('/', function (Request $request) {
      (new Auth())->isUserAuthorized();

      return (new RequestCasting(new Post))->createEntity($request);
    });
    $router->post('/external', function () {
      (new Auth())->isAdmin();

      return (new Post())->saveExternalPosts();
    });

    $router->put('/', function (Request $request) {
      (new Auth())->isAdmin();

      return (new RequestCasting(new Post))->setIsActiveStatusToMany($request);
    });
    $router->put('/{id}', function (int $id, Request $request) {
      $auth = new Auth();

      $auth->isUserAuthorized();
      $userId = $auth->getUserId();

      return (new RequestCasting(new Post))->updateByUser($userId, $id, $request);
    });
    $router->put('/{id}/full', function (int $id, Request $request) {
      (new Auth())->isAdmin();

      return (new RequestCasting(new Post))->update($id, $request);
    });
    $router->put('/setStatus/{id}', function (int $id, Request $request) {
      (new Auth())->isAdmin();

      return (new RequestCasting(new Post))->setIsActiveStatus($id, $request['status']);
    });

    $router->delete('/{id}', function (int $id) {
      $auth = new Auth();

      $auth->isUserAuthorized();

      return (new Post())->deleteByUser($auth->getUserId(), $id);
    });
    $router->delete('/{id}/full', function (int $id) {
      (new Auth())->isAdmin();

      return (new Post())->delete($id);
    });
  }
);

// comments
$router->group(
  ['namespace' => "api\\$version\controllers", 'prefix' => "$commentsPrefix/$version"],
  function (Router $router) {
    $router->get('/byPost/{id}', function (int $id) {
      (new Auth())->isUserAuthorized();

      return (new Comment())->get($id);
    });
    $router->get('/{id}', function (int $id) {
      (new Auth())->isUserAuthorized();

      return (new Post())->readEntity($id);
    });

    $router->post('/', function (Request $request) {
      (new Auth())->isUserAuthorized();

      return (new RequestCasting(new Comment))->create($request);
    });
    $router->post('/external', function () {
      (new Auth())->isAdmin();

      return (new Comment())->saveExternalComments();
    });

    // update for users 
    $router->put('/', function (Request $request) {
      (new Auth())->isAdmin();

      return (new RequestCasting(new Comment))->setIsActiveStatusToMany($request);
    });
    $router->put('/{id}', function (int $id, Request $request) {
      $auth = new Auth();
      $auth->isUserAuthorized();
      $userId = $auth->getUserId();

      return (new RequestCasting(new Comment))->updateByUser($userId, $id, $request);
    });
    $router->put('/{id}/full', function (int $id, Request $request) {
      (new Auth())->isAdmin();

      return (new RequestCasting(new Comment))->update($id, $request['status']);
    });
    $router->put('/setStatus', function (int $id, Request $request) {
      (new Auth())->isAdmin();

      return (new RequestCasting(new Comment))->setIsActiveStatus($id, $request['status']);
    });

    $router->delete('/', function (int $id) {
      (new Auth())->isAdmin();

      return (new Comment())->delete($id);
    });

    $router->delete('/{id}', function (int $id) {
      $auth = new Auth();

      $auth->isUserAuthorized();

      return (new Comment())->deleteByUser($auth->getUserId(), $id);
    });
    $router->delete('/{id}/full', function (int $id) {
      (new Auth())->isAdmin();

      return (new Comment())->delete($id);
    });
  }
);