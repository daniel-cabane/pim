<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Survey;

class SurveyController extends Controller
{
    public function create(Request $request)
    {
        $attrs = $request->validate([
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

        $attrs['answerEditable'] = false;

        $survey = Survey::create([
            'author_id' => auth()->id(),
            'questions' => $questions,
            'options' => $attrs,
            'status' => 'closed'
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
            'status' => 'required|String|min:3|max:10',
            'workshopId' => 'sometimes|Integer|min:1|nullable'
        ]);

        $survey->update([
            'options' => $attrs['options'],
            'questions' => $attrs['questions'],
            'status' => $attrs['status'],
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

    public function send(Survey $survey)
    {
        $survey->send();

        return response()->json([
                'survey' => $survey->format(),
                'message' => [
                        'text' => 'Survey sent',
                        'type' => 'success'
                    ]
            ]);
    }

    public function view(Survey $survey)
    {
        return response()->json(['survey' => $survey->format()]);
    }

    public function submit(Survey $survey, Request $request)
    {
        $answers = $request->validate(['answers' => 'required|Array'])['answers'];

        foreach($survey->questions as $i => $q){
            if($q->required && ($answers[$i] === null || $answers[$i] === '')){
                return response()->json(['message' => ['text' => 'Missing required answer', 'type' => 'error']]);
            }
        }

        $survey->answers()->updateExistingPivot(auth()->id(), ['data' => json_encode($answers)]);

        return response()->json([
                'survey' => $survey,
                'message' => [
                        'text' => 'Answers submitted',
                        'type' => 'success'
                    ]
            ]);
    }

    public function destroy(Survey $survey)
    {
        $survey->delete();

        $surveys = [];
        foreach(Survey::orderBy('created_at', 'desc')->get() as $survey){
            $surveys[] = $survey->format();
        }
        return response()->json(['surveys' => $surveys]);
    }
}
