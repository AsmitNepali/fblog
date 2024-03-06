<?php

namespace Magan\FilamentBlog\Resources\PostResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Magan\FilamentBlog\Resources\PostResource;
use Magan\FilamentBlog\Resources\SeoDetailResource;

class CreatePost extends CreateRecord
{
    protected static string $resource = PostResource::class;

    protected function getRedirectUrl(): string
    {
        return SeoDetailResource::getUrl('create', ['post_id' => $this->record->id]);
    }
}
