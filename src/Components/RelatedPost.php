<?php

namespace Magan\FilamentBlog\Components;

use Illuminate\View\Component;
use Magan\FilamentBlog\Models\Post;

class RelatedPost extends Component
{
    /**
     * {@inheritDoc}
     */
    public function render()
    {
        $post = (request('post'));
        $posts = Post::query()->where('slug', '!=', $post)->inRandomOrder()->take(3)->get();

        return view('filament-blog::components.recent-post', [
            'posts' => $posts,
        ]);
    }
}
