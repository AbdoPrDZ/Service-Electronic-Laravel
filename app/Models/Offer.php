<?php

namespace App\Models;

use App\Events\Offer\OfferCreatedEvent;
use App\Events\Offer\OfferDeletedEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Offer
 *
 * @property int $id
 * @property string $image_id
 * @property array $title
 * @property array $description
 * @property array $sub_offers
 * @property array $fields
 * @property array $data
 * @property array $unreades
 * @property bool $is_deleted
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Offer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Offer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Offer query()
 * @method static \Illuminate\Database\Eloquent\Builder|Offer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Offer whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Offer whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Offer whereFields($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Offer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Offer whereImageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Offer whereIsDeleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Offer whereSubOffers($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Offer whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Offer whereUnreades($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Offer whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Offer extends Model {
  use HasFactory, GetNextSequenceValue;

  protected $fillable = [
    'image_id',
    'title',
    'description',
    'sub_offers',
    'fields',
    'data',
    'unreades',
    'is_deleted',
  ];

  protected $casts =  [
    'title' => 'array',
    'description' => 'array',
    'sub_offers' => 'array',
    'fields' => 'array',
    'data' => 'array',
    'unreades' => 'array',
    'is_deleted' => 'boolean',
    'created_at' => 'datetime:Y-m-d H:m:s',
  ];

  protected $dispatchesEvents = [
    'created' => OfferCreatedEvent::class,
    'deleted' => OfferDeletedEvent::class,
  ];

  static function news($admin_id) {
    $offers = Offer::where('unreades', '!=', '[]')->get();
    $newsOffers = [];
    foreach ($offers as $offer) {
      if(in_array($admin_id, $offer->unreades))
        $newsOffers[$offer->id] = $offer;
    }
    return $newsOffers;
  }

  static function readNews($admin_id) {
    $items = Offer::news($admin_id);
    foreach ($items as $item) {
      $item->unreades = array_diff($item->unreades, [$admin_id]);
      $item->save();
    }
  }

  // public function preDelete() {
  //   $this->is_deleted = true;
  //   $this->save();
  // }

}
