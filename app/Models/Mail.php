<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mail extends Model {
  use HasFactory;

  protected $fillable = [
    'template_id',
    'manager',
    'data',
    'targets',
    'attachments',
    'unreades',
  ];

  protected $casts = [
    'data' => 'array',
    'targets' => 'array',
    'attachments' => 'array',
    'unreades' => 'array',
    'created_at' => 'datetime:Y-m-d H:m:s',
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
    $this->managerClass = app($this->manager);
  }

  protected $dispatchesEvents = [
    'created' => FileDeleteEvent::class,
  ];

}
