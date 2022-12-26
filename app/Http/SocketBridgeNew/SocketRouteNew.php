<?php

namespace App\Http\SocketBridgeNew;

use App\Http\Kernel;
use App\Http\Middleware\MultiAuth;
use GuzzleHttp\Middleware;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Error;

class SocketRouteNew {

  public $room;

  public $routeName;

  private $class;

  private $funcName;

  public $targets = [];

  private $middlewares = [];

  public function __construct($room, $routeName, $class, $funcName, $targets = [], $middlwares = []) {
    $this->room = $room;
    $this->routeName = $routeName;
    $this->class = $class;
    $this->funcName = $funcName;
    $this->targets = $targets;
    // $this->middlewares = $middlwares;
  }

   /**
    * Summary of on
    * @param mixed $room
    * @param mixed $routeName
    * @param mixed $callback
    * @param mixed $targets
    * @throws Error
    * @return SocketRouteNew
    */
  static function on($room, $routeName, $callback, $targets = ['*']) {
    $class = null;
    $funcName = null;
    // $defualtArgs = [];
    if(is_array($callback)) {
      list($class, $funcName) = $callback;
      // $class = $target[0];
      // $target = $target[1];
    } else if(is_string($callback) && strpos($callback, '@')) {
      list($class, $funcName) = explode('@', $callback);
      $class = app('App\\Http\\Controllers\\'.$class);
    } else if(is_callable($callback)) {
      throw new Error('Invalid target');
      // $class = SocketRoute::class;
      // $funcName = 'toFunction';
      // $defualtArgs[] = $target;
    } else {
      throw new Error('Invalid target ' . gettype($callback));
    }
    if(!is_string($funcName) || !method_exists($class, $funcName)) {
      throw new Error('Invalid function ' . $callback);
    }
    $routes = Cache::store('file')->get(config('socket_bridge_new.manager.cache.routes'));
    if (!array_key_exists($room, $routes)) $routes[$room] = [];
    $routes[$room][$routeName] = new SocketRouteNew($room, $routeName, $class, $funcName, $targets);
    Cache::store('file')->put(config('socket_bridge_new.manager.cache.routes'), $routes);
    return $routes[$room][$routeName];
  }

  public function middleware($middlware) {
    if(is_string($middlware)) {
      $middlware = [$middlware];
    }
    $this->middlewares = $middlware;
  }

  // static function toFunction($function, ...$args) {
  //   $function(...$args);
  // }

  public function call($request, $client, ...$args) {
    // $gotoNext = true;
    // $check = function($targetParrent, $targetFunc) {
    //   global $request, $gotoNext;
    //   $success = function($_request) {
    //     global $request;
    //     $request = $_request;
    //   };
    //   try {
    //     $targetParrent->{$targetFunc}($request, $success);
    //   } catch (\Throwable $th) {
    //     $gotoNext = false;
    //   }
    // };
    // $next = function($request) {
    //   global $client, $args;
      return (new $this->class())->{$this->funcName}($request, $client, ...$args);
    // };
    // foreach ($this->middlewares as $middleware) {
    //   if(is_string($middleware) && isset(Kernel::$routeMiddleware[$middleware])) {
    //     $middlName = $middleware;
    //     $guards = [];
    //     if (strpos($middleware, ':')) {
    //       $items = explode($middleware, ':');
    //       $middlName = $items[0];
    //       unset($items[0]);
    //       $guards = $items;
    //     }
    //     return (new Kernel::$routeMiddleware[$middlName](auth()))->handle($request, $next);
    //   } else if(is_callable($middleware)) {
    //     return $middleware($request, $next);
    //   } else if(is_object($middleware)) {
    //     return (new $middleware())->handle($request, $next);
    //   }
    // }
  }

}

