<?php

namespace App\Models;

use App\Events\TemplateCreatedEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Template extends Model {

  use HasFactory, GetNextSequenceValue;

  public $incrementing = false;

  protected $primaryKey = 'name';

  protected $fillable = [
    'name',
    'content',
    'args',
    'type',
    'unreades',
    'is_deleted',
  ];

  protected $casts = [
    'args' => 'array',
    'unreades' => 'array',
    'is_deleted' => 'boolean',
    'created_at' => 'datetime:Y-m-d H:m:s',
  ];

  protected $dispatchesEvents = [
    'created' => TemplateCreatedEvent::class,
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

  public function preDelete() {
    $this->is_deleted = true;
    $this->save();
  }

}
