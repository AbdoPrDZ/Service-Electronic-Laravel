<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
  return $request->user();
});


Route::group([
  'prefix' => 'auth'
],
function($router) {
  Route::post('/login', [UserController::class, 'login']);
  Route::post('/signup', [UserController::class, 'signup']);
  Route::post('/email_verify', [UserController::class, 'emailVerify']);
  Route::get('/logout', [UserController::class, 'logout']);
  Route::post('/refresh', [UserController::class, 'refresh']);
  Route::get('/user', [UserController::class, 'index']);

  Route::post('/password_forgot', [UserController::class, 'passwordForgot']);
  Route::post('/password_forgot/email_verify', [UserController::class, 'pf_emailVerify']);
  Route::post('/password_forgot/password_reset', [UserController::class, 'pf_passwordReset']);
});


Route::get('/category', [CategoryController::class, 'all']);
Route::get('/currency', [CurrencyController::class, 'all']);

Route::group(['prefix' => 'transfer'], function ($router) {
  Route::get('', [TransferController::class, 'all']);
  Route::post('/create', [TransferController::class, 'create']);
});

Route::group(['prefix' => 'product'], function ($router) {
  Route::get('', [ProductController::class, 'all']);
  Route::get('/{id}', [ProductController::class, 'find']);
  Route::post('/{id}/edit', [ProductController::class, 'edit']);
  Route::post('/create', [ProductController::class, 'create']);
});

Route::get('/files/{filename}/', function(Request $request, $filename, $fileRow = null) {
  print_r($request->get('file')->name);
})->middleware('file.access:public');
