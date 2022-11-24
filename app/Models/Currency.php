<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Transfer;

class Currency extends Model {
  use HasFactory;

  protected $table = 'currencies';

  protected $fillable = [
    'name',
    'char',
    'max_receive',
    'wallet',
    'prices',
  ];

  protected $casts = [
    'prices' => 'array',
  ];

}
