<?php

namespace api\v1\crypt;

use Nullix\CryptoJsAes\CryptoJsAes;
require "CryptoJsAes.php";

class Cryptographer
{
  private $secret;

  public function __construct($hashType)
  {
    $this->secret = $hashType === 'token' ? $_SERVER['SECRET'] : $_SERVER['SECRET_PASSWORD'];
  }

  /**
   * Шифрование
   * @param array $data - данные для шифрования
   */
  public function encrypt(array $data): string
  {
    try {
      $secretKey = $this->secret;

      return CryptoJsAes::encrypt($data, $secretKey);
    } catch (\Exception $e) {
      throw new \Exception('error encrypt data', 500);
    }
  }

  /**
   * Расшифровка
   * @param string $data - данные для расшифровки
   */
  public function decrypt(string $data): mixed
  {
    try {
      $secretKey = $this->secret;

      return CryptoJsAes::decrypt($data, $secretKey);
    } catch (\Exception $e) {
      throw new \Exception('error decrypt data', 500);
    }
  }
}