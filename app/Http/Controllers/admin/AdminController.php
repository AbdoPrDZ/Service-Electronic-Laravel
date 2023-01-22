<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
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
    $admin = $request->user();
    $admin->linking();
    if(!$request->session()->exists('socketToken')) {
      $socketToken = Hash::make(csrf_token());
      VerifyToken::create([
        'token' => $socketToken,
        'user_id' => $admin->id,
        'model' => Admin::class,
        'code' => '',
      ]);
      $request->session()->put('socketToken', $socketToken);
    }

    return view('admin.index', [
      'admin' => $admin,
      'token' => $request->session()->get('token'),
      'socketToken' => $request->session()->get('socketToken'),
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

      foreach ($validator->errors()->toArray() as $name => $error) {
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
        'invalidates' => $validator->errors()->toArray(),
        'messages' => $messages,
      ]);
    }

    session()->flush();
    if (!Auth::guard('admin')->attempt($validator->validated())) {
      $request->session()->regenerate();
      return view('admin.login', [
        'email' => $request->email,
        'password' => '',
        'invalidates' => [
          'email' => ['Invalid email'],
          'password' => ['Invalid password'],
        ],
        'messages' => [
          [
            'title' => 'Login error',
            'name' => 'password-error',
            'text' => 'Invalid email or password',
            'type' => 'danger',
          ]
        ]
      ]);
    } else {
      return redirect()->route('admin.dashboard');
    }

  }

  public function logout(Request $request) {
    VerifyToken::find($request->session()->get('socketToken'))?->delete();
    session()->flush();
    // auth()->guard('admins')->logout();
    auth()->logout();
    return redirect()->route('admin.login');
  }

  public function loadTab(Request $request, $tabName) {
    $tabNames = [
      'users' => UserController::class,
      'sellers' => SellerController::class,
      'transfers' => TransferController::class,
      'currencies' => CurrencyController::class,
      'products' => ProductController::class,
      'purchases' => PurchaseController::class,
      'offers' => OfferController::class,
      'mails' => MailController::class,
    ];
    if(array_key_exists($tabName, $tabNames)) {
      return $tabNames[$tabName]::all($request);
    } else {
      return $this->apiErrorResponse("Invalid tab name [$tabName]");
    }
  }

}
