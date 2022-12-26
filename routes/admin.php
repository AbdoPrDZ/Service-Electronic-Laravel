<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CurrencyController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\SellerController;
use App\Http\Controllers\Admin\TransferController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AdminController::class, 'loginView'])->name('admin.login')->middleware('guest:admin');
Route::post('/login', [AdminController::class, 'login']);
Route::get('/logout', [AdminController::class, 'logout']);

Route::get('/', [AdminController::class, 'index'])->name('admin.dashboard');
Route::get('/load/{target}', [AdminController::class, 'loadTab']);
Route::get('/news/{target}', [AdminController::class, 'getNews']);
Route::get('/news/{target}/read', [AdminController::class, 'readNews']);

Route::get('/notifications/all', [NotificationController::class, 'all']);
Route::get('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);

Route::get('/user/{id}/delete', [UserController::class, 'delete']);
Route::post('/user/{id}/change_identity_status', [UserController::class, 'changeIdentityStatus']);

Route::post('/seller/{id}/change_status', [SellerController::class, 'changeStatus']);

Route::post('/transfer/{id}/change_status', [TransferController::class, 'changeStatus']);
Route::get('/transfer/{id}/delete', [TransferController::class, 'delete']);

Route::post('/currency/create_currency', [CurrencyController::class, 'create']);
Route::post('/currency/{id}/edit', [CurrencyController::class, 'edit']);
Route::get('/currency/{id}/delete', [CurrencyController::class, 'delete']);

Route::post('/category/create_category', [CategoryController::class, 'create']);
Route::post('/category/{id}/edit', [CategoryController::class, 'edit']);
Route::get('/category/{id}/delete', [CategoryController::class, 'delete']);
