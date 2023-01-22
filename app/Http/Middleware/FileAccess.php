<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use \Illuminate\Contracts\Auth\Factory as Auth;

class FileAccess {
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
   * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
   * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\StreamedResponse
   */
  public function handle(Request $request, Closure $next) {
    $appId = $request->route()->parameter('appId');;
    $auth = config("file_access.clients.$appId.auth");
    if ($auth == false || $auth && $this->auth->guard(config("file_access.clients.$appId.guard"))->check()) {
      return $next($request);
    }
    return abort(401);
  }

}
