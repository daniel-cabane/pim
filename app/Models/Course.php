<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = ['title_fr', 'title_en', 'description', 'join_code', 'sections', 'rewards', 'instructor_id'];

    protected $casts = [
        'description' => 'object',
        'rewards' => 'object',
        'sections' => 'array'
    ];

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function students()
    {
      return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function objectives()
    {
        return $this->hasMany(Objective::class);
    }

    public function bonuses()
    {
        return $this->hasMany(Bonus::class);
    }

    public function format()
    {
        $students = [];
        $bonuses = [];
        $user = auth()->user();
        if($user->is['teacher']){
            foreach($this->bonuses as $bonus){
                $bonuses[] = $bonus->format();
            }
            foreach($this->students as $student){
                $objectives = [];
                foreach($student->objectives()->where('course_id', $this->id)->get() as $objective){
                    $objectives[] = [
                        'id' => $objective->id,
                        'score' => $objective->pivot->score
                    ];
                }
                $students[] = [
                    'id' => $student['id'],
                    'name' => $student['name'],
                    'level' => $student['level'],
                    'className' => $student['class_name'],
                    'objectives' => $objectives
                ];
            }
        } else {
            foreach($this->bonuses()->where('user_id', $user->id)->get() as $bonus){
                $bonuses[] = $bonus->format();
            }
        }
        $sections = [];
        foreach($this->sections as $section){
            $objectives = [];
            foreach($section['objectives'] as $objectiveId){
                $objectives[] = (Objective::find($objectiveId))->format();
            }
            $sections[] = [
                'title' => [
                    'fr' => $section['title_fr'],
                    'en' => $section['title_en'],
                ],
                'description' => $section['description'],
                'objectives' => $objectives
            ];
        }
        return [
            'id' => $this->id,
            'title' => ['fr' => $this->title_fr, 'en' => $this->title_en],
            'description' => $this->description,
            'rewards' => $this->rewards,
            'joinCode' => $this->join_code,
            'sections' => $sections,
            'instructor' => [
                'id' => $this->instructor->id,
                'name' => $this->instructor->formalName
            ],
            'students' => $students,
            'bonuses' => $bonuses
        ];
    }
}
