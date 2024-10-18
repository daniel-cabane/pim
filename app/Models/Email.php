<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use App\Mail\WorkshopCommunication;
use Illuminate\Support\Facades\Mail;

class Email extends Model
{
    use HasFactory;

    protected $fillable = ['subject_fr', 'subject_en', 'language', 'data', 'sender_id', 'workshop_id', 'admin', 'checked', 'sent', 'schedule'];

    protected $casts = ['data' => 'object'];

    protected $appends = ['editable'];

    public function recipients()
    {
      return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function sender()
    {
      return $this->belongsTo(User::class);
    }

    public function workshop()
    {
      return $this->belongsTo(Workshop::class);
    }

    public function surveys()
    {
        return $this->belongsToMany(Survey::class)->withTimestamps();
    }

    public function getEditableAttribute()
    {
      return Gate::allows('update', $this);
    }

    public function send()
    {
      if($this->workshop_id){
        if($this->admin){
          $bcc = 'pim@g.lfis.edu.hk';
          $cc = [];
          $to = $this->data->to ? $this->data->to : 'ogazeau@g.lfis.edu.hk';
          $sentTo = [1];
        } else {
          $students = $this->workshop->applicants()->wherePivot('confirmed', 1)->get();
          $bcc = 'pim@g.lfis.edu.hk';
          $cc = $this->workshop->organiser->email;
          $to = $students->pluck('email')->toArray();
          $sentTo = $students->pluck('id')->toArray();
        }
        foreach($this->surveys as $survey){
          if($survey->status == 'draft'){
            $survey->send();
          }
        }
        Mail::to($to)->cc($cc)->bcc($bcc)->send(new WorkshopCommunication($this));
        $data = $this->data;
        $data->sentTo = $sentTo;
        $this->update(['sent' => 1, 'schedule' => now(), 'data' => $data]);
      }
    }
}
