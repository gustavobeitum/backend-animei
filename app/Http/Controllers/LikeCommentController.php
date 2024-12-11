<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\LikeComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class LikeCommentController extends Controller
{
    public function likeComment(Request $request, $commentId)
    {
        // Usuário logado
        $user = $request->user();

        // Verifica se o comentário existe
        if (!Comment::find($commentId)) {
            return response()->json(['messagem' => 'Comentário não encontrado'], Response::HTTP_NOT_FOUND);
        }

        // Verifica se já existe uma curtida
        $like = LikeComment::where('user_id', $user->id)
                           ->where('comment_id', $commentId)
                           ->first();

        if ($like) {
            //  Opção de descurtir
            $like->delete();
            $this->updateLikeCountCache($commentId);
            return response()->json(['messagem' => 'Curtida removida'], Response::HTTP_OK);
        } else {
            // Opção de curtir
            LikeComment::create([
                'user_id' => $user->id,
                'comment_id' => $commentId,
            ]);
            $this->updateLikeCountCache($commentId);
            return response()->json(['message' => 'Curtida adicionada'], Response::HTTP_OK);
        }
    }

    public function likeCountComment($commentId)
    {
        // Cache da contagem
        $count = Cache::remember("comment_{$commentId}_like_count", 60, function () use ($commentId) {
            return LikeComment::where('comment_id', $commentId)->count();
        });

        return response()->json(['likes' => $count]);
    }
    // Atualiza o cache da contagem de curtidas
    private function updateLikeCountCache($commentId)
    {
        $count = LikeComment::where('comment_id', $commentId)->count();
        Cache::put("comment_{$commentId}_like_count", $count, 60); // Atualiza o cache por 60 minutos
    }
}
