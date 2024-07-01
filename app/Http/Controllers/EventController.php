<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Holiday;
use App\Models\OpenDoor;
use App\Models\Session;

class EventController extends Controller
{
    // public function index()
    // {
    //     $events = [];
    //     $start = (Carbon::today())->addMonth(-2);
    //     $finish = (Carbon::today())->addMonth(3);

    //     foreach(Holiday::whereBetween('start', [$start, $finish])->get() as $holiday){
    //         $events[] = $holiday->formatForCalendar();
    //     }
    //     foreach(OpenDoor::whereBetween('date', [$start, $finish])->get() as $openDoor){
    //         $events[] = $openDoor->formatForCalendar();
    //     }
    //     foreach(Session::whereBetween('date', [$start, $finish])->get() as $session){
    //         $events[] = $session->formatForCalendar();
    //     }

    //     return response()->json([
    //         'events' => $events,
    //         'boundaries' => [
    //             'start' => $start,
    //             'finish' => $finish
    //         ]
    //     ]);
    // }

    public function holidays()
    {
        return response()->json(['holidays' => Holiday::all()]);
    }

    public function openDoors()
    {
        return response()->json(['openDoors' => OpenDoor::with(['teacher:id,name'])->orderBy('date')->orderBy('start')->get()]);
    }

    // public function getCurrentMonth()
    // {
    //     $start = Carbon::today()->startOfMonth()->startOfWeek();
    //     $finish = Carbon::today()->endOfMonth()->endOfWeek();

    //     $holidays = Holiday::where('start', '<=', $finish)->orWhere('finish', '>=', $start)->get();
    //     $events = collect([]);
    //     foreach(OpenDoor::whereBetween('date', [$start, $finish])->get() as $openDoor){
    //         $events->push($openDoor->formatForCalendar());
    //     }
    //     foreach(Session::whereBetween('date', [$start, $finish])->get() as $session){
    //         $events->push($session->formatForCalendar());
    //     }
    //     $weeks = [];
    //     $weekNbs = [];
    //     $finish->subWeek()->addDay();
    //     while($start->lt($finish)){
    //         $weekNumber = $start->weekOfYear;
    //         $year = $start->format('Y');
    //         $weekNbs[] = $weekNumber;
    //         $weekDates = [];
    //         for ($i = 0; $i <= 6; $i++) {
    //             $date = $start->toDateString();
    //             $weekDates[] = [
    //                 'date' => $date,
    //                 'events' => $events->where('date', $date),
    //                 'isHoliday' => $holidays->where('start', '<=', $date)->where('finish', '>=', $date)->count() > 0
    //             ];
    //             $start->addDays();
    //         }

    //         $weeks[] = ['days' => $weekDates, 'nb' => $weekNumber, 'year' => $year];
    //     }

    //     $now = Carbon::now();
    //     return response()->json([
    //         'months' => [[
    //             'name' => $now->format('F'),
    //             'nb' => $now->format('m'),
    //             'year' => $now->format('Y'),
    //             'index' => intval($now->format('m')) + intval($now->format('Y'))*100,
    //             'weekNbs' => $weekNbs
    //         ]],
    //         'weeks' => $weeks
    //     ]);
    // }

    public function getCalendarMonths(Request $request)
    {
        $attrs = $request->validate([
            'org' => 'required|Date',
            'forward' => 'required|Integer|min:0|max:5',
            'backward' => 'required|Integer|min:0|max:5',
        ]);

        // $firstMonday = Holiday::where('name', 'First Monday')->first();
        // $endOfSchool = Holiday::where('name', 'End of school')->first();

        $months = [];
        $globalWeekNbs = [];
        $startDate = (new Carbon($attrs['org']))->startOfMonth()->subMonths($attrs['backward']);
        $eventStart = null;
        $today = Carbon::today();
        for($i=0 ; $i<$attrs['backward'] + $attrs['forward']+1 ; $i++){
            if(abs($today->diffInYears($startDate)) > 2){
                break;
            }
            $endDate = $startDate->copy()->endOfMonth();
    
            $weekStart = $startDate->copy()->startOfWeek();
            $weekEnd = $endDate->copy()->endOfWeek();
            if($eventStart == null){
                $eventStart = $weekStart->copy();
            } 
    
            $weekNumbers = [];
    
            while ($weekStart <= $weekEnd) {
                $weekNumbers[] = ['nb' => $weekStart->weekOfYear, 'year' => $weekEnd->format('Y')];
                $globalWeekNbs[] = ['nb' => $weekStart->weekOfYear, 'year' => $weekEnd->format('Y')];
                $weekStart->addWeek();
            }
    
            $months[] = [
                'name' => $startDate->format('F'),
                'nb' => $startDate->format('m'),
                'year' => $startDate->format('Y'),
                'index' => intval($startDate->format('m')) + intval($startDate->format('Y'))*100,
                'weekRefs' => $weekNumbers
            ];
            $startDate->addMonth();
        }
        $eventEnd = $weekEnd->copy();

        $holidays = Holiday::where('start', '<=', $eventEnd)->orWhere('finish', '>=', $eventStart)->get();
        $events = collect([]);
        foreach(OpenDoor::whereBetween('date', [$eventStart, $eventEnd])->get() as $openDoor){
            $events->push($openDoor->formatForCalendar());
        }
        foreach(Session::whereBetween('date', [$eventStart, $eventEnd])->get() as $session){
            $events->push($session->formatForCalendar());
        }

        $weeks = [];
        $uniqueWeeks = array_unique(array_map('serialize', $globalWeekNbs), SORT_REGULAR);
        $uniqueWeeks = array_map('unserialize', $uniqueWeeks);
        foreach($uniqueWeeks as $week){
            $startDate = Carbon::now()->setISODate($week['year'], $week['nb'], 1);
            $days = [];

            for ($i = 0; $i < 7; $i++) {
                $date = $startDate->format('Y-m-d');
                $days[] = [
                    'date' => $date,
                    'events' => $events->where('date', $date)->values()->toArray(),
                    'isHoliday' => $holidays->where('start', '<=', $date)->where('finish', '>=', $date)->count() > 0
                ];
                $startDate->addDay();
            }

            $weeks[] = ['days' => $days, 'nb' => $week['nb'], 'year' => $week['year']];
        }

        return response()->json(['months' => $months, 'weeks' => $weeks]);
    }

    // public function weeks(Request $request)
    // {
    //     $attrs = $request->validate([
    //         'start' => 'required|Date',
    //         'finish' => 'required|Date',
    //     ]);

    //     $start = (new Carbon($attrs['start']))->startOfWeek();
    //     $finish = (new Carbon($attrs['finish']))->endOfWeek();
    //     $events = [];
    //     foreach(OpenDoor::whereBetween('date', [$start, $finish])->get() as $openDoor){
    //         $events[] = $openDoor->formatForCalendar();
    //     }
    //     foreach(Session::whereBetween('date', [$start, $finish])->get() as $session){
    //         $events[] = $session->formatForCalendar();
    //     }
        

    //     return response()->json(['weeks' => [$start, $finish]]);
    // }
}
