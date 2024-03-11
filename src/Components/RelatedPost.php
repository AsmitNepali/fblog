<?php

namespace Magan\FilamentBlog\Components;

use Illuminate\View\Component;

class RelatedPost extends Component
{
    /**
     * {@inheritDoc}
     */
    public function render()
    {
        $post = (request('post'));
        $posts = $post::whereHas('categories', function ($query) use ($post) {
            $query->whereIn('categories.id', $post->categories->pluck('id'));
        })->with('user')->get();

        return view('filament-blog::components.recent-post', [
            'posts' => $posts,
        ]);
    }
}
