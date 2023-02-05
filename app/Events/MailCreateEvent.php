<?php

namespace App\Events;

use App\Models\Admin;
use App\Models\Mail;
use App\Models\Notification;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Mail\Message;
use Illuminate\Queue\SerializesModels;

class MailCreateEvent {
  use Dispatchable, InteractsWithSockets, SerializesModels;

  /**
   * Create a new event instance.
   *
   * @return void
   */
  public function __construct(Mail $mail) {
    $mail->linking();
    \Illuminate\Support\Facades\Mail::raw($mail->title, function (Message $message) use($mail) {
      foreach ($mail->targetsMails as $email) {
       $message->to($email)
               ->subject($mail->title)
               ->html($mail->rendredContent);
      }
    });
    foreach ($mail->unreades ?? [] as $admin_id) {
      Notification::create([
        'to_id' => $admin_id,
        'to_model' => Admin::class,
        'name' => 'new-mail-created',
        'title' => 'A new mail created',
        'message' => 'Mail sended to (' . join(', ', $mail->targets) . ')',
        'data' => [
          'mail_id' => $mail->id,
        ],
        'image_id' => 'logo',
        'type' => 'emit',
      ]);
    }
  }

  /**
   * Get the channels the event should broadcast on.
   *
   * @return \Illuminate\Broadcasting\Channel|array
   */
  public function broadcastOn() {
    return new PrivateChannel('channel-name');
  }
}
