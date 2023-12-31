<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\WorkshopController;
use App\Http\Controllers\AdminController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


/*
*
*   ADMIN
* 
*/

Route::group(['middleware'=>['role:admin']], function(){
    Route::get('/admin/fetchUsers/{string}', [AdminController::class, 'fetchUsers']);
    Route::post('/admin/users/{user}/updateRoles', [AdminController::class, 'updateUserRoles']);
});

Route::group(['middleware'=>['auth:sanctum']], function(){
    Route::get('/userinfo', [UserController::class, 'info']);
});

/*
*
*   GUESTS
* 
*/

Route::get('/posts', [PostController::class, 'index']);

Route::get('/workshops', [WorkshopController::class, 'index']);
Route::get('/workshops/themes', [WorkshopController::class, 'themes']);


/*
*
*   POSTS
* 
*/

Route::group(['middleware'=>['can:view,post']], function(){
    Route::get('/posts/{post}', [PostController::class, 'show']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware'=>['auth:sanctum', 'can:create,App\Models\Post']], function(){
    Route::get('/myPosts', [PostController::class, 'myPosts']);
    Route::post('/posts', [PostController::class, 'store']);
});

Route::group(['middleware'=>['auth:sanctum', 'can:update,post']], function(){
    Route::patch('/posts/{post}', [PostController::class, 'update']);
});

Route::group(['middleware'=>['auth:sanctum', 'can:delete,post']], function(){
    Route::delete('/posts/{post}', [PostController::class, 'destroy']);
});

/*
*
*   WORKSHOPS
* 
*/

Route::group(['middleware'=>['auth:sanctum', 'can:create,App\Models\Workshop']], function(){
    Route::post('/workshops', [WorkshopController::class, 'store']);
    Route::get('/myWorkshops', [WorkshopController::class, 'myWorkshops']);
});

Route::group(['middleware'=>['can:view,workshop']], function(){
    Route::get('/workshops/{workshop}', [WorkshopController::class, 'show']);
});

Route::group(['middleware'=>['can:update,workshop']], function(){
    Route::patch('/workshops/{workshop}', [WorkshopController::class, 'update']);
});