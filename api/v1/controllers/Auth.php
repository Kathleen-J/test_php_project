<?php

namespace api\v1\controllers;

use DateTime;
use api\v1\controllers\Users;
use Exception;
use src\traits\CryptHelper;

class Auth
{
  use CryptHelper;

  /**
   * Генерация токена
   * @param int $userId
   */
  private function genToken(int $userId)
  {
    $hours = $_ENV['TOKEN_LIFETIME'] ?? '6';
    $dateTime = (new DateTime())->modify("+ $hours hour")->format('Y-m-d H:i:s');

    return $this->encryptToken([
      'userId' => $userId,
      'expireAt' => $dateTime
    ]);
  }

  /**
   * Процесс авторизации
   * @param array $authParams - почта и пароль пользователя
   */
  public function authorize(array $authParams)
  {
    try {
      $email = $authParams['email'] ?? null;
      $password = $authParams['password'] ?? null;

      $usersController = new Users();
      $userParams = array('email' => $email);
      $foundUser = $usersController->find($userParams, true);

      if (!$foundUser) {
        throw new Exception('User with current email is not exist', 400);
      }

      $decryptedPasswordData = $this->decryptPassword($foundUser['password']);
      $decryptedPassword = $decryptedPasswordData['password'];

      if ($decryptedPassword !== $password) {
        throw new Exception('Incorrect password', 400);
      }

      $userId = $foundUser['id'];

      $token = $this->genToken($userId);

      $usersController->updateRememberToken($userId, $token);

      return $token;
    } catch (Exception $e) {
      throw new Exception(
        $e->getMessage(),
        $e->getCode()
      );
    }
  }

  /**
   * Проверка актуальности токена
   */
  private function checkLifetimeToken($authToken)
  {
    try {
      $tokenData = $this->decryptToken($authToken);

      $userId = $tokenData['userId'];
      $expireAt = $tokenData['expireAt'];
      $now = (new DateTime())->format('Y-m-d H:i:s');

      if ($now > $expireAt) {
        $this->logout($userId);

        throw new Exception('logged out', 401);
      }

      return true;
    } catch (Exception $e) {
      throw new Exception($e->getMessage(), $e->getCode() ?? 500);
    }
  }

  /**
   * Разлогин
   * @param int $userId
   */
  public function logout()
  {
    try {
      $userId = $this->getUserId();

      (new Users())->updateRememberToken($userId, null);
    } catch (Exception $e) {
      throw new Exception('Ошибка при деавторизации', 500);
    }
  }

  /**
   * Получение id пользователя из заголовков авторизации
   * @return int
   */
  public function getUserId(): int
  {
    try {
      $token = $_SERVER['HTTP_AUTHORIZATION'] ?? null;

      if (!$token) {
        throw new Exception('No token', 403);
      }

      $authData = $this->decryptToken($_SERVER['HTTP_AUTHORIZATION']);

      if (!$authData) {
        throw new Exception('No auth data', 403);
      }

      return $authData['userId'];
    } catch (Exception $th) {
      throw new Exception($th->getMessage(), $th->getCode());
    }
  }

  /**
   * Проверка на наличие прав администратора
   */
  public function isAdmin(): void
  {
    try {
      $userId = $this->getUserId();
      $user = (new Users())->getById($userId, true);

      $this->checkLifetimeToken($_SERVER['HTTP_AUTHORIZATION']);

      if (!$user['isAdmin'] ?? null) {
        throw new Exception('access denied', 403);
      };
    } catch (Exception $th) {
      throw new Exception($th->getMessage(), $th->getCode());
    }
  }

  /**
   * Проверка авторизованности
   */
  public function isUserAuthorized(): void
  {
    $userId = $this->getUserId();
    $foundUser = (new Users())->getById($userId);

    $this->checkLifetimeToken($_SERVER['HTTP_AUTHORIZATION']);

    if (!empty($foundUser)) {
      throw new Exception('access denied', 403);
    };
  }
}