<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Template extends Model {
  use HasFactory;

  protected $fillable = [
    'content',
    'args',
    'for_what',
    'unreades',
  ];

  protected $casts = [
    'args' => 'array',
    'unreades' => 'array',
    'created_at' => 'datetime:Y-m-d H:m:s',
  ];

  static function news($admin_id) {
    $templates = Template::where('unreades', '!=', '[]')->get();
    $newsTemplates = [];
    foreach ($templates as $template) {
      if(in_array($admin_id, $template->unreades))
        $newsTemplates[$template->id] = $template;
    }
    return $newsTemplates;
  }

  static function readNews($admin_id) {
    $items = Template::news($admin_id);
    foreach ($items as $item) {
      $item->unreades = array_diff($item->unreades, [$admin_id]);
      $item->save();
    }
  }
}
