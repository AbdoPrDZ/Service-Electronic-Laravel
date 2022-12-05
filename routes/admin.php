<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\CurrencyController;
use App\Http\Controllers\Admin\TransferController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;


Route::get('/login', [AdminController::class, 'loginView'])->name('admin.login')->middleware('guest:admin');
Route::post('/login', [AdminController::class, 'login']);
Route::get('/logout', [AdminController::class, 'logout']);

Route::get('/', [AdminController::class, 'index'])->name('admin.dashboard');
Route::get('/load/{target}', [AdminController::class, 'loadValues']);
Route::get('/user/{id}/delete', [UserController::class, 'delete']);
Route::post('/transfer/{id}/change_status', [TransferController::class, 'changeStatus']);
Route::post('/currency/create_currency', [CurrencyController::class, 'create']);
Route::get('/currency/{id}/delete', [CurrencyController::class, 'delete']);
