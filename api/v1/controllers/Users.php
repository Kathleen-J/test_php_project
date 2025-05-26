<?php

namespace api\v1\controllers;

use api\v1\controllers\BaseController;
use api\v1\controllers\Auth;
use src\traits\CryptHelper;
use src\models\UsersModel;
use RuntimeException;

Class Users extends BaseController
{
  use CryptHelper;

  protected $model;

  public function __construct()
  {
    $this->model = new UsersModel();
    $this->entity = 'Users';
  }

  /**
   * Создание администратора
   * @return string
   * @throws RuntimeException
   */
  public function createAdmin(): string
  {
    try {
      $hashedPassword = $this->encryptPassword([
        'password' => 'admin'
      ]);

      $attributes = [
        'name' => 'admin',
        'last_name' => 'admin',
        'email' => 'system@email.ru',
        'password' => $hashedPassword,
        'is_admin' => true
      ];
      $attributes['is_admin'] = true;

      $this->createEntity($attributes);

      return 'admin was created';
    } catch (RuntimeException) {
      throw new RuntimeException('admin already exist', 400);
    }
  }

  /**
   * Получение пользователей из внешнего сервиса
   * @return string - список пользователей в формате json
   * @throws RuntimeException
   */
  public function getExternalUsers(): string
  {
    $url = $_SERVER['EXTERNAL_SERVICE'];

    return file_get_contents("$url/users");
  }

  /**
   * Сохранение пользователей из внешнего сервиса
   * @return string - результат сохранения
   * @throws RuntimeException
   */
  public function saveExternalUsers(): string
  {
    try {
      $externalUsers = $this->getExternalUsers();
      $formattedUsers = json_decode($externalUsers, true);

      $result = array_map(function ($item) {
        list($name, $lastName) = explode(' ', $item['name']);

        $hashedPassword = $this->encryptPassword([
          'password' => $this->generatePassword()
        ]);

        return [
          'external_id'     => $item['id'],
          'name'            => $name,
          'last_name'       => $lastName,
          'email'           => $item['email'],
          'phone'           => $item['phone'],
          'password'        => $hashedPassword
        ];
      }, $formattedUsers);

      $this->model->insert($result);

      return 'External users was created';
    } catch (RuntimeException $e) {
      throw new RuntimeException($e->getMessage());
    }
  }

  protected function generatePassword($length = 12) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+-=';
    $password = '';
    $maxIndex = strlen($chars) - 1;

    for ($i = 0; $i < $length; $i++) {
      $password .= $chars[random_int(0, $maxIndex)];
    }

    return $password;
  }

  /**
   * Поиск пользователя по id
   * @param int $id
   * @param bool $withToken
   * @return array
   * @throws Exception
   */
  public function getById(int $id, bool $withToken = false): array
  {
    try {
      $user = $this->find(['id' => $id]);

      $result = [
        'id' => $user['id'],
        'externalId' => $user['external_id'],
        'name' => $user['name'],
        'lastName' => $user['last_name'],
        'email' => $user['email'],
        'phone' => $user['phone'],
        'isAdmin' => $user['is_admin'],
        'createdAt' => $user['created_at'],
        'updatedAt' => $user['updated_at']
      ];

      if ($withToken) {
        $result['rememberToken'] = $user['remember_token'];
      }

      return $result;
    } catch (\Exception $e) {
      throw new \Exception('error getting user by params', 500);
    }
  }

  /**
   * Получение всех пользователей
   * @return array
   * @throws Exception
   */
  public function getAll(): array
  {
    try {
      return $this->model
        ->select(
          'id',
          'external_id',
          'name',
          'last_name',
          'email',
          'phone',
          'created_at',
          'updated_at'
        )
        ->get()
        ->toArray();
    } catch (\Exception $e) {
      throw new \Exception('error getting user by params', 500);
    }
  }

  /**
   * Поиск пользователя
   * @param array $attributes - данные запроса
   * @param bool $withPass - с паролем
   * @throws Exception
   */
  public function find(array $attributes, $withPass = false): object
  {
    try {
      $this->checkAttributes($attributes, $this->model->getFillable());

      $id = $attributes['id'] ?? null;
      $externalId = $attributes['external_id'] ?? null;
      $name = $attributes['name'] ?? null;
      $lastName = $attributes['last_name'] ?? null;
      $email = $attributes['email'] ?? null;
      $phone = $attributes['phone'] ?? null;

      $req = $this->model->select(
        'id',
        'external_id',
        'name',
        'last_name',
        'email',
        'password',
        'phone',
        'is_admin',
        'created_at',
        'updated_at'
      );

      if ($id) {
        $req->where('id', '=', $id);
      }

      if ($externalId) {
        $req->where('external_id', '=', $externalId);
      }

      if ($name) {
        $req->where('name', '=', $name);
      }

      if ($lastName) {
        $req->where('last_name', '=', $lastName);
      }

      if ($email) {
        $req->where('email', '=', $email);
      }

      if ($phone) {
        $req->where('phone', '=', $phone);
      }

      $result = $req->first();

      if (!$withPass) {
        $result['password'] = null;
      }

      return $result;
    } catch (\Exception $e) {
      echo $e->getMessage();
      throw new \Exception('error getting user by params', 500);
    }
  }

  /**
   * Обновление токена пользователя
   * @param int $userId - id пользователя
   * @param string|null $token
   * @return string - результат выполнения
   * @throws RuntimeException
   */
  public function updateRememberToken(int $userId, string | null $token): string
  {
    try {
      $this->model
        ->where('id', '=', $userId)
        ->update([
          'remember_token' => $token
        ]);

      return 'token was updated';
    } catch (RuntimeException $e) {
      throw new RuntimeException('error updating token', 500);
    }
  }

  /**
   * Обновление пароля пользователя
   * @param string $password
   * @return string - результат выполнения
   * @throws RuntimeException
   */
  public function updatePassword(string $password): string
  {
    try {
      $userId = (new Auth())->getUserId();

      $this->model
        ->where('id', '=', $userId)
        ->update([
          'password' => $this->encryptPassword([
            'password' => $password
          ])
        ]);

      return 'password was updated';
    } catch (RuntimeException $e) {
      throw new RuntimeException('error updating password', 500);
    }
  }

  /**
   * Обновление данных пользователя
   * @param int $userId - id пользователя
   * @param array $attributes
   * @return string - результат выполнения
   * @throws RuntimeException
   */
  public function update(int $userId, array $attributes): string
  {
    try {
      $this->checkAttributes($attributes, $this->model->getFillable());

      $name = $attributes['name'] ?? null;
      $lastName = $attributes['last_name'] ?? null;
      $email = $attributes['email'] ?? null;
      $phone = $attributes['phone'] ?? false;

      $updateParams = [];

      if ($name) {
        $updateParams['name'] = $name;
      }

      if ($lastName) {
        $updateParams['last_name'] = $lastName;
      }

      if ($email) {
        $updateParams['email'] = $email;
      }

      if (is_null($phone) || $phone) {
        $updateParams['phone'] = $phone;
      }

      $this->model
        ->where('id', '=', $userId)
        ->update($updateParams);

      return 'user was updated';
    } catch (RuntimeException $e) {
      throw new RuntimeException('error updating user by params', 500);
    }
  }

  /**
   * Обновление данных пользователя
   * @param int $userId - id пользователя
   * @param array $attributes
   * @return string - результат выполнения
   * @throws RuntimeException
   */
  public function updateByUser(int $userId, array $attributes)
  {
    $operatorId = (new Auth())->getUserId();

    if ($operatorId !== $userId) {
      throw new \Error('access denied', 403);
    }

    return $this->update($userId, $attributes);
  }

  public function create(array $attributes)
  {
    $this->checkAttributes($attributes, $this->model->getFillable());

    $email = $attributes['email'] ?? null;

    if (!$email || filter_var($email, FILTER_VALIDATE_EMAIL)) {
      throw new \Exception('incorrect email', 400);
    }

    $name = $attributes['name'] ?? null;
    $lastName = $attributes['last_name'] ?? null;
    $password = $attributes['password'] ?? null;
    $phone = $attributes['phone'] ?? null;

    if (!$name || !$lastName || !$password) {
      throw new \Exception('incorrect user data', 400);
    }

    $this->model
      ->create([
        'email' => $email,
        'name' => $name,
        'last_name' => $lastName,
        'password' => $this->encryptPassword([
          'password' => $this->generatePassword()
        ]),
        'phone' => $phone,
      ]);
  }
}