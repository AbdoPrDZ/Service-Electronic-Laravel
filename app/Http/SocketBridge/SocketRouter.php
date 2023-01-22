<?php

namespace App\Http\SocketBridge;

use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Error;


class SocketRouter {

  protected $manager;

  protected $room;

  public function __construct($manager, $room) {
    $this->manager = $manager;
    $this->room = $room;
  }

  public function emitToFunction($class, $funcName, ...$args) {
    return $class::$funcName(...$args);
  }

  public function setupRoutes() {
    include base_path(config("socket_bridge.manager.rooms.$this->room.routes_source"));
  }

  public function all() {
    return Cache::store('file')->get(config('socket_bridge.manager.cache.routes'))[$this->room];
  }

  public function routeExists($routeName) {
    return array_key_exists($routeName, $this->all());
  }

  public function find($routeName) {
    if($this->routeExists($routeName)) return $this->all()[$routeName];
    throw new Error('Invalid route '.$routeName);
  }

}
