<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Post extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'slug', 'language', 'status', 'post', 'images', 'author_id', 'published_at', 'translation_id'];

    public function getRouteKeyName(){
        return 'slug';
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function translation()
    {
        return $this->belongsTo(Post::class, 'translation_id');
    }

    public function format()
    {
        $author = $this->author;
        $publishDate = Carbon::create($this->published_at);
        $updateDate = Carbon::create($this->updated_at);
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'status' => $this->status,
            'language' => $this->language,
            'post' => $this->post,
            'cover' => (json_decode($this->images))->cover,
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
}
