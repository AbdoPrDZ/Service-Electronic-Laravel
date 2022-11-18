<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\VerifyToken;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Str;
use Validator;

class UserController extends Controller {
    public function __construct() {
        // $this->middleware('auth:sanctum', ['except' => [
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
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return $this->apiErrorResponse(null, [
                'errors' => $validator->errors(),
            ]);
        }

        $user = User::where('email', '=', $request->email)->first();
        if(!is_null($user)) {
            $authed = Auth::attempt($validator->validated());
            if($authed) {
                $token = $request->user()->createToken('access.token', ['remember'])->plainTextToken;
                return $this->apiSuccessResponse('Successfully login', [
                    'token' => $token,
                ]);
            } else {
                return $this->apiErrorResponse('Invalid password');
            }
        } else {
            return $this->apiErrorResponse('Invalid email');
        }
    }

    public function signup(Request $request) {

        $validator = Validator::make($request->all(), [
            'firstname' => 'required|string|between:2,100',
            'lastname' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100',
            'phone' => 'required|string',
            'password' => 'required|string|min:6',
        ]);
        if($validator->fails()){
            return $this->apiErrorResponse(null, [
                'errors' => $validator->errors(),
            ]);
        }

        $user = User::create(array_merge(
            $validator->validated(),
            [
            'password' => bcrypt($request->password),
            'profile_image_id' => 'api_profile_default',
            'balance' => 0,
            'settings' => json_encode([
                'theme' => 'Light',
                'languange' => 'En',
            ]),
            ]
        ));

        // send email
        $verifyCode = random_int(100000, 999999);
        $token = Str::random(64);
        VerifyToken::create([
            'token' => $token,
            'user_id' => $user->id,
            'code' => $verifyCode,
        ]);


        return $this->apiSuccessResponse('User successfully registered code ("' . $verifyCode . '")', [
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
        $user->update([
          'email_verified_at' => Carbon::now(),
        ]);
        $token->update([
          'used_at' => Carbon::now(),
        ]);
        return $this->apiSuccessResponse('User successfully verifing email', [
        //   'user' => $user,
        ]);
      } else {
        return $this->apiErrorResponse('Invalid token or user id');
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
            return $this->apiErrorResponse('Email not verfited, please verify your email');
          }
          // send email
          $verifyCode = random_int(100000, 999999);
          $token = Str::random(64);
          VerifyToken::create([
            'token' => $token,
            'user_id' => $user->id,
            'code' => $verifyCode,
          ]);
          return $this->apiSuccessResponse('Successfully verifing email code ("' . $verifyCode . '")', [
            'token' => $token,
            'user_id' => $user->id,
          ]);
        } else {
          return $this->apiErrorResponse('Invalid email.');
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

        $token = VerifyToken::where('token', '=', $request->token)->first();
        $user = User::where('id', '=', $request->user_id)->first();
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
          return $this->apiErrorResponse('Invalid user_id or token');
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

        $token = VerifyToken::where('token', '=', $request->token)->first();
        $user = User::where('id', '=', $request->user_id)->first();
        if(!is_null($token) && !is_null($user) && $token->used_at == null) {
          $token->update([
            'used_at' => Carbon::now(),
          ]);
          $user->update([
            'password' => bcrypt($request->new_password)
          ]);
          return $this->apiSuccessResponse('Successfully reset password');
        } else {
          return $this->apiErrorResponse('Invalid user_id or token');
        }
      }

    public function index(Request $request) {
        $user = auth()->user();
        $user->email_is_verifited = $user->email_verified_at != null;
        $user->identity_is_verifited = $user->identity_verifited_at != null;
        return $this->apiSuccessResponse('Successfully getting user', [
            'user' => $user,
        ]);
    }

    public function logout() {
        auth()->logout();
        return $this->apiSuccessResponse('User successfully signed out');
      }
}
