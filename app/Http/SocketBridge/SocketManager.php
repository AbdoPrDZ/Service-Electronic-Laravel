<?php

namespace App\Http\SocketBridge;

use \App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

class SocketManager extends Controller {

  protected $routers = [];

  public function __construct($boot = false) {
    $rooms = config('socket_bridge.manager.rooms');
    if(!Cache::store('file')->has(config('socket_bridge.manager.cache.routes'))) {
      Cache::store('file')->set(config('socket_bridge.manager.cache.routes'), []);
    }
    foreach ($rooms as $name => $room) {
      if($boot) {
        $this->setupBridgeRoutes($name);
      }
      $router = new SocketRouter($this, $name);
      $router->setupRoutes();
      $this->routers[$name] = $router;
    }
  }

  private function setupBridgeRoutes($room) {
    $middleware = config("socket_bridge.manager.rooms.$room.auth.middleware");
    Route::group([
      'prefix' => config('socket_bridge.manager.path') . "/$room",
      'middleware' => [$middleware, 'host.access:localhost'],
    ], function ($router) use ($room) {
      Route::get(config('socket_bridge.manager.onConnect'), function (Request $request, $clientId) use ($room) {
        return $this->onClientConnect($request, $room, $clientId);
      });
      Route::get(config('socket_bridge.manager.onDisconnect'), function (Request $request, $clientId) use ($room) {
        return $this->onClientDisconnect($request, $room, $clientId);
      });
      Route::post(config('socket_bridge.manager.from_bridge'), function (Request $request, $clientId, $routeName) use ($room) {
        return $this->onRoute($request, $room, $clientId, $routeName);
      });
    });
  }

  public function onClientConnect(Request $request, $room, $clientId) {
    $client = new SocketClient($clientId, $room);
    if($this->routers[$room]->routeExists('onConnect')) {
      $route = $this->routers[$room]->find('onConnect');
      $request->request->add(['socketClientId' => $clientId]);
      $route->call($request, $client);
      if($request->user()) {
        $primary = app(config("socket_bridge.manager.rooms.$room.client_driver"))->getKeyName();
        $clientId = $request->user()[$primary];
      }
    }
    return $this->getRoutes($request, $room, $clientId);
  }

  public function onClientDisconnect(Request $request, $room, $clientId) {
    if($this->routers[$room]->routeExists('onDisconnect')) {
      $route = $this->routers[$room]->find('onDisonnect');
      $route->call($request, $clientId);
    }
    return Controller::apiSuccessResponse('successfully disconnecting');
  }

  public function onRoute(Request $request, $room, $clientId, $routeName) {
    $client = new SocketClient($clientId, $room);
    return $this->routers[$room]->find($routeName)->call($client, ...$request->args);
  }

  public function getRoutes(Request $request, $room, $clientId) {
    $routes = $this->routers[$room]->all();
    $clientRoutes = [];
    foreach ($routes as $route) {
      if($route->routeName != 'onConnect' &&
        $route->routeName != 'onDisconnect' &&
        (in_array($clientId, $route->targets) || in_array('*', $route->targets))) {
        $clientRoutes[] = $route->routeName;
      }
    }
    return Controller::apiSuccessResponse('Successfully connecting', [
      'routes' => $clientRoutes,
      'clientId' => $clientId,
    ]);
  }

  public function __toString() {
    return 'App\Http\SocketBridge\SocketManager';
  }
}
