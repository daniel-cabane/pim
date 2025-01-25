<?php

namespace App\Http\Controllers;

use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Term;
use App\Models\Holiday;
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
    $preferences = isset($user->preferences) ? $user->preferences : (object)[];
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
        'preferences' => ['notifications' => 'all']
      ]);

      $teacherEmails = [
            'adelettrez' => ['name' => 'Antoine Delettrez', 'campus' => 'BPR', 'language' => 'both'],
            'bbarre' => ['name' => 'Benjamin Barre', 'campus' => 'both', 'language' => 'both'],
            'jcabrit' => ['name' => 'Jose Cabrit', 'campus' => 'BPR', 'language' => 'both'],
            'ngouez' => ['name' => 'Nicolas Gouez', 'campus' => 'BPR', 'language' => 'both'],
            'dcabane' => ['name' => 'Daniel Cabane', 'campus' => 'both', 'language' => 'both'],
            'rdelpuech' => ['name' => 'Rémi Delpuech', 'campus' => 'both', 'language' => 'both'],
            'sparis' => ['name' => 'Sébastien Paris', 'campus' => 'both', 'language' => 'both'],
            'ghenry' => ['name' => 'Guillaume Henry', 'campus' => 'both', 'language' => 'both'],
            'tbelmekki' => ['name' => 'Tarik Belmekki', 'campus' => 'both', 'language' => 'both'],
            'fmarmounier' => ['name' => 'Florent Marmounier', 'campus' => 'both', 'language' => 'both'],
            'hwright' => ['name' => 'Hannah Wright', 'campus' => 'BPR', 'language' => 'en'],
            'mechevarria' => ['name' => 'Mikel Echevarria', 'campus' => 'BPR', 'language' => 'en'],
            'slai' => ['name' => 'Sylvia Lai', 'campus' => 'BPR', 'language' => 'en'],
            'jhamilton' => ['name' => 'Jonathan Hamilton', 'campus' => 'BPR', 'language' => 'en'],
            'tmaclean' => ['name' => 'Timothy Maclean', 'campus' => 'BPR', 'language' => 'en']
        ];
        $emailParts = explode('@', $user->email);

        if($user->google_id != null && $emailParts[1] == 'g.lfis.edu.hk'){
          if(isset($teacherEmails[$emailParts[0]])){
            $user->assignRole('teacher');
            $user->assignRole('publisher');
            $user->update([
                'name' => $teacherEmails[$emailParts[0]]['name'],
                'preferences' => [
                  'notifications' => 'all',
                  'title' => 'M.',
                  'campus' => $teacherEmails[$emailParts[0]]['campus'],
                  'language' => $teacherEmails[$emailParts[0]]['language']
                ]
            ]);
          } else if(is_numeric(substr($emailParts[0], -1))){
            $user->assignRole('student');
          }
        }
    }

    Auth::login($user);

    logger(session('route'));
    if(session('route')){
      return redirect()->intended(session('route'));
    }

    return redirect()->intended('/');
  }

  public function myActivity()
  {
    return response()->json(['activity' => auth()->user()->activity()]);
    // $user = auth()->user();
    // $terms = [];
    // $hoursDone = $user->hoursPerTerm();
    // foreach(Term::orderBy('nb')->get() as $term){
    //     $weeksHoliday = 0;
    //     foreach(Holiday::whereBetween('start', [$term->start_date, $term->finish_date])->get() as $holiday){
    //         $holidayLength = (Carbon::parse($holiday->start))->diffInDays($holiday->finish);
    //         if($holidayLength >= 5){
    //             $weeksHoliday++;
    //         }
    //         if($holidayLength >= 10){
    //             $weeksHoliday++;
    //         }
    //     }
    //     $terms[] = [
    //       'nb' => $term->nb,
    //       'nbWeeks' => (Carbon::parse($term->start_date))->diffInWeeks($term->finish_date) - $weeksHoliday,
    //       'hoursDone' => $hoursDone[$term->nb-1]
    //     ];
    // }
    // return response()->json([
    //   'terms' => $terms,
    //   'hoursPerWeek' => isset($user->preferences->hoursDuePerWeek) ? floatval($user->preferences->hoursDuePerWeek) : 0
    // ]);
  }

  public function setCurrentRoute(Request $request)
  {
    $route = $request->validate(['route' => 'required|max:100'])['route'];
    $request->session()->put('route', $route);

    return response()->json('OK');
  }

  public function pobpr(Request $request)
  {
    logger("********** READ TAG **********");
    logger($request);

    return response()->json(['text' => 'tag read :-)']);
  }
}
