<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories';

    protected $fillable = [
      'id',
      'name',
      'image_id',
    ];

    function linking() {
        $this->name = json_decode($this->name);
    }

}
