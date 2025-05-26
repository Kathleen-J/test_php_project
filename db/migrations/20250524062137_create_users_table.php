<?php
declare(strict_types=1);

use db\Migration;

final class CreateUsersTable extends Migration
{
    public function up()
    {
        $con = $this->capsule->connection();
        $con->transaction(function() use ($con) {
            $this->schema->create(
                'users',
                function(Illuminate\Database\Schema\Blueprint $table) {
                    $table->increments('id');
                    $table->integer("external_id")->nullable(true);
                    $table->string("name");
                    $table->string("last_name");
                    $table->string("email");
                    $table->string("phone")->nullable(true);
                    $table->string("password");
                    $table->string("remember_token")->nullable(true);
                    $table->timestamp("created_at")->useCurrent();
                    $table->timestamp("updated_at")->useCurrent();
                    $table->boolean("is_admin")->default(false);
                    $table->unique("external_id");
                    $table->index('email');
                }
            );

            $con->statement(
                'CREATE UNIQUE INDEX unique_admin ON users (is_admin) WHERE is_admin = TRUE'
            );
        });
    }

    public function down()
    {
        $con = $this->capsule->connection();
        $con->transaction(function() use ($con) {
            $this->schema->drop('users');
        });
    }
}