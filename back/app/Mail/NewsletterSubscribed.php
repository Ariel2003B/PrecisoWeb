<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewsletterSubscribed extends Mailable
{
    use Queueable, SerializesModels;

    public $correo;

    public function __construct($correo)
    {
        $this->correo = $correo;
    }

    public function build()
    {
        return $this->subject('¡Gracias por suscribirte!')
                    ->view('emails.newsletter');
    }
}
