<?php

namespace App\Models;

use App\Events\ExchangeCreatedEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Exchange
 *
 * @property int $id
 * @property int $from_id
 * @property string $from_model
 * @property int $to_id
 * @property string $to_model
 * @property string|null $from_wallet_id
 * @property string $to_wallet_id
 * @property float $balance
 * @property string $status
 * @property string|null $anower_description
 * @property string|null $answered_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Exchange newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Exchange newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Exchange query()
 * @method static \Illuminate\Database\Eloquent\Builder|Exchange whereAnowerAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Exchange whereAnowerDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Exchange whereBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Exchange whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Exchange whereFromId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Exchange whereFromModel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Exchange whereFromWalletId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Exchange whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Exchange whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Exchange whereToId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Exchange whereToModel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Exchange whereToWalletId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Exchange whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Exchange extends Model {
  use HasFactory;

  protected $fillable = [
    'name',
    'from_wallet_id',
    'to_wallet_id',
    'sended_balance',
    'received_balance',
    'status',
    'anower_description',
    'answered_at',
    'unreades',
  ];

  protected $casts = [
    'answered_at' => 'datetime:Y-m-d H:m:s',
    'unreades' => 'array',
    'created_at' => 'datetime:Y-m-d H:m:s',
  ];

  protected $dispatchesEvents = [
    'created' => ExchangeCreatedEvent::class,
  ];

  static function news($admin_id) {
    $exchanges = Exchange::where('unreades', '!=', '[]')->get();
    $newsExchanges = [];
    foreach ($exchanges as $exchange) {
      if(in_array($admin_id, $exchange->unreades))
        $newsExchanges[$exchange->id] = $exchange;
    }
    return $newsExchanges;
  }

  static function readNews($admin_id) {
    $items = Exchange::news($admin_id);
    foreach ($items as $item) {
      $item->unreades = array_diff($item->unreades, [$admin_id]);
      $item->save();
    }
  }

  public function linking($gettingTargetUser = false) {
    $this->from_wallet = Wallet::find($this->from_wallet_id);
    if($this->from_wallet) $this->from_wallet->linking();
    $this->to_wallet = Wallet::find($this->to_wallet_id);
    if($this->to_wallet) $this->to_wallet->linking();
    if ($gettingTargetUser) $this->target_user = User::where('wallet_id', '=', $this->to_wallet_id)->first();
  }

  public function unlinking() {
    unset($this->from_wallet);
    unset($this->to_wallet);
  }

  public function unlinkingAndSave() {
    $this->unlinking();
    $this->save();
  }

  public function accept() {
    $this->linking();
    if(!is_null($this->answered_at)) {
      return [
        'success' => false,
        'message' => 'You already ansowred',
      ];
    }
    if($this->from_wallet && $this->from_wallet->checking_withdraw_balance < $this->sended_balance) return [
      'success' => false,
      'message' => 'The sender balance is insufficient',
    ];
    if($this->to_wallet) {
      $this->to_wallet->balance += $this->received_balance;
      $this->to_wallet->checking_recharge_balance -= $this->received_balance;
      $this->to_wallet->unlinkingAndSave();
    }
    if($this->from_wallet) {
      $this->from_wallet->checking_withdraw_balance -= $this->sended_balance;
      $this->from_wallet->unlinkingAndSave();
    }
    $this->answered_at = now();
    $this->status = 'received';
    $this->unlinkingAndSave();
    return [
      'success' => true,
    ];
  }

  public function refuse($message) {
    $this->linking();
    if(!is_null($this->answered_at)) {
      return [
        'success' => false,
        'message' => 'You already ansowred',
      ];
    }
    $this->anower_description = $message;
    $this->answered_at = now();
    $this->status = 'blocked';
    if($this->from_wallet) {
      $this->from_wallet->checking_withdraw_balance -= $this->sended_balance;
      $this->from_wallet->balance += $this->sended_balance;
      $this->from_wallet->unlinkingAndSave();
    }
    if($this->to_wallet) {
      $this->to_wallet->checking_recharge_balance -= $this->received_balance;
      $this->to_wallet->unlinkingAndSave();
    }
    $this->unlinkingAndSave();
    return [
      'success' => true,
    ];
  }
}
