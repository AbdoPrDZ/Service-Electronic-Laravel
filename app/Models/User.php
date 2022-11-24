<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable {
  use HasApiTokens, HasFactory, Notifiable;

  protected $fillable = [
    'firstname',
    'lastname',
    'email',
    'phone',
    'balance',
    'profile_image_id',
    'password',
    'email_verified_at',
    'settings',
  ];

  protected $hidden = [
    'password',
    'remember_token',
  ];

  protected $casts = [
    'email_verified_at' => 'datetime',
    'settings' => 'array',
  ];

  public function linking() {
    $this->email_verified = !is_null($this->email_verified_at);
    $this->profile_image = File::find($this->profile_image_id)->name;
  }

}
