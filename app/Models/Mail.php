<?php

namespace App\Models;

use App\Events\MailCreatedEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Mail
 *
 * @property int $id
 * @property string $title
 * @property string $template_id
 * @property array $data
 * @property array $targets
 * @property array $unreades
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Mail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Mail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Mail query()
 * @method static \Illuminate\Database\Eloquent\Builder|Mail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mail whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mail whereTargets($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mail whereTemplateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mail whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mail whereUnreades($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mail whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Mail extends Model {
  use HasFactory;

  protected $fillable = [
    'title',
    'template_id',
    'data',
    'targets',
    'unreades',
  ];

  protected $casts = [
    'data' => 'array',
    'targets' => 'array',
    'unreades' => 'array',
    'created_at' => 'datetime:Y-m-d H:m:s',
  ];

  protected $dispatchesEvents = [
    'created' => MailCreatedEvent::class,
  ];

  static function news($admin_id) {
    $mails = Mail::where('unreades', '!=', '[]')->get();
    $newsMails = [];
    foreach ($mails as $mail) {
      if(in_array($admin_id, $mail->unreades))
        $newsMails[$mail->id] = $mail;
    }
    return $newsMails;
  }

  static function readNews($admin_id) {
    $items = Mail::news($admin_id);
    foreach ($items as $item) {
      $item->unreades = array_diff($item->unreades, [$admin_id]);
      $item->save();
    }
  }

  public function linking() {
    $this->template = Template::find($this->template_id);
    if(is_string($this->data)) $this->data = json_decode($this->data);
    $targetsMails = [];
    foreach ($this->targets as $userId) {
      $user = User::find($userId);
      if($user) $targetsMails[] = $user->email;
    }
    $this->targetsMails = $targetsMails;
    $this->rendredContent = str_replace(
      array_keys($this->data),
      array_values($this->data),
      $this->template->content
    );
  }

  public function unlinking() {
    unset($this->template);
    unset($this->data);
    unset($this->rendredContent);
  }

  public function unlinkingAndSave() {
    $this->unlinking();
    $this->save();
  }

  static function clearCache() {
    async(function() {
      $mails = Mail::all();
      foreach ($mails as $mail) {
        $mail->delete();
      }
    })->start();
  }
}
