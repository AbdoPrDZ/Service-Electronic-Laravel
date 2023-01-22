<?php

namespace App\Models;

use App\Events\ProductCreatedEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Product
 *
 * @property int $id
 * @property int $seller_id
 * @property string $name
 * @property string $description
 * @property float $price
 * @property int $count
 * @property int $category_id
 * @property array $images_ids
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Product newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Product query()
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereImagesIds($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereSellerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Product extends Model {

  use HasFactory, GetNextSequenceValue;

  protected $fillable = [
    'seller_id',
    'name',
    'description',
    'inside_country',
    'price',
    'commission',
    'count',
    'rates',
    'likes',
    'category_id',
    'images_ids',
    'unreades',
    'is_deleted',
  ];

  protected $casts = [
    'inside_country' => 'boolean',
    'images_ids' => 'array',
    'rates' => 'array',
    'likes' => 'array',
    'unreades' => 'array',
    'is_deleted' => 'boolean',
    'created_at' => 'datetime:Y-m-d H:m:s',
  ];

  protected $dispatchesEvents = [
    'created' => ProductCreatedEvent::class,
  ];

  static function news($admin_id) {
    $products = Product::where('unreades', '!=', '[]')->get();
    $newsProducts = [];
    foreach ($products as $product) {
      if(in_array($admin_id, $product->unreades))
        $newsProducts[$product->id] = $product;
    }
    return $newsProducts;
  }

  static function readNews($admin_id) {
    $items = Product::news($admin_id);
    foreach ($items as $item) {
      $item->unreades = array_diff($item->unreades, [$admin_id]);
      $item->save();
    }
  }

  public function linking(User $user = null, $linkingSeller = true) {
    $this->display_price = $this->price + ($this->price * $this->commission);
    $this->seller = Seller::find($this->seller_id);
    if($linkingSeller) $this->seller->linking();
    $this->category = Category::find($this->category_id);
    $totalRate = 0;
    foreach ($this->rates as $rate) {
      $totalRate += $rate;
    }
    $this->is_liked = $user ? in_array($user->id, $this->likes) : false;
    $this->is_rated = $user ? key_exists($user->id, $this->rates) : false;
    $this->rate = count($this->rates) != 0 ? ($totalRate / count($this->rates)) : 0;
    $this->like_count = count($this->likes);
  }

  public function unlinking() {
    unset($this->display_price);
    unset($this->seller);
    unset($this->category);
    unset($this->is_liked);
    unset($this->is_rated);
    unset($this->rate);
    unset($this->like_count);
  }

  public function unlinkingAndSave() {
    $this->unlinking();
    $this->save();
  }

  public function preDelete() {
    $this->is_deleted = true;
    $this->unlinkingAndSave();
  }

}
