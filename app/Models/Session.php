<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        return [
            'title' => "$workshopTitle ($index)",
            'date' => $this->date,
            'start' => $this->start,
            'end' => $this->finish,
            'color' => $colors[$this->workshop->campus],
            'eventType' => 'session',
            'campus' => $this->workshop->campus,
            'roomNb' => json_decode($this->workshop->details)->roomNb,
            'id' => "s$this->id",
            'url' => "/workshops/".$this->workshop->id,
            'teacher' => [
              'id' => $this->workshop->organiser->id,
              'name' => $this->workshop->organiser->name
            ]
        ];
    }
}
