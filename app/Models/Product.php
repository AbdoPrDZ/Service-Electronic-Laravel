<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\File;

class Product extends Model {

    use HasFactory;

    protected $fillable = [
      'name',
      'user_id',
      'price',
      'category_id',
      'tags',
      'images_ids',
      'description',
    ];

    function linking() {
        $this->user = User::find($this->user_id);
        $this->images_ids = json_decode($this->images_ids);
    }
}
