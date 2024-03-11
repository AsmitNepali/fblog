<?php

namespace Magan\FilamentBlog\Resources\PostResource\Pages;

use Carbon\Carbon;
use Filament\Resources\Pages\CreateRecord;
use Magan\FilamentBlog\Jobs\PostScheduleJob;
use Magan\FilamentBlog\Resources\PostResource;
use Magan\FilamentBlog\Resources\SeoDetailResource;

class CreatePost extends CreateRecord
{
    protected static string $resource = PostResource::class;

    protected function afterCreate()
    {
        if ($this->record->isScheduled()) {
            $scheduledFor = Carbon::parse($this->record->scheduled_for);
            PostScheduleJob::dispatchSync($this->record)->delay($scheduledFor);
        }

    }

    protected function getRedirectUrl(): string
    {
        return SeoDetailResource::getUrl('create', ['post_id' => $this->record->id]);
    }
}
