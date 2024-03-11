<?php

namespace Magan\FilamentBlog\Http\Controllers;

use Artesaos\SEOTools\Facades\SEOMeta;
use Illuminate\Support\Facades\Request;
use Magan\FilamentBlog\Models\Post;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $posts = Post::query()->with(['categories', 'user'])
            ->published()
            ->paginate(10);

        return view('filament-blog::blogs.index', [
            'posts' => $posts,
        ]);
    }

    public function show(Post $post)
    {
        SEOMeta::setTitle($post->seoDetail->title);
        SEOMeta::setDescription($post->seoDetail->description);
        $post->load(['user', 'categories']);

        return view('filament-blog::blogs.show', [
            'post' => $post,
        ]);
    }
}
