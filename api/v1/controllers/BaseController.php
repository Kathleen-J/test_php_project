<?php

namespace api\v1\controllers;

use src\traits\AttributeCheck;
use RuntimeException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;

class BaseController
{
  use AttributeCheck;

  protected $entity;

  /**
   * Создание записи о сущности
   * @param array $attributes - данные запроса
   * @return object - новая сущность
   * @throws RuntimeException
   */
  public function createEntity(array $attributes): object
  {
    try {
      $this->checkAttributes($attributes, $this->model->getFillable());

      $newRecord = $this->model->create($attributes);

      $entity = strtolower($this->entity);

      if (!$newRecord) {
        throw new RuntimeException("Unable to create $entity");
      }

      return $newRecord;
    } catch (QueryException) {
      throw new \RuntimeException('entity already exist', 400);
    } catch (\Exception $e) {
      throw new \Exception($e->getMessage(), 400);
    }
  }

  /**
   * Получение сущности по id
   * @param int $id - id сущности
   * @return array - параметры сущности
   * @throws RuntimeException
   */
  public function readEntity(int $id): array
  {
    $record = $this->model->find($id);
    $entity = $this->entity;

    if (!$record) {
      throw new RuntimeException("$entity not found");
    }

    return $record->toArray();
  }

  /**
   * Получение всех записей сущности
   * @return array
   */
  public function readAllEntities(): array
  {
    try {
      return $this->model
        ->where('status', '=', 'active')
        ->get()
        ->toArray();
      } catch (\Exception $e) {
        throw new \Exception('error getting offices list', 500);
      }
  }

  /**
   * Обновление сущности
   * @param int $id - id сущности
   * @param array $attributes - данные запроса
   * @return void
   */
  public function updateEntity(int $id, array $attributes)
  {
    try {
      $this->updateProcessing($id, $attributes);

      return true;
    } catch (\Exception $e) {
      throw new \Exception('error update entity data', 500);
    }
  }

  /**
   * Обновление записи сущности в таблице
   * @param int $id - id сущности
   * @param array $attributes - атрибуты для обновления
   * @return void
   */
  protected function updateProcessing(int $id, array $attributes)
  {
    try {
      $this->checkAttributes($attributes, $this->model->getFillable());
      $this->model->findOrFail($id)->update($attributes);
    } catch (ModelNotFoundException $e) {
      throw new \Exception('entity not found', 404);
    } catch (\Exception $e) {
      throw new \Exception('error update entity data', 500);
    }
  }

  /**
   * Удаление сущности
   * @param int $id - id сущности
   * @return void
   * @throws RuntimeException
   */
  public function deleteEntity(int $id)
  {
    try {
      $this->model
        ->where('id', '=', $id)
        ->delete();

      return true;
    } catch (\Exception $e) {
      throw new \Exception('error deleting entity', 500);
    }
  }

  /**
   * Удаление сущностей
   * @param int $ids - ids сущностей
   * @return void
   * @throws RuntimeException
   */
  public function deleteMany(array $ids): string
  {
    try {
      $this->model
        ->whereIn('id', $ids)
        ->delete();

      return true;
    } catch (\Exception $e) {
      throw new \Exception('error deleting entity', 500);
    }
  }
}