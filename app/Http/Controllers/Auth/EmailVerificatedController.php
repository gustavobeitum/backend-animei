<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

class EmailVerificatedController extends Controller
{
    public function verification_email(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'], // Garante que o email seja enviado na requisição
            'code' => ['required', 'numeric']
        ]);

        $email = $request->input('email');
        $code = $request->input('code');

        // Verifica o código no cache
        $cachedCode = Cache::get("email_verification_code_{$email}");
        if (!$cachedCode || $cachedCode != $code) {
            return response()->json(['message' => 'Código inválido ou expirado', 'status' => 400], Response::HTTP_BAD_REQUEST);
        }

        // Busca o usuário pelo e-mail
        $user = \App\Models\User::where('email', $email)->first();
        if (!$user) {
            return response()->json(['message' => 'Usuário não encontrado', 'status' => 404], Response::HTTP_NOT_FOUND);
        }

        // Salva o dia e hora que foi feita a confirmação do e-mail
        $user->email_verified_at = now();
        $user->save();

        // Remove o código do cache
        Cache::forget("email_verification_code_{$email}");

        return response()->json(['message' => 'E-mail verificado com sucesso', 'status' => 200], Response::HTTP_OK);
    }
}
