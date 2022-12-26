<?php

namespace App\Http\SocketBridgeNew;

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
   * @return SocketRouteNew
   */
  public function on($routeName, $callback, $targets = ['*']) {
    $route = SocketRouteNew::on($this->room, $routeName, $callback, $targets);
    $this->routes[] = $route;
    return $route;
  }

}
  