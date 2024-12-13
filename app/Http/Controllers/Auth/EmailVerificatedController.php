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
            'code' => ['required','numeric']
        ]);

        $user = $request->user();

        $code = $request->code;

        // Verifica o código no cache
        $cachedCode = Cache::get("email_verification_code_{$user->email}");
        if (!$cachedCode || $cachedCode != $code) {
            return response()->json(['message' => 'Código inválido ou expirado','status' => 400], Response::HTTP_BAD_REQUEST);
        }
        //Salva o dia e hora que foi feito a confirmação do email
        $user->email_verified_at = time();
        $user->save();

        // Remove o código do cache
        Cache::forget("email_verification_code_{$user->email}");

        return response()->json(['message' => 'Email verificado com sucesso', 'status' => 200], Response::HTTP_OK);
    }
}
