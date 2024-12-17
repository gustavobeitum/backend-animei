<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class NewPasswordController extends Controller
{
    public function check_code_password(Request $request)
    {
        $request->validate([
            'code' => ['required','numeric']
        ]);

        $user = $request->user();
        $code = $request->code;

        // Verifica o código no cache
        $cachedCode = Cache::get("password_reset_code_{$user->email}");
        if (!$cachedCode || $cachedCode != $code) {
            return response()->json(['message' => 'Código inválido ou expirado', 'status' =>400], Response::HTTP_BAD_REQUEST);
        } 
        return response()->json(['message' => 'Código válido']);
    }

    public function newpassword(Request $request){
        $request->validate([
            'email' => ['required','email','exists:users','email'],
            'password' => ['required','min:6','confirmed'],
        ]);
        $email = $request->email;

        // Atualiza a senha do usuário
        $user = User::where('email', $email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        // Remove o código do cache
        Cache::forget("password_reset_code_{$email}");

        return response()->json(['message' => 'Senha redefinida com sucesso', 'status' => 200], Response::HTTP_OK);
    }
}
