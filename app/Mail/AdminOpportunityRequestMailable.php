<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminOpportunityRequestMailable extends Mailable
{
    use Queueable, SerializesModels;

    public array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function build()
    {
        return $this
            ->subject('New Volunteer Opportunity Request')
            ->view('emails.new-opportunity-admin')
            ->with([
                'name' => $this->data['name'] ?? '',
                'office' => $this->data['office'] ?? '',
                'date_time' => $this->data['date_time'] ?? '',
                'location' => $this->data['location'] ?? '',
                'title' => $this->data['title'] ?? '',
                                'type_of_activity' => $this->data['type_of_activity'] ?? '', // <-- added

            ]);
    }
}
