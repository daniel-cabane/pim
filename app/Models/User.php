<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['name', 'email', 'password', 'google_id', 'email_verified_at', 'preferences', 'level', 'section'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'preferences' => 'object'
    ];

    // protected $with = ['surveys'];

    protected $appends = ['is', 'open_surveys', 'class_name', 'unread_messages'];

    public function getIsAttribute()
    {
      return [
          'student' => $this->hasRole('student'),
          'publisher' => $this->hasRole('publisher'),
          'teacher' => $this->hasRole('teacher'),
          'hod' => $this->hasRole('hod'),
          'admin' => $this->hasRole('admin')
      ];
    }

    public function getClassNameAttribute()
    {
      return "$this->level$this->section";
    }

    public function posts()
    {
      return $this->hasMany(Post::class, 'author_id');
    }

    public function workshops()
    {
      return $this->hasMany(Workshop::class, 'organiser_id')->where('archived', 0);
    }

    public function archivedWorkshops()
    {
      return $this->hasMany(Workshop::class, 'organiser_id')->where('archived', 1);
    }

    public function enrollements()
    {
      return $this->belongsToMany(Workshop::class)->withPivot(['comment', 'available', 'confirmed'])->withTimestamps();
    }

    public function surveys()
    {
      return $this->belongsToMany(Survey::class)->withPivot(['data', 'submitted'])->withTimestamps();
    }

    public function postsLiked()
    {
      return $this->belongsToMany(Post::class)->withTimestamps();
    }

    public function emailsReceived()
    {
      return $this->belongsToMany(Email::class)->withTimestamps();
    }

    public function scopeTeachers($query)
    {
        return $query->whereHas("roles", function($q){ $q->where("name", "teacher"); });
    }

    public function getFormalNameAttribute()
    {
      $name = $this->name;
      if($this->is['teacher']){
        $nameParts = explode(' ', $this->name);
        if(count($nameParts) > 1){
          array_shift($nameParts);
        }
        $name = implode(' ', $nameParts);
        if($this->preferences->title){
          $name = $this->preferences->title." ".$name;
        }
      }
      if($this->is['student']){
        $nameParts = explode(' ', $this->name);
        $name = $nameParts[0]." ";
        array_shift($nameParts);
        foreach($nameParts as $namePart){
          $name .= substr($namePart,0,1).".";
        }
        $name .= " ($this->level$this->section)";
      }

      return $name;
    }

    public function getOpenSurveysAttribute()
    {
      $surveys = [];
      foreach($this->surveys as $survey){
        if($survey->status == 'open' && $survey->pivot->data == null){
          $mainTitle = $survey->options->language == 'fr' ? $survey->options->title_fr : $survey->options->title_en;
          $surveys[] = [
            'id' => $survey->id,
            'title' => $mainTitle
          ];
        }
      }
      unset($this->surveys);
      return $surveys;
    }

    public function getUnreadMessagesAttribute()
    {
      $messages = [];
      foreach(Message::where('to', $this->id)->where('status', '!=', 'read')->get() as $message){
        $messages[] = $message->format();
      }

      return $messages;
    }
}
