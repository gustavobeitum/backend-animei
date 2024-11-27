<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class AuthenticatedSessionController extends Controller
{
    //Método de Login
    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Busca o usuário pelo email
        $user = User::where('email', $request->email)->first();

        // Verifica se o usuário existe e se a senha está correta
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(
                ['message' => 'Invalid credentials'],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        // Gera o token de autenticação e retorna o usuário e token
        return response()->json([
            'message' => 'Login successful',
            'token' => $user->createToken($request->email)->plainTextToken,
            'user' => $user,  // Já tem o usuário disponível
        ], Response::HTTP_OK);
    }

    // Método para logout
    public function destroy(Request $request)
    {
        // Remove o token de acesso atual do usuário autenticado
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout successful',
        ], Response::HTTP_OK);
    }
}
