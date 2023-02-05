<?php

namespace App\Models;

use App\Events\User\UserCreatedEvent;
use App\Events\User\UserUpdatedEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * App\Models\User
 *
 * @property int $id
 * @property string $firstname
 * @property string $lastname
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $phone
 * @property string $password
 * @property string|null $wallet_id
 * @property array $verification_images_ids
 * @property \Illuminate\Support\Carbon|null $identity_verifited_at
 * @property string $profile_image_id
 * @property string|null $messaging_token
 * @property string|null $remember_token
 * @property array $settings
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Laravel\Sanctum\PersonalAccessToken[] $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\UserFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereFirstname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereIdentityVerifitedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLastname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereMessagingToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereProfileImageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSettings($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereVerificationImagesIds($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereWalletId($value)
 * @mixin \Eloquent
 */
class User extends Authenticatable {
  use HasApiTokens, HasFactory, Notifiable;

  protected $fillable = [
    'firstname',
    'lastname',
    'email',
    'email_verified_at',
    'phone',
    'password',
    'wallet_id',
    'profile_image_id',
    'verification_images_ids',
    'identity_verifited_at',
    'identity_status',
    'identity_answer_description',
    'messaging_token',
    'settings',
    'unreades',
    'is_deleted',
  ];

  protected $hidden = [
    'password',
    'remember_token',
  ];

  protected $casts = [
    'email_verified_at' => 'datetime:Y-m-d H:m:s',
    'verification_images_ids' => 'array',
    'identity_verifited_at' => 'datetime:Y-m-d H:m:s',
    'settings' => 'array',
    'unreades' => 'array',
    'is_deleted' => 'boolean',
    'created_at' => 'datetime:Y-m-d H:m:s',
  ];

  protected $dispatchesEvents = [
    'created' => UserCreatedEvent::class,
    'updated' => UserUpdatedEvent::class,
  ];

  static function news($admin_id) {
    $users = User::where('unreades', '!=', '[]')->get();
    $newUsers = [];
    foreach ($users as $user) {
      if(in_array($admin_id, $user->unreades))
        $newUsers[$user->id] = $user;
    }
    return $newUsers;
  }

  static function readNews($admin_id) {
    $items = User::news($admin_id);
    foreach ($items as $item) {
      $item->unreades = array_diff($item->unreades, [$admin_id]);
      $item->save();
    }
  }

  public function linking($linkSeller = true) {
    $this->fullname = $this->firstname . ' ' . $this->lastname;
    $this->email_verified = !is_null($this->email_verified_at);
    $seller = Seller::where('user_id', '=', $this->id)->first();
    if(!is_null($seller)) {
      $this->seller = $seller;
      if($linkSeller) $this->seller->linking(false);
    }
    $this->wallet = Wallet::find($this->wallet_id);
    $balance = 0;
    $checking_balance = 0;
    if(!is_null($this->wallet)) {
      $this->wallet->linking();
      $balance = $this->wallet->balance;
      $checking_balance = $this->wallet->checking_recharge_balance + $this->wallet->checking_withdraw_balance;
    }
    $this->balance = $balance;
    $this->checking_balance = $checking_balance;

    $platform_currency = Currency::find(Setting::find('platform_currency_id')?->value[0]);
    $display_currency = Currency::find(Setting::find('display_currency_id')?->value[0]);
    $platform_currency->linking();
    $display_currency->linking();
    $this->platform_settings = [
      'platform_currency' => $platform_currency,
      'display_currency' => $display_currency,
      'commission' => Setting::find('commission')?->value[0],
      'services_status' => Setting::servicesStatus(),
    ];
  }

  public function unlinking($linkSeller = true) {
    unset($this->fullname);
    unset($this->email_verified);
    unset($this->wallet);
    unset($this->balance);
    unset($this->checking_balance);
    unset($this->platform_settings);
  }

  public function unlinkingAndSave() {
    $this->unlinking();
    $this->save();
  }

  public function preDelete() {
    $this->is_deleted = true;
    $this->unlinkingAndSave();
  }

  public function purchases() {
    $items = Purchase::where('user_id', '=', $this->id);
    $purchases = [];
    foreach ($purchases as $purchase) {
      $purchase->linking();
      $purchases[$purchase->id] = $purchase;
    }
    return $purchases;
  }

  static function notify($notification, $users = '*') {
    return async(function () use ($notification, $users) {
      if ($users == '*') $users = User::all();
      foreach ($users as $user) {
        Notification::create([
          'to_id' => $user->id,
          'to_model' => User::class,
          ...$notification,
        ]);
      }
    })->start();
  }
}
