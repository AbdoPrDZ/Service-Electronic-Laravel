<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Purchase
 *
 * @property int $id
 * @property int $product_id
 * @property int $count
 * @property string $fullname
 * @property string $phone
 * @property string|null $address
 * @property string $delivery_type
 * @property float $delivery_price
 * @property \Illuminate\Support\Carbon|null $charging_date
 * @property \Illuminate\Support\Carbon|null $delivery_date
 * @property \Illuminate\Support\Carbon|null $received_date
 * @property float $total_price
 * @property string|null $pay_exchange_id
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Purchase newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Purchase newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Purchase query()
 * @method static \Illuminate\Database\Eloquent\Builder|Purchase whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Purchase whereChargingDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Purchase whereCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Purchase whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Purchase whereDeliveryDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Purchase whereDeliveryPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Purchase whereDeliveryType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Purchase whereFullname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Purchase whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Purchase wherePayExchangeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Purchase wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Purchase whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Purchase whereReceivedDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Purchase whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Purchase whereTotalPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Purchase whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Purchase extends Model {
  use HasFactory;

  protected $table = 'purchases';

  protected $fillable = [
    'product_id',
    'count',
    'fullname',
    'phone',
    'address',
    'delivery_type',
    'delivery_price',
    'charging_date',
    'delivery_date',
    'received_date',
    'total_price',
  ];

  protected $casts = [
    'charging_date' => 'datetime',
    'received_date' => 'datetime',
    'delivery_date' => 'datetime',
  ];

  public function linking() {
    $this->product= Product::find($this->product_id);
    $this->product->linking();
  }

}
