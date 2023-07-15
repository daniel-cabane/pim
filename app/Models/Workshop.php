<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Workshop extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'details', 'organiser_id', 'start_date', 'status'];

    public function organiser()
    {
      return $this->belongsTo(User::class);
    }

    public function themes()
    {
      return $this->belongsToMany(Theme::class);
    }

    public function format()
    {
      return [
        'id' => $this->id,
        'title' => $this->title,
        'description' => $this->description,
        'details' => json_decode($this->details),
        'organiser' => [
          'id' => $this->organiser_id,
          'name' => $this->organiser->name
        ],
        'themes' => $this->themes()->pluck('themes.id'),
        'start_date' => $this->start_date,
        'status' => $this->status,
        'accepting_students' => $this->accepting_students == 1 && $this->status == 'confirmed',
        'editable' => auth()->check() && auth()->user()->can('update', $this),
      ];
    }
}
