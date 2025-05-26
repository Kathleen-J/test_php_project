<?php


namespace api\v1\middleWares;

use Illuminate\Http\Request;

class RequestCasting
{
  private $instance;

  /**
   * @param API
   * @return void
   */
  public function __construct($instance)
  {
    $this->instance = $instance;
  }

  public function __call($method, $arguments)
  {
    $parsedArgs = [];

    foreach ($arguments as $key => $value) {
      $parsedArgs[$key] = $value instanceof Request ? $value->toArray() : $value;
    }

    return $this->instance->$method(...$parsedArgs);
  }
}