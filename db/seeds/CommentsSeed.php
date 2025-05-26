<?php

use Phinx\Seed\AbstractSeed;
use api\v1\controllers\Comment;

require_once __DIR__ . '../../../apibootstrap.php';

class CommentsSeed extends AbstractSeed
{
    public function run(): void
    {
      (new Comment())->saveExternalComments();
    }
}
