<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Email extends Model
{
    use HasFactory;

    protected $fillable = ['subject_fr', 'subject_en', 'language', 'data', 'sender_id', 'sent', 'schedule'];

    protected $casts = ['data' => 'object'];

    public function recipients()
    {
      return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function workshop()
    {
      return $this->belongsTo(workshop::class);
    }

    public function surveys()
    {
        return $this->belongsToMany(Survey::class)->withTimestamps();
    }
}
