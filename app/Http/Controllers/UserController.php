<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\File;
use App\Models\Mail;
use App\Models\Setting;
use App\Models\User;
use App\Models\VerifyToken;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Str;
use Validator;

class UserController extends Controller {

  public function __construct() {
    $this->middleware('multi.auth:sanctum', ['except' => [
      'login',
      'signup',
      'emailVerify',
      'passwordForgot',
      'pf_emailVerify',
      'pf_passwordReset',
    ]]);
  }

  public function login(Request $request) {
    $validator = Validator::make($request->all(), [
      'email' => 'required|email',
      'password' => 'required|string',
    ]);

    if ($validator->fails()) {
      return $this->apiErrorResponse('has errors', [
        'errors' => $validator->errors(),
      ]);
    }

    $user = User::where('email', '=', $request->email)->first();
    if(!is_null($user)) {
      if(is_null($user->email_verified_at)) {
        return $this->apiErrorResponse('Your email is not verifited.', [
          'errors' => ['email' => 'Your email is not verifited']
        ]);
      }
      $authed = Auth::attempt($validator->validated());
      if($authed) {
        $token = $request->user()->createToken('access.token', ['remember'])->plainTextToken;
        $socketToken = $request->user()->createToken('socket.access.token', ['socket_token'])->plainTextToken;
        VerifyToken::create([
          'token' => $socketToken,
          'user_id' => $request->user()->id,
          'model' => User::class,
          'code' => '',
        ]);
        return $this->apiSuccessResponse('Successfully login', [
          'token' => $token,
          'socket.token' => $socketToken,
        ]);
      } else {
        return $this->apiErrorResponse('Invalid password', [
          'errors' => [
            'password' => 'Invalid Password',
          ]
        ]);
      }
    } else {
      return $this->apiErrorResponse('Invalid email', [
        'errors' => [
          'email' => 'Invalid email'
        ]
      ]);
    }
  }

  public function signup(Request $request) {
    $validator = Validator::make($request->all(), [
      'firstname' => 'required|string|between:2,100',
      'lastname' => 'required|string|between:2,100',
      'email' => 'required|string|email|max:100',
      'phone' => 'required|string',
      'password' => 'required|string|min:6',
      'messaging_token' => 'required|string',
    ]);
    if($validator->fails()){
      return $this->apiErrorResponse(null, [
        'errors' => $validator->errors(),
      ]);
    }

    $email = User::where('email', '=', $request->email)->first();
    $phone = User::where('phone', '=', $request->phone)->first();
    if(!is_null($email)) {
      return $this->apiErrorResponse('You already registred please go to login', [
        'errors' => ['email' => 'You already registred please go to login']
      ]);
    }
    if(!is_null($phone)) {
      return $this->apiErrorResponse('You already registred please go to login', [
        'errors' => ['phone' => 'You already registred please go to login']
      ]);
    }

    $user = User::create([
      'firstname' => $request->firstname,
      'lastname' => $request->lastname,
      'email' => $request->email,
      'phone' => $request->phone,
      'password' => bcrypt($request->password),
      'messaging_token' => $request->messaging_token,
      'settings' => [
        'theme' => 'Light',
        'languange' => 'En',
      ],
      'unreades' => Admin::unreades(),
    ]);

    $verifyCode = random_int(100000, 999999);
    $token = Str::random(64);
    VerifyToken::create([
      'token' => $token,
      'user_id' => $user->id,
      'code' => $verifyCode,
    ]);

    Mail::create([
      'title' => 'Email Verification',
      'template_id' => Setting::emailVerificationTemplateId(),
      'data' => ['<-user->' => $user->fullname, '<-code->' => $verifyCode],
      'targets' => [$request->email]
    ]);

    return $this->apiSuccessResponse("User successfully registered code", [
      'token' => $token,
      'user_id' => $user->id,
    ]);
  }

  public function emailVerify(Request $request) {
    $validator = Validator::make($request->all(), [
      'user_id' => 'required|integer',
      'token' => 'required|string',
      'code' => 'required|string|max:6',
    ]);
    if ($validator->fails()) {
      return $this->apiErrorResponse(null, [
        'errors' => $validator->errors(),
      ]);
    }

    $token = VerifyToken::where('token', '=', $request->token)->first();
    $user = User::where('id', '=', $request->user_id)->first();
    if(!is_null($token) && !is_null($user) && $token->used_at == null && $token->code == $request->code) {
      if($user->email_verified_at != null) {
        return $this->apiErrorResponse('This email already verifited', [
          'errors' => ['email' => 'This email already verifited']
        ]);
      }
      $user->update([
        'email_verified_at' => Carbon::now(),
      ]);
      $token->update([
        'used_at' => Carbon::now(),
      ]);

      if($user->wallet_id == null) {
        $walletId = bin2hex('w-' . date_format(Carbon::now(), 'yyyy-MM-dd HH:mm:ss') . "-u-$user->id");
        $wallet = Wallet::create([
          'id' => $walletId,
          'user_id' => $user->id,
          'user_model' => User::class,
          'balance' => 0,
          'status' => 'active',
          'answored_at' => Carbon::now(),
        ]);
        $user->wallet_id = $wallet->id;
      } else {
        $wallet = Wallet::find($user->wallet_id);
        $wallet->status = 'active';
        $wallet->save();
      }
      $user->save();
      return $this->apiSuccessResponse('User successfully verifing email');
    } else {
      return $this->apiErrorResponse('Invalid code', [
        'errors' => ['code' => 'Invalid code']
      ]);
    }
  }

  public function passwordForgot(Request $request) {
    $validator = Validator::make($request->all(), [
      'email' => 'required|email',
    ]);
    if ($validator->fails()) {
      return $this->apiErrorResponse(null, [
        'errors' => $validator->errors(),
      ]);
    }
    $user = User::where('email', '=', $request->email)->first();
    if(!is_null($user)) {
      if(is_null($user->email_verified_at)) {
        return $this->apiErrorResponse('Email not verfited, please verify your email', [
          'errors' => ['email' => 'Email not verfited, please verify your email']
        ]);
      }
      $user->linking();
      // send email
      $verifyCode = random_int(100000, 999999);
      $token = Str::random(64);
      VerifyToken::create([
        'token' => $token,
        'user_id' => $user->id,
        'code' => $verifyCode,
      ]);
      Mail::create([
        'title' => 'Email Verification',
        'template_id' => Setting::emailVerificationTemplateId(),
        'data' => ['<-code->' => $verifyCode, '<-user->' => $user->fullname],
        'targets' => [$request->email]
      ]);
      return $this->apiSuccessResponse("Successfully verifing email code", [
        'token' => $token,
        'user_id' => $user->id,
      ]);
    } else {
      return $this->apiErrorResponse('Invalid email', [
        'errors' => ['email' => 'Invalid email']
      ]);
    }
  }

  public function pf_emailVerify(Request $request) {
    $validator = Validator::make($request->all(), [
      'user_id' => 'required|integer',
      'token' => 'required|string',
      'code' => 'required|string|max:6',
    ]);
    if ($validator->fails()) {
      return $this->apiErrorResponse(null, [
        'errors' => $validator->errors(),
      ]);
    }

    $user = User::where('id', '=', $request->user_id)->first();
    if(is_null($user->email_verified_at)) {
      return $this->apiErrorResponse('Your email is not verifited', [
        'errors' => ['email' => 'Your email is not verifited']
      ]);
    }

    $token = VerifyToken::where('token', '=', $request->token)->first();
    if(!is_null($token) && !is_null($user) && $token->used_at == null && $token->code == $request->code) {
      $token->update([
      'used_at' => Carbon::now(),
      ]);
      $token = Str::random(64);
      VerifyToken::create([
        'token' => $token,
        'user_id' => $user->id,
        'code' => '',
      ]);
      return $this->apiSuccessResponse('Successfully verifing email', [
        'token' => $token,
        'user_id' => $user->id,
      ]);
    } else {
      return $this->apiErrorResponse('Invalid code', [
        'errors' => ['code' => 'Invalid code']
      ]);
    }
  }

  public function pf_passwordReset(Request $request) {
    $validator = Validator::make($request->all(), [
      'user_id' => 'required|integer',
      'token' => 'required|string',
      'new_password' => 'required|string|min:6',
    ]);
    if ($validator->fails()) {
      return $this->apiErrorResponse(null, [
        'errors' => $validator->errors(),
      ]);
    }

    $user = User::where('id', '=', $request->user_id)->first();
    if(is_null($user->email_verified_at)) {
      return $this->apiErrorResponse('Your email is not verifited.', [
        'errors' => ['email' => 'Your email is not verifited']
      ]);
    }

    $token = VerifyToken::where('token', '=', $request->token)->first();
    if(!is_null($token) && !is_null($user) && $token->used_at == null) {
      $token->update([
      'used_at' => Carbon::now(),
      ]);
      $user->update([
        'password' => bcrypt($request->new_password)
      ]);
      return $this->apiSuccessResponse('Successfully reset password');
    } else {
      return $this->apiErrorResponse('Invalid token');
    }
  }

  public function verifyIdentity(Request $request) {
    $validator = Validator::make($request->all(), [
      'identity_image' => 'required|file|mimes:png,jpeg,jpg,gif|max:4048',
      'address_image' => 'required|file|mimes:png,jpeg,jpg,gif|max:4048',
    ]);
    if ($validator->fails()) {
      return $this->apiErrorResponse(null, [
        'errors' => $validator->errors(),
      ]);
    }

    $user = $request->user();
    if(is_null($user->email_verified_at)) {
      return $this->apiErrorResponse('Your email is not verifited.', [
        'errors' => ['email' => 'Your email is not verifited']
      ]);
    }

    if(!Storage::disk('api')->exists('users_data')) {
      Storage::disk('api')->makeDirectory('users_data');
    }
    $userFilesPath = 'users_data/' . $user->id;
    if(!Storage::disk('api')->exists($userFilesPath)) {
      Storage::disk('api')->makeDirectory($userFilesPath);
      Storage::disk('api')->makeDirectory("$userFilesPath/identity_verifications");
    }
    $dirPath = "$userFilesPath/identity_verifications/";

    $images = [];
    $request->file('identity_image')->move(Storage::disk('api')->path($dirPath), 'vi');
    $name = "u-" . $user->id . '-vi';
    File::updateOrCreate(['name' => $name], [
      'disk' => 'api',
      'type' => 'image',
      'path' => "$dirPath/vi",
    ]);
    $images[] = $name;
    $request->file('address_image')->move(Storage::disk('api')->path($dirPath), 'va');
    $name = "u-" . $user->id . '-va';
    File::updateOrCreate(['name' => $name], [
      'disk' => 'api',
      'type' => 'image',
      'path' => "$dirPath/va",
    ]);
    $images[] = $name;

    $user->verification_images_ids = $images;
    $user->identity_status = 'checking';
    $user->save();

    return $this->apiSuccessResponse('Your Verify identity in progress.');
  }

  public function editProfile(Request $request) {
    $validator = Validator::make($request->all(), [
      'firstname' => 'string',
      'lastname' => 'string',
      'phone' => 'numeric',
      'messaging_token' => 'string',
      'profile_image' => 'file|mimes:jpg,png,jpeg',
    ]);
    if ($validator->fails()) {
      return $this->apiErrorResponse(null, [
        'messages' => $validator->errors(),
      ]);
    }

    $user = $request->user();
    if(is_null($user->email_verified_at)) {
      return $this->apiErrorResponse('Your email is not verifited.', [
        'errors' => ['email' => 'Your email is not verifited']
      ]);
    }

    $user->firstname = $request->firstname ?? $user->firstname;
    $user->lastname = $request->lastname ?? $user->lastname;
    $user->phone = $request->phone ?? $user->phone;
    $user->messaging_token = $request->messaging_token ?? $user->messaging_token;

    if($request->file('profile_image')) {
      if(!Storage::disk('api')->exists('users_data')) {
        Storage::disk('api')->makeDirectory('users_data');
      }
      $userFilesPath = 'users_data/'.$request->user()->id;
      if(!Storage::disk('api')->exists($userFilesPath)) {
        Storage::disk('api')->makeDirectory($userFilesPath);
      }
      $allpath = Storage::disk('api')->path("$userFilesPath");
      $shortPath = "$userFilesPath/pi";
      if(Storage::exists("$allpath/pi")) {
        Storage::delete("$allpath/pi");
      }
      $request->file('profile_image')->move($allpath, "pi");
      File::updateOrCreate(['name' => 'u-'.$request->user()->id.'-pi'],[
        'disk' => 'api',
        'type' => 'image',
        'path' => $shortPath,
      ]);
      $user->profile_image_id = "u-".$request->user()->id."-pi";
    }
    $user->save();

    return $this->apiSuccessResponse('Successfully editing profile data');
  }

  public function changeEmail(Request $request) {
    $validator = Validator::make($request->all(), [
      'new_email' => 'required|email',
    ]);
    if ($validator->fails()) {
      return $this->apiErrorResponse(null, [
        'messages' => $validator->errors(),
      ]);
    }

    $user = User::whereEmail($request->new_email)->first();
    if(!is_null($user) && $user->email_verified_at != null) {
      return $this->apiErrorResponse('Invalid email', [
        'errors' => ['email' => 'Invalid email']
      ]);
    }

    $user = $request->user();
    if(is_null($user->email_verified_at)) {
      return $this->apiErrorResponse('Your email is not verifited.', [
        'errors' => ['email' => 'Your email is not verifited']
      ]);
    }
    $user->email = $request->new_email;
    $user->email_verified_at = null;
    $user->save();
    $user->linking();
    $user->wallet->status = 'blocked';
    $user->wallet->unlinkingAndSave();

    $verifyCode = random_int(100000, 999999);
    $token = Str::random(64);
    VerifyToken::create([
      'token' => $token,
      'user_id' => $user->id,
      'code' => $verifyCode,
    ]);
    Mail::create([
      'title' => 'Email Verification',
      'template_id' => Setting::emailVerificationTemplateId(),
      'data' => ['<-user->' => $user->fullname, '<-code->' => $verifyCode],
      'targets' => [$request->email]
    ]);
    $user->tokens()->delete();
    return $this->apiSuccessResponse("Successfully changing email", [
      'token' => $token,
      'user_id' => $user->id,
    ]);
  }

  public function rp_check_password(Request $request) {
    $validator = Validator::make($request->all(), [
      'password' => 'required',
    ]);
    if ($validator->fails()) {
      return $this->apiErrorResponse(null, [
        'messages' => $validator->errors(),
      ]);
    }

    $user = $request->user();
    if(is_null($user->email_verified_at)) {
      return $this->apiErrorResponse('Your email is not verifited.', [
        'errors' => ['email' => 'Your email is not verifited']
      ]);
    }
    if(!Hash::check($request->password, $user->password)) {
      return $this->apiErrorResponse('Invalid password', [
        'errors' => ['password' => 'Invalid password']
      ]);
    }
    $user->linking();
    $user->wallet->status = 'blocked';
    $user->wallet->unlinkingAndSave();

    $token = Str::random(64);
    VerifyToken::create([
      'token' => $token,
      'user_id' => $user->id,
      'code' => '',
    ]);
    $user->tokens()->delete();
    return $this->apiSuccessResponse('Successfully verifing email', [
      'token' => $token,
      'user_id' => $user->id,
    ]);
  }

  public function index(Request $request) {
    $user = $request->user();
    if(is_null($user->email_verified_at)) {
      return $this->apiErrorResponse('Your email is not verifited.', [
        'errors' => ['email' => 'Your email is not verifited']
      ]);
    }
    $user->linking();
    return $this->apiSuccessResponse('Successfully getting user', [
      'user' => $user,
    ]);
  }

  public function logout(Request $request) {
    $user = request()->user();
    $user->tokens()->delete();
    return $this->apiSuccessResponse('User successfully signed out');
  }

}
