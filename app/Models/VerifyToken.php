<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class VerifyToken extends Model
{
  use HasFactory;

  protected $table = 'verify_token';

  protected $fillable = [
    'token',
    'user_id',
    'used_at',
    'code',
  ];

  protected $hidden = [
    'code',
  ];

  protected $casts = [
    'used_at' => 'datetime',
  ];

  protected $primaryKey = 'token';

  function linking() {
    $this->user = User::find($this->user_id);
  }

}


