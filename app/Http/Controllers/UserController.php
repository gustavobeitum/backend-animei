<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::all();
        return response()->json(['message' => 'Usuários encontrado', 'status' => 200,'data' => $users], Response::HTTP_OK);
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
            'username' => ['required', 'string', 'unique:users'],
            'email' => ['required', 'string', 'email', 'unique:users'],
            'password' => ['required', 'string', 'min:6', 'confirmed']
        ]);


        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['message' => 'Usuário criado','status' =>201, 'token' => $token,'data' => $user], Response::HTTP_CREATED);
    }

     /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'Usuário não encontrado','status' => 204], Response::HTTP_NO_CONTENT);
        }
        return response()->json(['message' => 'Usuário encontrado','status' => 200,'data' => $user], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'Usuário não encontrado','status' => 204], Response::HTTP_NO_CONTENT);
        }

        $request->validate([
            'username' => ['string', 'max:25', Rule::unique('users')->ignore($user->id)],
            'name' => ['string', 'min:3'],
            'surname' => ['string'],
            'image' => ['file', 'image'],
            'email' => ['string', 'email', 'max:90', Rule::unique('users')->ignore($user->id)],
        ]);
        //Verifica se o usuário que está logado é o mesmo que está tentando atualizar, para não permitir alterar dados de outros usuários
        if ($user->id == $request->user()->id){

            //Se passar uma nova imagem, apaga a antiga e coloca a nova
            if ($request->hasFile('image')) {
                if ($user->image) {
                    Storage::disk('public')->delete($user->image);
                }
                $image = $request->file('image');
                $image_url = $image->store('images', 'public');
            } else {
                $image_url = $user->image;
            };

            $user->update([
                'username' => $request->username ?: $user->username,
                'name' => $request->name ?: $user->name,
                'surname' => $request->surname ?: $user->surname,
                'image' => $image_url,
                'email' => $request->email ?: $user->email,
            ]);
            
            return response()->json(['message' => 'Usuário atualizado','status' => 200,'data' => $user], Response::HTTP_OK);
        }
        return response()->json(['message' => 'Você não tem permissão para atualizar este usuário', 'status' => 403], Response::HTTP_FORBIDDEN);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,$id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'Usuário não encontrado', 'status' =>204], Response::HTTP_NO_CONTENT);
        }
        //Verifica se o usuário que está logado é o mesmo que está tentando deletar, para não permitir deletar dados de outros usuários.
        if ($user->id == $request->user()->id) {
            //Deleta a imagem do usuário no storage/public caso exista
            if ($user->image) {
                Storage::disk('public')->delete($user->image);
            }
            $user->posts()->delete();
            $user->comments()->delete();
            $user->answers()->delete();
            $deleted = $user->delete();

            if ($deleted) {
                return response()->json(['message' => 'Usuário deletado com sucesso','status' => 200], Response::HTTP_OK);
            } else {
                return response()->json(['message' => 'Erro ao deletar o usuário', 'status' => 500], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
        return response()->json(['message' => 'Você não possui permissão para deletar este usuário', 'status' => 403], Response::HTTP_FORBIDDEN);
    }
}
