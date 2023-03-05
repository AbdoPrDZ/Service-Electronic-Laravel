<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\ExchangeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\OfferRequestController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\UserController;
use App\Models\Notification;
use App\Models\Offer;
use App\Models\Product;
use App\Models\Purchase;
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

Route::group([
  'prefix' => 'auth'
], function($router) {
  Route::post('/login', [UserController::class, 'login']);
  Route::post('/signup', [UserController::class, 'signup']);
  Route::post('/email_verify', [UserController::class, 'emailVerify']);
  Route::post('/resend_verifiy_email', [UserController::class, 'resendEmailVerifiy']);

  Route::get('/logout', [UserController::class, 'logout']);
  Route::post('/refresh', [UserController::class, 'refresh']);
  Route::get('/accept_policies', [UserController::class, 'acceptPolicies']);
  Route::get('/user', [UserController::class, 'index']);
  Route::get('/find/{email}', [UserController::class, 'findUser']);
  Route::post('/update_messaging_token', [UserController::class, 'updateMessagingToken']);
  Route::post('/edit', [UserController::class, 'editProfile']);
  Route::post('/change_email', [UserController::class, 'changeEmail']);
  Route::post('/rp_check_password', [UserController::class, 'rp_check_password']);
  Route::post('/verify_identity', [UserController::class, 'verifyIdentity']);

  Route::post('/password_forgot', [UserController::class, 'passwordForgot']);
  Route::post('/password_forgot/email_verify', [UserController::class, 'pf_emailVerify']);
  Route::post('/password_forgot/password_reset', [UserController::class, 'pf_passwordReset']);
});

Route::group([
  'prefix' => 'notification',
  'middleware' => ['multi.auth:sanctum'],
], function ($router) {
  Route::get('/all', [NotificationController::class, 'all']);
  Route::get('/{id}/read', [NotificationController::class, 'all'])->middleware('valid_id:' . Notification::class);
});

Route::get('/category', [CategoryController::class, 'all']);
Route::get('/currency', [CurrencyController::class, 'all']);
Route::get('/offer', [OfferController::class, 'all']);
Route::get('/exchanges', [ExchangeController::class, 'all']);
Route::post('/send_mony', [ExchangeController::class, 'create']);

Route::group([
  'prefix' => 'transfer',
  'middleware' => ['multi.auth:sanctum'],
], function ($router) {
  Route::get('/{target}', [TransferController::class, 'all']);
  Route::post('/create', [TransferController::class, 'create']);
  Route::post('/recharge', [TransferController::class, 'createRecharge']);
  Route::post('/withdraw', [TransferController::class, 'createWithdraw']);
});

Route::group([
  'prefix' => 'seller',
  'middleware' => ['multi.auth:sanctum'],
], function ($router) {
  Route::post('/register', [SellerController::class, 'register']);
  Route::post('/edit', [SellerController::class, 'edit']);
});

Route::group([
  'prefix' => 'product',
  'middleware' => ['multi.auth:sanctum'],
], function ($router) {
  Route::get('', [ProductController::class, 'all']);
  Route::post('/create', [ProductController::class, 'create']);
  Route::post('/{id}/edit', [ProductController::class, 'edit'])->middleware('valid_id:' . Product::class);
  Route::delete('/{id}/delete', [ProductController::class, 'delete'])->middleware('valid_id:' . Product::class);
  Route::post('/{id}/rate', [ProductController::class, 'rate'])->middleware('valid_id:' . Product::class);
  Route::get('/{id}/like', [ProductController::class, 'like'])->middleware('valid_id:' . Product::class);
  Route::get('/{id}/unLike', [ProductController::class, 'unLike'])->middleware('valid_id:' . Product::class);
});

Route::group([
  'prefix' => 'purchase',
  'middleware' => ['multi.auth:sanctum'],
], function ($router) {
  Route::get('/seller/all', [PurchaseController::class, 'sellerAll']);
  Route::get('/user/all', [PurchaseController::class, 'userAll']);
  Route::post('/{product_id}/create', [PurchaseController::class, 'create'])->middleware('valid_id:' . Product::class . ',product_id');
  Route::get('/{id}/read', [PurchaseController::class, 'read'])->middleware('valid_id:' . Purchase::class);
  Route::post('/{id}/seller_answer', [PurchaseController::class, 'sellerAnswer'])->middleware('valid_id:' . Purchase::class);
  Route::get('/{id}/next_step', [PurchaseController::class, 'nextStep'])->middleware('valid_id:' . Purchase::class);
  Route::post('/{id}/client_answer', [PurchaseController::class, 'clientAnswer'])->middleware('valid_id:' . Purchase::class);
  Route::post('/{id}/seller_report', [PurchaseController::class, 'sellerReport'])->middleware('valid_id:' . Purchase::class);
});

Route::group([
  'prefix' => 'offer_request',
  'middleware' => ['multi.auth:sanctum'],
], function ($router) {
  Route::get('/all', [OfferRequestController::class, 'all']);
  Route::post('/{offer_id}/create', [OfferRequestController::class, 'create'])->middleware('valid_id:' . Offer::class . ',offer_id');
});
