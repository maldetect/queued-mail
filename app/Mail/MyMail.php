<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MyMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $mail;

    /**
     * Create a new message instance.
     *
     * @return void
     */



    public function __construct($mail)
    {
        $this->mail = $mail;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $response = $this->subject($this->mail['subject']);
        if (array_key_exists ('attachments',$this->mail)){
            foreach($this->mail['attachments'] as $attachment){
                $response->attachData(base64_decode($attachment['base64']), $attachment['filename']);
            }
        }

        return $response->markdown('emails.mail');

    }
}
