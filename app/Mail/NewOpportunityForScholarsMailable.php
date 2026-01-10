<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewOpportunityForScholarsMailable extends Mailable
{
    use Queueable, SerializesModels;

    public function build()
    {
        return $this
            ->subject('New Volunteer Opportunity Available')
            ->view('emails.new-opportunity-scholar');
    }
}
