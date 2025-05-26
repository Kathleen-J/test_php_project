<?php

use Phinx\Seed\AbstractSeed;
use api\v1\controllers\Post;

require_once __DIR__ . '../../../apibootstrap.php';

class PostsSeed extends AbstractSeed
{
    public function run(): void
    {
      (new Post())->saveExternalPosts();
    }
}
