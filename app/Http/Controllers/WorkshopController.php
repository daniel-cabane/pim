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
            'language' => ['required', Rule::in(['fr', 'en', 'both'])],
            'term' => 'required|numeric|min:1|max:3'
        ]);

        if($attrs['language'] != 'fr' && $attrs['title_en'] == '' || $attrs['language'] != 'en' && $attrs['title_fr'] == ''){
            return response()->json(['message' => 'A title is required'], 422);
        }

        $workshop = Workshop::create([
            'title_fr' => $attrs['title_fr'],
            'title_en' => $attrs['title_en'],
            'description' => json_encode(['fr' => '', 'en' => '']),
            'language' => $attrs['language'],
            'term' => $attrs['term'],
            'details' => json_encode([
                'nbSessions' => 6,
                'roomNb' => 'π (314 BPR)',
                'campus' => 'BPR',
                'schedule' => [
                    ['day' => 'Monday', 'start' => '17:30', 'finish' => '18:30']
                ],
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
        $user = auth()->user();
        return response()->json(['workshop' => $workshop->format(), 'teachers' => $user ? $user->teachers : null]);
    }

    public function update(Workshop $workshop, Request $request)
    {
        $attrs = $request->validate([
            'title' => 'required',
            'description' => 'sometimes',
            'language' => ['required', Rule::in(['fr', 'en', 'both'])],
            'details' => 'required|Array',
            'startDate' => 'nullable|Date',
            'status' => 'required|min:4|max:12',
            'acceptingStudents' => 'required|Boolean',
            'themes' => 'sometimes|Array',
            'teacherId' => 'required|numeric',
            'term' => 'required|numeric'
        ]);

        if($attrs['language'] != 'fr' && $attrs['title']['en'] == '' || $attrs['language'] != 'en' && $attrs['title']['fr'] == ''){
            return response()->json(['message' => 'A title is required'], 422);
        }

        $workshop->update([
            'title_fr' => $attrs['title']['fr'],
            'title_en' => $attrs['title']['en'],
            'description' => json_encode($attrs['description']),
            'language' => $attrs['language'],
            'details' => json_encode($attrs['details']),
            'start_date' => $attrs['startDate'],
            'status' => $attrs['status'],
            'accepting_students' => $attrs['acceptingStudents'],
            'organiser_id' => $attrs['teacherId'],
            'term' => $attrs['term'],
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
        $posterFile = $request->validate([
            'poster' => 'required|file|image|max:2048|dimensions:max_width=1920,max_height=1080'
        ])['poster'];

        $fileName = pathinfo($posterFile->getClientOriginalName(), PATHINFO_FILENAME) . time() . '.' . $posterFile->getClientOriginalExtension();
        Storage::disk('public')->putFileAs('/images/workshops', $posterFile, $fileName);
 
        $workshop->deletePoster($language);
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

    public function deletePoster(Workshop $workshop,String $language)
    {
        $workshop->deletePoster($language);

        return response()->json([
            'workshop' => $workshop->format(),
            'message' => [
                'text' => 'Poster deleted',
                'type' => 'success'
            ]
        ]);
    }

    public function archive(Workshop $workshop)
    {
        return response()->json([
            'workshop' => $workshop->archive(),
            'message' => [
                'text' => 'Workshop archived',
                'type' => 'warning'
            ]
        ]);
    }

    public function destroy(Workshop $workshop)
    {
        if($workshop->archived){
            $workshop->deletePoster();
            return response()->json([
                'workshop' => $workshop->delete(),
                'message' => [
                    'text' => 'Workshop deleted',
                    'type' => 'error'
                ]
            ]);
        }
        return response()->json(['message' => 'You can only delete archived workshops'], 403);
    }

    public function apply(Workshop $workshop, Request $request)
    {
        $attrs = $request->validate([
            'availability' => 'required|Boolean',
            'comment' => 'sometimes|max:100'
        ]);

        $workshop->applicants()->detach(auth()->id());

        $workshop->applicants()->attach(auth()->id(), ['available' => $attrs['availability'], 'comment' => $attrs['comment']]);
        return response()->json([
            'workshop' => $workshop->format(),
            'message' => [
                    'text' => 'Application recorded',
                    'type' => 'success'
                ]
        ]);
    }

    
    public function withdraw(Workshop $workshop)
    {
        $workshop->applicants()->detach(auth()->id());
        return response()->json([
            'workshop' => $workshop->format(),
            'message' => [
                    'text' => 'Application withdrawn',
                    'type' => 'warning'
                ]
        ]);
    }
}
