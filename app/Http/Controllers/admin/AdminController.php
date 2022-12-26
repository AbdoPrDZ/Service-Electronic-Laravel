<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Currency;
use App\Models\Mail;
use App\Models\Notification;
use App\Models\Product;
use App\Models\Seller;
use App\Models\Transfer;
use App\Models\User;
use App\Models\VerifyToken;
use Hash;
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
    if(!$request->session()->exists('socketToken')) {
      $socketToken = Hash::make(csrf_token());
      VerifyToken::create([
        'token' => $socketToken,
        'user_id' => $request->user()->id,
        'model' => Admin::class,
        'code' => '',
      ]);
      $request->session()->put('socketToken', $socketToken);
    }
    $user = $request->user();
    return view('admin.index', [
      'admin' => $user,
      'token' => $request->session()->get('token'),
      'socketToken' => $request->session()->get('socketToken'),
      'notifications' => Notification::allUnreaded($user->id),
    ]);
  }

  public function loginView(Request $request) {
    return view('admin.login', [
      'email' => '',
      'password' => '',
      'invalidates' => []
    ]);
  }

  public function login(Request $request) {
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

    if (!Auth::guard('admin')->attempt($validator->validated())) {
      $request->session()->regenerate();
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

  }
  public function logout() {
    session()->flush();
    // auth()->guard('admins')->logout();
    auth()->logout();
    return redirect()->route('admin.login');
  }

  public function loadTab(Request $request, $target) {
    $targets = [
      'users' => UserController::class,
      'sellers' => SellerController::class,
      'transfers' => TransferController::class,
      'currencies' => CurrencyController::class,
      'products' => ProductController::class,
      'purchases' => PurchaseController::class,
      'mails' => MailController::class,
      // 'settings' => SettingsController::class,
    ];
    if(array_key_exists($target, $targets)) {
      return $targets[$target]::all($request);
    } else {
      return $this->apiErrorResponse("Invalid target [$target]");
    }
  }

  public function getNews(Request $request, $target) {
    $targets = [
      'users' => UserController::class,
      'sellers' => SellerController::class,
      'transfers' => TransferController::class,
      'currencies' => CurrencyController::class,
      'products' => ProductController::class,
      'purchases' => PurchaseController::class,
      'mails' => MailController::class,
      // 'settings' => SettingsController::class,
    ];
    if(array_key_exists($target, $targets)) {
      try {
        return $targets[$target]::news($request);
      } catch (\Throwable $th) {
        return $this->apiErrorResponse("Invalid target [$target]");
      }
    } else {
      return $this->apiErrorResponse("Invalid target [$target]");
    }
  }

  public function readNews(Request $request, $target) {
    $user = $request->user();
    $targets = [
      'users' => UserController::class,
      'sellers' => SellerController::class,
      'transfers' => TransferController::class,
      'currencies' => CurrencyController::class,
      'products' => ProductController::class,
      'purchases' => PurchaseController::class,
      'mails' => MailController::class,
      // 'settings' => SettingsController::class,
    ];
    if(array_key_exists($target, $targets)) {
      try {
        return $targets[$target]::readNews($request);
      } catch (\Throwable $th) {
        return $this->apiErrorResponse("Invalid target [$target]");
      }
    } else {
      return $this->apiErrorResponse("Invalid [$target]");
    }
  }

}
