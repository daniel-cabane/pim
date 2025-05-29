<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mission extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'teacher_id', 'approver_id', 'lesson_hours', 'prep_hours', 'data'];

    public function teacher()
    {
      return $this->belongsTo(User::class, 'teacher_id');
    }

    public function approver()
    {
      return $this->belongsTo(User::class, 'approver_id');
    }

    protected $casts = [
        'data' => 'object'
    ];

    public function format()
    {
      return [
        'id' => $this->id,
        'title' => $this->title,
        'teacherId' => $this->teacher->id,
        'teacher' => [
          'id' => $this->teacher->id,
          'name' => $this->teacher->name
        ],
        'approver' => $this->approver_id ? ['name' => $this->approver->name, 'id' => $this->approver->id] : ['name' => '--', 'id' => 0],
        'lessonHours' => $this->lesson_hours,
        'prepHours' => $this->prep_hours,
        'date' => $this->created_at,
        'comment' => isset($this->data->comment) ? $this->data->comment : null
      ];
    }
}
