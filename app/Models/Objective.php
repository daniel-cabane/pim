<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Objective extends Model
{
    use HasFactory;

    protected $fillable = ['title_fr', 'title_en', 'description', 'points', 'course_id'];

    public function course()
    {
      return $this->belongsTo(Course::class);
    }

    public function students()
    {
      return $this->belongsToMany(User::class)->withPivot('score')->withTimestamps();
    }

    protected $casts = [
        'description' => 'object'
    ];

    public function format()
    {
      $studentLink = $this->students()->where('user_id', auth()->id())->first();
      return [
          'id' => $this->id,
          'title' => ['fr' => $this->title_fr, 'en' => $this->title_en],
          'description' => $this->description,
          'points' => $this->points,
          'userScore' => $studentLink ? $studentLink->pivot->score : 0
      ];
    }
}
