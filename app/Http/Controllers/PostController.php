<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class PostController extends Controller
{
    /**
     * Display a listing of the resource. aol
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //Busca todas as postagens retornando junto o Id e o username do user de quem fez o post
        $posts = Post::with(['user:id,username'])->select('id', 'user_id', 'text', 'image', 'type', 'created_at')->paginate(8);
        if($posts->isEmpty()){
            return response()->json(['message' => 'Nenhuma postagem encontrada', 'status' => 204],Response::HTTP_NO_CONTENT);
        }
        return response()->json(['message' => 'Postagem encontrada', 'status' => 200, 'data' => $posts], Response::HTTP_OK);
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
            'text' => ['string'],
            'image' => ['file', 'image'],
            'type' => ['required', 'in:news, cancellations,curiosities, updates']
        ]);
        //Verifica se a imagem foi enviada, se não foi enviada salva como null o campo
        if ($request->hasFile('image')) {
            $images = $request->file('image');
            $images_url = $images->store('images_post', 'public');
        } else {
            $images_url = null;
        }

        $post = Post::create([
            'user_id' => $request->user()->id,
            'text' => $request->text,
            'image' => "storage/".$images_url,
            'type' => $request->type
        ]);

        return response()->json(['message' => 'Postagem criada', 'status' => 201,'data' => $post], Response::HTTP_CREATED);
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //Busca a postagem escolhida retornando junto o Id e o username do user de quem fez o post
        $post = Post::with('user:id,username')->select('id', 'user_id', 'text', 'image', 'type', 'created_at')->find($id);
        if (!$post) {
            return response()->json(['message' => 'Nenhuma postagem encontrada', 'status' => 204],Response::HTTP_NO_CONTENT);
        }
        return response()->json(['message' => 'Postagem encontrada', 'status' => 200, 'data' => $post], Response::HTTP_OK);
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $post = Post::find($id);

        $request->validate([
            'text' => ['string'],
            'image' => ['file', 'image']
        ]);

        if (!$post) {
            return response()->json(['message' => 'Postagem não encontrada', 'status' => 204], Response::HTTP_NO_CONTENT);
        }

        //verifica se foi enviado imagem, se sim exclui a antiga no storage/public, obtém o arquivo da nova imagem, armazena o arquivo
        if ($request->hasFile('image')) {
            if ($post->image) {
                $imagePath = str_replace('storage/', '', $post->image);
                Storage::disk('public')->delete($imagePath);
            }
            $image = $request->file('image');
            $images_url = $image->store('images_post', 'public');
        } else {
            $images_url = $post->image;
        }

        if ($request->user()->id == $post->user_id) {
            $post->update([
                'text' => $request->text ?: $post->text,
                'image' => "storage/".$images_url
            ]);
            $post->save();

            return response()->json(['message' => 'Postagem atualizada', 'status' => 200,'data' => $post], Response::HTTP_OK);
        }
        return response()->json(['message' => 'Você não tem permissão para realizar esta ação', 'status' => 403], Response::HTTP_FORBIDDEN);
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $post = Post::find($id);

        if (!$post) {
            return response()->json(['message' => 'Postagem não encontrada', 'status' => 204],Response::HTTP_NO_CONTENT);
        }

        //Verifica se o usuario logado é o dono do post para excluir. Se sim apaga primerio a imagem salva no storage/public e em seguida o post e suas dependencias.
        if ($request->user()->id == $post->user_id) {
            if ($post->image) {
                $imagePath = str_replace('storage/', '', $post->image);
                Storage::disk('public')->delete($imagePath);
            }
            $post->likes()->delete();
            $post->delete();
            return response()->json(['message' => 'Postagem excluída com sucesso', 'status' => 200], Response::HTTP_OK);
        }
        return response()->json(['message' => 'Você não tem permissão para realizar esta ação', 'status' => '403'], Response::HTTP_FORBIDDEN);
    }
    

    public function comments_post($post_id)
    {
        // Busca os comentários do post especificado
        $comments = Comment::where('post_id', $post_id)
            ->with('user:id,username')
            ->select('id', 'user_id', 'post_id', 'comment')
            ->get();

        if ($comments->isEmpty()) {
            return response()->json([
                'message' => 'Nenhum comentário encontrado',
                'status' => 204
            ], Response::HTTP_NO_CONTENT);
        }

        return response()->json([
            'message' => 'Comentários encontrados',
            'status' => 200,
            'data' => $comments
        ], Response::HTTP_OK);
    }

}
