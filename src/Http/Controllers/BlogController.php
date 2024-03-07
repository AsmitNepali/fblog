<?php

namespace Magan\FilamentBlog\Http\Controllers;

use Illuminate\Support\Facades\Request;
use Magan\FilamentBlog\Enums\PostStatus;
use Magan\FilamentBlog\Models\Post;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $posts = Post::query()->with(['categories', 'tags'])->where('status', PostStatus::PUBLISHED)->paginate(10);
        $recentPost = Post::query()->latest()->take(5)->get();

        return view('filament-blog::blogs.index', [
            'posts' => $posts,
            'recentPost' => $recentPost,
        ]);
    }

    public function show(Post $post)
    {
        $post->load(['categories', 'tags', 'user']);

        return view('filament-blog::blogs.show', [
            'post' => $post,
        ]);
    }
}
