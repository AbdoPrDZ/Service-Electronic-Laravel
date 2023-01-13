<?php

namespace App\Models;

use App\Events\OfferRequestCreatedEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfferRequest extends Model {
  use HasFactory;

  protected $fillable = [
    'user_id',
    'offer_id',
    'sub_offer',
    'fields',
    'data',
    'status',
    'total_price',
    'exchange_id',
    'unreades',
  ];

  protected $casts =  [
    'fields' => 'array',
    'data' => 'array',
    'unreades' => 'array',
    'created_at' => 'datetime:Y-m-d H:m:s',
  ];

  protected $dispatchesEvents = [
    'created' => OfferRequestCreatedEvent::class,
  ];

  static function news($admin_id) {
    $offers = OfferRequest::where('unreades', '!=', '[]')->get();
    $newsOffers = [];
    foreach ($offers as $offer) {
      if(in_array($admin_id, $offer->unreades))
        $newsOffers[$offer->id] = $offer;
    }
    return $newsOffers;
  }

  static function readNews($admin_id) {
    $items = OfferRequest::news($admin_id);
    foreach ($items as $item) {
      $item->unreades = array_diff($item->unreades, [$admin_id]);
      $item->save();
    }
  }

  public function linking() {
    $this->user = User::find($this->user_id);
    $this->user->linking();
    $this->offer = Offer::find($this->offer_id);
    $this->exchange = Exchange::find($this->exchange_id);
    $this->exchange->linking();
  }

  public function unlinking() {
    unset($this->user);
    unset($this->offer);
    unset($this->exchange);
  }

  public function unlinkingAndSave() {
    $this->unlinking();
    $this->save();
  }

}
