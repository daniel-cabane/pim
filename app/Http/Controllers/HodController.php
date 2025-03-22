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
use App\Models\Mission;

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

    public function missionIndex()
    {
        $missions = [];
        foreach(Mission::all() as $mission){
            $missions[] = $mission->format();
        }

        return response()->json(['missions' => $missions]);
    }

    public function addMission(Request $request)
    {
        $attrs = $request->validate([
            'title' => 'required|min:3|max:100',
            'teacherId' => 'required|Integer|min:1',
            'lessonHours' => 'sometimes|Integer|min:0|max:100|nullable',
            'prepHours' => 'sometimes|Integer|min:0|max:100|nullable',
            'comment' => 'sometimes|max:255|nullable'
        ]);

        $mission = Mission::create([
            'title' => $attrs['title'],
            'teacher_id' => $attrs['teacherId'],
            'approver_id' => auth()->id(),
            'lesson_hours' => intval($attrs['lessonHours']),
            'prep_hours' => intval($attrs['prepHours']),
            'data' => isset($attrs['comment']) ? ['comment' => $attrs['comment']] : null
        ]);

        return response()->json([
            'mission' => $mission->format(),
            'message' => [
                'text' => 'Mission created',
                'type' => 'success'
            ]
        ]);
    }

    public function updateMission(Mission $mission, Request $request)
    {
        $attrs = $request->validate([
            'title' => 'required|min:3|max:100',
            'teacherId' => 'required|Integer|min:1',
            'lessonHours' => 'sometimes|Integer|min:0|max:100|nullable',
            'prepHours' => 'sometimes|Integer|min:0|max:100|nullable',
            'comment' => 'sometimes|max:255|nullable'
        ]);

        $data = $mission->data ? $mission->data : (object) [];
        if($attrs['comment']){
            $data->comment = $attrs['comment'];
        }

        $mission->update([
            'title' => $attrs['title'],
            'teacher_id' => $attrs['teacherId'],
            'lesson_hours' => intval($attrs['lessonHours']),
            'prep_hours' => intval($attrs['prepHours']),
            'data' => $data
        ]);

        return response()->json([
            'mission' => $mission->format(),
            'message' => [
                'text' => 'Mission updated',
                'type' => 'success'
            ]
        ]);
    }

    public function deleteMission(Mission $mission)
    {
        $mission->delete();

        return response()->json([
            'message' => [
                'text' => 'Mission deleted',
                'type' => 'info'
            ]
        ]);
    }

}
