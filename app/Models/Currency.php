<?php

namespace App\Models;

use App\Events\CurrencyDeleteEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Transfer;

/**
 * App\Models\Currency
 *
 * @property int $id
 * @property string $name
 * @property string $char
 * @property float $max_receive
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
  use HasFactory;

  protected $table = 'currencies';

  protected $fillable = [
    'name',
    'char',
    'max_receive',
    'proof_is_required',
    'wallet',
    'platform_wallet_id',
    'prices',
  ];

  protected $casts = [
    'prices' => 'array',
  ];

  public function linking() {
    $this->platform_wallet = Wallet::find($this->platform_wallet_id);
    // $this->platform_wallet->linking();
  }

  protected $dispatchesEvents = [
    'deleted' => CurrencyDeleteEvent::class,
  ];
}
