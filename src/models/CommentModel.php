<?php

namespace src\models;

use Illuminate\Database\Eloquent\Model;

class CommentModel extends Model
{
  protected $connection = 'service';
  protected $table = 'comments';

  public $primaryKey = 'id';
  public $incrementing = true;
  public $fillable = [
    'id',
    'external_id',
    'is_active',
    'name',
    'email',
    'post_id',
    'description',
    'created_at',
    'updated_at'
  ];
  public $timestamps = false;

  public function posts()
  {
    return $this->hasMany('src\models\PostModel', 'id', 'post_id');
  }

  public function post()
  {
    return $this->belongsTo('src\models\PostModel', 'post_id');
  }

  public function scopeActive($query)
  {
    return $query->where('is_active', true);
  }
}