<?php

namespace src\models;

use Illuminate\Database\Eloquent\Model;

class UsersModel extends Model
{
  protected $connection = "service";
  protected $table = "users";

  public $primaryKey = "id";
  public $incrementing = true;
  public $fillable = ["id", "external_id", "name", "last_name", "email", "phone", "password", "remember_token", "created_at", "updated_at", "is_admin"];
  public $timestamps = false;

  public function posts()
  {
    return $this->hasMany("src\models\Post", "user_id", "id");
  }
}