<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class OpenDoor extends Model
{
    use HasFactory;

    protected $fillable = ['teacher_id', 'type', 'date', 'start', 'finish', 'roomNb', 'campus'];

    public function teacher()
    {
      return $this->belongsTo(User::class, 'teacher_id');
    }

    public function futureSessions($endDate)
    {
      $currentDate = new Carbon($this->date);
      $endDate = $endDate ? new Carbon($endDate) : new Carbon('2025-07-07'); // TODO CHANGE THAT TO END OF SCHOOL HOLIDAY

      $dates = [];
      while($currentDate->lte($endDate)){
        $dates[] = $currentDate->format('Y-m-d');       
        $currentDate->addWeek();
      }

      return OpenDoor::where('teacher_id', $this->teacher_id)
            ->whereIn('date', $dates)
            ->where('campus', $this->campus)
            ->where('roomNb', $this->roomNb)
            ->where('start', $this->start)
            ->get();
    }

    public function formatForCalendar()
    {
        $colors = [
          'BPR' => [
            'openDoors' => '#9999FF',
            'other' => '#7777FF'
          ],
          'TKO' => [
            'openDoors' => '#FF9999',
            'other' => '#FF7777'
          ]
        ];
        $type = $this->type == 'Open doors' ? 'openDoors' : 'other';
        return [
            'title' => $this->type,
            'date' => $this->date,
            'start' => $this->start,
            'end' => $this->finish,
            'color' => $colors[$this->campus][$type],
            'eventType' => 'openDoor',
            'id' => $this->id
        ];
    }
}
