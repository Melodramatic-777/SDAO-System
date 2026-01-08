<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordChangedMailable extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $email;
    public $date;

    public function __construct($name, $email)
    {
        $this->name = $name;
        $this->email = $email;
        $this->date = now()->setTimezone('Asia/Manila')->toDateTimeString();
    }

    public function build()
    {
        return $this->subject('Your Password Has Been Changed')
            ->view('emails.password-changed');
    }
}
