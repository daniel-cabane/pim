<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

class Survey extends Model
{
    use HasFactory;

    protected $fillable = ['author_id', 'workshop_id', 'questions', 'options', 'status', 'scope'];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function workshop()
    {
        return $this->belongsTo(Workshop::class);
    }

    public function answers()
    {
        return $this->belongsToMany(User::class)->withPivot(['data', 'submitted'])->withTimestamps();
    }

    public function emails()
    {
        return $this->belongsToMany(Email::class)->withTimestamps();
    }

    protected $casts = ['options' => 'object', 'questions' => 'object'];

    public function getNbAnswersAttribute()
    {
        return $this->answers()->where('submitted', 1)->count();
    }

    public function format($includeWorkshopName = false)
    {
        $mainTitle = $this->options->language == 'fr' ? $this->options->title_fr : $this->options->title_en;
        $formatted = [
            'id' => $this->id,
            'mainTitle' => $mainTitle,
            'questions' => $this->questions,
            'options' => $this->options,
            'status' => $this->status,
            'author' => [
                'id' => $this->author_id,
                'name' => $this->author->formal_name
            ],
            'nbAnswers' => $this->nb_answers,
            'workshopId' => $this->workshop_id,
            'editable' => Gate::allows('update', $this)
        ];
        if($includeWorkshopName && $this->workshop){
            $workshopMainTitle = $this->workshop->language == 'fr' ? $this->workshop->title_fr : $this->workshop->title_en;
            $formatted['workshopName'] = $workshopMainTitle;
        }
        $user = auth()->user();
        if($user->is['student']){
            $answers = $this->answers()->where('user_id', $user->id)->first();
            if($answers != null){
                $formatted['answers'] = json_decode($answers->pivot->data);
            } else if(Gate::allows('view', $this)){
                $this->answers()->attach($user);
            }
        }

        return $formatted;
    }

    public function send($dest = null, $sendMail = true)
    {
        $this->update(['status' => 'open']);
        if($this->workshop_id){
            foreach($this->workshop->applicants as $applicant){
                if($applicant->pivot->confirmed){
                    $this->answers()->attach($applicant);
                    $applicants[] = $applicant->id;
                }
            }
        }
    }

    public function results()
    {
        $byQuestion = [];
        foreach($this->questions as $question){
            $data = [];
            if($question->type == 'radio' || $question->type == 'checkbox'){
                foreach($question->options as $option){
                    $data[] = [];
                }
            }
            $byQuestion[] = [
                'type' => $question->type,
                'data' => $data
            ];
        }
        $byStudent = [];
        foreach($this->answers()->where('submitted', 1)->get() as $answer){
            $data = json_decode($answer->pivot->data);
            $byStudent[] = [
                'id' => $answer->id,
                'name' => $answer->name,
                'data' => $data
            ];
            foreach($this->questions as $questionIndex => $question){
                if($question->type == 'radio'){
                    $byQuestion[$questionIndex]['data'][$data[$questionIndex]-1][] = $answer->name;
                } else if($question->type == 'checkbox'){
                    foreach($data[$questionIndex] as $answerIndex){
                        $byQuestion[$questionIndex]['data'][$answerIndex-1][] = $answer->name;
                    }
                } else {
                    $byQuestion[$questionIndex]['data'][] = ['name' => $answer->name , 'comment' => $data[$questionIndex]];
                }
            }
        }

        return ['byQuestion' => $byQuestion, 'byStudent' => $byStudent];
    }
}
