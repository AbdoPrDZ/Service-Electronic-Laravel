<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Storage;

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
 * @property string|null $answered_at
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
    'store_image_id',
    'delivery_prices',
    'status',
    'answer_description',
    'answered_at',
    'unreades',
  ];

  protected $casts = [
    'delivery_prices' => 'array',
    'answered_at' => 'datetime:Y-m-d H:m:s',
    'unreades' => 'array',
    'created_at' => 'datetime:Y-m-d H:m:s',
  ];

  static function news($admin_id) {
    $sellers = Seller::where('unreades', '!=', '[]')->get();
    $newsSellers = [];
    foreach ($sellers as $seller) {
      if(in_array($admin_id, $seller->unreades))
        $newsSellers[$seller->id] = $seller;
    }
    return $newsSellers;
  }

  static function readNews($admin_id) {
    $items = Seller::news($admin_id);
    foreach ($items as $item) {
      $item->unreades = array_diff($item->unreades, [$admin_id]);
      $item->save();
    }
  }

  public function linking() {
    // $countries = json_decode(Storage::disk('public')->get('countries.json'));
    // list($country, $state, $address) = explode('->', $this->store_address);
    // $this->strAddress = $countries[intVal($country)]->country . "-" .$countries[intVal($country)]->states[intVal($state)] . "-" .$address;
    $this->user = User::find($this->user_id);
    $this->user->linking(false);
  }

}
