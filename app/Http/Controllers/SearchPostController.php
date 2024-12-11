<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SearchPostController extends Controller
{
    public function searchPost(request $request)
    {
        $validatedData = $request->validate([
            'type' => ['string','in:news, cancellations,curiosities, updates']
        ]);

        $type = $validatedData['type'];

        $posts = Post::where('type', $type)->get();

        if ($posts->isEmpty()) {
            return response()->json(['messagem' => 'Não há postagem'], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['data' => $posts], Response::HTTP_OK);
    }
}
