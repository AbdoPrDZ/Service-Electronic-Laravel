<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\TransferController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\FileController;
use App\Mail\TestEmail;
use Illuminate\Support\Facades\Route;
use Rap2hpoutre\LaravelLogViewer\LogViewerController;
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

Route::get('logs', [LogViewerController::class, 'index'])->middleware('host.access:localhost');

Route::get('/file/{appId}/{filename}', [FileController::class, 'find'])->middleware('file.access');
