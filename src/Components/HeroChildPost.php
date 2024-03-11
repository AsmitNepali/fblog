<?php

namespace Magan\FilamentBlog\Components;

use Illuminate\View\Component;
use Magan\FilamentBlog\Models\Post;

class HeroChildPost extends Component
{
    public function render()
    {
        $posts = Post::query()
            ->with('categories', 'user')
            ->published()
            ->offset(1)
            ->latest()
            ->take(2)
            ->get();

        return view('filament-blog::components.hero-child-post', [
            'posts' => $posts,
        ]);
    }
}
