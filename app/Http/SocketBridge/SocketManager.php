<?php

namespace App\Http\SocketBridge;

use \App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

class SocketManager {

  protected $router;

  public function __construct($boot = false) {
    if(!Cache::store('file')->has(config('socket_bridge.manager.cache.routes')) || $boot) {
      Cache::store('file')->set(config('socket_bridge.manager.cache.routes'), []);
      $this->setupBridgeRoutes();
    }
    $this->router = new SocketRouter($this);
    $this->router->setupRoutes();
  }

  private function setupBridgeRoutes() {
    Route::group([
      'prefix' => config('socket_bridge.manager.path'),
      'middleware' => [config('socket_bridge.manager.auth.middleware'), 'host.access:localhost'],
      'except'=> ['test'],
    ], function($router) {
        Route::get(config('socket_bridge.manager.onConnect'), [$this, 'onClientConnect']);
        Route::get(config('socket_bridge.manager.onDisconnect'), [$this, 'onClientDisconnect']);
        Route::post(config('socket_bridge.manager.from_bridge'), [$this, 'onRoute']);
    });
  }

  public function onClientConnect(Request $request, $clientId) {
    $client = new SocketClient($clientId);
    if($this->router->routeExists('onConnect')) {
      $route = $this->router->find('onConnect');
      $request->request->add(['socketClientId' => $clientId]);
      $route->call($request, $client);
      if($request->user()) {
        $primary = app(config('socket_bridge.manager.client_driver'))->getKeyName();
        $clientId = $request->user()[$primary];
      }
    }
    return $this->getRoutes($request, $clientId);
  }

  public function onClientDisconnect(Request $request, $clientId) {
    if($this->router->routeExists('onDisconnect')) {
      $route = $this->router->find('onDisonnect');
      $route->call($request, $clientId);
    }
  }

  public function onRoute(Request $request, $clientId, $routeName) {
    $client = new SocketClient($clientId);
    return $this->router->find($routeName)->call($client, $request->args);
  }

  public function getRoutes(Request $request, $clientId) {
    $routes = $this->router->all();
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
