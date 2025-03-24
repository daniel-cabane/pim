<?php

namespace App\Http\Controllers;

use Carbon\Carbon;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Workshop;
use App\Models\Theme;
use App\Models\Holiday;
use App\Models\Session;
use App\Models\OpenDoor;
use App\Models\Post;
use App\Models\Term;
use App\Models\Survey;
use App\Models\Message;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function fetchUsers(String $string)
    {
        $validator = Validator::make(['string' => $string], ['string' => 'required|min:3']);
        $validated = $validator->safe()->only(['string'])['string'];
        return response()->json(User::where('name', 'LIKE', "%$validated%")->orWhere('email', 'LIKE', "%$validated%")->get());
    }

    public function updateUserRoles(User $user, Request $request)
    {
        $attrs = $request->validate([
            'student' => 'required|boolean',
            'publisher' => 'required|boolean',
            'teacher' => 'required|boolean',
            'hod' => 'required|boolean'
        ]);

        $roles = [];
        foreach($attrs as $name => $value){
            if($value){
                $roles[] = $name;
            }
        }
        // if($attrs['student']){
        //     $roles[] = 'student';
        // }
        // if($attrs['publisher']){
        //     $roles[] = 'publisher';
        // }
        // if($attrs['teacher']){
        //     $roles[] = 'teacher';
        // }
        // if($attrs['hod']){
        //     $roles[] = 'hod';
        // }

        if(!$user->hasRole('admin')){
            $user->syncRoles($roles);
        }

        return response()->json(['message' => ['text' => 'Roles updated', 'type' => 'success']]);
    }

    public function updateUserName(User $user, Request $request)
    {
        $attrs = $request->validate([
            'name' => 'required|String|min:3|max:35',
            'title' => 'sometimes|String|min:1|max:10',
            'level' => 'sometimes|String|min:2|max:10',
            'section' => 'sometimes|String|min:1|max:10',
            'two_factor_secret' => 'sometimes|String|min:1|max:50',
        ]);
        
        $user->update($attrs);

        if(isset($attrs['title'])){
            $prefs = $user->preferences;
            $prefs->title = $attrs['title'];
            $user->update(['preferences' => $prefs]);
        }

        return response()->json(['user' => $user, 'message' => ['text' => 'User updated', 'type' => 'success']]);
    }


    public function allWorkshops()
    {
        $workshops = [];
        foreach(Workshop::orderBy('start_date', 'desc')->where('archived', 0)->take(50)->get() as $workshop){
            $workshops[] = $workshop->format();
        }
        return response()->json([
            'workshops' => $workshops,
            'themes' => Theme::all()
        ]);
    }

    public function themesWithStats()
    {
        return response()->json([
                'themes' => Theme::all()->map(function ($theme) { return $theme->withStats(); })
        ]);
    }

    public function updateTheme(Theme $theme, Request $request)
    {
        $attrs = $request->validate([
            'title_en' => 'required|String|min:2|max:100',
            'title_fr' => 'required|String|min:2|max:100',
            'forWorkshop' => 'required|Boolean',
            'forPost' => 'required|Boolean'
        ]);

        $theme->update($attrs);

        return response()->json([
            'theme' => $theme,
            'message' => [
                    'text' => 'Theme updated',
                    'type' => 'success'
                ]
        ]);
    }

    public function createTheme(Request $request)
    {
        $attrs = $request->validate([
            'title_en' => 'required|String|min:2|max:100',
            'title_fr' => 'required|String|min:2|max:100'
        ]);

        $theme = Theme::create($attrs);

        return response()->json([
            'theme' => $theme->withStats(),
            'message' => [
                    'text' => 'Theme created',
                    'type' => 'success'
                ]
        ]);
    }

    public function destroyTheme(Theme $theme)
    {
        if(count($theme->workshops) > 0 || count($theme->posts) > 0){
            return response()->json([
                'message' => [
                        'text' => 'Cannot delete used theme',
                        'type' => 'error'
                    ]
            ]);
        }

        $id = $theme->id;
        $theme->delete();

        return response()->json([
            'id' => $id,
            'message' => [
                    'text' => 'Theme deleted',
                    'type' => 'info'
                ]
        ]);
    }
    
    public function createHoliday(Request $request)
    {
        $attrs = $request->validate([
            'start' => 'required|date',
            'finish' => 'required|date',
            'name' => 'required|String|min:2|max:50'
        ]);

        $start_carbon = Carbon::parse($attrs['start']);
        $finish_carbon = Carbon::parse($attrs['finish']);
        if ($start_carbon->gt($finish_carbon)) {
            return response()->json([
                'holiday' => null,
                'message' => [
                        'text' => 'The finish date must be after the start date',
                        'type' => 'error'
                    ]
            ]); 
        }

        $holiday = Holiday::create([
            'start' => $attrs['start'],
            'finish' => $attrs['finish'],
            'name' => $attrs['name']
        ]);

        return response()->json([
            'holiday' => $holiday,
            'message' => [
                    'text' => 'Holiday added',
                    'type' => 'success'
                ]
        ]);
    }

    public function updateHoliday(Holiday $holiday, Request $request)
    {
        $attrs = $request->validate([
            'start' => 'required|date',
            'finish' => 'required|date',
            'name' => 'required|String|min:2|max:50'
        ]);

        $start_carbon = Carbon::parse($attrs['start']);
        $finish_carbon = Carbon::parse($attrs['finish']);
        if ($start_carbon->gt($finish_carbon)) {
            return response()->json([
                'holiday' => null,
                'message' => [
                        'text' => 'The finish date must be after the start date',
                        'type' => 'error'
                    ]
            ]); 
        }

        $holiday->update([
            'start' => $attrs['start'],
            'finish' => $attrs['finish'],
            'name' => $attrs['name']
        ]);

        return response()->json([
            'holiday' => $holiday,
            'message' => [
                    'text' => 'Holiday updated',
                    'type' => 'success'
                ]
        ]);
    }

    public function deleteHoliday(Holiday $holiday)
    {
        $id = $holiday->id;
        $holiday->delete();

        return response()->json([
            'id' => $id,
            'message' => [
                    'text' => 'Holiday deleted',
                    'type' => 'warning'
                ]
        ]);
    }

    public function prepareWorkshop(Workshop $workshop)
    {
        $endOfSchool = Term::orderBy('start_date', 'desc')->first();
        if($endOfSchool == null){
            return response()->json([
                'message' => [
                    'text' => 'No terms created yet. Create terms for the school year',
                    'type' => 'error'
                    ]
                ]);
        }
        $details = json_decode($workshop->details);
        $schedule = collect($details->schedule);
        $schedule = $schedule->sort(function($a, $b) {
            $dayOrder = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            $aIndex = array_search($a->day, $dayOrder);
            $bIndex = array_search($b->day, $dayOrder);
            
            if ($aIndex == $bIndex) {
                return strtotime($a->start) - strtotime($b->start);
            }
            return ($aIndex < $bIndex) ? -1 : 1;
        });
        
        $sessions = [];
        $currentDate = new Carbon($workshop->start_date);
        $end = new Carbon($endOfSchool->finish_date);
        $id = 0;
        while(count($sessions) < $details->nbSessions && $currentDate->lt($end)){
            $holiday = Holiday::where('start', '<=', $currentDate)->where('finish', '>=', $currentDate)->first();
            if(!$holiday){
                foreach($schedule->where('day', $currentDate->format('l')) as $session){
                    if(count($sessions) < $details->nbSessions){
                        $sessions[] = [
                            'id' => $id++,
                            'date' => $currentDate->format('Y-m-d'),
                            'start' => $session->start,
                            'finish' => $session->finish,
                            'status' => 'confirmed'
                        ];
                    }
                }
            }
            $currentDate->addDay();
        }

        return response()->json([
            'info' => [
                'sessions' => $sessions
            ]
        ]);
    }

    public function launchWorkshop(Workshop $workshop, Request $request)
    {
        $attrs = $request->validate([
            'sessions' => 'required|Array',
            'applicants' => 'sometimes|Array',
            'notifyApplicants' => 'required|Array',
        ]);

        if($workshop->status == 'confirmed'){
            $index = 0;
            foreach($attrs['sessions'] as $session){
                Session::create([
                    'workshop_id' => $workshop->id,
                    'index' => $index++,
                    'date' => $session['date'],
                    'start' => $session['start'],
                    'finish' => $session['finish'],
                    'status' => $session['status']
                ]);
            }
    
            foreach($attrs['applicants'] as $applicant){
                if($applicant['confirmed']){
                    $workshop->applicants()->updateExistingPivot($applicant['id'], ['confirmed' => 1]);
                }
            }
            
            $workshop->update(['status' => 'launched', 'accepting_students' => 0]);
            $workshop->orderSessions();
    
            $workshop->createExitSurvey(null, false);
            $workshop->createEmails($attrs['notifyApplicants']);
    
            return response()->json([
                'workshop' => $workshop->format(),
                'message' => [
                        'text' => 'Workshop launched !',
                        'type' => 'success'
                    ]
            ]);
        }
        return response()->json([
            'workshop' => $workshop->format(),
            'message' => [
                    'text' => 'Workshop has already been launched...',
                    'type' => 'warning'
                ]
        ]);
    }

    public function createOpenDoor(Request $request)
    {
        $attrs = $request->validate([
            'date' => 'required|Date',
            'isRecurring' => 'required|Boolean',
            'finishDate' => 'sometimes|Date|nullable',
            'start' => 'required|max:10',
            'finish' => 'required|max:10',
            'type' => 'required|max:50',
            'roomNb' => 'required|max:50',
            'teacher_id' => 'required|Integer',
            'campus' => 'required|max:10'
        ]);

        $currentDate = new Carbon($attrs['date']);
        $endDate = $attrs['isRecurring'] ? new Carbon($attrs['finishDate']) : new Carbon($attrs['date']);

        $count = 0;
        while($currentDate->lte($endDate)){
            $count++;
            OpenDoor::create([
                'teacher_id' => $attrs['teacher_id'],
                'type' => $attrs['type'],
                'date' => $currentDate,
                'start' => $attrs['start'],
                'finish' => $attrs['finish'],
                'roomNb' => $attrs['roomNb'],
                'campus' => $attrs['campus']
            ]);
            $currentDate->addWeek();
        }


        return response()->json([
            'openDoors' => OpenDoor::with(['teacher:id,name'])->orderBy('date')->orderBy('start')->get(),
            'message' => [
                    'text' => $count > 1 ? 'Sessions added' : 'Session added',
                    'type' => 'success'
                ]
        ]);
    }

    public function updateOpenDoor(OpenDoor $openDoor, Request $request)
    {
        $attrs = $request->validate([
            'date' => 'required|Date',
            'isRecurring' => 'required|Boolean',
            'finishDate' => 'sometimes|Date|nullable',
            'start' => 'required|max:10',
            'finish' => 'required|max:10',
            'type' => 'required|max:50',
            'roomNb' => 'required|max:50',
            'teacher_id' => 'required|Integer',
            'campus' => 'required|max:10'
        ]);

        $currentDate = new Carbon($openDoor->date);
        $diffInDays = $currentDate->diffInDays($attrs['date'], false);
        $endDate = $attrs['isRecurring'] ? new Carbon($attrs['finishDate']) : $currentDate;

        $count = 0;
        foreach($openDoor->futureSessions($endDate) as $session){
            $count++;
            $session->update([
                'teacher_id' => $attrs['teacher_id'],
                'type' => $attrs['type'],
                'date' => (new Carbon($session->date))->addDay($diffInDays)->format('Y-m-d'),
                'start' => $attrs['start'],
                'finish' => $attrs['finish'],
                'roomNb' => $attrs['roomNb'],
                'campus' => $attrs['campus']
            ]);
        }

        return response()->json([
            'openDoors' => OpenDoor::with(['teacher:id,name'])->orderBy('date')->orderBy('start')->get(),
            'message' => [
                    'text' => $count > 1 ? 'Sessions updated' : 'Session updated',
                    'type' => 'success'
                ]
        ]);
    }

    public function deleteOpenDoor(OpenDoor $openDoor, Request $request)
    {
        $attrs = $request->validate([
            'isRecurring' => 'required|Boolean',
            'finishDate' => 'sometimes|Date|nullable'
        ]);

        $currentDate = new Carbon($openDoor->date);
        $endDate = $attrs['isRecurring'] ? new Carbon($attrs['finishDate']) : $currentDate;

        $count = 0;
        foreach($openDoor->futureSessions($endDate) as $session){
            $count++;
            $session->delete();
        }

        return response()->json([
            'openDoors' => OpenDoor::with(['teacher:id,name'])->orderBy('date')->orderBy('start')->get(),
            'message' => [
                    'text' => $count > 1 ? 'Sessions deleted' : 'Session deleted',
                    'type' => 'info'
                ]
        ]);
    }

    public function getPosts()
    {
        return response()->json(['posts' => [
            'submitted' => Post::where('status', 'submitted')->get(),
            'published' => Post::where('status', 'published')->orderBy('published_at', 'desc')->take(20)->get()
        ]]);

    }
    public function getMorePosts(Request $request)
    {
        $date = $request->validate(['date' => 'required|Date'])['date'];

        return response()->json(['posts' => Post::where('status', 'published')->where('published_at', '<', $date)->orderBy('published_at', 'desc')->take(50)->get()]);
    }

    public function createTerm(Request $request)
    {
        $attrs = $request->validate([
            'nb' => 'required|Integer|min:1|max:10',
            'start_date' => 'required|date',
            'finish_date' => 'required|date'
        ]);

        Term::create($attrs);

        $startDate = Carbon::create(Carbon::now()->subYear()->format('Y'), 8, 1, 0, 0, 0);
        $endDate = Carbon::create(Carbon::now()->addYear()->format('Y'), 7, 31, 23, 59, 59);

        return response()->json([
            'terms' => Term::whereBetween('start_date', [$startDate, $endDate])->get(),
            'message' => [
                    'text' => 'Term created',
                    'type' => 'success'
                ]
        ]);
    }

    public function editTerm(Term $term, Request $request)
    {
        $attrs = $request->validate(['start' => 'required|date', 'finish' => 'required|date']);

        $term->update(['start_date' => $attrs['start'], 'finish_date' => $attrs['finish'],]);

        $startDate = Carbon::create(Carbon::now()->subYear()->format('Y'), 8, 1, 0, 0, 0);
        $endDate = Carbon::create(Carbon::now()->addYear()->format('Y'), 7, 31, 23, 59, 59);

        return response()->json([
            'terms' => Term::whereBetween('start_date', [$startDate, $endDate])->get(),
            'message' => [
                    'text' => 'Term updated',
                    'type' => 'success'
                ]
        ]);
    }

    public function deleteTerm(Term $term)
    {
        $term->delete();

        $startDate = Carbon::create(Carbon::now()->subYear()->format('Y'), 8, 1, 0, 0, 0);
        $endDate = Carbon::create(Carbon::now()->addYear()->format('Y'), 7, 31, 23, 59, 59);

        return response()->json([
            'terms' => Term::whereBetween('start_date', [$startDate, $endDate])->get(),
            'message' => [
                    'text' => 'Term deleted',
                    'type' => 'warning'
                ]
        ]);
    }

    // public function createSurvey(Request $request)
    // {
    //     $attrs = $request->validate([
    //         'language' => 'required|min:2|max:5',
    //         'title_fr' => 'sometimes|max:100',
    //         'title_en' => 'sometimes|max:100'
    //     ]);

    //     if($attrs['language'] != 'fr' && strlen($attrs['title_en']) < 3 || $attrs['language'] != 'en' && strlen($attrs['title_fr']) < 3){
    //         return response()->json(['message' => ['text' => 'Titles have to be 3 characters or more']]);
    //     }

    //     $questions = [[
    //         'q_fr' => 'Poser une question',
    //         'q_en' => 'Ask a question',
    //         'type' => 'radio',
    //         'options' => [
    //             ['fr' => 'Réponse A','en' => 'Answer A'],
    //             ['fr' => 'Réponse B', 'en' => 'Answer B']
    //         ]
    //     ]];

    //     $survey = Survey::create([
    //         'author_id' => auth()->id(),
    //         'questions' => json_encode($questions),
    //         'options' => json_encode($attrs),
    //         'status' => 'closed'
    //     ]);

    //     return response()->json([
    //             'survey' => $survey->format(),
    //             'message' => [
    //                     'text' => 'Survey created',
    //                     'type' => 'success'
    //                 ]
    //         ]);
    // }

    public function getSurveys()
    {
        $surveys = [];
        foreach(Survey::orderBy('created_at', 'desc')->get() as $survey){
            $surveys[] = $survey->format();
        }
        return response()->json(['surveys' => $surveys]);
    }

    public function updateMessageStatus(Message $message, Request $request)
    {
        $status = $request->validate(['status' => 'required|String|max:10'])['status'];

        $message->update(['status' => $status]);

        return response()->json([
            'msg' => $message->format(),
            'message' => 'Message updated'
        ]);
    }

    public function getMessages()
    {
        $messages = [];
        foreach(Message::all() as $message){
            $messages[] = $message->format();
        }
        return response()->json(['messages' => $messages]);
    }

    public function deleteMessage(Message $message)
    {
        $id = $message->id;
        $message->delete();
        return response()->json([
            'id' => $id,
            'message' => 'Message deleted'
        ]);
    }

    public function addStudents(Request $request)
    {
        $attrs = $request->validate([
            'campus' => 'required|min:3|max:3',
            'level' => 'required|min:2|max:5',
            'section' => 'required|min:1|max:1',
            'students' => 'required|array'
        ]);

        $nbStudentsAdded = 0;
        $nbStudentsAlreadyRegistered = 0;

        $students = [];
        foreach($attrs['students'] as $student){
            if(User::where('email', $student['email'])->exists()){
                $nbStudentsAlreadyRegistered++;
            } else {
                $user = User::create([
                    'name' => $student['name'],
                    'email' => $student['email'],
                    'level' => $attrs['level'],
                    'section' => $attrs['section'],
                    'password' => Hash::make(Str::password()),
                    'preferences' => [
                        'notifications' => 'all',
                        'campus' => $attrs['campus'],
                        'language' => substr($attrs['level'], 0, 1) == 'Y' ? 'en' : 'fr'
                        ]
                ]);
                $user->assignRole('student');
                $nbStudentsAdded++;
            }
        }

        return response()->json([
            'message' => [
                    'text' => "$nbStudentsAdded students added -- $nbStudentsAlreadyRegistered already registered",
                    'type' => 'success'
                ]
            ]);
    }

    public function findStudentsByTag(Request $request)
    {
        $attrs = $request->validate([
            'users' => 'required|array'
        ]);

        $users = [];
        foreach($attrs['users'] as $user){
            $name1 = $user['name1'];
            $name2 = $user['name2'];
            $possibleMatch = User::where('two_factor_secret', null)->where('name', 'like', "%$name1%")->where('name', 'like', "%$name2%")->get();
            while(count($possibleMatch) == 0 && strlen($name1) > 3 && strlen($name2) > 3){
                $name1 = substr($name1, 1, -1);
                $name2 = substr($name2, 1, -1);
                $possibleMatch = User::where('two_factor_secret', null)->where('name', 'like', "%$name1%")->where('name', 'like', "%$name2%")->get();
            }
            $user['possibleMatch'] = $possibleMatch;
            $users[] = $user;
        }

        return response()->json(['users' => $users]);
    }

    public function attributeTag(User $user, Request $request)
    {
        $attrs = $request->validate(['tagNb' => 'required|integer']);

        if($user->two_factor_secret != null){
            return response()->json(['message' => 'This user already has a tag number'], 403);
        }

        $user->update(['two_factor_secret' => intval($attrs['tagNb'])]);

        return response()->json([
            'tagNb' => $attrs['tagNb'],
            'message' => [
                    'text' => 'Tag number attributed',
                    'type' => 'success'
                ]
            ]);
    }

    public function massAttributeTag(Request $request)
    {
        $data = ($request->validate(['data' => 'required|array']))['data'];

        foreach($data as $record){
            $user = User::find($record['id']);
            $user->update([
                'two_factor_secret' => $record['tag_number']
            ]);
        }

        return response()->json([
            'data' => $data,
            'message' => [
                    'text' => 'Tags attributed',
                    'type' => 'success'
                ]
            ]);
    }

    public function lostStudents()
    {
        $users = [];
        foreach(User::where('email', 'like', '%@g.lfis.edu.hk')->get() as $user){
            if(!$user->hasRole('student') && !$user->hasRole('teacher')){
                $identifier = explode("@", $user->email)[0];
                if(is_numeric(substr($identifier, -1))){
                    $user->assignRole('student');
                    $users[] = $user->email;
                }
            }
        }

        return response()->json([
            'users' => $users,
            'message' => [
                    'text' => 'Saul Goodman',
                    'type' => 'success'
                ]
            ]);
    }

    public function registerStudents(Request $request)
    {
        $data = ($request->validate(['data' => 'required|array']))['data'];

        return response()->json([
            'data' => $data,
            'message' => [
                    'text' => 'Saul Goodman',
                    'type' => 'success'
                ]
            ]);
    }

}
