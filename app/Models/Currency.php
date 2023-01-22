<?php

namespace App\Models;

use App\Events\Currency\CurrencyCreatedEvent;
use App\Events\Currency\CurrencyDeletedEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Currency
 *
 * @property int $id
 * @property string $name
 * @property string $char
 * @property bool $proof_is_required
 * @property string $wallet
 * @property string $platform_wallet_id
 * @property Wallet $platform_wallet
 * @property array $prices
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Currency newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Currency newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Currency query()
 * @method static \Illuminate\Database\Eloquent\Builder|Currency whereChar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Currency whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Currency whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Currency whereMaxReceive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Currency whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Currency wherePlatformWalletId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Currency wherePrices($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Currency whereProofIsRequired($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Currency whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Currency whereWallet($value)
 * @mixin \Eloquent
 */
class Currency extends Model {
  use HasFactory, GetNextSequenceValue;

  protected $table = 'currencies';

  protected $fillable = [
    'name',
    'char',
    'proof_is_required',
    'image_pick_type',
    'wallet',
    'platform_wallet_id',
    'prices',
    'unreades',
    'is_deleted',
  ];

  protected $casts = [
    'prices' => 'array',
    'unreades' => 'array',
    'proof_is_required' => 'boolean',
    'is_deleted' => 'boolean',
    'created_at' => 'datetime:Y-m-d H:m:s',
  ];

  protected $dispatchesEvents = [
    'created' => CurrencyCreatedEvent::class,
    'deleted' => CurrencyDeletedEvent::class,
  ];

  static function news($admin_id) {
    $currencies = Currency::where('unreades', '!=', '[]')->get();
    $newsCurrencies = [];
    foreach ($currencies as $currency) {
      if(in_array($admin_id, $currency->unreades))
        $newsCurrencies[$currency->id] = $currency;
    }
    return $newsCurrencies;
  }

  static function readNews($admin_id) {
    $items = Currency::news($admin_id);
    foreach ($items as $item) {
      $item->unreades = array_diff($item->unreades, [$admin_id]);
      $item->save();
    }
  }

  public function linking() {
    $this->platform_wallet = Wallet::find($this->platform_wallet_id);
    // $this->platform_wallet->linking();
    $rendred_prices = [];
    foreach ($this->prices as $currencyId => $price) {
      $rendred_prices[$currencyId] = [
        'currency' => Currency::find($currencyId),
        'price' => $price
      ];
    }
    $this->rendred_prices = $rendred_prices;
  }

  public function unlinking() {
    unset($this->platform_wallet);
    unset($this->rendred_prices);
  }

  public function unlinkingAndSave() {
    $this->unlinking();
    $this->save();
  }

  public function preDelete() {
    $this->is_deleted = true;
    $this->unlinkingAndSave();
  }
}
