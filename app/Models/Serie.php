<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Serie extends Model
{
    use HasFactory;

    protected $fillable = ['author_id', 'title', 'options'];

    protected $casts = ['options' => 'object'];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function posts()
    {
            return $this->belongsToMany(Post::class)
                ->withPivot('order')
                ->withTimestamps()
                ->orderBy('post_serie.order');
    }

    public function format()
    {
        $author = $this->author;

        return [
            'id' => $this->id,
            'title' => $this->title,
            'options' => $this->options,
            'color' => $this->options->color,
            'editable' => auth()->check() && auth()->id() == $author->id,
            'posts' => $this->posts()->select('id', 'title', 'slug')->get(),
            'author' => [
                'id' => $author->id,
                'name' => $author->formal_name
            ]
        ];
    }
}
