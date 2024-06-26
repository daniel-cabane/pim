<?php

namespace App\Http\Controllers;

use Carbon\Carbon;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Workshop;
use App\Models\Holiday;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    public function fetchUsers(String $string)
    {
        $validator = Validator::make(['string' => $string], ['string' => 'required|min:3']);
        $validated = $validator->safe()->only(['string'])['string'];
        return response()->json(User::where('name', 'LIKE', "%$validated%")->orWhere('email', 'LIKE', "%$validated%")->get());
    }

    public function updateUserRoles(User $user, Request $request)
    {
        $attrs = $request->validate([
            'teacher' => 'required|boolean',
            'hod' => 'required|boolean'
        ]);

        $roles = [];
        if($attrs['teacher']){
            $roles[] = 'teacher';
        }
        if($attrs['hod']){
            $roles[] = 'hod';
        }

        if(!$user->hasRole('admin')){
            $user->syncRoles($roles);
        }

        return response()->json(['message' => "Roles updated"]);
    }


    public function allWorkshops()
    {
        $workshops = [];
        foreach(Workshop::orderBy('start_date', 'desc')->take(50)->get() as $workshop){
            $workshops[] = $workshop->format();
        }
        return response()->json(['workshops' => $workshops]);
    }
    
    public function createHoliday(Request $request)
    {
        $attrs = $request->validate([
            'start' => 'required|date',
            'finish' => 'required|date',
            'name' => 'required|String|min:2|max:50'
        ]);

        $start_carbon = Carbon::parse($attrs['start']);
        $finish_carbon = Carbon::parse($attrs['finish']);
        if ($start_carbon->gt($finish_carbon)) {
            return response()->json([
                'holiday' => null,
                'message' => [
                        'text' => 'The finish date must be after the start date',
                        'type' => 'error'
                    ]
            ]); 
        }

        $holiday = Holiday::create([
            'start' => $attrs['start'],
            'finish' => $attrs['finish'],
            'name' => $attrs['name']
        ]);

        return response()->json([
            'holiday' => $holiday,
            'message' => [
                    'text' => 'Holiday added',
                    'type' => 'success'
                ]
        ]);
    }

    public function updateHoliday(Holiday $holiday, Request $request)
    {
        $attrs = $request->validate([
            'start' => 'required|date',
            'finish' => 'required|date',
            'name' => 'required|String|min:2|max:50'
        ]);

        $start_carbon = Carbon::parse($attrs['start']);
        $finish_carbon = Carbon::parse($attrs['finish']);
        if ($start_carbon->gt($finish_carbon)) {
            return response()->json([
                'holiday' => null,
                'message' => [
                        'text' => 'The finish date must be after the start date',
                        'type' => 'error'
                    ]
            ]); 
        }

        $holiday->update([
            'start' => $attrs['start'],
            'finish' => $attrs['finish'],
            'name' => $attrs['name']
        ]);

        return response()->json([
            'holiday' => $holiday,
            'message' => [
                    'text' => 'Holiday updated',
                    'type' => 'success'
                ]
        ]);
    }

    public function deleteHoliday(Holiday $holiday)
    {
        $id = $holiday->id;
        $holiday->delete();

        return response()->json([
            'id' => $id,
            'message' => [
                    'text' => 'Holiday deleted',
                    'type' => 'warning'
                ]
        ]);
    }
}
