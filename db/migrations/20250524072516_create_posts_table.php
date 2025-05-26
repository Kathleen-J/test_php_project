<?php
declare(strict_types=1);

use db\Migration;

final class CreatePostsTable extends Migration
{
    public function up()
    {
        $con = $this->capsule->connection();
        $con->transaction(function() use ($con) {
            $this->schema->create('posts', function(Illuminate\Database\Schema\Blueprint $table) {
                $table->increments('id');
                $table->integer("external_id")->nullable(true);
                $table->boolean("is_active")->default(false);
                $table->string("title");
                $table->text("description")->nullable(true);
                $table->integer("user_id");
                $table->unique("external_id");
                $table->foreign("user_id")->references('id')->on('users');
            });
        });
    }

    public function down()
    {
        $con = $this->capsule->connection();
        $con->transaction(function() use ($con) {
            $this->schema->drop('posts');
        });
    }
}