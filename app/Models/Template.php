<?php

namespace App\Models;

use App\Events\Template\TemplateCreatedEvent;
use App\Events\Template\TemplateDeletedEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Template
 *
 * @property string $name
 * @property string $content
 * @property array $args
 * @property string $type
 * @property array $unreades
 * @property bool $is_deleted
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Template newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Template newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Template query()
 * @method static \Illuminate\Database\Eloquent\Builder|Template whereArgs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Template whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Template whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Template whereIsDeleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Template whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Template whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Template whereUnreades($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Template whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
    'delete' => TemplateDeletedEvent::class,
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
