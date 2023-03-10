<?php

namespace App\Models;

use App\Events\OfferRequest\OfferRequestCreatedEvent;
use App\Events\OfferRequest\OfferRequestDeletedEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\OfferRequest
 *
 * @property int $id
 * @property int $user_id
 * @property int $offer_id
 * @property string|null $sub_offer
 * @property array $fields
 * @property array|null $data
 * @property string $status
 * @property float $total_price
 * @property int $exchange_id
 * @property array $unreades
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|OfferRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OfferRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OfferRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder|OfferRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfferRequest whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfferRequest whereExchangeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfferRequest whereFields($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfferRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfferRequest whereOfferId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfferRequest whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfferRequest whereSubOffer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfferRequest whereTotalPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfferRequest whereUnreades($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfferRequest whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfferRequest whereUserId($value)
 * @mixin \Eloquent
 */
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
    'deleted' => OfferRequestDeletedEvent::class,
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

  static function clearCache() {
    async(function() {
      $offerRequests = OfferRequest::whereIn('status', ['admin_refuse', 'admin_accept'])->get();
      foreach ($offerRequests as $offerRequest) {
        $offerRequest->delete();
      }
    })->start();
  }

}
