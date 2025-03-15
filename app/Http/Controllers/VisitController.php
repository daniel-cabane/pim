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
        $currentSession = openDoor::whereDate('date', $currentDateTime->toDateString())
            ->where('roomNb', 'π (314 BPR)')
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
        $email = $request->validate(['email' => 'required|min:2|max:255'])['email'];
        
        $visit->update(['data' => json_encode(['email' => "$email@g.lfis.edu.hk"])]);
        logger("|||||||||||||||||||||||||||||||||||||||||||");
        logger($visit);

        return response()->json(['message' => [ 'text' => 'Thank you for registering', 'type' => 'success']]);
    }

    public function recent()
    {
        $visits = [];
        foreach(Visit::orderByDesc('created_at')->take(50)->get() as $visit){
            $visits[] = $visit->format();
        }

        return response()->json(['visits' => $visits]);
    }

    public function updateByEmail(Visit $visit, Request $request)
    {
        $email = $request->validate(['email' => 'required|email|min:5|max:100'])['email'];

        $data = $visit->data ? json_decode($visit->data) : (object) [];
        $data->email = $email;
        $data->edited = true;
        $visit->update(['data' => json_encode($data)]);

        return response()->json([
            'visit' => $visit->format(),
            'message' => [
                'text' => 'Visit updated',
                'type' => 'success'
            ]
        ]);
    }

    public function updateByTagNb(Visit $visit, Request $request)
    {
        $tagNb = $request->validate(['tagNb' => 'required|min:6|max:30'])['tagNb'];

        $data = $visit->data ? json_decode($visit->data) : (object) [];
        $data->edited = true;

        $visit->update(['tag_nb' => $tagNb, 'data' => json_encode($data)]);

        return response()->json([
            'visit' => $visit->format(),
            'message' => [
                'text' => 'Visit updated',
                'type' => 'success'
            ]
        ]);
    }

    public function newVisit(Request $request)
    {
        $attrs = $request->validate([
            'email' => 'sometimes|max:60',
            'tagNb' => 'sometimes|min:6|max:30'
        ]);
        $currentDateTime = Carbon::now();
        $currentSession = openDoor::whereDate('date', $currentDateTime->toDateString())
            ->where('roomNb', 'π (314 BPR)')
            ->whereTime('start', '<=', $currentDateTime->toTimeString())
            ->whereTime('finish', '>=', $currentDateTime->toTimeString())
            ->first();

        
        $data = isset($attrs['email']) ? json_encode(['email' => $attrs['email'], 'added' => true]) : json_encode(['added' => true]);
        $visit = Visit::create([
            'tag_nb' => isset($attrs['tagNb']) ? $attrs['tagNb'] : 0,
            'data' => $data,
            'open_door_id' => $currentSession ? $currentSession->id : null,
        ]);

        return response()->json([
            'visit' => $visit->format(),
            'message' => [
                'text' => 'Visit added',
                'type' => 'success'
            ]
        ]);
    }

    public function delete(Visit $visit)
    {
        $visit->delete();

        return response()->json(['message' => [ 'text' => 'Visit deleted', 'type' => 'info']]);
    }

    public function toReview()
    {
        $visits = [];
        foreach(Visit::where('data', '!=', null)->get() as $visit){
            $visits[] = $visit->format();
        }

        return response()->json(['visits' => $visits]);
    }

    public function findMatch(Request $request)
    {
        logger($request);
        $attrs = $request->validate([
            'email' => 'sometimes|max:100',
            'name' => 'sometimes|max:100'
        ]);

        if(isset($attrs['email'])){
            $matches = User::where('email', $attrs['email'])->get();
            if(count($matches) == 0){
                $emailId = (explode('@', $attrs['email']))[0];
                $matches = User::where('email', 'like',"%".$emailId."%")->get();
            }
        }
        if(count($matches) == 0){
            $nameParts = explode(" ", $attrs['name']);
            $matches = User::where('name', 'like',"%$nameParts[0]%")->orWhere('name', 'like',"%".end($nameParts)."%")->get();
        }

        if(count($matches) == 0){
            $nameParts = explode(" ", $attrs['name']);
            $name1 = substr($nameParts[0], 1, -1);
            $name2 = substr(end($nameParts), 1, -1);
            $matches = User::where('name', 'like',"%$name1%")->orWhere('name', 'like',"%$name2%")->get();
        }

        if(count($matches) == 0){
            return response()->json([
                'matches' => $matches,
                'message' => [ 'text' => 'No match found', 'type' => 'error']
            ]);
        }

        return response()->json(['matches' => $matches]);
    }

    public function confirmMatch(Visit $visit, User $user)
    {
        $visit->update([
            'user_id' => $user->id,
            'data' => null
        ]);

        foreach(Visit::where('tag_nb', $visit->tag_nb)->get() as $v){
            $v->update([
                'user_id' => $user->id,
                'data' => null
            ]);
        }

        $alert = null;
        if($user->two_factor_secret != $visit->tag_nb){
            $alert = [
                'text' => $user->two_factor_secret ? "$user->name tag number changed from $user->two_factor_secret to $visit->tag_nb" : "$user->name tag number set to $visit->tag_nb",
                'type' => $user->two_factor_secret ? "warning" : "success"
            ];
        }

        $user->update([
            'two_factor_secret' => $visit->tag_nb
        ]);

        $visits = [];
        foreach(Visit::where('data', '!=', null)->get() as $visit){
            $visits[] = $visit->format();
        }

        return response()->json([
            'visits' => $visits,
            'alert' => $alert,
            'message' => [
                'text' => 'Visit linked',
                'type' => 'success'
            ]
        ]);
    }
}
