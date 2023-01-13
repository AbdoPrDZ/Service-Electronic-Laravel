<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ValidIdMiddleware {
  /**
   * Handle an incoming request.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
   * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
   */
  public function handle(Request $request, Closure $next, $model, $index = 'id') {
    $item = app($model)::find($request->route()->parameter($index));
    if(is_null($item)) {
      return abort(404, 'Invalid Id');
    }
    $request->route()->setParameter($index, $item);
    return $next($request);
  }
}
