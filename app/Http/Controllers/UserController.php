<?php

namespace App\Http\Controllers;

use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use Carbon\Carbon;

class UserController extends Controller
{
  public function info()
  { 
    return response()->json(['user' => auth()->user()]);
  }

  public function teachers()
  { 
    return response()->json(User::teachers()->get());
  }

  public function updatePreferences(Request $request)
  {
    $attrs = $request->validate([
      'init' => 'required|Boolean',
      'title' => 'sometimes|String|Nullable|max:16',
      'firstName' => 'sometimes|String|Nullable|max:25',
      'lastName' => 'sometimes|String|Nullable|max:25',
      'classLevel' => 'sometimes|String|Nullable|max:5',
      'className' => 'sometimes|String|Nullable|max:1',
      'language' => ['required', Rule::in(['fr', 'en', 'both'])],
      'campus' => ['required', Rule::in(['BPR', 'TKO', 'both'])]
    ]);

    $user = auth()->user();
    $preferences = $user->preferences;
    $preferences->language = $attrs['language'];
    $preferences->campus = $attrs['campus'];
    if($attrs['init']){
      if(!isset($attrs['firstName']) || !isset($attrs['lastName']) || ($user->is['teacher'] && !isset($attrs['title']))){
        return response()->json(['message' => 'Incomplete data'], 422);
      }
      $firstName = implode('-', explode(' ', ucwords(strtolower($attrs['firstName']))));
      $lastName = strtoupper($attrs['lastName']);
      if($user->is['student'] && isset($attrs['classLevel']) && isset($attrs['className'])){
        $user->update([
          'name' => "$firstName $lastName",
          'level' => $attrs['classLevel'],
          'section' => $attrs['className']
        ]);
      } else {
        $user->update(['name' => "$firstName $lastName"]);
      }
    }
    if($user->is['teacher'] && strlen($attrs['title']) >= 2){
      $preferences->title = $attrs['title'];
    }
    $user->update(['preferences' => $preferences]);

    return response()->json(['user' => $user]);
  }

  public function updateDetails(Request $request)
  {
    $attrs = $request->validate([
      'title' => 'sometimes|String|Nullable|max:16',
      'name' => 'sometimes|String|Nullable|max:50',
      'language' => ['required', Rule::in(['fr', 'en', 'both'])],
      'campus' => ['required', Rule::in(['BPR', 'TKO', 'both'])]
    ]);
    $user = auth()->user();
    $preferences = $user->preferences;
    if($user->is['teacher']){
      $preferences->title = $attrs['title'];
      $user->update(['name' => $attrs['name']]);
    }
    $preferences->language = $attrs['language'];
    $preferences->campus = $attrs['campus'];
    $user->update(['preferences' => $preferences]);

    return response()->json(['message' => 'Details updated']);
  }

  public function googleSigninRedirect()
  {
    return Socialite::driver('google')->redirect();
  }

  public function googleSigninCallback()
  {
    $google_user = Socialite::driver('google')->user();
    $user = User::where('email', $google_user->getEmail())->first();

    if(!$user){
      $user = User::create([
        'name' => $google_user->getName(),
        'email' => $google_user->getEmail(),
        'google_id' => $google_user->getId(),
        'email_verified_at' => Carbon::now(),
        'password' => Hash::make(Str::password()),
        'prefrences' => json_encode(['notifications' => 'all'])
      ]);
    }

    Auth::login($user);

    return redirect()->intended('/');
  }
}
