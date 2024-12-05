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
        return response()->json(['data' => $users], Response::HTTP_OK);
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

        return response()->json(['data' => $user, 'token' => $token], Response::HTTP_CREATED);
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
            return response()->json(['messagem' => 'Usuário não encontrado'], Response::HTTP_NO_CONTENT);
        }
        return response()->json(['data' => $user], Response::HTTP_OK);
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
            return response()->json(['messagem' => 'Impossível realizar atualização, usuário não encontrado'], Response::HTTP_NO_CONTENT);
        }

        $request->validate([
            'username' => ['string', 'max:25', Rule::unique('users')->ignore($user->id)],
            'name' => ['string', 'min:3'],
            'surname' => ['string'],
            'image' => ['file', 'image'],
            'email' => ['string', 'email', 'max:90', Rule::unique('users')->ignore($user->id)],
        ]);

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
        
        return response()->json(['data' => $user], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['messagem' => 'Impossível deletar, usuário não encontrado'], Response::HTTP_NO_CONTENT);
        }

        if ($user->image) {
            Storage::disk('public')->delete($user->image);
        }

        $deleted = $user->delete();

        if ($deleted) {
            return response()->json(['messagem' => 'Usuário deletado com sucesso']);
        } else {
            return response()->json(['messagem' => 'Erro ao deletar o usuário'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
