<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\WorkshopController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\SurveyController;

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

    Route::post('/admin/openDoor/', [AdminController::class, 'createOpenDoor']);
    Route::patch('/admin/openDoors/{openDoor}', [AdminController::class, 'updateOpenDoor']);
    Route::post('/admin/openDoors/{openDoor}/delete', [AdminController::class, 'deleteOpenDoor']);

    Route::get('/admin/posts/', [AdminController::class, 'getPosts']);
    Route::get('/admin/morePosts/', [AdminController::class, 'getMorePosts']);

    Route::post('/admin/term', [AdminController::class, 'createTerm']);
    Route::delete('/admin/term/{term}', [AdminController::class, 'deleteTerm']);

    Route::get('/admin/surveys', [AdminController::class, 'getSurveys']);
});

Route::group(['middleware'=>['auth:sanctum']], function(){
    Route::get('/userinfo', [UserController::class, 'info']);
    Route::get('/userinfo/teachers', [UserController::class, 'teachers']);
    Route::patch('/userinfo/preferences', [UserController::class, 'updatePreferences']);
});

/*
*
*   GUESTS
* 
*/

Route::get('/posts/published', [PostController::class, 'published']);
Route::get('/posts/search', [PostController::class, 'search']);

Route::get('/terms', [EventController::class, 'getTerms']);

Route::get('/workshops', [WorkshopController::class, 'index']);
Route::get('/workshops/themes', [WorkshopController::class, 'themes']);
Route::get('/workshops/{workshop}', [WorkshopController::class, 'show']);

Route::get('/events/upcoming', [EventController::class, 'upcoming']);
Route::get('/holidays', [EventController::class, 'holidays']);
Route::get('/openDoors', [EventController::class, 'openDoors']);

Route::get('/calendar/getMonths', [EventController::class, 'getCalendarMonths']);
// Route::get('/calendar/adjacentMonths', [EventController::class, 'getAdjacentMonths']);

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
    Route::patch('/posts/{post}/status', [PostController::class, 'updateStatus']);
    Route::post('/posts/{post}/cover', [PostController::class, 'updateCoverImage']);
    Route::post('/posts/{post}/image', [PostController::class, 'uploadImage']);
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
    Route::post('/workshops/{workshop}/apply', [WorkshopController::class, 'apply']);
    Route::post('/workshops/{workshop}/withdraw', [WorkshopController::class, 'withdraw']);

    Route::get('/workshops/{workshop}/surveys', [WorkshopController::class, 'surveys']);
});

Route::group(['middleware'=>['can:update,workshop']], function(){
    Route::patch('/workshops/{workshop}', [WorkshopController::class, 'update']);
    Route::post('/workshops/{workshop}/poster/{language}', [WorkshopController::class, 'poster']);
    Route::post('/workshops/{workshop}/editApplicantName/{user}', [WorkshopController::class, 'editApplicantName']);
    Route::delete('/workshops/{workshop}/poster/{language}', [WorkshopController::class, 'deletePoster']);
    Route::delete('/workshops/{workshop}/archive', [WorkshopController::class, 'archive']);
    Route::delete('/workshops/{workshop}', [WorkshopController::class, 'destroy']);

    Route::post('/workshops/{workshop}/session', [WorkshopController::class, 'createSession']);
    Route::patch('/workshops/{workshop}/sessions/{session}', [WorkshopController::class, 'updateSession']);
    Route::delete('/workshops/{workshop}/sessions/{session}', [WorkshopController::class, 'deleteSession']);
    Route::patch('/workshops/{workshop}/orderSessions', [WorkshopController::class, 'orderSessions']);
});

/*
*
*   SURVEYS
* 
*/

Route::group(['middleware'=>['auth:sanctum', 'can:create,App\Models\Survey']], function(){
    Route::post('/survey', [SurveyController::class, 'create']);
});

Route::group(['middleware'=>['can:update,survey']], function(){
    Route::patch('/survey/{survey}', [SurveyController::class, 'update']);
    Route::delete('/survey/{survey}', [SurveyController::class, 'destroy']);
});