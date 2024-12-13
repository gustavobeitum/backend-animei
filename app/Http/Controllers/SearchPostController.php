<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SearchPostController extends Controller
{
    public function searchPost(request $request)
    {
        // Valida os dados recebidos na requisição
        $validatedData = $request->validate([
            'type' => ['string','in:news, cancellations,curiosities, updates']
        ]);

        // Valida os dados recebidos na requisição
        $type = $validatedData['type'];

        // Realiza uma consulta na tabela 'posts' para encontrar registros onde o campo 'type' seja igual ao valor fornecido
        $posts = Post::where('type', $type)->get();

        if ($posts->isEmpty()) {
            return response()->json(['message' => 'Não há postagem', 'status' => 204], Response::HTTP_NO_CONTENT);
        }

        return response()->json(['message' => 'Postagem encontrada com sucesso','status' => 200,'data' => $posts], Response::HTTP_OK);
    }
}
