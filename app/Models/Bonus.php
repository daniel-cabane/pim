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
}
