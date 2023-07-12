<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Theme extends Model
{
    use HasFactory;
    protected $fillable = ['title_en', 'title_fr'];

    public function workshops()
    {
      return $this->belongsToMany(Workshop::class);
    }
}
