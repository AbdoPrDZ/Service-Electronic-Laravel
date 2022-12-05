<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

/**
 * App\Models\VerifyToken
 *
 * @property int $token
 * @property string $code
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $used_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|VerifyToken newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VerifyToken newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VerifyToken query()
 * @method static \Illuminate\Database\Eloquent\Builder|VerifyToken whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VerifyToken whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VerifyToken whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VerifyToken whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VerifyToken whereUsedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VerifyToken whereUserId($value)
 * @mixin \Eloquent
 */
class VerifyToken extends Model
{
  use HasFactory;

  protected $table = 'verify_token';

  protected $fillable = [
    'token',
    'user_id',
    'used_at',
    'code',
  ];

  protected $hidden = [
    'code',
  ];

  protected $casts = [
    'used_at' => 'datetime',
  ];

  protected $primaryKey = 'token';

  public function linking() {
    $this->user = User::find($this->user_id);
  }

}


