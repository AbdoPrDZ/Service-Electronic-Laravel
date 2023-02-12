<?php

namespace App\Models;

use App\Events\Transfer\TransferCreatedEvent;
use App\Events\Transfer\TransferUpdatedEvent;
use App\Events\Transfer\TransferDeletedEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


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
 * @method static \Illuminate\Database\Eloquent\Builder|Transfer whereAnswerDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transfer whereAnswerdAt($value)
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
  use HasFactory, GetNextSequenceValue;

  protected $fillable = [
    'user_id',
    'sended_balance',
    'received_balance',
    'sended_currency_id',
    'received_currency_id',
    'proof_id',
    'data',
    'exchange_id',
    'for_what',
    'status',
    'answered_at',
    'answer_description',
    'unreades'
  ];

  protected $casts = [
    'data' => 'array',
    'answered_at' => 'datetime:Y-m-d H:m:s',
    'unreades' => 'array',
    'created_at' => 'datetime:Y-m-d H:m:s',
  ];

  protected $dispatchesEvents = [
    'created' => TransferCreatedEvent::class,
    'updated' => TransferUpdatedEvent::class,
    'deleted' => TransferDeletedEvent::class,
  ];

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

  public function linking() {
    $this->user = User::find($this->user_id);
    $this->sended_currency = Currency::find($this->sended_currency_id);
    $this->sended_currency->linking();
    $this->received_currency = Currency::find($this->received_currency_id);
    $this->received_currency->linking();
    $this->exchange = Exchange::find($this->exchange_id);
    if(!is_null($this->exchange)) $this->exchange->linking();
    return $this;
  }

  public function unlinking() {
    unset($this->user);
    unset($this->sended_currency);
    unset($this->received_currency);
    unset($this->exchange);
  }

  public function unlinkingAndSave() {
    $this->unlinking();
    $this->save();
  }

  public function answer($status, $answer_description, $admin_id) {
    if(!in_array($status, ['accepted', 'refused'])) return [
      'success' => false,
      'message' => 'Invalid status',
    ];
    if(!is_null($this->answered_at)) return [
      'success' => false,
      'message' => 'This Transfer Already answered',
    ];
    $this->status = $status;
    $this->answered_at = now();
    $this->answer_description = $answer_description;
    $exchange = Exchange::find($this->exchange_id);
    if(!is_null($exchange)) $exchange->linking();

    $sended_currency = Currency::find($this->sended_currency_id);
    $sended_currency->linking();

    $answers = [
      'accepted' => 'Congratulations, your transfer request has been accepted',
      'refused' => 'Unfortunately your transfer request has been refused, for more information please contact support.'
    ];
    if($this->for_what == 'recharge') {
      $answers = [
        'accepted' => 'Congratulations, your recharge request has been accepted',
        'refused' => 'Unfortunately your recharge request has been refused, for more information please contact support.'
      ];
    } else if($this->for_what == 'withdraw') {
      $user = User::find($this->user_id);
      $user->linking();
      if($status == 'refused') {
        $user->wallet->balance += $this->sended_balance;
      }
      $user->wallet->checking_withdraw_balance -= $this->sended_balance;
      $user->wallet->unlinkingAndSave();
    }
    if($exchange) {
      if($this->status == 'accepted') $exchangeRes = $exchange->accept();
      else $exchangeRes = $exchange->refuse($answer_description);
      if(!$exchangeRes['success']) return $exchangeRes;
    }

    Notification::create([
      'name' => 'transfer-answer',
      'title' => "Transfer (#$this->id) Status Changed",
      'message' => $answers[$this->status],
      'from_id' => $admin_id,
      'from_model' => Admin::class,
      'to_id' => $this->user_id,
      'to_model' => User::class,
      'data' => [
        'event_name' => 'transfer-answer',
        'data' => json_encode([
          'id' => "$this->id",
          'status' => $this->status,
        ]),
      ],
      'image_id' => 'transfers',
      'type' => 'emitOrNotify',
    ]);

    $this->unlinkingAndSave();
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
}
