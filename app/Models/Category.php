<?php

namespace App\Models;

use App\Events\Category\CategoryCreatedEvent;
use App\Events\Category\CategoryDeletedEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Category
 *
 * @property int $id
 * @property array $name
 * @property string $image_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Category newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Category newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Category query()
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereImageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Category extends Model {
  use HasFactory, GetNextSequenceValue;

  protected $table = 'categories';

  protected $fillable = [
    'id',
    'name',
    'image_id',
    'unreades',
    'is_deleted',
  ];

  protected $casts = [
    'name' => 'array',
    'unreades' => 'array',
    'is_deleted' => 'boolean',
    'created_at' => 'datetime:Y-m-d H:m:s',
  ];

  protected $dispatchesEvents = [
    'created' => CategoryCreatedEvent::class,
    'deleted' => CategoryDeletedEvent::class,
  ];

  static function news($admin_id) {
    $categories = Category::where('unreades', '!=', '[]')->get();
    $newsCategories = [];
    foreach ($categories as $category) {
      if(in_array($admin_id, $category->unreades))
        $newsCategories[$category->id] = $category;
    }
    return $newsCategories;
  }

  static function readNews($admin_id) {
    $items = Category::news($admin_id);
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
