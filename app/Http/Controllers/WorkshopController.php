<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Workshop;
use App\Models\Theme;
use Carbon\Carbon;

class WorkshopController extends Controller
{
    public function index ()
    {
        $workshops = [];
        foreach(Workshop::where('status', 'confirmed')->where('start_date', '<=', Carbon::today())->get() as $workshop){
            $workshops[] = $workshop->format();
        }
        return response()->json($workshops);
    }

    public function myWorkshops ()
    {
        $user = auth()->user();
        $upcoming = [];
        $pastAndCurrent = [];
        foreach($user->workshops as $workshop){
            if($workshop->start_date && (Carbon::create($workshop->start_date))->isPast()){
                $pastAndCurrent[] = $workshop->format();
            } else {
                $upcoming[] = $workshop->format();
            }
        }
        return response()->json(['workshops' => [
            'pastAndCurrent' => $pastAndCurrent,
            'upcoming' => $upcoming
        ]]);
    }

    public function themes()
    {
        return response()->json(Theme::all());
    }

    public function store(Request $request)
    {
        $attrs = $request->validate([
            'title' => 'required|min:4|max:100',
            'themes' => 'sometimes|Array',
        ]);

        $workshop = Workshop::create([
            'title' => $attrs['title'],
            'details' => json_encode([
                'nbSessions' => 6,
                'location' => 'Salle π (314 BPR)',
                'campus' => 'BPR',
                'schedule' => [
                    ['day' => 'Monday', 'start' => '17:30', 'finish' => '18:30']
                ],
                'language' => 'français',
                'maxStudents' => 15
            ]),
            'organiser_id' => auth()->id()
        ]);

        foreach($attrs['themes'] as $theme){
            $workshop->themes()->attach($theme);
        }

        return response()->json([
            'workshop' => $workshop->format(),
            'message' => [
                'text' => 'Workshop created',
                'type' => 'success'
            ]
        ]);
    }

    public function show(Workshop $workshop)
    {
        return response()->json(['workshop' => $workshop->format()]);
    }

    public function update(Workshop $workshop, Request $request)
    {
        $attrs = $request->validate([
            'title' => 'required|min:4|max:100',
            'description' => 'sometimes',
            'details' => 'required|Array',
            'start_date' => 'nullable|Date',
            'status' => 'required|min:4|max:12',
            'accepting_students' => 'required|Boolean',
            'themes' => 'sometimes|Array'
        ]);

        $workshop->update([
            'title' => $attrs['title'],
            'description' => $attrs['description'],
            'details' => json_encode($attrs['details']),
            'start_date' => $attrs['start_date'],
            'status' => $attrs['status'],
            'accepting_students' => $attrs['accepting_students'],
        ]);

        $workshop->themes()->sync($attrs['themes']);

        return response()->json([
            'workshop' => $workshop->format(),
            'message' => [
                'text' => 'Workshop updated',
                'type' => 'success'
            ]
        ]);
    }
}
