<?php

namespace api\v1\controllers;

use api\v1\controllers\BaseController;
use src\models\CommentModel;
use RuntimeException;

Class Comment extends BaseController
{
  protected $model;

  public function __construct()
  {
    $this->model = new CommentModel();
    $this->entity = 'Comment';
  }

  /**
   * Создание комментария к посту
   * @param array $attributes - данные комментария
   * @return string - список комментариев в формате json
   * @throws RuntimeException
   */
  public function create(array $attributes): string {
    try {
      $comment = $this->createEntity($attributes);
      $comment->load('post.user');

      $emailCreator = $comment->post->user->email;
      $postTitle = $comment->post->title;

      $msg = "System message.\nSomeone added comment to your post'$$postTitle'!";

      $this->sendToMail($emailCreator, $msg);

      return 'Comment was created';
    } catch (RuntimeException) {
      throw new RuntimeException('error creating comment', 500);
    }
  }

  /**
   * Получение комментариев постов из внешнего сервиса
   * @return string - список комментариев в формате json
   * @throws RuntimeException
   */
  public function getExternalComments(): string
  {
    $url = $_SERVER['EXTERNAL_SERVICE'];

    return file_get_contents("$url/comments");
  }

  /**
   * Сохранение постов из внешнего сервиса
   * @return string - результат сохранения
   * @throws RuntimeException
   */
  public function saveExternalComments(): string
  {
    try {
      $externalComments = $this->getExternalComments();
      $formattedComments = json_decode($externalComments, true);

      $result = array_map(function ($item) {
        return [
          'external_id' => $item['id'],
          'name'       => $item['name'],
          'email'       => $item['email'],
          'post_id'       => $item['postId'],
          'description'       => $item['body']
        ];
      }, $formattedComments);

      $this->model->insert($result);

      return 'External comments was created';
    } catch (RuntimeException $e) {
      throw new RuntimeException($e->getMessage());
    }
  }

  /**
   * Управление активностью комментария
   * @param int $commentId - id комментария
   * @param bool $status - статус комментария
   * @return string - результат выполнения
   * @throws RuntimeException
   */
  public function setIsActiveStatus(int $commentId, bool $status): string
  {
    try {
      if (!is_bool($status) && !is_null($status)) {
        throw new RuntimeException('incorrect status', 400);
      }

      $params = array(
        'is_active' => $status,
        'updated_at' => date('Y-m-d H:i:s')
      );

      $this->updateEntity($commentId, $params);

      return 'Comment\'s status was updated';
    } catch (RuntimeException $e) {
      throw new RuntimeException($e->getMessage());
    }
  }

  /**
   * Управление активностью списка комментариев
   * @param array $attributes - ids комментариев
   * @return string - результат выполнения
   * @throws RuntimeException
   */
  public function setIsActiveStatusToMany(array $attributes): string
  {
    try {
      $commentIds = $attributes['commentIds'] ?? null;
      $status = $attributes['postIds'] ?? false;

      if (!$commentIds || !is_bool($status) && !is_null($status)) {
        throw new RuntimeException('incorrect data', 400);
      }

      $this->model
        ->whereIn('id', $commentIds)
        ->update([
          'is_active' => $status,
          'updated_at' => date('Y-m-d H:i:s')
        ]);

      return 'Comments statuses was updated';
    } catch (RuntimeException $e) {
      throw new RuntimeException($e->getMessage());
    }
  }

  public function updateManyByIds($ids, $status): string
  {
    try {
      if (empty($ids) || !is_bool($status)) {
        throw new RuntimeException('incorrect data', 400);
      }

      $this->model
        ->whereIn('id', $ids)
        ->update([
          'is_active' => $status,
          'updated_at' => date('Y-m-d H:i:s')
        ]);

      return 'Comments statuses was updated';
    } catch (RuntimeException $e) {
      throw new RuntimeException($e->getMessage());
    }
  }

  /**
   * Отправление письма на почту отправителя
   * @param string $emailCreator - данные отправителя
   * @param string $msg - тело письма
   * @return bool - результат выполнения
   * @throws RuntimeException
   */
  public function sendToMail(string $emailCreator, string $msg): bool
  {
    try {
      $subject = 'Notification';
      $headers = "From: no-reply@example.com\r\n" .
                  "Content-Type: text/plain; charset=utf-8\r\n";

      return mail($emailCreator, $subject, $msg, $headers);
    } catch (RuntimeException $e) {
      throw new RuntimeException($e->getMessage());
    }
  }

  /**
   * Получение списка комментариев
   * @param int $postId - id поста
   * @param int $offset
   * @return array
   * @throws RuntimeException
   */
  public function get($postId): array
  {
    $offset = $_GET['offset'] ?? 0;

    return $this->model
      ->where('post_id', '=', $postId)
      ->where('is_active', '=', true)
      ->orderBy('created_at', 'desc')
      ->limit($_ENV['PAGINATION'])
      ->offset($offset)
      ->get()
      ->toArray();
  }

  /**
   * Обновление комментария пользователем
   * @param int $userId
   * @param int $id
   * @param array $attributes
   * @return string
   * @throws RuntimeException
   */
  public function updateByUser(int $userId, int $id, array $attributes)
  {
    $userEmail = (new Users())->find(array([
      'id' => $userId
    ]))['email'];
    $comment = $this->readEntity($id);

    if ($comment['email'] !== $userEmail) {
      throw new RuntimeException('access denied');
    }

    $result = [];

    if ($attributes['title']) {
      $result['title'] = $attributes['title'];
    }

    if ($attributes['description']) {
      $result['description'] = $attributes['description'];
    }

    $this->model
      ->where('id', '=', $id)
      ->update($result);

    return 'comment updated';
  }

  public function update(int $id, array $attributes)
  {
    $this->model
      ->where('id', '=', $id)
      ->update([
        'external_id' => $attributes['externalId'],
        'name' => $attributes['name'],
        'email' => $attributes['email'],
        'post_id' => $attributes['postId'],
        'description' => $attributes['description']
      ]);

    return 'post updated';
  }

  /**
   * Удаление комментария создателем
   * @param int $userId
   * @param int $id
   * @return string
   * @throws RuntimeException
   */
  public function deleteByUser($userId, $id): string
  {
    try {
      $userEmail = (new Users())->find(array([
        'id' => $userId
      ]))['email'];
      $comment = $this->model->find($id);

      if ($comment['email'] !== $userEmail) {
        throw new RuntimeException('access denied');
      }

      $this->deleteEntity($id);

      return 'comment deleted';
    } catch (RuntimeException) {
      throw new RuntimeException('error deleting comment');
    }
  }

  /**
   * Удаление комментария
   * @param int $id
   * @return string
   * @throws RuntimeException
   */
  public function delete($id): string
  {
    try {
      $this->deleteEntity($id);

      return 'comment deleted';
    } catch (RuntimeException) {
      throw new RuntimeException('error deleting comment');
    }
  }
}