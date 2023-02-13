<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * App\Models\Admin
 *
 * @property int $id
 * @property string $username
 * @property string $email
 * @property string $phone
 * @property string $profile_image_id
 * @property string $password
 * @property string|null $remember_token
 * @property array $permissions
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @method static \Illuminate\Database\Eloquent\Builder|Admin newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Admin newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Admin query()
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin wherePermissions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereProfileImageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereUsername($value)
 * @mixin \Eloquent
 */
class Admin extends Authenticatable{
  use HasApiTokens, HasFactory, Notifiable;

  protected $fillable = [
    'username',
    'email',
    'phone',
    'profile_image_id',
    'wallet_id',
    'password',
    'permissions',
  ];

  protected $hidden = [
    'password',
    'remember_token',
  ];

  protected $casts = [
    'email_verified_at' => 'datetime:Y-m-d H:m:s',
    'permissions' => 'array',
    'created_at' => 'datetime:Y-m-d H:m:s',
  ];

  public function linking() {
    // $this->balance = $this->id == 1? Setting::platformCurrency()?->platform_wallet->balance ?? 0 : null;
    $this->wallet = Wallet::find($this->wallet_id);
    $balance = 0;
    if($this->wallet) {
      $this->wallet->linking();
      $balance = $this->wallet->balance;
    }
    $this->balance = $balance;
  }

  static function unreades($admin_id = null) {
    $admins = is_null($admin_id) ? Admin::all() :Admin::where('id', '!=', $admin_id)->get();
    $ids = [];
    foreach ($admins as $admin) {
      $ids[] = $admin->id;
    }
    return $ids;
  }

  static function notify($notification, $admins = '*') {
    return async(function () use ($notification, $admins) {
      if ($admins == '*')
        $admins = Admin::all();
      foreach ($admins as $admin) {
        Notification::create([
          'to_id' => $admin->id,
          'to_model' => Admin::class,
          ...$notification,
        ]);
      }
    })->start();
  }

}

