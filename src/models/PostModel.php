<?php

namespace src\models;

use Illuminate\Database\Eloquent\Model;

class PostModel extends Model
{
  protected $connection = 'service';
  protected $table = 'posts';

  public $primaryKey = 'id';
  public $incrementing = true;
  public $fillable = [
    'id',
    'external_id',
    'is_active',
    'title',
    'description',
    'user_id'
  ];
  public $timestamps = false;

  public function users()
  {
    return $this->hasMany('src\models\UsersModel', 'id', 'user_id');
  }

  public function user()
  {
    return $this->belongsTo('src\models\UsersModel', 'user_id');
  }

  public function comments()
  {
    return $this->hasMany('src\models\CommentModel', 'post_id', 'id');
  }

  public function comment()
  {
    return $this->belongsTo('src\models\CommentModel', 'id');
  }

  public function scopeActive($query)
  {
    return $query->where('is_active', true);
  }
}