<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Holiday;
use App\Models\OpenDoor;
use App\Models\Session;
use App\Models\Term;
use App\Models\Workshop;

class EventController extends Controller
{
    public function upcoming()
    {
        $campus = ['BPR', 'TKO'];
        $user = auth()->user();
        if($user && $user->preferences->campus != 'both'){
            $campus = [$user->preferences->campus];
        }

        $workshops = [];
        foreach(Workshop::upcoming()->whereIn('campus', $campus)->orderBy('start_date')->take(6)->get() as $workshop){
            $workshops[] = $workshop->format();
        }
        return response()->json(['events' => $workshops]);
    }


    public function holidays()
    {
        return response()->json(['holidays' => Holiday::all()]);
    }

    public function openDoors()
    {
        return response()->json(['openDoors' => OpenDoor::with(['teacher:id,name'])->orderBy('date')->orderBy('start')->get()]);
    }

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
        $eventStart = $startDate->copy()->startOfWeek();
        $eventEnd = $startDate->copy()->addMonths($attrs['backward'] + $attrs['forward'] + 1)->endOfWeek();
        $today = Carbon::today();
        for($i=0 ; $i<$attrs['backward'] + $attrs['forward']+1 ; $i++){
            if(abs($today->diffInYears($startDate)) > 2){
                break;
            }
            $endDate = $startDate->copy()->endOfMonth();
    
            $weekStart = $startDate->copy()->startOfWeek();
            $weekEnd = $endDate->copy()->endOfWeek();
    
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
            $startMonthName = $startDate->format('F');
            $startYear = $startDate->format('Y');
             $monthNb = $startDate->format('m');
            $days = [];

            for ($i = 0; $i < 7; $i++) {
                $date = $startDate->format('Y-m-d');
                $weekEvents = $events->where('date', $date)->values()->toArray();
                $days[] = [
                    'date' => $date,
                    'events' => $weekEvents,
                    'unfilteredEvents' => $weekEvents,
                    'isHoliday' => $holidays->where('start', '<=', $date)->where('finish', '>=', $date)->count() > 0
                ];
                $startDate->addDay();
            }
            $endMonthName = $startDate->format('F');
            $endYear = $startDate->format('Y');

            $monthDisplay = ['name' => $startMonthName, 'year' => $startYear, 'over' => false];
            if($startMonthName != $endMonthName){
                $monthDisplay = $startYear == $endYear ? 
                    ['name1' => $startMonthName, 'name2' => $endMonthName, 'year' => $startYear, 'over' => 'month'] 
                    : ['name1' => $startMonthName, 'name2' => $endMonthName, 'year1' => $startYear, 'year2' => $endYear, 'over' => 'year'];
            }


            $weeks[] = [
                'days' => $days,
                'nb' => $week['nb'],
                'year' => $week['year'],
                'monthDisplay' => $monthDisplay,
                'monthNb' => $monthNb
            ];
        }

        return response()->json(['months' => $months, 'weeks' => $weeks]);
    }

    public function getTerms()
    {
        $startDate = Carbon::create(Carbon::now()->subYear()->format('Y'), 8, 1, 0, 0, 0);
        $endDate = Carbon::create(Carbon::now()->addYear()->format('Y'), 7, 31, 23, 59, 59);

        return response()->json(['terms' => Term::whereBetween('start_date', [$startDate, $endDate])->get()]);
    }
}
