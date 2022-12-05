<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
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
    'settings',
  ];

  protected $hidden = [
    'password',
    'remember_token',
  ];

  protected $casts = [
    'email_verified_at' => 'datetime',
    'verification_images_ids' => 'array',
    'identity_verifited_at' => 'datetime',
    'settings' => 'array',
  ];

  public function linking() {
    $this->email_verified = !is_null($this->email_verified_at);
    $this->profile_image = File::find($this->profile_image_id)->name;
    $this->identity_verifited = !is_null($this->identity_verifited_at);
    $this->identity_status = $this->identity_verifited ? 'verifted' :
      (count($this->verification_images_ids) > 0 ?
        'checking' :
        'not_verifited');
    $this->seller = Seller::where('user_id', '=', $this->id)->first();
    if(!is_null($this->sellet)) $this->sellet->linking();
    $this->wallet = Wallet::find($this->wallet_id);
    $balance = 0;
    $checking_balance = 0;
    if(!is_null($this->wallet)) {
      $this->wallet->linking();
      $balance = $this->wallet->balance;
      $checking_balance = $this->wallet->checking_balance;
    }
    $this->balance = $balance;
    $this->checking_balance = $checking_balance;

    $recherge_currencies = [];
    $recherge_currencies_ids = Setting::find('recherge_currencies')->value;
    foreach ($recherge_currencies_ids as $id) {
      $recherge_currencies[$id] = Currency::find($id);
    }
    $this->platform_settings = [
      'platform_currency' => Currency::find(Setting::find('platform_currency_id')->value[0]),
      'recherge_currencies' => $recherge_currencies,
    ];
  }

}
