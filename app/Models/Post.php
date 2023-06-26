<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'slug', 'post','author_id', 'published_at'];

    public function getRouteKeyName(){
        return 'slug';
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function shortFormat()
    {
        $author = $this->author;
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'published_at' => $this->published_at,
            'updated_at' => $this->updated_at,
            'editable' => auth()->user()->can('update', $this),
            'author' => [
                'id' => $author->id,
                'name' => $author->name
            ]
        ];
    }

    public function longFormat()
    {
        $author = $this->author;
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'post' => $this->post,
            'published_at' => $this->published_at,
            'updated_at' => $this->updated_at,
            'editable' => auth()->user()->can('update', $this),
            'author' => [
                'id' => $author->id,
                'name' => $author->name
            ]
        ];
    }
}
