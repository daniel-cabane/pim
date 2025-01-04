<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Session extends Model
{
    use HasFactory;

    protected $fillable = ['workshop_id', 'index', 'date', 'start', 'finish', 'status'];

    public function workshop()
    {
      return $this->belongsTo(Workshop::class);
    }

    public function formatForCalendar()
    {
        $colors = ['BPR' => '#0000FF', 'TKO' => '#FF0000'];
        $workshopTitle = $this->workshop->language == 'fr' ? $this->workshop->title_fr : $this->workshop->title_en;
        $index = $this->index + 1;
        $details = json_decode($this->workshop->details);
        return [
            'title' => "$workshopTitle ($index)",
            'date' => $this->date,
            'dayOfWeek' => (Carbon::parse($this->date))->dayOfWeek,
            'start' => $this->start,
            'end' => $this->finish,
            'color' => $colors[$this->workshop->campus],
            'eventType' => 'session',
            'campus' => $this->workshop->campus,
            'roomNb' => $details->roomNb,
            'allowSilentGames' => isset($details->allowSilentGames) ? $details->allowSilentGames : false,
            'id' => "s$this->id",
            'url' => "/workshops/".$this->workshop->id,
            'teacher' => [
              'id' => $this->workshop->organiser->id,
              'name' => $this->workshop->organiser->formal_name
            ]
        ];
    }
}
