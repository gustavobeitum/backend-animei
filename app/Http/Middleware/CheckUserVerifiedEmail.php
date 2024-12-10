<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserVerifiedEmail
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        //verifica se o usuario fez a verificação do email
        if (Auth::check() && Auth::user()->email_verified_at == null) {
            return response()->json(['mensage' => 'Usuário não verificou o e-mail. Verifique antes de prosseguir.'], Response::HTTP_NO_CONTENT);
        }
        return $next($request);
    }
}
