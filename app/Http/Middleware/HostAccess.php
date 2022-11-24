<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HostAccess {
  /**
   * Handle an incoming request.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
   * @param array|string $hosts
   * @param array|string $ports
   * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
   */
  public function handle(Request $request, Closure $next, ...$hosts) {
    $requestHost = $request->getHttpHost();
    if(strpos($requestHost, ':')) {
      list($requestHost, $requestPort) = explode(':', $requestHost);
    }
    if(in_array($requestHost, $hosts)) {
      return $next($request);
    }
    return abort(404);
  }
}
