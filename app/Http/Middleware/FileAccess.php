<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Controller;
use App\Models\File;
use Closure;
use ErrorException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use \Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Support\Facades\Log;

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
   * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
   */
  public function handle(Request $request, Closure $next) {
    $appId = $request->route()->parameter('appId');;
    $auth = config("file_access.clients.$appId.auth");
    if ($auth == false || $auth && $this->auth->guard(config("file_access.clients.$appId.guard"))->check()) {
      $file = File::find($request->filename);
      // try {
        if(is_null($file)) {
          return $this->throwResponse($request, 'Invalid file name', 404);
        } else if(in_array($file->disk, config("file_access.clients.$appId.access_disks"))) {
          $request->attributes->add(['file'=> $file]);
          return $next($request);
        } else {
          return $this->throwResponse($request, 'Unauthenticated File', 401);
        }
      // } catch (\Throwable $th) {
      //   Log::error('app id', [$appId, $th, $request]);
      // }
    }
    return $this->throwResponse($request, '1-Unauthenticated', 401);
  }

  /**
   * Handle an incoming request.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \Illuminate\Http\Request  $error
   * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
   */
  public function throwResponse(Request $request, string $error, $code = 404) {
    $wantsJson = !is_null($request->header('Accept')) && $request->header('Accept') == 'application/json';
    if($wantsJson) {
      return Controller::apiErrorResponse($error, [], $code);
    } else {
      return abort($code, $error);
    }
  }

}
