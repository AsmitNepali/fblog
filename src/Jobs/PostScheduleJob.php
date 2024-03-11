<?php

namespace Magan\FilamentBlog\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Magan\FilamentBlog\Enums\PostStatus;
use Magan\FilamentBlog\Models\Post;

class PostScheduleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private Post $post)
    {
        //
    }

    public function handle(): void
    {

        $this->post->update([
            'status' => PostStatus::PUBLISHED,
            'published_at' => now(),
            'scheduled_for' => null,
        ]);
    }
}
