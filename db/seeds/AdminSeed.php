<?php

use Phinx\Seed\AbstractSeed;
use api\v1\controllers\Users;

require_once __DIR__ . '../../../apibootstrap.php';

class AdminSeed extends AbstractSeed
{
    public function run(): void
    {
      (new Users())->createAdmin();
    }
}
