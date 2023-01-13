<?php

namespace App\Models;

use App\Events\MailCreateEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    'created' => MailCreateEvent::class,
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

}
