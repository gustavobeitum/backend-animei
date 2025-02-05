<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

class EmailVerificationWithCodeController extends Controller
{
    public function request_code_email(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $email = $request->input('email');

        // Gera um código de 6 dígitos
        $code = random_int(100000, 999999);

        // Armazena o código no cache por 15 minutos
        Cache::put("email_verification_code_{$email}", $code, 900);

        // Envia o código por e-mail
        Mail::send('emails.confirmation-email', ['code' => $code], function ($message) use ($email) {
            $message->to($email)->subject('Código de recuperação de senha');
        });
        
        return response()->json(['message' => 'Código enviado para o e-mail', 'status' => 200], Response::HTTP_OK);
    }
}
