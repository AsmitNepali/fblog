<?php

namespace Magan\FilamentBlog\Components;

use Illuminate\View\Component;
use Magan\FilamentBlog\Models\Post;

class RecentPost extends Component
{
    /**
     * {@inheritDoc}
     */
    public function render()
    {
        $posts = Post::query()->whereNot('slug', request('post'))->latest()->take(5)->get();

        return view('filament-blog::components.recent-post', [
            'posts' => $posts,
        ]);
    }
}
