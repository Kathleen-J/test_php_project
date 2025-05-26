<?php

namespace src\traits;

use RuntimeException;

trait AttributeCheck
{
  /**
   * Проверка атрибутов на вхождение в разрешенные
   * @param array $attributes - атрибуты
   * @param array $allowedAttributes - разрешенные атрибуты
   * @return void
   * @throws RuntimeException
   */
  public function checkAttributes(array $attributes, array $allowedAttributes)
  {
    $attributesNames = array_keys($attributes);

    if (count(
      $this->getUnknownAttributes($attributesNames, $allowedAttributes)
    ) > 0) {

      var_dump($this->getUnknownAttributes($attributesNames, $allowedAttributes));
      var_dump(json_encode($attributesNames, true));
      var_dump(json_encode($attributes, true));
      throw new RuntimeException('Unknown attributes in request');
    }

    foreach ($attributes as $key => $value) {
      if (!isset($value)) {
        throw new RuntimeException("Empty $key attribute");
      }
    }
  }

  /**
   * Получение списка не разрешенных атрибутов
   * @param array $attributesNames - имена атрибутов
   * @param array $allowedAttributes - разрешенные атрибуты
   * @return array
   */
  private function getUnknownAttributes(array $attributesNames, array $allowedAttributes): array
  {
    return array_diff($attributesNames, $allowedAttributes);
  }
}