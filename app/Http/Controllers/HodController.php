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
            $stats = [];
            foreach(Term::orderBy('nb')->get() as $term){
                $openDoorSessions = 0;
                foreach(OpenDoor::whereBetween('date', [$term->start_date, $term->finish_date])->where('teacher_id', $teacher->id)->get() as $session){
                    $openDoorSessions += round(((Carbon::createFromFormat('H:i', $session->start))->diffInMinutes(Carbon::createFromFormat('H:i', $session->finish)))/60);
                }
                $workshopSessions = 0;
                foreach($teacher->workshops()->where('term', $term->nb)->get() as $workshop){
                    foreach($workshop->sessions as $session){
                        $workshopSessions += round(((Carbon::createFromFormat('H:i', $session->start))->diffInMinutes(Carbon::createFromFormat('H:i', $session->finish)))/60);
                    }
                }
                $stats[] = [
                    'openDoorSessions' => $openDoorSessions,
                    'workshopSessions' => $workshopSessions,
                    'hoursDone' => 0.5*$openDoorSessions + $workshopSessions
                ];
            }
            $teacher->stats = $stats;
            $teachers[] = $teacher;
        }

        return response()->json(['workshops' => $workshops, 'teachers' => $teachers, 'terms' => $terms]);
    }
}
