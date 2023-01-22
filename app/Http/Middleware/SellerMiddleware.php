<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Controller;
use App\Models\Seller;
use Closure;
use Illuminate\Http\Request;

class SellerMiddleware {
  /**
   * Handle an incoming request.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
   * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
   */
  public function handle(Request $request, Closure $next) {
    $user = $request->user();
    if($user) {
      $user->linking();
      $seller = Seller::where('user_id', '=', $user->id)->first();
      if(is_null($seller)) {
        return Controller::apiErrorResponse('You are not seller');
      }
      if($user->identity_verifited_at == null) {
        return Controller::apiErrorResponse('You identity not verifited');
      }
      if($seller->status != 'accepted') {
        return Controller::apiErrorResponse('Your store not accepted');
      }
      return $next($request);
    }
    return Controller::apiErrorResponse('Unauthenticated');
  }
}
