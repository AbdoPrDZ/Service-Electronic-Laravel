<?php

namespace App\Models;

use App\Events\TransferEvent;
use App\Models\Currency;
use App\Models\File;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


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
    'status',
    'ansowerd_at',
  ];

  protected $casts = [
    'ansowerd_at' => 'datetime',
  ];
  function linking() {
    $this->user = User::find($this->user_id);
    $this->sended_currency = Currency::find($this->sended_currency_id);
    $this->received_currency = Currency::find($this->received_currency_id);
    return $this;
  }

  protected $dispatchesEvents = [
    'updated' => TransferEvent::class,
  ];

}
