<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

use Illuminate\Http\Request;
use App\Models\Workshop;
use App\Models\Theme;
use Carbon\Carbon;

class WorkshopController extends Controller
{
    public function index ()
    {
        $workshops = [];
        foreach(Workshop::where('status', 'confirmed')->where('start_date', '>=', Carbon::today())->get() as $workshop){
            $workshops[] = $workshop->format();
        }
        return response()->json(['workshops' => $workshops]);
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
            'title_fr' => 'sometimes|max:100',
            'title_en' => 'sometimes|max:100',
            'themes' => 'sometimes|Array',
            'language' => ['required', Rule::in(['fr', 'en', 'both'])]
        ]);

        if($attrs['language'] != 'fr' && $attrs['title_en'] == '' || $attrs['language'] != 'en' && $attrs['title_fr'] == ''){
            return response()->json(['message' => 'A title is required'], 422);
        }

        $workshop = Workshop::create([
            'title_fr' => $attrs['title_fr'],
            'title_en' => $attrs['title_en'],
            'description' => json_encode(['fr' => '', 'en' => '']),
            'details' => json_encode([
                'nbSessions' => 6,
                'roomNb' => 'π (314 BPR)',
                'campus' => 'BPR',
                'schedule' => [
                    ['day' => 'Monday', 'start' => '17:30', 'finish' => '18:30']
                ],
                'language' => $attrs['language'],
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
        return response()->json(['workshop' => $workshop->format(), 'teachers' => auth()->user()->teachers]);
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

    public function poster(Workshop $workshop,String $language, Request $request)
    {
        logger($language);
        $posterFile = $request->validate([
            'poster' => 'required|file|image|max:2048|dimensions:max_width=1920,max_height=1080'
        ])['poster'];

        $fileName = pathinfo($posterFile->getClientOriginalName(), PATHINFO_FILENAME) . time() . '.' . $posterFile->getClientOriginalExtension();
        Storage::disk('public')->putFileAs('/images/workshops', $posterFile, $fileName);
 
        $posterLanguage = $language == 'fr' ? 'poster_fr' : 'poster_en';
        $details = json_decode($workshop->details);
        $details->{$posterLanguage} = "/images/workshops/$fileName";
        $workshop->update([
            'details' => json_encode($details),
        ]);


        return response()->json([
            'workshop' => $workshop->format(),
            'message' => [
                'text' => 'Poster saved',
                'type' => 'success'
            ]
        ]);
    }
}
