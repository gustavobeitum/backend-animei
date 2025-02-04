<?php

use App\Http\Controllers\AnswerController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificatedController;
use App\Http\Controllers\Auth\EmailVerificationWithCodeController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\LikeCommentController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\SearchPostController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->group(function(){
    
    Route::middleware('check')->group(function(){

        Route::delete('/user/{user_id}', [UserController::class, 'destroy']);

        Route::post('/post', [PostController::class, 'store']);
        Route::put('/post/{post_id}', [PostController::class, 'update']);
        Route::delete('/post/{post_id}', [PostController::class, 'destroy']);

        Route::post('/like/{post_id}', [LikeController::class, 'LikePost']);

        Route::post('/like-comment/{comment_id}', [LikeCommentController::class, 'likeComment']);

        Route::post('/comment', [CommentController::class, 'store']);
        Route::put('/comment/{comment_id}', [CommentController::class, 'update']);
        Route::delete('/comment/{comment_id}', [CommentController::class, 'destroy']);

        
        Route::post('/answer', [AnswerController::class, 'store']);
        Route::put('/answer/{answer_id}', [AnswerController::class, 'update']);
        Route::delete('/answer/{answer_id}', [AnswerController::class, 'destroy']);
    });

    Route::put('/user/{user_id}', [UserController::class, 'update']);

    Route::delete('/logout', [AuthenticatedSessionController::class, 'destroy']);

    Route::post('/reset-code-of-password', [NewPasswordController::class, 'check_code_password']);
    Route::post('/reset-password', [NewPasswordController::class, 'newpassword']);

    Route::post('/request-code-email', [EmailVerificationWithCodeController::class, 'request_code_email']);
    Route::post('/verification-code-email', [EmailVerificatedController::class, 'verification_email']);

    Route::get('/post', [PostController::class, 'index']);
    Route::get('/post/{post}/comments', [PostController::class,'comments_post']);
    Route::get('/post/{post_id}', [PostController::class, 'show']);

    Route::get('/countLikes-post/{post_id}', [LikeController::class, 'likeCount']);

    Route::get('/countLikes-comment/{comment_id}', [LikeCommentController::class, 'likeCountComment']);

    Route::get('/search-post', [SearchPostController::class, 'searchPost']);

    Route::get('/comment', [CommentController::class, 'index']);
    Route::get('/comment/{comment_id}', [CommentController::class, 'show']);

    Route::get('/answer', [AnswerController::class, 'index']);
    Route::get('/answer/{answer_id}', [AnswerController::class, 'show']);
});

Route::get('/user', [UserController::class, 'index']);
Route::get('/user/{user_id}', [UserController::class, 'show']);
Route::post('/user', [UserController::class, 'store']);

Route::post('/login', [AuthenticatedSessionController::class, 'store']);
Route::post('/forgot-password', [PasswordResetLinkController::class, 'store']);
