<?php

namespace App\Http\Controllers;
use App\Models\Post;

use Illuminate\Http\Request;

class SpaController extends Controller
{
    public function index(Request $request)
    {
        $path = $request->path();
        $seo = [
            'title' => 'PIM',
            'description' => 'Pôle Informatique et Mathématique du Lycée Français International de Hong Kong',
            'image' => asset('images/pim logo.png')
        ];

        // Check if the URL is for a specific resource (e.g., /posts/my-first-post)
        if (str_starts_with($path, 'posts/')) {
            $slug = explode('/', $path)[1] ?? null;
            $post = Post::where('slug', $slug)->first();

            if ($post) {
                $seo['title'] = "$post->title - PIM Blog";
                $seo['description'] = $post->description;

                // only decode JSON if images isn't already an object
                $images = $post->images;
                if (is_string($images)) {
                    $images = json_decode($images);
                }

                // convert any storage path into a public URL
                if (!empty($images->cover->url)) {
                    // use Storage facade to respect disks and url configuration
                    $seo['image'] = asset($images->cover->url);
                } else {
                    $seo['image'] = null;
                }
            }
        }

        return view('welcome', compact('seo'));
    }
}
