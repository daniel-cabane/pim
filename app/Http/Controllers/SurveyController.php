<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Survey;

class SurveyController extends Controller
{
    public function create(Request $request)
    {
        $attrs = $request->validate([
            'workshopId' => 'sometimes|Integer|nullable',
            'language' => 'required|min:2|max:5',
            'title_fr' => 'sometimes|max:100',
            'title_en' => 'sometimes|max:100'
        ]);

        if($attrs['language'] != 'fr' && strlen($attrs['title_en']) < 3 || $attrs['language'] != 'en' && strlen($attrs['title_fr']) < 3){
            return response()->json(['message' => ['text' => 'Titles have to be 3 characters or more']]);
        }

        $questions = [[
            'q_fr' => 'Poser une question',
            'q_en' => 'Ask a question',
            'type' => 'radio',
            'options' => [
                ['fr' => 'Réponse A','en' => 'Answer A'],
                ['fr' => 'Réponse B', 'en' => 'Answer B']
            ],
            'required' => true
        ]];

        $survey = Survey::create([
            'author_id' => auth()->id(),
            'questions' => $questions,
            'workshop_id' => isset($attrs['workshopId']) ? $attrs['workshopId'] : null,
            'options' => [
                'language' => $attrs['language'],
                'title_fr' => $attrs['title_fr'],
                'title_en' => $attrs['title_en'],
                'answerEditable' => true
            ],
            'status' => 'draft'
        ]);

        return response()->json([
                'survey' => $survey->format(),
                'message' => [
                        'text' => 'Survey created',
                        'type' => 'success'
                    ]
            ]);
    }

    public function update(Survey $survey, Request $request)
    {
        $attrs = $request->validate([
            'options' => 'required|Array',
            'questions' => 'required|Array',
            'workshopId' => 'sometimes|Integer|min:1|nullable'
        ]);

        $survey->update([
            'options' => $attrs['options'],
            'questions' => $attrs['questions'],
            'workshop_id' => $attrs['workshopId']
        ]);

        return response()->json([
                'survey' => $survey->format(),
                'message' => [
                        'text' => 'Survey updated',
                        'type' => 'success'
                    ]
            ]);
    }

    public function send(Survey $survey, Request $request)
    {
        $sendEmail = ($request->validate(['sendEmail' => 'required|Boolean']))['sendEmail'];
        $survey->send($sendEmail);

        return response()->json([
                'survey' => $survey->format(),
                'message' => [
                        'text' => 'Survey opened',
                        'type' => 'success'
                    ]
            ]);
    }

    public function open(Survey $survey)
    {
        $survey->update(['status' => 'open']);

        return response()->json([
                'survey' => $survey->format(),
                'message' => [
                        'text' => 'Survey reopened',
                        'type' => 'success'
                    ]
            ]);
    }

    public function close(Survey $survey)
    {
        $survey->update(['status' => 'closed']);

        return response()->json([
                'survey' => $survey->format(),
                'message' => [
                        'text' => 'Survey closed',
                        'type' => 'success'
                    ]
            ]);
    }

    public function view(Survey $survey)
    {
        $user = auth()->user();
        if(!$survey->answers->contains($user) && $user->hasRole('student')){
            $survey->answers()->attach($user);
        }
        if($user->hasRole('teacher')){
            $survey->answers()->detach($user);
        }
        return response()->json(['survey' => $survey->format()]);
    }

    public function submit(Survey $survey, Request $request)
    {
        $user = auth()->user();
        if($survey->answers->contains($user)){
            $answers = $request->validate(['answers' => 'required|Array'])['answers'];

            foreach($survey->questions as $i => $q){
                if($q->required && ($answers[$i] === null || $answers[$i] === '')){
                    return response()->json(['message' => ['text' => 'Missing required answer', 'type' => 'error']]);
                }
            }

            $survey->answers()->updateExistingPivot(auth()->id(), ['data' => json_encode($answers), 'submitted' => 1]);

            return response()->json([
                    'survey' => $survey,
                    'message' => [
                            'text' => 'Answers submitted',
                            'type' => 'success'
                        ]
                ]);
        }
        return response()->json([
                    'survey' => $survey,
                    'message' => [
                            'text' => 'You cannot submit an answer to this survey',
                            'type' => 'error'
                        ]
                ]);
    }

    public function results(Survey $survey)
    {
        return response()->json(['results' => $survey->results()]);
    }

    public function destroy(Survey $survey)
    {
        $id = $survey->id;
        $survey->delete();

        $surveys = [];
        foreach(Survey::orderBy('created_at', 'desc')->get() as $survey){
            $surveys[] = $survey->format();
        }
        return response()->json([
            'id' => $id,
            'message' => [
                        'text' => 'Survey deleted',
                        'type' => 'info'
                    ]

        ]);
    }
}
