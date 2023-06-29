<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PostController extends Controller
{
    /**
     * Display all the recent posts
     */
    public function index()
    {
        $posts = [];
        foreach(Post::whereNotNull('published_at')->orderBy('published_at', 'desc')->get() as $post){
            $posts[] = $post->format();
        }
        return response()->json(['posts' => $posts]);
    }

    /**
     * Display all the post written by the user
     */
    public function myPosts()
    {
        $user = auth()->user();
        $posts = [];
        foreach($user->posts()->where('author_id', $user->id)->get() as $post){
            $posts[] = $post->format();
        }
        return response()->json(['posts' => $posts]);
    }

    /**
     * Store a newly created post in storage.
     */
    public function store(Request $request)
    {
        $attrs = $request->validate([
            'title' => 'required|min:8|max:50',
            'description' => 'required|min:20|max:255',
        ]);

        return response()->json(Post::create([
            'title' => $attrs['title'],
            'description' => $attrs['description'],
            'slug' => Str::slug($attrs['title']),
            'author_id' => auth()->id()
        ]));
    }

    /**
     * Display the full post
     */
    public function show(Post $post)
    {
        if($post->published_at != null || auth()->user()->can('view', $post)){
            return response()->json(['post' => $post->format()]);
        }
        
        return response()->json(['message' => 'You cannot view this post', 403]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post)
    {
        $attrs = $request->validate([
            'title' => 'required|min:8|max:50',
            'description' => 'required|min:20|max:255',
            'post' => 'required',
            'publish' => 'required|min:4|max:15'
        ]);

        $newData = [
            'title' => $attrs['title'],
            'description' => $attrs['description'],
            'post' => $attrs['post'],
        ];
        if ($attrs['publish'] == 'publish'){
            $newData['published_at'] = Carbon::now();
        } else if ($attrs['publish'] == 'publish'){
            $newData['published_at'] = null;
        }

        $post->update($newData);

        return response()->json($post->format());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        return response()->json($post->delete());
    }
}
