<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Wallet
 *
 * @property string $id
 * @property int $user_id
 * @property string $user_model
 * @property float $balance
 * @property float $checking_balance
 * @property float $total_received_balance
 * @property float $total_withdrawn_balance
 * @property string $status
 * @property string|null $answored_at
 * @property string|null $ansower_description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Wallet newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Wallet newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Wallet query()
 * @method static \Illuminate\Database\Eloquent\Builder|Wallet whereAnsowerDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wallet whereAnsworedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wallet whereBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wallet whereCheckingBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wallet whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wallet whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wallet whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wallet whereTotalReceivedBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wallet whereTotalWithdrawnBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wallet whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wallet whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wallet whereUserModel($value)
 * @mixin \Eloquent
 */
class Wallet extends Model {
  use HasFactory;

  protected $fillable = [
    'id',
    'user_id',
    'user_model',
    'balance',
    'checking_balance',
    'total_received_balance',
    'total_withdrawn_balance',
    'status',
    'answored_at',
    'ansower_description',
  ];

  protected $casts = [
    'id' => 'string',
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
