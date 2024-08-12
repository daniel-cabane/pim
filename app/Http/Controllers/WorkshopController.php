<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Gate;

use Illuminate\Http\Request;
use App\Models\Workshop;
use App\Models\Theme;
use App\Models\User;
use App\Models\Session;
use App\Models\Email;
use Carbon\Carbon;

class WorkshopController extends Controller
{
    public function index ()
    {
        $user = auth()->user();
        $upcoming = [];
        foreach(Workshop::upcoming()->get() as $workshop){
            $upcoming[] = $workshop->format();
        }

        $enrollements = [];
        if($user){
            foreach($user->enrollements as $workshop){
                $enrollements[] = $workshop->format();
            }
        }

        $past = [];
        foreach(Workshop::where('status', 'finished')->orderBy('start_date', 'desc')->take(10)->get() as $workshop){
            $past[] = $workshop->format();
        }
        return response()->json(['workshops' => ['upcoming' => $upcoming, 'enrollements' => $enrollements, 'past' => $past]]);
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
            'campus' => 'BPR',
            'details' => json_encode([
                'nbSessions' => 6,
                'roomNb' => 'π (314 BPR)',
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
            'campus' => ['required', Rule::in(['BPR', 'TKO', 'JL', 'CW'])],
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

        if($workshop->status == 'launched'){
            $workshop->update([
                'title_fr' => $attrs['title']['fr'],
                'title_en' => $attrs['title']['en'],
                'description' => json_encode($attrs['description']),
                'language' => $attrs['language']
            ]);
        } else {
            $workshop->update([
                'title_fr' => $attrs['title']['fr'],
                'title_en' => $attrs['title']['en'],
                'description' => json_encode($attrs['description']),
                'language' => $attrs['language'],
                'campus' => $attrs['campus'],
                'details' => json_encode($attrs['details']),
                'start_date' => $attrs['startDate'],
                'status' => $attrs['status'],
                'accepting_students' => $attrs['acceptingStudents'],
                'organiser_id' => $attrs['teacherId'],
                'term' => $attrs['term'],
            ]);
    
            $workshop->themes()->sync($attrs['themes']);
        }

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

    public function editApplicant(Workshop $workshop, User $user, Request $request)
    {
        $attrs = $request->validate([
            'name' => 'required|max:50',
            'available' => 'required|Boolean',
            'confirmed' => 'required|Boolean',
            'comment' => 'sometimes|max:100',
        ]);

        $user->update(['name' => $attrs['name']]);

        unset($attrs['name']);

        $workshop->applicants()->updateExistingPivot($user->id, $attrs);

        $applicant = $workshop->applicants()->where('user_id', $user->id)->first();

        return response()->json([
            'applicant' => [
                'id' => $applicant->id,
                'name' => $applicant->name,
                'available' => $applicant->pivot->available == 1,
                'confirmed' => $applicant->pivot->confirmed == 1,
                'comment' => $applicant->pivot->comment
            ],
            'message' => [
                    'text' => 'Application updated',
                    'type' => 'success'
                ]
        ]);
    }

    public function removeApplicant(Workshop $workshop, User $user, Request $request)
    {
        $workshop->applicants()->detach($user);

        return response()->json([
            'id' => $user->id,
            'message' => [
                    'text' => 'Application removed',
                    'type' => 'info'
                ]
        ]);
    }

    public function deleteSession(Workshop $workshop, Session $session)
    {
        $session->delete();
        $workshop->orderSessions();

        return response()->json([
            'workshop' => $workshop->format(),
            'message' => [
                    'text' => 'Session deleted',
                    'type' => 'info'
                ]
        ]);
    }

    public function updateSession(Workshop $workshop, Session $session, Request $request)
    {
        $attrs = $request->validate([
            'date' => 'required|Date',
            'start' => 'required|max:10',
            'finish' => 'required|max:10',
            'status' => 'required|max:50',
        ]);

        $session->update([
            'date' => $attrs['date'],
            'start' => $attrs['start'],
            'finish' => $attrs['finish'],
            'status' => $attrs['status'],
        ]);

        $workshop->orderSessions();

        return response()->json([
            'workshop' => $workshop->format(),
            'message' => [
                    'text' => 'Session updated',
                    'type' => 'info'
                ]
        ]);
    }

    public function createSession(Workshop $workshop)
    {
        $lastSession = $workshop->sessions()->orderBy('index', 'desc')->first();

        if($lastSession){
            $index = $lastSession->index + 1;
            $date = (new Carbon($lastSession->date))->addWeek();
            $start = $lastSession->start;
            $finish = $lastSession->finish;
        } else {
            $index = 0;
            $date = (Carbon::today())->addWeek();
            $start = '12:30';
            $finish = '13:30';
        }

        Session::create([
                'workshop_id' => $workshop->id,
                'index' => $index,
                'date' => $date,
                'start' => $start,
                'finish' => $finish,
                'status' => 'confirmed'
            ]);

        return response()->json([
                'workshop' => $workshop->format(),
                'message' => [
                        'text' => 'Session added',
                        'type' => 'info'
                    ]
            ]); 
    }

    public function orderSessions(Workshop $workshop)
    {
        $workshop->orderSessions();
        
        return response()->json([
                'workshop' => $workshop->format(),
                'message' => [
                        'text' => 'Sessions ordered',
                        'type' => 'info'
                    ]
            ]); 
    }

    public function surveys(Workshop $workshop)
    {
        $surveys = [];
        foreach($workshop->surveys as $survey){
            if(Gate::allows('update', $survey) || $survey->status == 'open'){
                $surveys[] = $survey->format();
            }
        }

        return response()->json(['surveys' => $surveys]);
    }

    public function searchStudent(Workshop $workshop, Request $request)
    {
        $name = ($request->validate(['name' => 'required|String|min:3|max:100']))['name'];

        $students = [];
        foreach(User::role('student')->where('name', 'like', "%$name%")->get() as $student){
            if(!$student->enrollements()->where('workshop_id', $workshop->id)->exists()){
                $students[] = [
                    'id' => $student->id,
                    'name' => $student->name,
                    'email' => $student->email,
                    'available' => false,
                    'confirmed' => false
                ];
            }
            if(count($students) > 2) break;
        }

        return response()->json(['students' => $students]);
    }

    public function addStudent(Workshop $workshop, Request $request)
    {
        $attrs = $request->validate([
            'id' => 'required|Integer',
            'available' => 'required|Boolean',
            'confirmed' => 'required|Boolean'
        ]);

        $workshop->applicants()->detach($attrs['id']);

        $workshop->applicants()->attach($attrs['id'], ['available' => $attrs['available'], 'confirmed' => $attrs['confirmed'], 'comment' => '']);
        return response()->json([
            'workshop' => $workshop->format(),
            'message' => [
                    'text' => 'Student added',
                    'type' => 'success'
                ]
        ]);
    }

    public function emails(Workshop $workshop)
    {
        return response()->json([
            'emails' => auth()->user()->is['admin'] ? $workshop->emails : $workshop->emails()->where('admin', 0)->get()
        ]);
    }

    public function addEmail(Workshop $workshop, Request $request)
    {
        $subject = ($request->validate(['subject' => 'required|String|min:3|max:100']))['subject'];

        $email = Email::create([
            'subject_fr' => $subject,
            'subject_en' => $subject,
            'language' => $workshop->language == 'fr' ? 'fr' : 'en',
            'data' => [
                'body_fr' => 'Saisir un message...',
                'body_en' => 'Type your message',
                'closing_fr' => "Cordialement",
                'closing_en' => "Best regards",
                'actionButton' => [
                    'value' => 'none',
                    'text_fr' => '',
                    'text_en' => '',
                    'url' => ''
                ]
            ],
            'sender_id' => auth()->id(),
            'workshop_id' => $workshop->id,
            'schedule' => null
        ]);

        return response()->json([
            'email' => $email,
            'message' => [
                    'text' => 'Email created',
                    'type' => 'success'
                ]
        ]);
    }
}
