<?php

namespace App\Models;

use App\Events\Transfer\TransferCreatedEvent;
use App\Events\Transfer\TransferDeletedEvent;
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
 * @property \Illuminate\Support\Carbon|null $answered_at
 * @property string|null $answer_description
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
    'for_what',
    'status',
    'answered_at',
    'answer_description',
    'unreades'
  ];

  protected $casts = [
    'answered_at' => 'datetime:Y-m-d H:m:s',
    'unreades' => 'array',
    'created_at' => 'datetime:Y-m-d H:m:s',
  ];

  public function linking() {
    $this->user = User::find($this->user_id);
    $this->sended_currency = Currency::find($this->sended_currency_id);
    $this->sended_currency->linking();
    $this->received_currency = Currency::find($this->received_currency_id);
    $this->received_currency->linking();
    $this->exchange = Exchange::find($this->exchange_id);
    if(!is_null($this->exchange)) $this->exchange->linking();
    $this->proof = File::find($this->proof_id);
    return $this;
  }

  static function news($admin_id, $forWhat) {
    $transfers = Transfer::where([['unreades', '!=', '[]'], ['for_what', '=', $forWhat]])->get();
    $newsTransfers = [];
    foreach ($transfers as $transfer) {
      if(in_array($admin_id, $transfer->unreades))
        $newsTransfers[$transfer->id] = $transfer;
    }
    return $newsTransfers;
  }

  static function readNews($admin_id, $forWhat) {
    $items = Transfer::news($admin_id, $forWhat);
    foreach ($items as $item) {
      $item->unreades = array_diff($item->unreades, [$admin_id]);
      $item->save();
    }
  }

  public function ansower($status, $answer_description, $admin_id) {
    if(!in_array($status, ['accepted', 'refused'])) return [
      'success' => false,
      'message' => 'Invalid status',
    ];
    if(!is_null($this->answered_at)) return [
      'success' => false,
      'message' => 'This Transfer Already ansowred',
    ];
    $this->status = $status;
    $this->answered_at = Carbon::now();
    $this->answer_description = $answer_description;
    $exchange = Exchange::find($this->exchange_id);
    if(!is_null($exchange)) $exchange->linking();

    $sended_currency = Currency::find($this->sended_currency_id);
    $sended_currency->linking();

    $ansowers = [
      'accepted' => 'Congratulations, your transfer request has been accepted',
      'refused' => 'Unfortunately your transfer request has been refused, for more information please contact support.'
    ];
    $eventName = 'transfer-ansower';
    if($this->for_what == 'recharge') {
      if($this->status == 'accepted') $exchangeRes = $exchange->accept();
      else $exchangeRes = $exchange->refuse($answer_description);
      if(!$exchangeRes['success']) return $exchangeRes;
      $ansowers = [
        'accepted' => 'Congratulations, your recharge request has been accepted',
        'refused' => 'Unfortunately your recharge request has been refused, for more information please contact support.'
      ];
      $eventName = 'transfer-ansower';
    } else if($this->for_what == 'withdraw') {
      $user = User::find($this->user_id);
      $user->linking();
      if($status == 'refused') {
        $user->wallet->balance += $this->sended_balance;
      }
      $user->wallet->checking_withdraw_balance -= $this->sended_balance;
      $user->wallet->unlinkingAndSave();
    } else {
      $sended_currency->platform_wallet->balance += $this->sended_balance;
      $sended_currency->platform_wallet->save();
    }
    $notification = Notification::create([
      'name' => 'notifications',
      'title' => "Transfer (#$this->id) Status Changed",
      'message' => $ansowers[$this->status],
      'from_id' => $admin_id,
      'from_model' => Admin::class,
      'to_id' => $this->user_id,
      'to_model' => User::class,
      'data' => [
        'event_name' => $eventName,
        'data' => json_encode([
          'id' => "$this->id",
          'status' => $this->status,
        ]),
      ],
      'image_id' => 'currency-4',
      'type' => 'emitOrNotify',
    ]);
    Log::info('send transfer update notification', [$notification]);

    $this->save();
    return [
      'success' => true,
      'message' => 'successfully',
    ];
  }

  /**
   * @return array
   */
  static function trnasfers() {
    $transfers = Transfer::where([
      ['for_what', '=', 'transfer'],
    ])->get();
    $fTransfers = [];
    foreach ($transfers as $transfer) {
      $transfer->linking();
      $transfer->user->linking();
      $fTransfers[$transfer->id] = $transfer;
    }
    return $fTransfers;
  }

  /**
   * @return array
   */
  static function recharges() {
    $transfers = Transfer::where([
      ['for_what', '=', 'recharge'],
      ['exchange_id', '!=', NULL],
      ['answered_at', '=', NULL],
      ['status', '=', 'checking'],
    ])->get();
    $fTransfers = [];
    foreach ($transfers as $transfer) {
      $transfer->linking();
      $transfer->user->linking();
      $fTransfers[$transfer->id] = $transfer;
    }
    return $fTransfers;
  }

  /**
   * @return array
   */
  static function withdrawes() {
    $transfers = Transfer::where([
      ['for_what', '=', 'withdraw'],
      ['answered_at', '=', NULL],
      ['status', '=', 'checking'],
    ])->get();
    $fTransfers = [];
    foreach ($transfers as $transfer) {
      $transfer->linking();
      $transfer->user->linking();
      $fTransfers[$transfer->id] = $transfer;
    }
    return $fTransfers;
  }

  protected $dispatchesEvents = [
    'created' => TransferCreatedEvent::class,
    'deleted' => TransferDeletedEvent::class,
  ];
}
