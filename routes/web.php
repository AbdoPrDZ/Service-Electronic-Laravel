<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\TransferController;
use App\Http\Controllers\FileController;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function (Request $request) {
  return view('welcome');
});
Route::get('logs', [\Rap2hpoutre\LaravelLogViewer\LogViewerController::class, 'index'])->middleware('host.access:localhost');

Route::group(['prefix' => 'admin'],
  function ($router) {
  Route::get('/login', [AdminController::class, 'loginView'])->name('admin.login');
  Route::post('/login', [AdminController::class, 'login']);
  Route::get('/logout', [AdminController::class, 'logout']);
  // Route::post('/refresh', [AdminController::class, 'refresh']);

  // Route::post('/password_forgot', [UserController::class, 'passwordForgot']);
  // Route::post('/password_forgot/email_verify', [UserController::class, 'pf_emailVerify']);
  // Route::post('/password_forgot/password_reset', [UserController::class, 'pf_passwordReset']);
  Route::get('/', [AdminController::class, 'index'])->name('admin.dashboard');
  Route::get('/load/{target}', [AdminController::class, 'loadValues']);
  Route::post('/transfer/change_status', [TransferController::class, 'changeStatus']);
});

Route::get('/file/{filename}', [FileController::class, 'find']);

Route::get('/files/{filename}/', function(Request $request, $filename) {
  echo 'success';
})->middleware('file.access');

