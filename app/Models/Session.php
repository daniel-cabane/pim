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
}
