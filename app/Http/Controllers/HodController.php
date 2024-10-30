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
        $workshops = [[], [], []];
        foreach(Workshop::orderBy('start_date', 'desc')->where('archived', 0)->get() as $workshop){
            $workshops[$workshop->term-1][] = $workshop->format();
        }

        $terms = [];
        foreach(Term::orderBy('nb')->get() as $term){
            $weeksHoliday = 0;
            foreach(Holiday::whereBetween('start', [$term->start_date, $term->finish_date])->get() as $holiday){
                $holidayLength = (Carbon::parse($holiday->start))->diffInDays($holiday->finish);
                if($holidayLength >= 5){
                    $weeksHoliday++;
                }
                if($holidayLength >= 10){
                    $weeksHoliday++;
                }
            }
            $terms[] = ['nb' => $term->nb, 'nbWeeks' => (Carbon::parse($term->start_date))->diffInWeeks($term->finish_date) - $weeksHoliday];
        }

        $teachers = [];
        foreach(User::role('teacher')->get() as $teacher){
            $teacher->hoursDone = $teacher->hoursPerTerm();
            $teachers[] = $teacher;
        }

        return response()->json(['workshops' => $workshops, 'teachers' => $teachers, 'terms' => $terms]);
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
