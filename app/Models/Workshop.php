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
      return $this->belongsToMany(User::class)->withPivot(['comment', 'available', 'confirmed'])->withTimestamps();
    }

    public function themes()
    {
      return $this->belongsToMany(Theme::class);
    }

    public function sessions()
    {
      return $this->hasMany(Session::class);
    }

    public function emails()
    {
      return $this->hasMany(Email::class);
    }

    public function getLinkFrAttribute()
    {
      $title = $this->title_fr == '' ? $this->title_en : $this->title_fr;
      return "<a href='https://pim.fis.edu.hk/workshops/$this->id'>$title</a>";
    }

    public function getLinkEnAttribute()
    {
      $title = $this->title_en == '' ? $this->title_fr : $this->title_en;
      return "<a href='https://pim.fis.edu.hk/workshops/$this->id'>$title</a>";
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
        foreach($this->applicants()->orderBy('pivot_created_at')->get() as $applicant){
          $applicants[] = [
            'id' => $applicant->id,
            'name' => $applicant->name,
            'email' => $applicant->email,
            'className' => $applicant->class_name,
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
      // $emails = [];
      // if($user && $user->hasRole('admin')){
      //   $emails = $this->emails;
      // }
      // if($user && $user->hasRole('teacher')){
      //   $emails = $this->emails()->where('admin', 0)->get();
      // }

      $details = json_decode($this->details);
      
      return [
        'id' => $this->id,
        'mainTitle' => $this->language == 'fr' ? $this->title_fr : $this->title_en,
        'title' => ['fr' => $this->title_fr, 'en' => $this->title_en],
        'description' => json_decode($this->description),
        'language' => $this->language,
        'term' => $this->term,
        'campus' => $this->campus,
        'details' => $details,
        'posters' => [
          'en' => isset($details->poster_en) ? url($details->poster_en) : null,
          'fr' => isset($details->poster_fr) ? url($details->poster_fr) : null,
        ],
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
        'joinable' => $user && $user->hasRole('student') && in_array($user->level, $details->levels)
        // 'emails' => $emails
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
      $description_fr = "Vous avez récemment participé à l'atelier $this->link_fr. Pourriez-vous prendre quelques minutes pour répondre au questionnaire ci-dessous et partager vos impressions ?";
      $description_en = "You recently participated in the workshop $this->link_en. Could you take a few minutes to answer the survey below and share you thoughts ?";
      $options = [
        'language' => $this->language,
        'title_fr' => "Bilan atelier - $this->title_fr",
        'title_en' => "Workshop review - $this->title_en",
        'description_fr' => $description_fr,
        'description_en' => $description_en,
        'answerEditable' => true
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
              ['fr' => "Oui tout à fait", 'en' => 'Yes, absolutely']
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
              ['fr' => "Trop long", 'en' => 'Too long'],
              ['fr' => "Un peu long", 'en' => 'Rather long'],
              ['fr' => "Bien choisie", 'en' => 'Well balanced'],
              ['fr' => "Un peu court", 'en' => 'Rather short'],
              ['fr' => "Trop court",'en' => 'Too short']
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
              ['fr' => "Oui, cela m'intéresserait beaucoup", 'en' => "Yes I'd be very interested"]
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
        'author_id' => 1,
        'questions' => $questions,
        'options' => $options,
        'workshop_id' => $this->id,
        'status' => 'draft'
      ]);

      $body_fr = "<p>Vous avez récemment participé à l'atelier $this->link_fr.</p>";
      $body_fr .= "<p>Pourriez-vous prendre quelques minutes pour répondre au questionnaire ci-dessous et partager vos impressions ?</p>";
      $body_en = "<p>You recently participated in the workshop $this->link_en.</p>";
      $body_en .= "<p>Could you take a few minutes to answer the survey below and share you thoughts ?</p>";
      $lastSession = $this->sessions()->orderBy('date', 'desc')->first();
      $email = Email::create([
        'subject_fr' => "Bilan atelier - $this->title_fr",
        'subject_en' => "Workshop review - $this->title_en",
        'language' => $this->language == 'fr' ? 'fr' : 'en',
        'data' => [
            'body_fr' => $body_fr,
            'body_en' => $body_en,
            'closing_fr' => "Merci d'avance",
            'closing_en' => "Thanks in advance",
            'actionButton' => [
                    'value' => $survey->id,
                    'text_fr' => 'Répondre',
                    'text_en' => 'Answer',
                    'url' => ''
                ]
        ],
        'sender_id' => 1,
        'workshop_id' => $this->id,
        'schedule' => (Carbon::parse($lastSession->date))->addDay()->setTime(8,00)
      ]);

      $email->surveys()->attach($survey);
    }

    public function createEmails($nofityApplicants)
    {
      $teacherName = $this->organiser->formalName;
      $details = json_decode($this->details);
      /**
       *  ENROLLMENT CONFIRMATION
       */
      if($nofityApplicants['confirmed']){
        $firstSession = $this->sessions()->orderBy('date')->first();
        $firstSessionDatetime = Carbon::parse("$firstSession->date $firstSession->start");
        $room = $details->roomNb;
  
        $dateString_fr = $firstSessionDatetime->locale('fr')->translatedFormat('l j F \à\ H:i');
        $dateString_en = $firstSessionDatetime->locale('en')->translatedFormat('l j F \a\t H:i');
        $body_fr = "<p>Votre inscription à l'atelier $this->link_fr, animé par $teacherName, est <b>confirmée</b>.</p>";
        $body_fr .= "<p>La première séance aura lieu en salle $room le</p>";
        $body_fr .= "<p style='text-align: center;font-size:24px;'>$dateString_fr</p><br>";
        $body_fr .= "<p>Cet atelier sera composé de 8 séances qui seront portées sur Pronote sous peu.<br>Votre présence à l'ensemble des séances est obligatoire. Merci de prévenir l'enseignant le plus tôt possible en cas d'impossibilité.</p>";
        $body_en = "<p>Your enrollment in the workshop $this->link_en, led by $teacherName, is <b>confirmed</b></p>";
        $body_en .= "<p>The first session will be held in room $room on</p>";
        $body_en .= "<p style='text-align: center;font-size:24px;'>$dateString_en</p><br>";
        $body_en .= "<p>This workshop will consist of 8 sessions which will be posted on Pronote shortly.<br>Your attendance to each session is mandatory. Please notify the teacher as soon as possible in case of unavailability.</p>";
        Email::create([
          'subject_fr' => "Inscription atelier - $this->title_fr",
          'subject_en' => "Workshop enrollment - $this->title_en",
          'language' => $this->language == 'fr' ? 'fr' : 'en',
          'data' => [
              'body_fr' => $body_fr,
              'body_en' => $body_en,
              'closing_fr' => "Bon atelier",
              'closing_en' => "Have a good workshop",
              'actionButton' => [
                      'value' => 'none',
                      'text_fr' => '',
                      'text_en' => '',
                      'url' => ''
                  ]
          ],
          'sender_id' => 1,
          'workshop_id' => $this->id,
          'schedule' => (Carbon::today())->addDay()->setTime(8,00)
        ]);
      }

      /**
       *  UNCONFIRMED APPLICANTS
       */
      if($nofityApplicants['unconfirmed']){
        $body_fr = "<p>Votre inscription à l'atelier $this->link_fr, animé par $teacherName, n'a pas pu être retenue. Nous en sommes désolés</p>";
        $body_fr .= "<p>Nous vous tiendrons au courant si une nouvelle session venait à être organisée à l'avenir.</p>";
        $body_en = "<p>Your enrollment in the workshop $this->link_en, led by $teacherName, could not be approved. We are sorry about that.</p>";
        $body_en .= "<p>We will keep you informed if a new session were to be organized in the future.</p>";
        Email::create([
          'subject_fr' => "Inscription non retenue - $this->title_fr",
          'subject_en' => "Enrollment not approved - $this->title_en",
          'language' => $this->language == 'fr' ? 'fr' : 'en',
          'data' => [
              'body_fr' => $body_fr,
              'body_en' => $body_en,
              'closing_fr' => "Cordialement",
              'closing_en' => "Best regards",
              'actionButton' => [
                      'value' => 'none',
                      'text_fr' => '',
                      'text_en' => '',
                      'url' => ''
                  ]
          ],
          'sender_id' => 1,
          'workshop_id' => $this->id,
          'schedule' => (Carbon::today())->addDay()->setTime(8,00)
        ]);
      }

      /**
       *  1ST SESSION REMINDER
       */
      $time = $firstSessionDatetime->format('H:i');
      $body_fr = "<p>Ceci est un rappel pour l'atelier $this->link_fr.</p>";
      $body_fr .= "<p>La première séance a lieu <b>aujourd'hui à $time</b> en salle $room.</p>";
      $body_en = "<p>This is a reminder for the workshop $this->link_en.</p>";
      $body_en .= "<p>The first session will be held <b>today at $time</b> in room $room.</p>";
      Email::create([
        'subject_fr' => "Première séance - $this->title_fr",
        'subject_en' => "First session - $this->title_en",
        'language' => $this->language == 'fr' ? 'fr' : 'en',
        'data' => [
            'body_fr' => $body_fr,
            'body_en' => $body_en,
            'closing_fr' => "Bon atelier",
            'closing_en' => "Have a good workshop",
            'actionButton' => [
                    'value' => 'none',
                    'text_fr' => '',
                    'text_en' => '',
                    'url' => ''
                ]
        ],
        'sender_id' => 1,
        'workshop_id' => $this->id,
        'schedule' => (Carbon::parse($firstSession->date))->setTime(8,00)
      ]);

      /**
       *  PRONOTE UPDATE (ADMIN)
       */
      $daysToFrench = ['Monday' => 'Lundi', 'Tuesday' => 'Mardi', 'Wednesday' => 'Mercredi', 'Thursday' => 'Jeudi', 'Friday' => 'Vendredi', 'Saturday' => 'Samedi', 'Sunday' => 'Dimanche'];
      $scheduleString = "";
      foreach($details->schedule as $session){
        $scheduleString .= $daysToFrench[$session->day]." à ".str_replace(":", "h", $session->start).", ";
      }
      $sessions = $this->sessions;
      $scheduleString = substr($scheduleString, 0, -2);
      $body_fr = "<p>L'atelier $this->link_fr, organisé par le PIM, est maintenant finalisé.</p>";
      $body_fr .= "<b>Enseignant : </b>".$this->organiser->formalName."<br>";
      $body_fr .= "<b>Séances : </b>$scheduleString<br>";
      $body_fr .= "<b>Calendrier : </b>".count($sessions)." séances à partir du ".$firstSessionDatetime->locale('fr')->translatedFormat('l j F')."<br>";
      $body_fr .= "<b>Salle : </b>".$details->roomNb."<br>";
      $body_fr .= "<b>Elèves : </b>";
      $body_en = "";
      $ps_fr = "Pour confirmation, la liste complète des séance est :<br><ul>";
      foreach($sessions as $session){
        $ps_fr .= "<li>".(Carbon::parse($session->date))->locale('fr')->translatedFormat('l j F'). " de $session->start à $session->finish</li>";
      }
      $ps_fr .= "</ul>";
      $ps_en = "";
      $students = collect([]);
      foreach($this->applicants()->wherePivot('confirmed', 1)->get() as $student){
        $students->push([
          'id' => $student->id,
          'name' => $student->name,
          'className' => $student->class_name,
          'level' => $student->level,
          'section' => $student->section,
          'lastName' => isset((explode(' ', $student->name))[1]) ? (explode(' ', $student->name))[1] : $student->name
        ]);
      }
      $students = $students->sortBy([['level', 'desc'], ['section', 'asc'], ['lastName', 'asc'], ['name', 'asc']]);

      $workshopTitle = $this->language == 'fr' ? $this->title_fr : $this->title_en;
      Email::create([
        'subject_fr' => "Atelier - $workshopTitle",
        'subject_en' => "Workshop - $workshopTitle",
        'language' => 'fr',
        'data' => [
            'body_fr' => $body_fr,
            'body_en' => $body_en,
            'closing_fr' => "<p>Pourriez-vous porter ces cours sur Pronote s'il vous plait ?</p><br>Cordialement",
            'closing_en' => "",
            'ps_fr' => $ps_fr,
            'ps_en' => "",
            'students' => $students->values()->toArray(),
            'actionButton' => [
                    'value' => 'none',
                    'text_fr' => '',
                    'text_en' => '',
                    'url' => ''
            ],
            'to' => $this->campus == 'BPR' ? 'dcabane@g.lfis.edu.hk' : 'dcabane@g.lfis.edu.hk' // Obviously, change this...
        ],
        'sender_id' => 1,
        'admin' => 1,
        'workshop_id' => $this->id,
        'schedule' => (Carbon::today())->addDay()->setTime(8,00)
      ]);
    }

}
