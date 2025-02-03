<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WialonUpdateReport extends Mailable
{
    use Queueable, SerializesModels;

    public $pdfPath;

    public function __construct($pdfPath)
    {
        $this->pdfPath = $pdfPath;
    }

    public function build()
    {
        return $this->from('elvisguato02@gmail.com', 'Elvis Guato')
                    ->subject('Reporte de Actualización en Wialon')
                    ->view('emails.reporte')
                    ->attach($this->pdfPath);
    }
}
