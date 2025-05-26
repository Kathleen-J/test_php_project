<?php

namespace src\traits;

use api\v1\crypt\Cryptographer;

trait CryptHelper
{
  public function encryptPassword(array $password)
  {
    return (new Cryptographer('password'))->encrypt($password);
  }

  public function decryptPassword(string $password)
  {
    return (new Cryptographer('password'))->decrypt($password);
  }

  public function encryptToken(array $token)
  {
    return (new Cryptographer('token'))->encrypt($token);
  }

  public function decryptToken(string $token)
  {
    return (new Cryptographer('token'))->decrypt($token);
  }
}