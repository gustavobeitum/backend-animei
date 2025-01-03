<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class LikeController extends Controller
{
    public function LikePost(Request $request, $postId)
    {
        $user = $request->user(); // Usuário autenticado

        // Verifica se o post existe (supondo que há um modelo Post)
        if (!Post::find($postId)) {
            return response()->json(['message' => 'Post não encontrado','status' => 204], Response::HTTP_NO_CONTENT);
        }
        // Verifica se já existe uma curtida 
        $like = Like::where('user_id', $user->id)->where('post_id', $postId)->first();

        if ($like) {
            // Opção de descurtir
            $like->delete();
            $this->updateLikeCountCache($postId);
            return response()->json(['message' => 'Curtida removida', 'status' => 200], Response::HTTP_OK);
        } else {
            // Opção de curtir
            Like::create([
                'user_id' => $user->id,
                'post_id' => $postId,
            ]);
            $this->updateLikeCountCache($postId);
            return response()->json(['message' => 'Curtida adicionada', 'status' => 200], Response::HTTP_OK);
        }
    }

    // Retornar contagem de curtidas
    public function LikeCount($postId)
    {
        // Cache da contagem
        $count = Cache::remember("post_{$postId}_like_count", 60, function () use ($postId) {
            return Like::where('post_id', $postId)->count();
        });

        return response()->json(['message' => 'Contagem feita com sucesso', 'status' => 200,'likes' => $count]);
    }

    // Atualiza o cache da contagem de curtidas
    private function updateLikeCountCache($postId)
    {
        $count = Like::where('post_id', $postId)->count();
        Cache::put("post_{$postId}_like_count", $count, 60); // Atualiza o cache por 60 minutos
    }
}
