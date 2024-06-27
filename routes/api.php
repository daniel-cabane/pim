<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\WorkshopController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\EventController;

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
    
    Route::get('/admin/workshops/', [AdminController::class, 'allWorkshops']);
    Route::get('/admin/workshops/{workshop}/prepare', [AdminController::class, 'prepareWorkshop']);
    Route::post('/admin/workshops/{workshop}/launch', [AdminController::class, 'launchWorkshop']);

    Route::post('/admin/holiday/', [AdminController::class, 'createHoliday']);
    Route::patch('/admin/holidays/{holiday}', [AdminController::class, 'updateHoliday']);
    Route::delete('/admin/holidays/{holiday}', [AdminController::class, 'deleteHoliday']);
});

Route::group(['middleware'=>['auth:sanctum']], function(){
    Route::get('/userinfo', [UserController::class, 'info']);
    Route::get('/userinfo/teachers', [UserController::class, 'teachers']);
});

/*
*
*   GUESTS
* 
*/

Route::get('/posts', [PostController::class, 'index']);

Route::get('/workshops', [WorkshopController::class, 'index']);
Route::get('/workshops/themes', [WorkshopController::class, 'themes']);

Route::get('/events', [EventController::class, 'index']);
Route::get('/holidays', [EventController::class, 'holidays']);


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
    Route::post('/workshops/{workshop}/apply', [WorkshopController::class, 'apply']);
    Route::post('/workshops/{workshop}/withdraw', [WorkshopController::class, 'withdraw']);
});

Route::group(['middleware'=>['can:update,workshop']], function(){
    Route::patch('/workshops/{workshop}', [WorkshopController::class, 'update']);
    Route::post('/workshops/{workshop}/poster/{language}', [WorkshopController::class, 'poster']);
    Route::post('/workshops/{workshop}/editApplicantName/{user}', [WorkshopController::class, 'editApplicantName']);
    Route::delete('/workshops/{workshop}/poster/{language}', [WorkshopController::class, 'deletePoster']);
    Route::delete('/workshops/{workshop}/archive', [WorkshopController::class, 'archive']);
    Route::delete('/workshops/{workshop}', [WorkshopController::class, 'destroy']);
});

/*
*
*   EVENTS
* 
*/