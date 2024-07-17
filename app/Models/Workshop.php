<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

use Carbon\Carbon;

class Workshop extends Model
{
    use HasFactory;

    protected $fillable = [
      'title_fr', 'title_en', 'description', 'language', 'campus', 'details', 'organiser_id', 'start_date', 'status', 'accepting_students', 'archived', 'term'
    ];

    public function organiser()
    {
      return $this->belongsTo(User::class, 'organiser_id');
    }

    public function applicants()
    {
      return $this->belongsToMany(User::class)->withPivot(['comment', 'available', 'confirmed']);
    }

    public function themes()
    {
      return $this->belongsToMany(Theme::class);
    }

    public function sessions()
    {
      return $this->hasMany(Session::class);
    }

    public function surveys()
    {
      return $this->hasMany(Survey::class);
    }

    public function format()
    {
      $user = auth()->user();
      $applicants = [];
      if($user && $user->hasRole('teacher')){
        foreach($this->applicants as $applicant){
          $applicants[] = [
            'id' => $applicant->id,
            'name' => $applicant->name,
            'email' => $applicant->email,
            'available' => $applicant->pivot->available,
            'confirmed' => $applicant->pivot->confirmed,
            'comment' => $applicant->pivot->comment
          ];
        }
      }
      $application = null;
      if($user && $user->hasRole('student')){
        $link = $this->applicants()->where('user_id', $user->id)->first();
        if($link){
          $application = [
            'submitted' => true,
            'available' => !!$link->pivot->available,
            'confirmed' => !!$link->pivot->confirmed,
            'comment' => $link->pivot->comment
          ];
        } else {
          $application = [
            'submitted' => false,
            'available' => true,
            'confirmed' => false,
            'comment' => ''
          ];
        }
      }
      // $surveys = [];
      // foreach($this->surveys as $survey){
      //   $surveys[] = $survey->format();
      // }
      
      return [
        'id' => $this->id,
        'title' => ['fr' => $this->title_fr, 'en' => $this->title_en],
        'description' => json_decode($this->description),
        'language' => $this->language,
        'term' => $this->term,
        'campus' => $this->campus,
        'details' => json_decode($this->details),
        'teacherId' => $this->organiser_id,
        'teacher' => $this->organiser->formal_name,
        'themes' => $this->themes()->pluck('themes.id'),
        'startDate' => $this->start_date,
        'formatedStartDate' => $this->start_date ? date_format(date_create($this->start_date) ,"d-m-y") : "Term $this->term",
        'status' => $this->status,
        'acceptingStudents' => $this->accepting_students == 1 && $this->status == 'confirmed',
        'editable' => auth()->check() && auth()->user()->can('update', $this),
        'application' => $application,
        'applicants' => $applicants,
        'sessions' => $this->sessions()->orderBy('index')->get(),
        // 'surveys' => $surveys
      ];
    }

    public function deletePoster($language = 'both')
    {
      $details = json_decode($this->details);
      $changed = false;
      if(isset($details->poster_fr) && $language != 'en'){
        Storage::delete("/public/$details->poster_fr");
        unset($details->poster_fr);
        $changed = true;
      }
      if(isset($details->poster_en) && $language != 'fr'){
        Storage::delete("/public/$details->poster_en");
        unset($details->poster_en);
        $changed = true;
      }
      if($changed){
        $this->update(['details' => json_encode($details)]);
      }
    }

    public function initSessions()
    {
      foreach($this->sessions as $session){
        //$session->delete();
      }
      // SHOULD I DO THIS ??
    }

    public function orderSessions()
    {
      $sessions = $this->sessions->sort(function($a, $b) {
          $dateTimeA = Carbon::createFromFormat('Y-m-d H:i', "$a->date $a->start");
          $dateTimeB = Carbon::createFromFormat('Y-m-d H:i', "$b->date $b->start");

          return $dateTimeA <=> $dateTimeB;
      });

      $index = 0;
      foreach($sessions as $session){
        $session->update(['index' => $index++]);
      }
    }

    public function archive()
    {
      if(isset($this->title_fr)){
        $this->update([
          'title_fr' => "ARCHIVED|O|$this->title_fr",
          'archived' => true
        ]);
      }
      if(isset($this->title_en)){
        $this->update([
          'title_en' => "ARCHIVED|O|$this->title_en",
          'archived' => true
        ]);
      } 
    }

    public function scopeUpcoming($query)
    {
      $today = Carbon::now();/////////////////// LINE BELOW CHANGE (2) TO (1) !!!!!!!!!!!!! ///////////////////////////////////////
      $terms = Term::whereDate('start_date', '<=', $today->addMonth(2))->whereDate('finish_date', '>=', $today)->pluck('nb');
      return $query->whereIn('status', ['confirmed', 'launched'])
                  ->where(function($q) use ($today, $terms) {
                        $q->where('start_date', '>=', $today)
                        ->orWhereIn('term', $terms);
                  });
    }

    public function createExitSurvey()
    {
      $options = [
        'language' => $this->language,
        'title_fr' => "Bilan atelier - $this->title_fr",
        'title_en' => "Workshop review - $this->title_en",
        'description_fr' => "Vous avez récemment participé à l'atelier $this->title_fr. Pourriez-vous partager vos impressions ?",
        'description_en' => "You recently participated in the workshop $this->title_en. Can you share you thoughts ?"
      ];
      $questions = [
        [
          'q_fr' => 'Le contenu de cet atelier correspondait-il à ce que vous espériez ?',
          'q_en' => 'Did the content of this workshop match what you were hoping for ?',
          'type' => 'radio',
          'options' => [
              ['fr' => "Pas du tout",'en' => 'Not at all'],
              ['fr' => "Pas vraiment", 'en' => 'Not really'],
              ['fr' => "Plus ou moins", 'en' => 'More or less'],
              ['fr' => "Oui, mais j'en attendais plus", 'en' => 'Yes but I excpected more'],
              ['fr' => "Oui tout à fait", 'en' => 'Not really']
          ],
          'required' => true
        ],
        [
          'q_fr' => "Avez vous des remarques sur le contenu ? Des points que vous auriez aimé aborder lors de l'atelier ?",
          'q_en' => "Do you have any comments on the content ? Are there any points that you would have liked to have addressed during the workshop ?",
          'type' => 'longText',
          'options' => [],
          'required' => false
        ],
        [
          'q_fr' => "Que pensez-vous de la durée de l'atelier ?",
          'q_en' => "What do you think about the duration of the workshop ?",
          'type' => 'radio',
          'options' => [
              ['fr' => "Trop court",'en' => 'Too short'],
              ['fr' => "Un peu court", 'en' => 'Rather short'],
              ['fr' => "Bien choisie", 'en' => 'Well balanced'],
              ['fr' => "Un peu long", 'en' => 'Rather long'],
              ['fr' => "Trop long", 'en' => 'Too long']
          ],
          'required' => true
        ],
        [
          'q_fr' => "Seriez-vous intéressé par une suite à cet atelier ?",
          'q_en' => "Would you be interested in a follow-up to this workshop ?",
          'type' => 'radio',
          'options' => [
              ['fr' => "Pas du tout",'en' => 'Not at all'],
              ['fr' => "Pas vraiment", 'en' => 'Not really'],
              ['fr' => "Peut-être", 'en' => 'Maybe'],
              ['fr' => "Oui, cela m'intéresserait", 'en' => "Yes I'd be interested"],
              ['fr' => "TOui, cela m'intéresserait beaucoup", 'en' => "Yes I'd be very interested"]
          ],
          'required' => true
        ],
        [
          'q_fr' => "Quels des éléments spécifiques que vous souhaiteriez aborder lors de cette suite ?",
          'q_en' => "What specific elements would you like to cover in a follow-up to this workshop ?",
          'type' => 'longText',
          'options' => [],
          'required' => false
        ],
        [
          'q_fr' => "Avez-vous des remarques générales sur cet atelier ou son organisation ?",
          'q_en' => "Do you have any general comments on this workshop or its organization ?",
          'type' => 'longText',
          'options' => [],
          'required' => false
        ],
      ];

      $survey = Survey::create([
            'author_id' => $this->organiser_id,
            'questions' => json_encode($questions),
            'options' => json_encode($options),
            'workshop_id' => $this->id,
            'status' => 'closed'
        ]);
    }
}
