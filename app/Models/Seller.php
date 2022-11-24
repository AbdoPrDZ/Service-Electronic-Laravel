<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seller extends Model
{
    use HasFactory;

    protected $table = 'sellers';

    protected $fillable = [
      'user_id',
      'delivery_config',
      'verification_images_ids',
      'identity_verifited_at',
      'balance',
      'total_cost',
      'total_commission',
    ];

    protected $casts = [
      'verification_images_ids' => 'array',
      'identity_verifited_at' => 'datetime',
    ];

    public function linking() {
      $this->identity_verifited = !is_null($this->identity_verifited_at);
      $ids = $this->verification_images_ids;
      $this->verification_images = [];
      foreach ($ids as $id) {
        $this->verification_images[$id] = File::find($id)->name;
      }
    }

}
