<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Workshop;
use App\Models\Term;
use App\Models\OpenDoor;
use App\Models\Session;
use App\Models\Holiday;

class HodController extends Controller
{
    public function index()
    {
        $workshops = [];
        foreach(Workshop::orderBy('start_date', 'desc')->where('archived', 0)->get() as $workshop){
            $workshops[] = $workshop->format();
        }

        $teachers = [];
        foreach(User::role('teacher')->get() as $teacher){
            $teacher->activity = $teacher->activity();
            $teachers[] = $teacher;
        }

        return response()->json(['workshops' => $workshops, 'teachers' => $teachers]);
    }

    public function teacherHours(Request $request)
    {
        $teachers = $request->validate(['teachers' => 'required|array'])['teachers'];
        foreach($teachers as $teacherData){
            $teacher = User::find(intval($teacherData['id']));
            if(isset($teacherData['hours'])){
                $prefs = $teacher->preferences;
                $prefs->hoursDuePerWeek = floatval($teacherData['hours']);
                $teacher->update(['preferences' => $prefs]);
            }
        }

        return response()->json(['message' => ['text' => 'Hours due updated', 'type' => 'success']]);
    }
}
