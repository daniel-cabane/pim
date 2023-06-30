<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use Carbon\Carbon;

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

  public function googleSigninRedirect()
  {
    logger('redirect');
    return Socialite::driver('google')->redirect();
  }

  public function googleSigninCallback()
  {
    logger('callback');
    $google_user = Socialite::driver('google')->user();
    $user = User::where('email', $google_user->getEmail())->first();

    if(!$user){
      $user = User::create([
        'name' => $google_user->getName(),
        'email' => $google_user->getEmail(),
        'google_id' => $google_user->getId(),
        'email_verified_at' => Carbon::now(),
        'password' => Hash::make(Str::password())
      ]);
    }

    Auth::login($user);

    return redirect()->intended('/');
  }
}
