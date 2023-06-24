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
    return response()->json(['user' => auth()->user()]);
  }
}
