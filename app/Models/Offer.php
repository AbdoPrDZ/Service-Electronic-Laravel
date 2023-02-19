<?php

namespace App\Models;

use App\Events\OfferCreatedEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
