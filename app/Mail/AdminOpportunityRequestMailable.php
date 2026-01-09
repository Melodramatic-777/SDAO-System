<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminOpportunityRequestMailable extends Mailable
{
    use Queueable, SerializesModels;

    public array $details;

    public function __construct(array $details)
    {
        $this->details = $details;
    }

    public function build()
    {
        return $this
            ->subject('New Volunteer Opportunity Request')
            ->view('emails.new-opportunity-admin');
    }
}
