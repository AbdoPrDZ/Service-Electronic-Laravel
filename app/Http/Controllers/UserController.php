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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Str;
use Validator;

class UserController extends Controller {

  public function __construct() {
    $this->middleware('multi.auth:sanctum', ['except' => [
      'login',
      'signup',
      'emailVerify',
      'resendEmailVerifiy',
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
        $user = $request->user();
        $token = $user->createToken('access.token', ['remember'])->plainTextToken;
        $socketToken = $user->createToken('socket.access.token', ['socket_token'])->plainTextToken;
        VerifyToken::create([
          'token' => $socketToken,
          'user_id' => $user->id,
          'model' => User::class,
          'code' => '',
        ]);
        $user->linking();
        return $this->apiSuccessResponse('Successfully login', [
          'token' => $token,
          'socket.token' => $socketToken,
          'user' => $user,
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
      'targets' => [$user->id]
    ]);

    return $this->apiSuccessResponse("Successfully registering", [
      'token' => $token,
      'user_id' => $user->id,
    ]);
  }

  public function resendEmailVerifiy(Request $request) {
    $validator = Validator::make($request->all(), [
      'user_id' => 'required|integer',
      'token' => 'required|string',
    ]);
    if ($validator->fails()) {
      return $this->apiErrorResponse(null, [
        'errors' => $validator->errors(),
      ]);
    }
    $user = User::find($request->user_id);
    if(is_null($user)) {
      return $this->apiErrorResponse('Invalid User', [
        'errors' => ['user_id' => 'Invalid User']
      ]);
    }
    $token = VerifyToken::find($request->token);
    if(is_null($token) || !is_null($token->user_at)) {
      return $this->apiErrorResponse('Invalid Token', [
        'errors' => ['token' => 'Invalid Token']
      ]);
    }
    if(now()->diffInSeconds($token->created_at) < 20) return $this->apiErrorResponse('Please wait until the waiting time has expired');
    $token->delete();

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
      'targets' => [$user->id]
    ]);

    return $this->apiSuccessResponse("User successfully resending verification email", [
      'token' => $token,
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

    $token = VerifyToken::find($request->token);
    $user = User::find($request->user_id);
    if(is_null($user)) {
      return $this->apiErrorResponse('Invalid User', [
        'errors' => ['user_id' => 'Invalid User']
      ]);
    }
    if(is_null($token) || !is_null($token->user_at)) {
      return $this->apiErrorResponse('Invalid Token', [
        'errors' => ['token' => 'Invalid Token']
      ]);
    }
    Log::info('Email Verification', [$token, $token->code, $request->code]);
    if($token->code == $request->code) {
      if($user->email_verified_at != null) {
        return $this->apiErrorResponse('This email already verifited', [
          'errors' => ['email' => 'This email already verifited']
        ]);
      }
      $user->update([
        'email_verified_at' => now(),
      ]);
      $token->update([
        'used_at' => now(),
      ]);
      if($user->wallet_id == null) {
        $walletId = bin2hex('w-' . date_format(now(), 'yyyy-MM-dd HH:mm:ss') . "-u-$user->id");
        $wallet = Wallet::create([
          'id' => $walletId,
          'user_id' => $user->id,
          'user_model' => User::class,
          'balance' => 0,
          'status' => 'active',
          'answored_at' => now(),
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
        'targets' => [$user->id]
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

    $user = User::find($request->user_id);
    if(is_null($user->email_verified_at)) {
      return $this->apiErrorResponse('Your email is not verifited', [
        'errors' => ['email' => 'Your email is not verifited']
      ]);
    }

    $token = VerifyToken::find($request->token);
    if(!is_null($token) && !is_null($user) && $token->used_at == null && $token->code == $request->code) {
      $token->update([
      'used_at' => now(),
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

    $user = User::find($request->user_id);
    if(is_null($user->email_verified_at)) {
      return $this->apiErrorResponse('Your email is not verifited.', [
        'errors' => ['email' => 'Your email is not verifited']
      ]);
    }

    $token = VerifyToken::find($request->token);
    if(!is_null($token) && !is_null($user) && $token->used_at == null) {
      $token->update([
      'used_at' => now(),
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

    foreach($user->verification_images_ids as $id) File::find($id)?->delete();

    if(!Storage::disk('api')->exists('users_data')) {
      Storage::disk('api')->makeDirectory('users_data');
    }
    $userFilesPath = 'users_data/' . $user->id;
    if(!Storage::disk('api')->exists($userFilesPath)) {
      Storage::disk('api')->makeDirectory($userFilesPath);
      Storage::disk('api')->makeDirectory("$userFilesPath/identity_verifications");
    }
    $dirPath = "$userFilesPath/identity_verifications/";
    $time = now()->timestamp;
    $request->file('identity_image')->move(Storage::disk('api')->path($dirPath), "vi-$time");
    $identityImage = File::create([
      'name' => "u-" . $user->id . "-vi-$time",
      'disk' => 'api',
      'type' => 'image',
      'path' => "$dirPath/vi-$time",
    ]);
    $request->file('address_image')->move(Storage::disk('api')->path($dirPath), "va-$time");
    $addressImage = File::create([
      'name' => "u-" . $user->id . "-va-$time",
      'disk' => 'api',
      'type' => 'image',
      'path' => "$dirPath/va-$time",
    ]);

    $user->verification_images_ids = [$identityImage->name, $addressImage->name];
    $user->identity_status = 'checking';
    $user->save();

    Admin::notify([
      'name' => 'new-user-sended-identity-verification',
      'from_id' => $user->id,
      'from_Model' => User::class,
      'title' => 'An user wants to verify his identity',
      'message' => "The user ($user->firstname $user->lastname) sended his identity verification images",
      'data' => [
        'user_id' => $user->id,
      ],
      'image_id' => $user->profile_image_id ?? 'logo',
      'type' => 'emit',
    ]);

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
        'errors' => $validator->errors(),
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
      if ($user->profile_image_id != 'api_profile_default') File::find($user->profile_image_id)?->delete();
      if(!Storage::disk('api')->exists('users_data')) {
        Storage::disk('api')->makeDirectory('users_data');
      }
      $userFilesPath = 'users_data/'.$request->user()->id;
      if(!Storage::disk('api')->exists($userFilesPath)) {
        Storage::disk('api')->makeDirectory($userFilesPath);
      }
      $allpath = Storage::disk('api')->path("$userFilesPath");
      $time = now()->timestamp;
      $shortPath = "$userFilesPath/pi-$time";
      $request->file('profile_image')->move($allpath, "pi-$time");
      $imageFile = File::create([
        'name' => 'u-'.$request->user()->id."-pi-$time",
        'disk' => 'api',
        'type' => 'image',
        'path' => $shortPath,
      ]);
      $user->profile_image_id = $imageFile->name;
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
        'errors' => $validator->errors(),
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
      'targets' => [$user->id]
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
        'errors' => $validator->errors(),
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

  public function updateMessagingToken(Request $request) {
    $validator = Validator::make($request->all(), [
      'token' => 'required|string',
    ]);
    if ($validator->fails()) {
      return $this->apiErrorResponse(null, [
        'errors' => $validator->errors(),
      ]);
    }

    $user = $requst->user();
    $user->messaging_token = $request->token;
    $user->save();

    return $this->apiSuccessResponse('Successfully updating messaging token');
  }

  public function logout(Request $request) {
    $user = request()->user();
    $user->tokens()->delete();
    return $this->apiSuccessResponse('User successfully signed out');
  }

}
