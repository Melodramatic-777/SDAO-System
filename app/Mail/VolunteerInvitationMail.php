<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VolunteerInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $officeName;

    public function __construct($name, $officeName)
    {
        $this->name = $name;
        $this->officeName = $officeName;
    }

    public function build()
    {
        return $this->subject('Invitation to Participate in a Volunteer Opportunity')
                    ->view('emails.volunteer-invitation');
    }
}
