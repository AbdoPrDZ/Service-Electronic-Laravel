<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\File;
use App\Models\Transfer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

class AdminController extends Controller {

  public function __construct() {
    $this->middleware('multi.auth:admin', ['except' => [
      'login',
      'loginView',
    ]]);
  }

  public function index(Request $request) {
    return view('admin.index', [
    'admin' => $request->user(),
    ]);
  }

  public function loginView(Request $request) {
    // if(!is_null($request->user())) {
    //   return redirect()->route('admin.dashboard');
    // }
    return view('admin.login', [
      'email' => '',
      'password' => '',
      'invalidates' => []
    ]);
  }

  public function login(Request $request) {
    // if(!is_null($request->user())) {
    //   return redirect()->route('admin.dashboard');
    // }
    $validator = Validator::make($request->all(), [
      'email' => 'required|email',
      'password' => 'required|string|min:6',
    ]);
    if ($validator->fails()) {
      $messages = [];

      foreach ($validator->errors()->all() as $name => $error) {
      $messages[] = [
        'title' => 'Login Error',
        'name' => $name,
        'text' => $error,
        'type' => 'danger',
      ];
      }
      return view('admin.login', [
      'email' => $request->email,
      'password' => $request->password,
      'invalidates' => $validator->errors(),
      'messages' => $messages,
    ]);
    }

    // $admin = Admin::where('email', '=', $request->email)->first();

    // if(is_null($admin)) {
    //   return view('admin.login', [
    //     'email' => $request->email,
    //     'password' => '',
    //     'invalidates' => [
    //       'email' => 'Invalid email',
    //     ],
    //   ]);
    // } else {
      $token = Auth::guard('admin')-> attempt($validator->validated());
      if (!$token) {
        return view('admin.login', [
          'email' => $request->email,
          'password' => '',
          'invalidates' => [
            'email' => 'Invalid email',
            'password' => 'Invalid password',
          ],
          'messages' => [
          [
            'title' => 'Login error',
            'name' => 'password-error',
            'text' => 'Invalid  email or password',
            'type' => 'danger',
          ]
          ]
        ]);
      } else {
        return redirect()->route('admin.dashboard');
      }
    // }

  }
  public function logout() {
    session()->flush();
    // auth()->guard('admins')->logout();
    auth()->logout();
    return redirect()->route('admin.login');
  }

  public function loadValues(Request $request, $target) {
    $targets = [
      'users' => UserController::class,
      'transfers' => TransferController::class,
      'currencies' => CurrencyController::class,
      'categories' => CategoryController::class,
      'products' => ProductController::class,
    ];
    if(array_key_exists($target, $targets)) {
      return $targets[$target]::all($request);
    } else {
      return $this->apiErrorResponse('Invalid target');
    }
  }

  }
