<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
   * Returns a listing of the 20 latest collectibles
   *
   * @return \Illuminate\Http\Response
   */
  public function info()
  {
    // $user = auth()->user();
    // $user->isTeacher = $user->hasRole('teacher');
    // $user->isHod = $user->hasRole('hod');
    // $user->isAdmin = $user->hasRole('admin');
    
    return response()->json(['user' => auth()->user()]);
  }
}
