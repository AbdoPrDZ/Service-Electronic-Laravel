<?php

namespace App\Http\SocketBridge;

class RoomSocketRoute {
  public $room;

  public $routes = [];

  public function __construct($room) {
    $this->room = $room;
  }

   /**
    *
    * @param string $prefix
    * @param mixed $setupRoutes
    * @return void
    */
  static function group(string $room, $setupRoutes) {
    $setupRoutes(new RoomSocketRoute($room));
  }

  /**
   * create
   * @param mixed $routeName
   * @param mixed $callback
   * @param mixed $targets
   * @return SocketRoute
   */
  public function on($routeName, $callback, $targets = ['*']) {
    $route = SocketRoute::on($this->room, $routeName, $callback, $targets);
    $this->routes[] = $route;
    return $route;
  }

}
