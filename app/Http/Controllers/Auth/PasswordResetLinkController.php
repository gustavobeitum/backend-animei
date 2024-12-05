<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

class PasswordResetLinkController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required','email','exists:users','email'],
        ]);

        $email = $request->email;
        $code = random_int(100000, 999999); // Gera um código de 6 dígitos

        // Armazena o código no cache por 15 minutos
        Cache::put("password_reset_code_{$email}", $code, 900);

        // Envia o código por e-mail
        Mail::send('emails.password-reset', ['code' => $code], function ($message) use ($email) {
            $message->to($email)->subject('Código de recuperação de senha');
        });
        

        return response()->json(['message' => 'Código enviado para o e-mail'], Response::HTTP_OK);
    }
}