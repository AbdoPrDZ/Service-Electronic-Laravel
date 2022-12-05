<?php

namespace App\Models;

use App\Models\Currency;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;


/**
 * App\Models\Transfer
 *
 * @property int $id
 * @property string $user_id
 * @property float $sended_balance
 * @property float $received_balance
 * @property int $sended_currency_id
 * @property int $received_currency_id
 * @property string|null $proof_id
 * @property string|null $wallet
 * @property int $exchange_id
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $ansowerd_at
 * @property string|null $ansower_description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Transfer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Transfer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Transfer query()
 * @method static \Illuminate\Database\Eloquent\Builder|Transfer whereAnsowerDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transfer whereAnsowerdAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transfer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transfer whereExchangeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transfer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transfer whereProofId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transfer whereReceivedBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transfer whereReceivedCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transfer whereSendedBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transfer whereSendedCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transfer whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transfer whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transfer whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transfer whereWallet($value)
 * @mixin \Eloquent
 */
class Transfer extends Model  {
  use HasFactory;

  protected $fillable = [
    'user_id',
    'sended_balance',
    'received_balance',
    'sended_currency_id',
    'received_currency_id',
    'proof_id',
    'wallet',
    'exchange_id',
    'status',
    'ansowerd_at',
    'ansower_description',
  ];

  protected $casts = [
    'ansowerd_at' => 'datetime',
  ];

  public function linking() {
    $this->user = User::find($this->user_id);
    $this->sended_currency = Currency::find($this->sended_currency_id);
    $this->sended_currency->linking();
    $this->received_currency = Currency::find($this->received_currency_id);
    $this->received_currency->linking();
    $this->exchange = Exchange::find($this->exchange_id);
    if(!is_null($this->exchange)) $this->exchange->linking();
    $platformCurrency = Setting::find('platform_currency_id')->value[0];
    $this->for_recharge = $this->received_currency_id == $platformCurrency && !is_null($this->exchange);
    return $this;
  }

  public function ansower($status, $ansower_description) {
    if(!in_array($status, ['accepted', 'refused'])) return [
      'success' => false,
      'message' => 'Invalid status',
    ];
    if(!is_null($this->ansowerd_at)) return [
      'success' => false,
      'message' => 'This Transfer Already ansowred',
    ];
    $this->status = $status;
    $this->ansowerd_at = Carbon::now();
    $this->ansower_description = $ansower_description;
    $exchange = Exchange::find($this->exchange_id);
    if(!is_null($exchange)) $exchange->linking();
    $platformCurrency = Setting::find('platform_currency_id')->value[0];
    $for_recharge = $this->received_currency_id == $platformCurrency && !is_null($exchange);
    $ansowers = [
      'accepted' => 'Congratulations, your transfer request has been accepted',
      'refused' => 'Unfortunately your transfer request has been refused, for more information please contact support.'
    ];
    $eventName = 'transfer-ansower';
    if($for_recharge) {
      if($this->status == 'accepted') $exchangeRes = $exchange->accept();
      else $exchangeRes = $exchange->refuse($ansower_description);
      if(!$exchangeRes['success']) return $exchangeRes;
      $ansowers = [
        'accepted' => 'Congratulations, your recharge request has been accepted',
        'refused' => 'Unfortunately your recharge request has been refused, for more information please contact support.'
      ];
      $eventName = 'transfer-ansower';
    }
    $notification = Notification::create([
      'name' => 'notifications',
      'title' => "Transfer (#$this->id) Status Changed",
      'message' => $ansowers[$this->status],
      'from_id' => '1',
      'from_model' => Admin::class,
      'to_id' => $this->user_id,
      'to_model' => User::class,
      'data' => [
        'event_name' => $eventName,
        'data' => json_encode([
          'id' => "$this->id",
          'status' => $this->status,
        ])
      ],
      'image_id' => 'currency-12',
      'type' => 'emitOrNotify',
    ]);
    Log::info('send transfer update notification', [$notification]);

    $this->save();
    return [
      'success' => true,
      'message' => 'successfully',
    ];
  }

}
