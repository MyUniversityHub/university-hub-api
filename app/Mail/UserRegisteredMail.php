<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserRegisteredMail extends Mailable
{
    use Queueable, SerializesModels;
    public $user_name;
    public $password;
    /**
     * Create a new message instance.
     */
    public function __construct($user_name, $password)
    {
        $this->user_name = $user_name;
        $this->password = $password;
    }

//    /**
//     * Get the message envelope.
//     */
//    public function envelope(): Envelope
//    {
//        return new Envelope(
//            subject: 'User Registered Mail',
//        );
//    }
//
//    /**
//     * Get the message content definition.
//     */
//    public function content(): Content
//    {
//        return new Content(
//            view: 'view.name',
//        );
//    }
//
//    /**
//     * Get the attachments for the message.
//     *
//     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
//     */
//    public function attachments(): array
//    {
//        return [];
//    }

    public function build()
    {
        return $this->subject('Thông tin tài khoản của bạn')
            ->view('emails.user_registered')
            ->with([
                'user' => $this->user_name,
                'password' => $this->password
            ]);
    }
}
