<?php

namespace App\Http\Controllers;

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
        $posts = Post::all();
        if($posts->isEmpty()){
            return response()->json(['messagem' => 'Não há postagens'], Response::HTTP_NOT_FOUND);
        }
        return response()->json(['data' => $posts], Response::HTTP_OK);
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

        Post::create([
            'user_id' => $request->user()->id,
            'text' => $request->text,
            'image' => $images_url,
            'type' => $request->type
        ]);

        return response()->json(['data' => 'Post criado com sucesso'], Response::HTTP_CREATED);
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $post = Post::find($id);
        if (!$post) {
            return response()->json(['messagem' => 'Postagem não encontrada'], Response::HTTP_NOT_FOUND);
        }
        return response()->json(['data' => $post], Response::HTTP_OK);
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
            return response()->json(['messagem' => 'Postagem não encontrada'], Response::HTTP_NOT_FOUND);
        }

        //verifica se foi enviado imagem, se sim exclui a antiga no storage, obtém o arquivo da nova imagem, armazena o arquivo e gera a URL pública da nova imagem
        if ($request->hasFile('image')) {
            if ($post->image) {
                Storage::disk('public')->delete($post->image);
            }
            $image = $request->file('image');
            $images_url = $image->store('images_post', 'public');
        } else {
            $images_url = $post->image;
        }

        if ($request->user()->id == $post->user_id) {
            $post->update([
                'text' => $request->text ?: $post->text,
                'image' => $images_url
            ]);
            $post->save();

            return response()->json(['data' => $post], Response::HTTP_OK);
        }
        return response()->json(['mensagem' => 'Você não tem permissão para realizar esta ação'], Response::HTTP_NO_CONTENT);
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
            return response()->json(['Erro' => 'Impossível deletar, postagem não encontrada'], Response::HTTP_NO_CONTENT);
        }

        if ($request->user()->id == $post->user_id) {
            if ($post->image) {
                Storage::disk('public')->delete($post->image);
            }
            $post->delete();
            return response()->json(['mensagem' => 'Postagem excluída com sucesso'], Response::HTTP_OK);
        }
        return response()->json(['mensagem' => 'Você não tem permissão para realizar esta ação'], Response::HTTP_NO_CONTENT);
    }
}
