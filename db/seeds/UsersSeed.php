<?php

require_once __DIR__ . '../../../apibootstrap.php';

use Phinx\Seed\AbstractSeed;
use api\v1\controllers\Users;

class UsersSeed extends AbstractSeed
{
    public function run(): void
    {
      try {
        (new Users())->saveExternalUsers();
      } catch (\Throwable $th) {
        var_dump($th->getMessage());
        exit;
      }
    }
}
