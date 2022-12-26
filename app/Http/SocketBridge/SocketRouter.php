<?php

namespace App\Http\SocketBridge;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Error;


class SocketRouter {

  protected $manager;

  public function __construct($manager) {
    $this->manager = $manager;
  }

  // public function router() {}

  // private function route($route) {
  //   // $route = new SocketRoute($this);
  //   $this->routes[] = $route;
  //   return $route;
  // }

  public function emitToFunction($class, $funcName, ...$args) {
    return $class::$funcName(...$args);
  }

  public function setupRoutes() {
    // include base_path('routes/socket.php');
    // $clients = config('socket_bridge.manager.clients');
    // foreach ($clients as $client) {
    //   include $client['routes_source'];
    // }
    include base_path(config('socket_bridge.manager.routes_source'));
    // foreach ($routes as $route) {
    //   print_r($route);
    // }
  }

  public function all() {
    return Cache::store('file')->get(config('socket_bridge.manager.cache.routes'));
  }

  public function routeExists($routeName) {
    return array_key_exists($routeName, $this->all());
  }

  public function find($routeName) {
    if($this->routeExists($routeName)) return $this->all()[$routeName];
    throw new Error('Invalid route '.$routeName);
  }

}
