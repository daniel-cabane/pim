<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Post extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'slug', 'language', 'status', 'post', 'stats', 'images', 'author_id', 'published_at', 'translation_id', 'isTranslation'];

    protected $casts = ['stats' => 'object'];

    public function getRouteKeyName(){
        return 'slug';
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function themes()
    {
      return $this->belongsToMany(Theme::class);
    }

    public function translation()
    {
        return $this->belongsTo(Post::class, 'translation_id');
    }

    public function likes()
    {
      return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function format()
    {
        $author = $this->author;
        $publishDate = Carbon::create($this->published_at);
        $updateDate = Carbon::create($this->updated_at);
        $themeTitles = [];
        foreach($this->themes as $theme){
            if($this->language == 'fr'){
                $themeTitles[] = $theme->title_fr;
            } else {
                $themeTitles[] = $theme->title_en;
            }
        }
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'status' => $this->status,
            'language' => $this->language,
            'post' => $this->post,
            'stats' => $this->stats,
            'cover' => (json_decode($this->images))->cover,
            'themes' => $this->themes()->pluck('themes.id'),
            'themeTitles' => $themeTitles,
            'translationId' => $this->translation_id,
            'published_at' => $this->published_at,
            'published_at_formated' => $this->published_at ? $publishDate->format('d/m/Y') : 'Not published',
            'updated_at' => $this->updated_at,
            'updated_at_formated' => $updateDate->format('d/m/Y'),
            'edited' => $publishDate < $updateDate,
            'editable' => auth()->check() && auth()->user()->can('update', $this),
            'author' => [
                'id' => $author->id,
                'name' => $author->formal_name
            ]
        ];
    }

    public function inscreaseReadCounter()
    {
        $stats = $this->stats;
        if($stats == null) {
            $this->update(['stats' => ['reads' => 1]]);
        } else if(isset($stats->reads)){
            $stats->reads = $stats->reads + 1;
            $this->update(['stats' => $stats]);
        } else {
            $stats->reads = 1;
            $this->update(['stats' => $stats]);
        }
    }
}
