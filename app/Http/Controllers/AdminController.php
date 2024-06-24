<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Workshop;
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
}
