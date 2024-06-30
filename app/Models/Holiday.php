<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'start', 'finish'];

    public function formatForCalendar()
    {
        return [
            'title' => $this->name,
            'start' => $this->start,
            'end' => $this->finish,
            'allDay' => true,
            'color' => '#999999',
            'eventType' => 'holiday',
            'id' => $this->id
        ];
    }
}
