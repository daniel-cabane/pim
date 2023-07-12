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
                'location' => 'Pi room (314 BPR)',
                'campus' => 'BPR',
                'schedule' => '',
                'maxStudents' => 15
            ]),
            'organiser_id' => auth()->id()
        ]);

        foreach($attrs['themes'] as $theme){
            logger($theme);
            // attach it to $workshop
        }

        return response()->json($workshop->format());
    }
}
