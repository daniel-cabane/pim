<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Carbon\Carbon;

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

    public function hoursPerTerm()
    {
      if(!$this->hasRole('teacher')){
        return null;
      }
      $hours = [];
      foreach(Term::orderBy('nb')->get() as $term){
          $openDoorSessions = 0;
          foreach(OpenDoor::whereBetween('date', [$term->start_date, $term->finish_date])->where('teacher_id', $this->id)->get() as $session){
              $openDoorSessions += round(((Carbon::createFromFormat('H:i', $session->start))->diffInMinutes(Carbon::createFromFormat('H:i', $session->finish)))/60);
          }
          $start = Carbon::parse($term->start_date);
          $finish = Carbon::parse($term->finish_date);
          $workshopSessions = 0;
          foreach($this->workshops as $workshop){
              foreach($workshop->sessions as $session){
                  if((Carbon::parse($session->date))->between($start, $finish)){
                      $workshopSessions += round(((Carbon::createFromFormat('H:i', $session->start))->diffInMinutes(Carbon::createFromFormat('H:i', $session->finish)))/60);
                  }
              }
          }
          $hours[] = [
              'openDoorSessions' => $openDoorSessions,
              'workshopSessions' => $workshopSessions,
              'hoursDone' => 0.5*$openDoorSessions + $workshopSessions
          ];
      }

      return $hours;
    }

    public function activity()
    {
      if(!$this->hasRole('teacher')){
        return null;
      }
      $firstTerm = Term::where('nb', 1)->first();
      $lastTerm = Term::where('nb', 3)->first();
      $startOfWeek = (Carbon::parse($firstTerm->start_date))->addWeek(2)->startOfWeek();
      $hours = ['past' => 0, 'future' => 0];
      $openDoorSessions = OpenDoor::where('teacher_id', $this->id)->orderBy('date')->get();
      foreach($openDoorSessions as $session){
        $isPast = (Carbon::parse($session->date))->isPast();
        $session->isPast = $isPast;
        if ($isPast){
          $hours['past'] = $hours['past'] + 0.5;
        } else {
          $hours['future'] = $hours['future'] + 0.5;
        }
      }
      $workshopSessions = collect([]);
      foreach($this->workshops as $workshop){
        foreach($workshop->sessions as $session){
          $session->workshopTitle = $workshop->language == 'fr' ? $workshop->title_fr : $workshop->title_en;
          $isPast = (Carbon::parse($session->date))->isPast();
          $session->isPast = $isPast;
          if ($isPast){
            $hours['past']++;
          } else {
            $hours['future']++;
          }
          $workshopSessions->push($session);
        }
      }
      $weeks = [];
      $weeksPast = 0;
      $weeksFuture = 0;
      $month = '';
      $endOfYear = Carbon::parse($lastTerm->finish_date)->subWeek();
      while($startOfWeek->isBefore($endOfYear)){
        $endOfWeek = $startOfWeek->copy()->endOfWeek()->startOfDay();
        $newMonth = $month != $startOfWeek->format('F');
        $month = $startOfWeek->format('F');

        if(Holiday::where('start', '<=', $startOfWeek)->where('finish', '>=', $endOfWeek)->exists()){
          $weeks[] = [
            'startOfWeek' => $startOfWeek->copy(),
            'month' => $month,
            'newMonth' => $newMonth,
            'isHoliday' => true,
            'openDoorSessions' => [],
            'workshopSessions' => []
          ];
        } else {
          if($startOfWeek->isPast()){
            $weeksPast++;
          } else {
            $weeksFuture++;
          }
          $thisWeekOpenDoorSessions = $openDoorSessions->filter(function ($item) use ($startOfWeek, $endOfWeek) {
              $date = (Carbon::parse($item->date))->startOfDay();
              return $date->gte($startOfWeek) && $date->lte($endOfWeek);
          });
          $thisWeekWorkshopSessions = $workshopSessions->filter(function ($item) use ($startOfWeek, $endOfWeek) {
              $date = (Carbon::parse($item->date))->startOfDay();
              return $date->gte($startOfWeek) && $date->lte($endOfWeek);
          });
          
          $weeks[] = [
            'startOfWeek' => $startOfWeek->copy(),
            'month' => $month,
            'newMonth' => $newMonth,
            'isHoliday' => false,
            'openDoorSessions' => $thisWeekOpenDoorSessions->values()->all(),
            'workshopSessions' => $thisWeekWorkshopSessions->sortBy('date')->values()->all()
          ];
        }

        $startOfWeek->addWeek();
      }
      return ['weeks' => $weeks, 'hours' => $hours, 'nbWeeks' => ['past' => $weeksPast, 'future' => $weeksFuture]];
    }
}
