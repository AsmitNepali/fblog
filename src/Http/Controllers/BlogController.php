<?php

namespace Magan\FilamentBlog\Http\Controllers;

use Illuminate\Support\Facades\Request;
use Magan\FilamentBlog\Models\Post;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $posts = Post::query()->with(['categories', 'tags'])->paginate(10);
        $recentPost = Post::query()->latest()->take(5)->get();

        return view('filament-blog::blogs.index', [
            'posts' => $posts,
            'recentPost' => $recentPost,
        ]);
    }
}
