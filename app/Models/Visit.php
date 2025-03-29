<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    use HasFactory;

    protected $fillable= ['user_id', 'open_door_id', 'data', 'tag_nb'];

    public function user()
    {
      return $this->belongsTo(User::class);
    }

    public function openDoor()
    {
      return $this->belongsTo(openDoor::class);
    }

    public function format()
    {
      $user = (object) [];
      if($this->user_id){
        $user = [
          'id' => $this->user_id,
          'email' => $this->user->email,
          'name' => $this->user->name,
          'level' => $this->user->level ? $this->user->level : '-',
          'section' => $this->user->section ? $this->user->section : '-'
        ];
      }
      $session = null;
      if($this->open_door_id){
        $teacher = $this->openDoor->teacher;
        $session = [
          'id' => $this->open_door_id,
          'start' => $this->openDoor->start,
          'finish' => $this->openDoor->finish,
          'teacher' => [
            'id' => $teacher->id,
            'name' => $teacher->formalName,
          ]
        ];
      }

      return [
        'id' => $this->id,
        'dateTime' => $this->created_at,
        'tagNb' => $this->tag_nb,
        'data' => json_decode($this->data),
        'user' => $user,
        'session' => $session
      ];
    }
}
