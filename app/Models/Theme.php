<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Theme extends Model
{
    use HasFactory;
    protected $fillable = ['title_en', 'title_fr', 'forWorkshop', 'forPost'];

    protected $casts = ['forWorkshop' => 'boolean', 'forPost' => 'boolean'];

    public function workshops()
    {
      return $this->belongsToMany(Workshop::class);
    }

    public function posts()
    {
      return $this->belongsToMany(Post::class);
    }

    public function withStats()
    {
      return [
        'id' => $this->id,
        'title_en' => $this->title_en,
        'title_fr' => $this->title_fr,
        'forPost' => $this->forPost,
        'forWorkshop' => $this->forWorkshop,
        'stats' => [
          'workshops' => count($this->workshops),
          'posts' => count($this->posts)
        ]
      ];
    }
}
