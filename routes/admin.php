<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CurrencyController;
use App\Http\Controllers\Admin\MailController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\OfferController;
use App\Http\Controllers\Admin\OfferRequestController;
use App\Http\Controllers\Admin\PurchaseController;
use App\Http\Controllers\Admin\SellerController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\TemplateController;
use App\Http\Controllers\Admin\TransferController;
use App\Http\Controllers\Admin\UserController;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Mail;
use App\Models\Notification;
use App\Models\Offer;
use App\Models\OfferRequest;
use App\Models\Purchase;
use App\Models\Seller;
use App\Models\Template;
use App\Models\Transfer;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AdminController::class, 'loginView'])->name('admin.login')->middleware('guest:admin');
Route::post('/login', [AdminController::class, 'login']);
Route::get('/logout', [AdminController::class, 'logout']);

Route::get('/', [AdminController::class, 'index'])->name('admin.dashboard');
Route::get('/load/{tabName}', [AdminController::class, 'loadTab']);

Route::get('/notifications/all', [NotificationController::class, 'all']);
Route::get('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->middleware('valid_id:' . Notification::class);

Route::group([
  'prefix' => 'user',
  'middleware' => ['multi.auth:admin', 'valid_id:' . User::class],
], function ($router) {
  Route::delete('/{id}/delete', [UserController::class, 'delete']);
  Route::post('/{id}/change_identity_status', [UserController::class, 'changeIdentityStatus']);
  Route::post('/{id}/send_notification', [UserController::class, 'sendNotification']);
  Route::get('/{id}/details', [UserController::class, 'details']);
});

Route::group([
  'prefix' => 'setting',
  'middleware' => ['multi.auth:admin'],
], function ($router) {
  Route::get('', [SettingsController::class, 'index']);
  Route::post('edit', [SettingsController::class, 'edit']);
});

Route::post('/seller/{id}/change_status', [SellerController::class, 'changeStatus'])->middleware('valid_id:' . Seller::class);

Route::post('/transfer/{id}/change_status', [TransferController::class, 'changeStatus'])->middleware('valid_id:' . Transfer::class);
Route::delete('/transfer/{id}/delete', [TransferController::class, 'delete'])->middleware('valid_id:' . Transfer::class);

Route::post('/currency/create_currency', [CurrencyController::class, 'create']);
Route::post('/currency/{id}/edit', [CurrencyController::class, 'edit'])->middleware('valid_id:' . Currency::class);
Route::delete('/currency/{id}/delete', [CurrencyController::class, 'delete'])->middleware('valid_id:' . Currency::class);

Route::post('/category/create_category', [CategoryController::class, 'create']);
Route::post('/category/{id}/edit', [CategoryController::class, 'edit'])->middleware('valid_id:' . Category::class);
Route::delete('/category/{id}/delete', [CategoryController::class, 'delete'])->middleware('valid_id:' . Category::class);

Route::post('/purchase/{id}/ansower', [PurchaseController::class, 'ansower'])->middleware('valid_id:' . Purchase::class);

Route::group([
  'prefix' => 'offer',
  'middleware' => ['multi.auth:admin'],
], function ($router) {
  Route::get('/all', [OfferController::class, 'all']);
  Route::post('/create', [OfferController::class, 'create']);
  Route::post('/{id}/edit', [OfferController::class, 'edit'])->middleware('valid_id:' . Offer::class);
  Route::delete('/{id}/delete', [OfferController::class, 'delete'])->middleware('valid_id:' . Offer::class);
});
Route::group([
  'prefix' => 'offer_request',
  'middleware' => ['multi.auth:admin'],
], function ($router) {
  Route::post('/{id}/ansower', [OfferRequestController::class, 'ansower'])->middleware('valid_id:' . OfferRequest::class);
  Route::delete('/{id}/delete', [OfferRequestController::class, 'delete'])->middleware('valid_id:' . OfferRequest::class);
});
Route::group([
  'prefix' => 'template',
  'middleware' => ['multi.auth:admin'],
], function ($router) {
  Route::post('/create', [TemplateController::class, 'create']);
  Route::post('/{id}/edit', [TemplateController::class, 'edit'])->middleware('valid_id:' . Template::class);
  Route::delete('/{id}/delete', [TemplateController::class, 'delete'])->middleware('valid_id:' . Template::class);
});

Route::group([
  'prefix' => 'mail',
  'middleware' => ['multi.auth:admin'],
], function ($router) {
  Route::post('/create', [MailController::class, 'create']);
  Route::post('/delete_mails', [MailController::class, 'deleteItems']);
  Route::delete('/{id}/delete', [MailController::class, 'delete'])->middleware('valid_id:' . Mail::class);
});

