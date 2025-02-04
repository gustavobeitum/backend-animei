<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CommentController extends Controller
{    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $comments = Comment::with(['user:id,username'])->select('id', 'user_id', 'post_id', 'comment')->get();
        if($comments->isEmpty()){
            return response()->json(['message' => 'Comentários não encontrado', 'status' => 204],Response::HTTP_NO_CONTENT);
        }
        return response()->json(['message' => 'Comentários encontrado', 'status' => 200,'data' => $comments], Response::HTTP_OK);
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

        return response()->json(['message' => 'Comentário criado', 'status' => 201,'data' => $comment], Response::HTTP_CREATED);
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
            return response()->json(['message' => 'Comentario não encontrado', 'status' => 204], Response::HTTP_NO_CONTENT);
        }
        return response()->json(['message' => 'Comentário encontrado', 'status' => 200,'data' => $comment], Response::HTTP_OK);
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
            return response()->json(['message' => 'Comentário não encontrado', 'status' => 204], Response::HTTP_NO_CONTENT);
        }

        //Verifica se o usuario logado é o dono do comentário para poder alterar
        if ($request->user()->id == $comment->user_id) {
            $comment->comment = $request->comment;
            $comment->save();

            return response()->json(['message' => 'Comentário atualizado', 'status' => 200,'data' => $comment], Response::HTTP_OK);
        }
        return response()->json(['message' => 'Você não possui permissão para editar este comentário', 'status' => 403], Response::HTTP_FORBIDDEN);
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
            return response()->json(['message' => 'Comentário não encontrado','status' => 204], Response::HTTP_NO_CONTENT);
        }
        if ($request->user()->id == $comment->user_id) {
            $comment->likesComment()->delete();
            $comment->answers()->delete();
            $comment->delete();
            return response()->json(['message' => 'Comentário deletado com sucesso','status' => 200], Response::HTTP_OK);
        }
        return response()->json(['message' => 'Você não possui permissão para deletar este comentário', 'status' => 403], Response::HTTP_FORBIDDEN);
    }
}
