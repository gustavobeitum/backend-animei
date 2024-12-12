<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $comments = Comment::paginate(2);
        return response()->json(['data' => $comments], Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => ['exists:users,id'],
            'post_id' => ['exists:posts,id'],
            'comment' => ['string','max:255']
        ]);

        $comment = Comment::create([
            'user_id' => $request->user()->id,
            'post_id' => $request->post_id,
            'comment' => $request->comment
        ]);

        return response()->json(['data' => $comment], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $comment = Comment::with('answers')->find($id);
        if (!$comment) {
            return response()->json(['messagem' => 'Comentario não encontrado'], Response::HTTP_NO_CONTENT);
        }
        return response()->json(['data' => $comment], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'comment' => ['string', 'max:255']
        ]);

        $comment = Comment::find($id);

        if (!$comment) {
            return response()->json(['messagem' => 'Comentário não encontrado'], Response::HTTP_NO_CONTENT);
        }

        if ($request->user()->id == $comment->user_id) {
            $comment->comment = $request->comment;
            $comment->save();

            return response()->json(['data' => $comment], Response::HTTP_OK);
        }
        return response()->json(['messagem' => 'Você não possui permissão para editar este comentário'], Response::HTTP_NO_CONTENT);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $comment = Comment::find($id);
        if (!$comment) {
            return response()->json(['Erro' => 'Impossível deletar, comentário não encontrado'], Response::HTTP_NO_CONTENT);
        }
        if ($request->user()->id == $comment->user_id) {
            $comment->likesComment()->delete();
            $comment->answers()->delete();
            $comment->delete();
            return response()->json(['messagem' => 'Comentário deletado com sucesso']);
        }
        return response()->json(['messagem' => 'Você não possui permissão para deletar este comentário'], Response::HTTP_NO_CONTENT);
    }
}
