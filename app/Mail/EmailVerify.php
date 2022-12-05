<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmailVerify extends Mailable {
  use Queueable, SerializesModels;

  public $verifyCode;

  /**
   * Create a new message instance.
   *
   * @return void
   */
  public function __construct($verifyCode) {
    $this->verifyCode = $verifyCode;
  }

  /**
   * Get the message envelope.
   *
   * @return \Illuminate\Mail\Mailables\Envelope
   */
  public function envelope() {
    $address = new Address('thowalid16@gmail.com', 'Service Electronic');
    return new Envelope(
      from: $address,
      subject: 'Email Verification',
    );
  }

  /**
   * Get the message content definition.
   *
   * @return \Illuminate\Mail\Mailables\Content
   */
  public function content() {
    return new Content(
      view: 'mails.email_verify',
      with: ['verify_code' => $this->verifyCode],
    );
  }

  /**
   * Get the attachments for the message.
   *
   * @return array
   */
  public function attachments() {
    return [];
  }
}
