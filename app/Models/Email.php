<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

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
      return $this->belongsTo(workshop::class);
    }

    public function surveys()
    {
        return $this->belongsToMany(Survey::class)->withTimestamps();
    }

    public function getEditableAttribute()
    {
      return Gate::allows('update', $this);
    }
}
