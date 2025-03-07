<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    use HasFactory;

    protected $fillable= ['user_id', 'open_door_id', 'data', 'tag_nb'];

    public function user()
    {
      return $this->belongsTo(User::class);
    }

    public function openDoor()
    {
      return $this->belongsTo(openDoor::class);
    }
}
