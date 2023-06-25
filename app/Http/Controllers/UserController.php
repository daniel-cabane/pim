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
    $user = auth()->user();
    $user->roles = $user->getRoleNames();
    return response()->json(['user' => $user]);
  }
}
