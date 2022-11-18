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
        'verification_images_ids',
        'email_verified_at',
        'identity_verifited_at',
        'settings',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'email_verified_at' => 'datetime',
        'identity_verifited_at' => 'datetime',
        'settings' => 'json',
        'verification_images_ids' => 'json',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function linking() {
        $this->email_verified = !is_null($this->email_verified_at);
        $this->identity_verifited = !is_null($this->identity_verifited_at);
        $this->profile_image = File::find($this->profile_image_id)->name;
        $ids = json_decode($this->verification_images_ids);
        $this->verification_images = [];
        foreach ($ids as $id) {
            $this->verification_images[$id] = File::find($id)->name;
        }
    }

}
