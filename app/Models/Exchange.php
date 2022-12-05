<?php

namespace App\Models;

use Carbon\Carbon;
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
 * @property string|null $anower_at
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
    'from_id',
    'from_model',
    'to_id',
    'to_model',
    'from_wallet_id',
    'to_wallet_id',
    'balance',
    'status',
    'anower_description',
    'anower_at',
  ];

  public function linking() {
    $this->from = app($this->from_model)::find($this->from_id);
    $this->from->linking();
    $this->to = app($this->to_model)::find($this->to_id);
    $this->to->linking();
    $this->from_wallet = Wallet::find($this->from_wallet_id);
    $this->from_wallet->linking();
    $this->to_wallet = Wallet::find($this->to_wallet_id);
    $this->to_wallet->linking();
  }

  public function unlinking() {
    unset($this->from);
    unset($this->to);
    unset($this->from_wallet);
    unset($this->to_wallet);
  }


  public function unlinkingAndSave() {
    $this->unlinking();
    $this->save();
  }

  public function accept() {
    if($this->from_wallet->balance < $this->balance) return [
      'success' => false,
      'message' => 'The sender balance is insufficient',
    ];
    $this->from_wallet->balance -= $this->balance;
    $this->to_wallet->balance += $this->balance;
    $this->to_wallet->checking_balance -= $this->balance;
    $this->from_wallet->unlinkingAndSave();
    $this->to_wallet->unlinkingAndSave();
    $this->anower_at = Carbon::now();
    $this->status = 'received';
    $this->unlinkingAndSave();
    return [
      'success' => true,
    ];
  }

  public function refuse($message) {
    $this->anower_description = $message;
    $this->anower_at = Carbon::now();
    $this->status = 'blocked';
    $this->unlinkingAndSave();
    return [
      'success' => true,
    ];
  }

}
