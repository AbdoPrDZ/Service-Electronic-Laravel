<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model {
  use HasFactory;

  protected $table = 'purchases';

  protected $fillable = [
    'product_id',
    'count',
    'fullname',
    'phone',
    'state',
    'address',
    'delivery_type',
    'delivery_price',
    'charging_date',
    'delivery_date',
    'received_date',
    'total_price',
  ];

  protected $casts = [
    'charging_date' => 'datetime',
    'received_date' => 'datetime',
    'delivery_date' => 'datetime',
  ];

  public function linking() {
    $this->product= Product::find($this->product_id);
  }

}
