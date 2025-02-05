<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CORREO;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewsletterSubscribed;

class NewsletterController extends Controller
{
    public function subscribe(Request $request)
    {
        // Validar el email
        $request->validate([
            'email' => 'required|email|unique:CORREOS,EMAIL'
        ]);

        // Guardar el email en la base de datos
        $correo = CORREO::create([
            'EMAIL' => $request->email
        ]);

        // Enviar el correo de bienvenida
        Mail::to($correo->EMAIL)->send(new NewsletterSubscribed($correo));

        return response()->json(['message' => 'Suscripción exitosa. Revisa tu correo.']);
    }
}
