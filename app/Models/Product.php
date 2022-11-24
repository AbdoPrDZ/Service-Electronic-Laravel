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
    'purchase_id',
    'price',
    'pricing_type',
    'category_id',
    'tags',
    'images_ids',
    'description',
  ];

  protected $casts = [
    'images_ids' => 'array',
    'tags' => 'array',
  ];

  function linking() {
    $this->purchase = User::find($this->purchase_id);
    // $this->images_ids = json_decode($this->images_ids);
  }
}
