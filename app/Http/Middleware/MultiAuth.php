<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Controller;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
   * @param  string[]  ...$guards
   * @return mixed
   *
   * @throws \Illuminate\Auth\AuthenticationException
   */
  public function handle(Request $request, Closure $next, $guard) {
    if ($this->auth->guard($guard)->check()) {
      $this->auth->shouldUse($guard);
      // if($guard == 'admin' && $request->route()->named('admin.login')) {
      //   return redirect()->route('admin.dashboard');
      // }
      return $next($request);
    } else {
      if ($guard == 'admin') {
        // throw new AuthenticationException(
        //   'Unauthenticated.',
        //   [$guard],
        //   route('admin.login'),
        // );
        return redirect()->route('admin.login');
      } else if ($guard == 'sanctum') {
        return Controller::apiErrorResponse('Unauthenticated');
      }
    }
  }
}
