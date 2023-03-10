<?php

namespace App\Models;

use App\Events\WalletUpdatedEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Wallet
 *
 * @property string $id
 * @property int $user_id
 * @property string $user_model
 * @property float $balance
 * @property float $checking_recharge_balance
 * @property float $checking_withdraw_balance
 * @property string $status
 * @property string|null $answored_at
 * @property string|null $answer_description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Wallet newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Wallet newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Wallet query()
 * @method static \Illuminate\Database\Eloquent\Builder|Wallet whereAnswerDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wallet whereAnsworedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wallet whereBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wallet whereCheckingRechargeBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wallet whereCheckingWithdrawBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wallet whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wallet whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wallet whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wallet whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wallet whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wallet whereUserModel($value)
 * @mixin \Eloquent
 */
class Wallet extends Model {
  use HasFactory;

  public $incrementing = false;

  protected $fillable = [
    'id',
    'user_id',
    'user_model',
    'balance',
    'checking_recharge_balance',
    'checking_withdraw_balance',
    'status',
    'answored_at',
    'answer_description',
  ];

  protected $casts = [
    'id' => 'string',
  ];

  protected $dispatchesEvents = [
    'updated' => WalletUpdatedEvent::class,
  ];

  public function linking() {
    $this->user = app($this->user_model)::find($this->user_id);
  }

  public function unlinking() {
    unset($this->user);
  }

  public function unlinkingAndSave() {
    $this->unlinking();
    $this->save();
  }

}
