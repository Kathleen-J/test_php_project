<?php

namespace db;

use Illuminate\Database\Capsule\Manager as Capsule;
use Phinx\Migration\AbstractMigration;

class Migration extends AbstractMigration {
    /** @var \Illuminate\Database\Capsule\Manager $capsule */
    public $capsule;
    /** @var \Illuminate\Database\Schema\Builder $schema */
    public $schema;

    public function init()
    {
        global $dbconn;

        $this->capsule = new Capsule();
        $this->capsule->addConnection($dbconn);
        $this->capsule->bootEloquent();
        $this->capsule->setAsGlobal();

        $this->capsule->bootEloquent();
        $this->capsule->setAsGlobal();
        $this->schema = $this->capsule->schema();
    }
}