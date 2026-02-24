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

    public function series()
    {
      return $this->belongsToMany(Serie::class)->withPivot('order')->withTimestamps();
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
        // $seriesDetails = $this->series()->get()->map->format();

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
            'series' => $this->series()->pluck('series.id'),
            'seriesDetails' => $this->series()->get()->map->format(),
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

    public function forBlog()
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
            'cover' => (json_decode($this->images))->cover,
            'themes' => $this->themes()->pluck('themes.id'),
            'series' => $this->series()->pluck('series.id'),
            'seriesDetails' => $this->series()->get()->map->format(),
            'themeTitles' => $themeTitles,
            'translationId' => $this->translation_id,
            'published_at' => $this->published_at,
            'published_at_formated' => $this->published_at ? $publishDate->format('d/m/Y') : 'Not published',
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

    public function similar($nb = 5)
    {
        // collect posts in order of priority
        $results = collect();
        $excluded = [$this->id];

        // 1. posts from the same series
        $serieIds = $this->series()->pluck('series.id')->toArray();
        if (!empty($serieIds)) {
            $seriePosts = Post::whereHas('series', function($q) use ($serieIds) {
                    $q->whereIn('series.id', $serieIds);
                })
                ->where('status', 'published')
                ->where('isTranslation', 0)
                ->whereNotIn('id', $excluded)
                ->orderBy('published_at', 'desc')
                ->take($nb)
                ->get();

            foreach ($seriePosts as $p) {
                $results->push($p);
                $excluded[] = $p->id;
            }
        }

        // 2. posts sharing themes (tags)
        if ($results->count() < $nb) {
            $themeIds = $this->themes()->pluck('themes.id')->toArray();
            if (!empty($themeIds)) {
                // fetch posts that share at least one theme, but order them by
                // the number of common themes (desc) so that posts with 3
                // tags in common come before those with 2, then 1.
                $themePosts = Post::whereHas('themes', function($q) use ($themeIds) {
                        $q->whereIn('themes.id', $themeIds);
                    })
                    ->where('status', 'published')
                    ->where('isTranslation', 0)
                    ->whereNotIn('id', $excluded)
                    ->withCount(['themes as common_themes_count' => function($q) use ($themeIds) {
                        $q->whereIn('themes.id', $themeIds);
                    }])
                    ->orderByDesc('common_themes_count')
                    ->orderBy('published_at', 'desc')
                    ->take($nb - $results->count())
                    ->get();

                foreach ($themePosts as $p) {
                    $results->push($p);
                    $excluded[] = $p->id;
                }
            }
        }

        // 3. fill with most recent published posts if still short
        if ($results->count() < $nb) {
            $recent = Post::where('status', 'published')
                ->where('isTranslation', 0)
                ->whereNotIn('id', $excluded)
                ->orderBy('published_at', 'desc')
                ->take($nb - $results->count())
                ->get();
            foreach ($recent as $p) {
                $results->push($p);
            }
        }

        // trim to requested limit and map to minimal structure
        return $results->take($nb)->map(function ($p) {
            $themeTitles = [];
            foreach ($p->themes as $theme) {
                if ($p->language == 'fr') {
                    $themeTitles[] = $theme->title_fr;
                } else {
                    $themeTitles[] = $theme->title_en;
                }
            }
            $publishDate = Carbon::create($p->published_at);

            return [
                'id' => $p->id,
                'title' => $p->title,
                'slug' => $p->slug,
                'description' => $p->description,
                'status' => $p->status,
                'cover' => (json_decode($p->images))->cover,
                'themeTitles' => $themeTitles,
                'author' => [
                    'id' => optional($p->author)->id,
                    'name' => optional($p->author)->formal_name
                ],
                'language' => $p->language,
                'series' => $p->series()->pluck('series.id'),
                'seriesDetails' => $p->series()->get()->map->format(),
                'published_at' => $p->published_at,
                'published_at_formated' => $p->published_at ? $publishDate->format('d/m/Y') : null,
                'stats' => $p->stats ?? (object)['reads' => 0]
            ];
        });
    }
}
