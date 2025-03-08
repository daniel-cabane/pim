<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Visit;
use App\Models\User;
use App\Models\OpenDoor;

class VisitController extends Controller
{
    public function store(Request $request)
    {
        $tagNb = intval($request->validate(['tagNb' => 'required|min:4|max:255'])['tagNb']);
        logger("\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/");
        logger($tagNb);
        $user = User::where('two_factor_secret', $tagNb)->first();
        $currentDateTime = Carbon::now();
        $currentSession = openDoor::whereDate('date', '=', $currentDateTime->toDateString())
            ->whereTime('start', '<=', $currentDateTime->toTimeString())
            ->whereTime('finish', '>=', $currentDateTime->toTimeString())
            ->first();

        $visit = Visit::create([
            'tag_nb' => $tagNb,
            'user_id' => $user ? $user->id : null,
            'open_door_id' => $currentSession ? $currentSession->id : null,
        ]);
        logger($visit);

        return response()->json([
            'visitId' => $visit->id,
            'name' => $user ? $user->name : null,
        ]);
    }

    public function register(Visit $visit, Request $request)
    {
        $attrs = $request->validate(['email' => 'required|min:2|max:255']);
        
        $visit->update(['data' => json_encode($attrs)]);
        logger("|||||||||||||||||||||||||||||||||||||||||||");
        logger($visit);

        return response()->json(['message' => [ 'text' => 'Thank you for registering', 'type' => 'success']]);
    }
}
