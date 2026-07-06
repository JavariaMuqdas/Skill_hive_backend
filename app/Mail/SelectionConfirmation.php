<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SelectionConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public string $name;
    public string $jobTitle;
    public string $companyName;
    /* Create a new message instance.*/

    public function __construct($name, $jobTitle, $companyName)
    {
        $this->name = $name;
        $this->jobTitle = $jobTitle;
        $this->companyName = $companyName;
    }
    
    public function build()
    {
        return $this->subject('Job Selection Confirmation')
            ->view('emails.selection'); // This must match your blade file name
    }
}
