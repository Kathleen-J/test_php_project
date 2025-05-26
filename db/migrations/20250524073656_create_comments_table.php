<?php
declare(strict_types=1);

use db\Migration;

final class CreateCommentsTable extends Migration
{
    public function up()
    {
        $con = $this->capsule->connection();
        $con->transaction(function() use ($con) {
            $this->schema->create('comments', function(Illuminate\Database\Schema\Blueprint $table) {
                $table->increments('id');
                $table->integer("external_id")->nullable(true);
                $table->boolean("is_active")->default(false);
                $table->string("name");
                $table->string("email");
                $table->integer("post_id");
                $table->text("description");
                $table->timestamp("created_at")->useCurrent();
                $table->timestamp("updated_at")->useCurrent();
                $table->unique("external_id");
                $table->foreign("post_id")->references('id')->on('posts');
            });
        });
    }

    public function down()
    {
        $con = $this->capsule->connection();
        $con->transaction(function() use ($con) {
            $this->schema->drop('comments');
        });
    }
}