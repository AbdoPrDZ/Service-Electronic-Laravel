<?php


namespace App\Http\SocketBridge;

use Illuminate\Support\ServiceProvider;

class SocketProvider extends ServiceProvider {

  /**
   * Bootstrap any application services.
   *
   * @return void
   */
  public function boot() {

    new SocketManager(true);
  }
}
