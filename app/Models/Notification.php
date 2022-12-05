<?php

namespace App\Models;

use App\Events\NotificationEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * App\Models\Notification
 *
 * @property int $id
 * @property string $from_id
 * @property string $from_model
 * @property string $to_id
 * @property string $to_model
 * @property string $name
 * @property string $title
 * @property string $message
 * @property array $data
 * @property string|null $image_id
 * @property int $is_readed
 * @property string $type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Notification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Notification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Notification query()
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereFromId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereFromModel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereImageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereIsReaded($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereToId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereToModel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Notification extends Model {
  use HasFactory;

  protected $fillable = [
    'from_id',
    'to_id',
    'from_model',
    'to_model',
    'name',
    'title',
    'message',
    'data',
    'image_id',
    'type',
    'is_readed',
  ];

  protected $casts =  [
    'data' => 'array',
  ];

  public function linking() {
    try {
      $this->sender = app($this->from_model)::find($this->from_id);
      $this->sender->linking();
    } catch (\Throwable $th) {
      Log::error('notification linking', [$th]);
      $this->sender = null;
    }
    try {
      $this->client = app($this->to_model)::find($this->to_id);
      $this->client->linking();
    } catch (\Throwable $th) {
      Log::error('notification linking', [$th]);
      $this->client = null;
    }
  }

  protected $dispatchesEvents = [
    'created' => NotificationEvent::class,
  ];
}
