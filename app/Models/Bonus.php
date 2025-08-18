<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bonus extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'course_id', 'description', 'score', 'instructor_id'];

    protected $casts = [
        'description' => 'object'
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function format()
    {
        return [
            'id' => $this->id,
            'student' => [
                'id' => $this->student->id,
                'name' => $this->student->name,
                'level' => $this->student->level,
                'className' => $this->student->className
            ],
            'description' => $this->description,
            'score' => $this->score,
            'instructor' => [
                'id' => $this->instructor->id,
                'name' => $this->instructor->name
            ]
        ];
    }
}
