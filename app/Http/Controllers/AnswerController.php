<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AnswerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $answers = Answer::with(['user:id,username'])->select('id', 'comment_id', 'user_id', 'response')->get();
        if($answers->isEmpty()){
            return response()->json(['message' => 'Resposta não encontrada', 'status' => 204],Response::HTTP_NO_CONTENT);
        }
        return response()->json(['message' => 'Respostas encontrada', 'status' => 200,'data' => $answers], Response::HTTP_OK);
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
            'user_id' => ['exists:users,id', 'integer'],
            'comment_id' => ['exists:comments,id', 'integer'],
            'response' => ['max:100'],
        ]);
        $answer = Answer::create([
            'user_id' => $request->user()->id,
            'comment_id' => $request->comment_id,
            'response' => $request->response,
        ]);

        return response()->json(['message' => 'Resposta criada', 'status' => 201,'data' => $answer], Response::HTTP_CREATED);
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Answer  $answer
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $answer = Answer::find($id);
        if (!$answer) {
            return response()->json(['message' => 'Resposta não encontrada', 'status' => 204], Response::HTTP_NO_CONTENT);
        }

        return response()->json(['message' => 'Resposta encontrada', 'status' => 200,'data' => $answer], Response::HTTP_OK);
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Answer  $answer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'response' => ['string','max:255'],
        ]);
        $answer = Answer::find($id);
        if (!$answer) {
            return response()->json(['message' => 'Resposta não encontrada', 'status' => 204], Response::HTTP_NO_CONTENT);
        }

        //Verifica se o usuario logado é o dono da resposta para poder alterar
        if ($request->user()->id == $answer->user_id) {
            $answer->response = $request->response;
            $answer->save();

            return response()->json(['message' => 'Resposta atualizada', 'status' => 200,'data' => $answer], Response::HTTP_OK);
        }
        return response()->json(['message' => 'Você não possui permissão para alterar essa resposta','status' => 403], Response::HTTP_FORBIDDEN);
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Answer  $answer
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $answer = Answer::find($id);
        if (!$answer) {
            return response()->json(['message' => 'Resposta não encontrada','status' => 204], Response::HTTP_NO_CONTENT);
        }

        if ($request->user()->id == $answer->user_id) {
            $answer->delete();

            return response()->json(['message' => 'Resposta deletada com sucesso','status' => 200], Response::HTTP_OK);
        }
        return response()->json(['message' => 'Você não possui permissão para deletar este comentário', 'status' => 403], Response::HTTP_FORBIDDEN);
    }
}
