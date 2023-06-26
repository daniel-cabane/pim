<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/{name}', function(){
  return view('welcome');
})->where(['name' => 'home|workshops|calendar|myPosts']);

Route::get('/posts/{slug}', function () {
    return view('welcome');
});

Route::get('/posts/{slug}/edit', function () {
    return view('welcome');
});

Route::get('/auth/reset-password', function () {
    return view('password-reset');
})->name('password.reset');
