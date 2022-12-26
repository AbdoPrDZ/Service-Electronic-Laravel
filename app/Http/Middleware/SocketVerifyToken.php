<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Controller;
use App\Models\VerifyToken;
use Closure;
use Illuminate\Http\Request;

class SocketVerifyToken {
  /**
   * Handle an incoming request.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
   * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
   */
  public function handle(Request $request, Closure $next, $guard) {
    $token = VerifyToken::find($request->header('token'));
    if($token) {
      $token->linking();
      $user = $token->user;
      $request->merge(['user' => $user ]);
      $request->setUserResolver(function () use ($user) {
        return $user;
      });
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
