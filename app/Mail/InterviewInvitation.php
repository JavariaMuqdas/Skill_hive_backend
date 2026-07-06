<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

// class InterviewInvitation extends Mailable
// {
//     use Queueable, SerializesModels;

//     public $name;
//     public $jobTitle;

//     /**
//      * Create a new message instance.
//      */
//     public function __construct($name, $jobTitle)
//     {
//         // Frontend se bheja gaya data yahan assign ho raha hai
//         $this->name = $name;
//         $this->jobTitle = $jobTitle;
//     }

//     /**
//      * Build the message.
//      */
//     public function build()
//     {
//         return $this->subject('Interview Invitation for ' . $this->jobTitle)
//             ->view('emails.interview'); // Ye file hum resources/views/emails mein banayenge
//     }
// }
class InterviewInvitation extends Mailable
{
    use Queueable, SerializesModels;

    // 1. Saare variables yahan declare karein
    public string $name;
    public string $jobTitle;
    public string $interviewDate;
    public string $interviewTime;
    public string $companyName;
    public string $location;
    public string $interviewerName;
    public string $hrName;

    public function __construct(array $details)
    {
        $this->name = $details['name'];
        $this->jobTitle = $details['jobTitle'];
        $this->interviewDate = $details['interviewDate'];
        $this->interviewTime = $details['interviewTime'];
        $this->companyName = $details['companyName'];
        $this->location = $details['location'] ?? 'Online';
        $this->interviewerName = $details['interviewerName'] ?? 'HR Team';
        $this->hrName = $details['hrName'] ?? 'HR Manager';
    }

    public function build()
    {
        return $this->subject('Interview Invitation from ' . $this->companyName)
            ->view('emails.interview');
    }
}
