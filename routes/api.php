<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\ExchangeController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SellerController;
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
  Route::post('/edit', [UserController::class, 'editProfile']);
  Route::post('/change_email', [UserController::class, 'changeEmail']);
  Route::post('/rp_check_password', [UserController::class, 'rp_check_password']);
  Route::post('/verify_identity', [UserController::class, 'verifyIdentity']);

  Route::post('/password_forgot', [UserController::class, 'passwordForgot']);
  Route::post('/password_forgot/email_verify', [UserController::class, 'pf_emailVerify']);
  Route::post('/password_forgot/password_reset', [UserController::class, 'pf_passwordReset']);
});

Route::get('/category', [CategoryController::class, 'all']);
Route::get('/currency', [CurrencyController::class, 'all']);
Route::post('/send_mony', [ExchangeController::class, 'create']);

Route::group(['prefix' => 'transfer'], function ($router) {
  Route::get('', [TransferController::class, 'all']);
  Route::post('/create', [TransferController::class, 'create']);
  Route::post('/recharge', [TransferController::class, 'createRecharge']);
  Route::post('/withdraw', [TransferController::class, 'createWithdraw']);
});

Route::group(['prefix' => 'seller'], function ($router) {
  // Route::get('/', [SellerController::class, 'all']);
  Route::post('/register', [SellerController::class, 'register']);
  Route::post('/edit', [SellerController::class, 'edit']);
});

Route::group(['prefix' => 'product'], function ($router) {
  Route::get('', [ProductController::class, 'all']);
  // Route::get('/{id}', [ProductController::class, 'find']);
  Route::post('/{id}/edit', [ProductController::class, 'edit']);
  Route::get('/{id}/like', [ProductController::class, 'like']);
  Route::get('/{id}/unLike', [ProductController::class, 'unLike']);
  Route::post('/{id}/rate', [ProductController::class, 'rate']);
  Route::post('/create', [ProductController::class, 'create']);
});

Route::group([
  'prefix' => 'purchase',
  'middleware' => ['multi.auth:sanctum'],
],
function ($router) {
  Route::get('/seller/all', [PurchaseController::class, 'sellerAll']);
  Route::get('/user/all', [PurchaseController::class, 'userAll']);
  Route::post('/{product_id}/create', [PurchaseController::class, 'create']);
  Route::get('/{id}/read', [PurchaseController::class, 'read']);
  Route::post('/{id}/ansower', [PurchaseController::class, 'ansower']);
  Route::get('/{id}/next_step', [PurchaseController::class, 'nextStep']);
  Route::post('/{id}/client_ansower', [PurchaseController::class, 'clientAnsower']);
  Route::post('/{id}/client_absent', [PurchaseController::class, 'clientAbsent']);
});

// Route::get('/files/{filename}/', function(Request $request, $filename, $fileRow = null) {
//   print_r($request->get('file')->name);
// })->middleware('file.access:public');
