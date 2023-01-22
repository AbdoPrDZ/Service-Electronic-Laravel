<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Controller;
use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests;
use Illuminate\Http\Request;

class MultiAuth implements AuthenticatesRequests {
  /**
   * The authentication factory instance.
   *
   * @var \Illuminate\Contracts\Auth\Factory
   */
  protected $auth;

  /**
   * Create a new middleware instance.
   *
   * @param  \Illuminate\Contracts\Auth\Factory  $auth
   * @return void
   */
  public function __construct(Auth $auth) {
    $this->auth = $auth;
  }

  /**
   * Handle an incoming request.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \Closure  $next
   * @param  string  $guard
   * @return mixed
   *
   * @throws \Illuminate\Auth\AuthenticationException
   */
  public function handle(Request $request, Closure $next, $guard) {
    if ($this->auth->guard($guard)->check()) {
      $this->auth->shouldUse($guard);
      return $next($request);
    } else {
      if($guard == 'admin' && !$request->wantsJson()) {
        return redirect()->route('admin.login');
      } else {
        return Controller::apiErrorResponse('Unauthenticated');
      }
    }
  }
}
