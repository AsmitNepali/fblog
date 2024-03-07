<?php

namespace Magan\FilamentBlog\Components;

use Illuminate\View\Component;
use Magan\FilamentBlog\Models\Post;

class HeroPost extends Component
{
    public function render()
    {
        $post = Post::latest()->with('categories', 'user')->first();

        return view('filament-blog::components.hero-post', [
            'post' => $post,
        ]);
    }
}
