<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
  /**
   * Register any application services.
   *
   * @return void
   */
  public function register() {
    if ($this->app->isLocal()) {
      $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
    }
  }

  /**
   * Bootstrap any application services.
   *
   * @return void
   */
  public function boot() {
    Sanctum::authenticateAccessTokensUsing(function (PersonalAccessToken $token, $isValid) {
      if($isValid) return true;
      return $token->can('remember') && $token->created_at->gt(now()->subMonths(1));
    });
  }
}
