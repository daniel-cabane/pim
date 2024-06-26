<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Workshop extends Model
{
    use HasFactory;

    protected $fillable = ['title_fr', 'title_en', 'description', 'language', 'details', 'organiser_id', 'start_date', 'status', 'accepting_students', "archived", "term"];

    public function organiser()
    {
      return $this->belongsTo(User::class);
    }

    public function applicants()
    {
      return $this->belongsToMany(User::class)->withPivot(['comment', 'available', 'confirmed']);
    }

    public function themes()
    {
      return $this->belongsToMany(Theme::class);
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
      return [
        'id' => $this->id,
        'title' => ['fr' => $this->title_fr, 'en' => $this->title_en],
        'description' => json_decode($this->description),
        'language' => $this->language,
        'term' => $this->term,
        'details' => json_decode($this->details),
        'teacherId' => $this->organiser_id,
        'teacher' => $this->organiser->name,
        'themes' => $this->themes()->pluck('themes.id'),
        'startDate' => $this->start_date,
        'formatedStartDate' => $this->start_date ? date_format(date_create($this->start_date) ,"d-m-y") : "Term $this->term",
        'status' => $this->status,
        'acceptingStudents' => $this->accepting_students == 1 && $this->status == 'confirmed',
        'editable' => auth()->check() && auth()->user()->can('update', $this),
        'application' => $application,
        'applicants' => $applicants
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
  }
