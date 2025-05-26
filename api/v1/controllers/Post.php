<?php

namespace api\v1\controllers;

use api\v1\controllers\BaseController;
use api\v1\controllers\Comment;
use src\models\PostModel;
use RuntimeException;

Class Post extends BaseController
{
  protected $model;

  public function __construct()
  {
    $this->model = new PostModel();
    $this->entity = 'Post';
  }

  /**
   * Получение постов из внешнего сервиса
   * @return string - список постов в формате json
   * @throws RuntimeException
   */
  public function getExternalPosts(): string
  {
    $url = $_SERVER['EXTERNAL_SERVICE'];

    return file_get_contents("$url/posts");
  }

  /**
   * Сохранение постов из внешнего сервиса
   * @return string - результат сохранения
   * @throws RuntimeException
   */
  public function saveExternalPosts(): string
  {
    try {
      $externalPosts = $this->getExternalPosts();
      $formattedPosts = json_decode($externalPosts, true);

      $result = array_map(function ($item) {
        return [
          'external_id' => $item['id'],
          'user_id'     => $item['userId'],
          'title'       => $item['title'],
          'description' => $item['body']
        ];
      }, $formattedPosts);

      $this->model->insert($result);

      return 'External posts was created';
    } catch (RuntimeException $e) {
      throw new RuntimeException($e->getMessage());
    }
  }

  /**
   * Управление активностью поста
   * @param int $postId - id поста
   * @param bool $status - статус поста
   * @return string - результат выполнения
   * @throws RuntimeException
   */
  public function setIsActiveStatus(int $postId, bool $status): string
  {
    try {
      if (!is_bool($status) && !is_null($status)) {
        throw new RuntimeException('incorrect status', 400);
      }

      $post = $this->model
        ->with('comments')
        ->find($postId);

      $post->is_active = $status;
      $post->save();

      $hasComments = $post->comments->isNotEmpty();

      if ($hasComments) {
        $commentIds = $post->comments->pluck('id')->toArray();

        (new Comment())->updateManyByIds($commentIds, $status);
      }

      $msg = $hasComments ? 'with comments' : '';

      return "Post's status was updated $msg";
    } catch (RuntimeException $e) {
      throw new RuntimeException($e->getMessage());
    }
  }

  /**
   * Управление активностью списка постов
   * @param array $attributes
   * @return string - результат выполнения
   * @throws RuntimeException
   */
  public function setIsActiveStatusToMany(array $attributes): string
  {
    $postIds = $attributes['postIds'] ?? null;
    $status = $attributes['postIds'] ?? false;
    $withComments = $attributes['postIds'] ?? true;

    if (!$postIds || !is_bool($status) && !is_null($status)) {
      throw new RuntimeException('incorrect data', 400);
    }

    $posts = $this->model
      ->with('comments')
      ->whereIn('id', $postIds)
      ->get();

    $allCommentIds = [];

    foreach ($posts as $post) {
      $allCommentIds = array_merge($allCommentIds, $post->comments->pluck('id')->toArray());
    }

    $this->model
      ->whereIn('id', $postIds)
      ->update([
        'is_active' => $status,
        'updated_at' => date('Y-m-d H:i:s')
      ]);

    $needUpdateComments = $withComments && !empty($allCommentIds);

    if ($needUpdateComments) {
      (new Comment())->setIsActiveStatusToMany($$allCommentIds, $status);
    }

    $msg = $needUpdateComments ? 'with all comments' : '';

    return "Posts statuses was updated $msg";
  }

  /**
   * Получение списка постов с комментариями
   * @param int $offset
   * @return array
   * @throws RuntimeException
   */
  public function getWithComments(): array
  {
    $offset = $_GET['offset'] ?? 0;

    return $this->model
      ->active()
      ->with(['comments' => function ($query) {
          $query
            ->active()
            ->orderBy('created_at', 'desc')
            ->take($_ENV['MAX_COMMENTS_OF_POST']);
      }])
      ->limit($_ENV['PAGINATION'])
      ->offset($offset)
      ->get()
      ->toArray();
  }

  /**
   * Удаление поста
   * @param int $id
   * @return string
   * @throws RuntimeException
   */
  public function delete($id): string
  {
    try {
      $post = $this->model
        ->with('comments')
        ->find($id);

      if ($post->comments->isNotEmpty()) {
        $commentIds = $post->comments->pluck('id')->toArray();

        (new Comment())->deleteMany($commentIds);
      }

      $this->deleteEntity($id);

      return 'post deleted';
    } catch (RuntimeException) {
      throw new RuntimeException('error deleting post');
    }
  }

  /**
   * Удаление поста создателем
   * @param int $id
   * @return string
   * @throws RuntimeException
   */
  public function deleteByUser($userId, $id): string
  {
    try {
      $post = $this->model
        ->with('comments')
        ->find($id);

      if ($post['user_id'] !== $userId) {
        throw new RuntimeException('access denied');
      }

      if ($post->comments->isNotEmpty()) {
        $commentIds = $post->comments->pluck('id')->toArray();

        (new Comment())->deleteMany($commentIds);
      }

      $this->deleteEntity($id);

      return 'post deleted';
    } catch (RuntimeException) {
      throw new RuntimeException('error deleting post');
    }
  }

  /**
   * Обновление поста
   * @param int $id
   * @param array $attributes
   * @return string
   * @throws RuntimeException
   */
  public function update(int $id, array $attributes)
  {
    $this->model
      ->where('id', '=', $id)
      ->update([
        'external_id' => $attributes['externalId'],
        'title' => $attributes['title'],
        'description' => $attributes['description'],
        'user_id' => $attributes['userId']
      ]);

    return 'post updated';
  }

  /**
   * Обновление поста пользователем
   * @param int $userId
   * @param int $id
   * @param array $attributes
   * @return string
   * @throws RuntimeException
   */
  public function updateByUser(int $userId, int $id, array $attributes)
  {
    $post = $this->readEntity($id);

    if ($post['user_id'] !== $userId) {
      throw new RuntimeException('access denied');
    }

    $this->model
      ->where('id', '=', $id)
      ->update([
        'title' => $attributes['title'],
        'description' => $attributes['description']
      ]);

    return 'post updated';
  }
}