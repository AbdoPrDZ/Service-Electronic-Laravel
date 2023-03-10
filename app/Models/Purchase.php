<?php

namespace App\Models;

use App\Events\Purchase\PurchaseCreatedEvent;
use App\Events\Purchase\PurchaseDeletedEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Purchase
 *
 * @property int $id
 * @property int $product_id
 * @property int $count
 * @property int $user_id
 * @property string $fullname
 * @property string $phone
 * @property string|null $address
 * @property string $delivery_type
 * @property float $total_price
 * @property int $delivery_cost_exchange_id
 * @property int $product_price_exchange_id
 * @property int $commission_exchange_id
 * @property array $delivery_steps
 * @property string $status
 * @property array $unreades
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Purchase newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Purchase newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Purchase query()
 * @method static \Illuminate\Database\Eloquent\Builder|Purchase whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Purchase whereCommissionExchangeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Purchase whereCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Purchase whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Purchase whereDeliveryCostExchangeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Purchase whereDeliverySteps($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Purchase whereDeliveryType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Purchase whereFullname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Purchase whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Purchase wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Purchase whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Purchase whereProductPriceExchangeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Purchase whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Purchase whereTotalPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Purchase whereUnreades($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Purchase whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Purchase whereUserId($value)
 * @mixin \Eloquent
 */
class Purchase extends Model {
  use HasFactory, GetNextSequenceValue;

  protected $table = 'purchases';

  protected $fillable = [
    'product_id',
    'count',
    'user_id',
    'fullname',
    'phone',
    'address',
    'delivery_type',
    'total_price',
    'delivery_steps',
    'delivery_cost_exchange_id',
    'product_price_exchange_id',
    'commission_exchange_id',
    'status',
    'unreades',
  ];

  protected $casts = [
    'delivery_steps' => 'array',
    'unreades' => 'array',
    'created_at' => 'datetime:Y-m-d H:m:s',
  ];

  protected $dispatchesEvents = [
    'created' => PurchaseCreatedEvent::class,
    'deleted' => PurchaseDeletedEvent::class,
  ];

  static function news($admin_id) {
    $purchases = Purchase::where('unreades', '!=', '[]')->get();
    $newsPurchases = [];
    foreach ($purchases as $purchase) {
      if(in_array($admin_id, $purchase->unreades))
        $newsPurchases[$purchase->id] = $purchase;
    }
    return $newsPurchases;
  }

  static function readNews($admin_id) {
    $items = Purchase::news($admin_id);
    foreach ($items as $item) {
      $item->unreades = array_diff($item->unreades, [$admin_id]);
      $item->save();
    }
  }

  public function linking() {
    $this->user = User::find($this->user_id);
    $this->user->linking();
    $this->product= Product::find($this->product_id);
    $this->product->linking();
    $this->delivery_cost_exchange = Exchange::find($this->delivery_cost_exchange_id);
    $this->delivery_cost_exchange->linking();
    $this->product_price_exchange = Exchange::find($this->product_price_exchange_id);
    $this->product_price_exchange->linking();
    $this->commission_exchange = Exchange::find($this->commission_exchange_id);
    $this->commission_exchange->linking();
  }

  public function unlinking() {
    unset($this->user);
    unset($this->product);
    unset($this->delivery_cost_exchange);
    unset($this->product_price_exchange);
    unset($this->commission_exchange);
  }

  public function unlinkingAndSave() {
    $this->unlinking();
    $this->save();
  }

  static function clearCache() {
    async(function() {
      $purchases = Purchase::whereIn('status', ['seller_refuse', 'client_accept', 'admin_answered'])->get();
      foreach ($purchases as $purchase) {
        $purchase->delete();
      }
    })->start();
  }
}
