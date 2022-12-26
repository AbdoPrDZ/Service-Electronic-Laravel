<?php


namespace App\Http\SocketBridgeNew;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\ServiceProvider;

class SocketProviderNew extends ServiceProvider {

  /**
   * Bootstrap any application services.
   *
   * @return void
   */
  public function boot() {

    new SocketManagerNew(true);
  }
}
