<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

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
        return response()->json([
            'posts' => $posts
        ]);
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
        return response()->json([
            'posts' => $posts
        ]);
    }

    /**
     * Store a newly created post in storage.
     */
    public function store(Request $request)
    {
        $attrs = $request->validate([
            'title' => 'required|min:5|max:50',
            'language'  => 'required|min:2|max:2',
            'description' => 'required|min:10|max:255',
        ]);

        $post = Post::create([
            'title' => $attrs['title'],
            'language' => $attrs['language'],
            'description' => $attrs['description'],
            'slug' => Str::slug($attrs['title']),
            'author_id' => auth()->id()
        ]);
        return response()->json([
            'post' => $post->format(),
            'message' => [
                'text' => 'Post created',
                'type' => 'success'
            ]
        ]);
    }

    /**
     * Display the full post
     */
    public function show(Post $post)
    {
        return response()->json([
            'post' => $post->format()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post)
    {
        $attrs = $request->validate([
            'title' => 'required|min:8|max:150',
            'description' => 'required|min:20|max:255',
            'language' => 'required|min:2|max:2',
            'post' => 'required|max:5000'
        ]);

        $post->update([
            'title' => $attrs['title'],
            'slug' => Str::slug($attrs['title']),
            'description' => $attrs['description'],
            'language' => $attrs['language'],
            'post' => $attrs['post'],
        ]);

        return response()->json([
            'post' => $post->format(),
            'message' => [
                'text' => 'Post updated',
                'type' => 'success'
            ]
        ]);
    }

    public function updateStatus(Request $request, Post $post)

    {
        $status = $request->validate(['status' => ['required', Rule::in(['draft', 'submitted', 'published'])]])['status'];

        if($status == 'published' && !auth()->user()->is['admin']){
            return response()->json(['message' => ['text' => 'This action is unauthorized', 'type' => 'error']]);
        }

        $post->update(['status' => $status]);

        return response()->json([
            'post' => $post->format(),
            'message' => [
                'text' => 'Status updated',
                'type' => 'success'
            ]
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        return response()->json([
            'post' => $post->delete(),
            'message' => [
                'text' => 'Post deleted',
                'type' => 'info'
            ]
        ]);
    }
}
