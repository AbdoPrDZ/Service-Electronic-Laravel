<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;

/**
 * App\Models\Seller
 *
 * @property int $id
 * @property int $user_id
 * @property string $store_name
 * @property string $store_address
 * @property string|null $store_image_id
 * @property string $status
 * @property string|null $anower_description
 * @property string|null $anower_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Seller newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Seller newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Seller query()
 * @method static \Illuminate\Database\Eloquent\Builder|Seller whereAnowerAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Seller whereAnowerDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Seller whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Seller whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Seller whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Seller whereStoreAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Seller whereStoreImageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Seller whereStoreName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Seller whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Seller whereUserId($value)
 * @mixin \Eloquent
 */
class Seller extends Model
{
  use HasFactory;

  protected $table = 'sellers';

  protected $fillable = [
    'id',
    'user_id',
    'store_name',
    'store_address',
    'store_phone',
    'store_image_id',
    'balance',
    'total_commission',
    'status',
  ];

  public function linking() {
    $this->user = User::find($this->user_id);
  }

}
