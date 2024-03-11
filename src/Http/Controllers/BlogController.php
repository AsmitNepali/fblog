<?php

namespace Magan\FilamentBlog\Http\Controllers;

use Illuminate\Support\Facades\Request;
use Magan\FilamentBlog\Models\Post;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $posts = Post::query()->with(['categories', 'user'])->published()->paginate(10);

        return view('filament-blog::blogs.index', [
            'posts' => $posts,
        ]);
    }

    public function show(Post $post)
    {
        $post->load(['user']);

        return view('filament-blog::blogs.show', [
            'post' => $post,
        ]);
    }
}
